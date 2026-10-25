"use strict";

const catalyst = require("zcatalyst-sdk-node");

function sendJson(res, status, data) {
    res.writeHead(status, {
        "Content-Type": "application/json"
    });
    res.end(JSON.stringify(data));
}

module.exports = async (req, res) => {
    if (req.method !== "GET") {
        return sendJson(res, 405, {
            success: false,
            message: "GET requests only"
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
        const app = catalyst.initialize(req);
        const table = app.datastore().table("HotelBookings");
        const result = await table.getPagedRows({ maxRows: 100 });

        return sendJson(res, 200, {
            success: true,
            count: result.data.length,
            bookings: result.data
        });
    } catch (error) {
        console.error("Cloud booking read failed:", error);

        return sendJson(res, 500, {
            success: false,
            message: "Unable to retrieve cloud bookings"
        });
    }
};