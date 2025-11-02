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
        <p>Cảm ơn bạn đã đặt vé tại <strong>Cinema Ticket</strong>! Dưới đây là thông tin vé của bạn:</p>

        <div class="movie-title">{{ $ticket->showtime->movie->title }}</div>

        <div class="ticket-info">
            <p>📍 <span class="highlight">Rạp:</span> {{ $ticket->showtime->theater->name }}</p>
            <p>🏠 <span class="highlight">Địa chỉ:</span> {{ $ticket->showtime->theater->address }}</p>
            <p>💺 <span class="highlight">Ghế đã chọn:</span> {{ $ticket->seats }}</p>
            <p>🕒 <span class="highlight">Thời gian chiếu:</span> {{ \Carbon\Carbon::parse($ticket->showtime->time)->format('H:i d/m/Y') }}</p>
            <p>🎫 <span class="highlight">Mã vé:</span> {{ $ticket->code }}</p>
            <p>💰 <span class="highlight">Tổng tiền:</span> {{ number_format($ticket->total_price) }} VND</p>
        </div>

        <p>hãy đến rạp và <strong>thanh toán</strong> tại rạp, xin cảm ơn ❤️</p>

        <div style="text-align:center; margin-top:20px;">
            <a href="{{ url('/') }}" class="button">🎞️ Đặt thêm vé khác</a>
        </div>
    </div>

    <div class="footer" style="text-align: left;">
        Cảm ơn bạn đã tin tưởng Cinema Ticket ❤️<br>
        Hẹn gặp bạn tại rạp!
    </div>
</div>

</body>
</html>
