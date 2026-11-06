<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎟️ Xác nhận đặt vé - Cinema Ticket</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f7f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #ffcc00;
            color: #111;
            text-align: center;
            padding: 20px;
            font-size: 26px;
            font-weight: bold;
        }
        .content {
            padding: 25px;
            line-height: 1.6;
            color: #333;
        }
        .movie-title {
            font-size: 22px;
            font-weight: bold;
            color: #e50914;
            text-align: center;
            margin-bottom: 10px;
        }
        .ticket-info {
            background-color: #f0f4f8;
            border-radius: 10px;
            padding: 15px 20px;
            margin-top: 15px;
        }
        .ticket-info p {
            margin: 6px 0;
            font-size: 15px;
        }
        .highlight {
            font-weight: bold;
            color: #111;
        }
        .footer {
            padding: 15px;
            background-color: #111;
            color: #fff;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #e50914;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="email-container">
    <div class="header">
        🎬 Cinema Ticket
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $ticket->user->name ?? 'khách hàng' }}</strong>,</p>

        <p>Bạn đã xem phim <b>{{ $booking->showtime->movie->title }}</b></p>

        <p>Hãy để lại đánh giá để giúp người khác nhé!</p>

        <a href="{{ url('/movies/' . $booking->showtime->movie->id) }}">
            Đánh giá ngay
        </a>
    </div>
    <div class="footer" style="text-align: left;">
        Cảm ơn bạn đã tin tưởng Cinema Ticket ❤️<br>
        Hẹn gặp bạn tại rạp!
    </div>
</div>

</body>
</html>
