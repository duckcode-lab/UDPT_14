<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎟️ Thông báo huỷ vé - Cinema Ticket</title>
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
            text-align: left;
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
        <p>Xin chào <strong>{{ $user->name ?? 'khách hàng' }}</strong>,</p>
        <p>Rất tiếc, vé xem phim của bạn đã bị <strong style="color: red">huỷ bởi Quản trị viên hệ thống</strong> do thay đổi lịch chiếu hoặc lý do kỹ thuật.</p>

        <div class="ticket-info">
            <p>🎬 <span class="highlight">phim:</span>{{ $movieTitle }}</p>
            <p>🕒 <span class="highlight">Suất chiếu:</span> {{ $showTime }}</p>
            <p>💺 <span class="highlight">Ghế:</span> {{ $seats }}</p>
        </div>

        <p>Chúng tôi thành thật xin lỗi vì sự bất tiện này và sẽ hỗ trợ bạn trong thời gian sớm nhất.</p>

        <p>Nếu bạn đã thanh toán, vui lòng liên hệ để được hoàn tiền nhanh nhất qua địa chỉ:</p>
        <p><strong>📧 support@cinematicket.vn</strong> hoặc số điện thoại <strong>1900-1234</strong>.</p>

        <div style="text-align:center; margin-top:20px;">
            <a href="{{ url('/') }}" class="button">🎞️ Đặt vé khác</a>
        </div>
    </div>
    <div class="footer">
        Cảm ơn bạn đã tin tưởng Cinema Ticket ❤️<br>
        Chúng tôi mong được phục vụ bạn trong lần tới!
    </div>
</div>

</body>
</html>
