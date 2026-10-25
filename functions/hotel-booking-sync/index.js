"use strict";

const catalyst = require("zcatalyst-sdk-node");

const TABLE_NAME = "HotelBookings";
const MAX_BODY_SIZE = 100000;

function sendJson(res, status, data) {
    res.writeHead(status, { "Content-Type": "application/json" });
    res.end(JSON.stringify(data));
}

function readBody(req) {
    return new Promise((resolve, reject) => {
        let body = "";

        req.on("data", chunk => {
            body += chunk;

            if (body.length > MAX_BODY_SIZE) {
                reject(new Error("Request body too large"));
                req.destroy();
            }
        });

        req.on("end", () => {
            try {
                resolve(JSON.parse(body));
            } catch {
                reject(new Error("Invalid JSON"));
            }
        });

        req.on("error", reject);
    });
}

module.exports = async (req, res) => {
    if (req.method !== "POST") {
        return sendJson(res, 405, {
            success: false,
            message: "POST requests only"
        });
    }

    const expectedSecret = process.env.BOOKING_SYNC_SECRET;
    const suppliedSecret = req.headers["x-booking-sync-secret"];

    if (!expectedSecret || suppliedSecret !== expectedSecret) {
        return sendJson(res, 401, {
            success: false,
            message: "Unauthorized"
        });
    }

    try {
        const body = await readBody(req);
        const booking = body.booking;

        if (
            !booking ||
            !Number.isSafeInteger(Number(booking.id)) ||
            Number(booking.id) <= 0
        ) {
            return sendJson(res, 400, {
                success: false,
                message: "A valid booking.id is required"
            });
        }

        const app = catalyst.initialize(req);
        const table = app.datastore().table(TABLE_NAME);

        const row = {
            BookingID: Number(booking.id),
            BookingReference: String(booking.reference || "").slice(0, 100),
            HotelID: Number(booking.hotel_id || 0),
            BookingStatus: String(booking.status || "unknown").slice(0, 50),
            CheckInDate: String(booking.check_in_date || "").slice(0, 30),
            CheckOutDate: String(booking.check_out_date || "").slice(0, 30),
            Action: String(body.action || "updated").slice(0, 50),
            PayloadJSON: JSON.stringify(body).slice(0, 9000),
            SyncUpdatedAt: new Date().toISOString()
        };

        const saved = await table.insertRow(row);

        return sendJson(res, 200, {
            success: true,
            message: "Booking stored in Catalyst",
            booking_id: booking.id,
            row_id: saved.ROWID
        });
    } catch (error) {
        console.error("Booking sync failed:", error);

        return sendJson(res, 500, {
            success: false,
            message: "Booking synchronization failed"
        });
    }
};
