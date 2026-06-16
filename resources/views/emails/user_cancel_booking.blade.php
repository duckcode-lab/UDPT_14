<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎟️ Xác nhận huỷ vé - Cinema Ticket</title>
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
            color: #fff;
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
            text-align: left;
            padding: 15px;
            background-color: #111;
            color: #fff;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #ff3d00;
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
        🎬 Huỷ Vé Thành Công
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $booking->user->name ?? 'khách hàng' }}</strong>,</p>
        <p>Chúng tôi xác nhận rằng vé của bạn đã được <strong style="color: red">huỷ thành công</strong> tại <strong>Cinema Ticket</strong>.</p>

        <div class="ticket-info">
            <p>🎬 <span class="highlight">phim:</span>{{ $booking->movie_title }}</p>
            <p>📍 <span class="highlight">Rạp:</span> {{ $booking->showtime->theater_name ?? ($booking->showtime->theater->name ?? 'Không rõ rạp') }}</p>
            <p>💺 <span class="highlight">Ghế:</span> {{ $booking->seats }}</p>
            <p>🕒 <span class="highlight">Suất chiếu:</span> {{ \Carbon\Carbon::parse($booking->show_time)->format('H:i d/m/Y') }}</p>
            <p>🎫 <span class="highlight">Mã vé:</span> {{ $booking->code }}</p>
        </div>



        <div style="text-align:center; margin-top:20px;">
            <a href="{{ url('/') }}" class="button">🎞️ Đặt vé khác ngay</a>
        </div>
    </div>

    <div class="footer">
        Cảm ơn bạn đã sử dụng dịch vụ của Cinema Ticket ❤️<br>
        Hẹn gặp lại bạn trong những buổi chiếu sắp tới!
    </div>
</div>

</body>
</html>
