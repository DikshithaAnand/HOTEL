<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotelia | Cloud Bookings</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            margin: 0;
            padding: 35px;
            color: #1e293b;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
            overflow-x: auto;
        }

        .badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 13px;
        }

        .count {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #f8fafc;
        }

        .error {
            padding: 15px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }

        .empty {
            padding: 20px 0;
            color: #64748b;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Hotelia Cloud Bookings</h1>

    <p class="subtitle">
        Booking records retrieved from Zoho Catalyst
    </p>

    <div class="card">

        @if (!empty($error))
            <p class="error">{{ $error }}</p>
        @else
            <span class="badge">Cloud connection successful</span>

            <h2 class="count">
                {{ count($bookings) }} booking record(s)
            </h2>

            @if (count($bookings) > 0)
                <table>
                    <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Reference</th>
                        <th>Hotel ID</th>
                        <th>Status</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>{{ $booking['BookingID'] ?? '—' }}</td>
                            <td>{{ $booking['BookingReference'] ?? '—' }}</td>
                            <td>{{ $booking['HotelID'] ?? '—' }}</td>
                            <td>{{ $booking['BookingStatus'] ?? '—' }}</td>
                            <td>{{ $booking['CheckInDate'] ?? '—' }}</td>
                            <td>{{ $booking['CheckOutDate'] ?? '—' }}</td>
                            <td>{{ $booking['Action'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty">No cloud bookings were found.</p>
            @endif
        @endif

    </div>
</div>
</body>
</html>