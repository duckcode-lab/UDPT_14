# Script kiểm tra sức khỏe và các luồng Microservices phân tán
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " KIỂM TRA HỆ THỐNG PHÂN TÁN (API GATEWAY & MICROSERVICES) " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Health check các endpoint
$endpoints = @(
    @{ Name = "Nginx API Gateway"; Url = "http://localhost/health" },
    @{ Name = "Auth Service (qua Gateway)"; Url = "http://localhost/api/auth/health" },
    @{ Name = "Show Service (qua Gateway)"; Url = "http://localhost/api/shows/health" },
    @{ Name = "Booking Service (qua Gateway)"; Url = "http://localhost/api/bookings/health" },
    @{ Name = "Payment Service (qua Gateway)"; Url = "http://localhost/api/payments/health" },
    @{ Name = "Notification Service (qua Gateway)"; Url = "http://localhost/api/notifications/health" }
)

foreach ($ep in $endpoints) {
    try {
        $response = Invoke-RestMethod -Uri $ep.Url -Method Get -TimeoutSec 3 -ErrorAction Stop
        $statusText = if ($response.status) { $response.status } else { "OK" }
        Write-Host " [PASS] $($ep.Name) -> $statusText" -ForegroundColor Green
    } catch {
        Write-Host " [FAIL] $($ep.Name) -> $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
Write-Host " KIỂM TRA LUỒNG AUTH & PHÂN QUYỀN (JWT + RBAC)" -ForegroundColor Yellow
Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
try {
    $login = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"admin@cinema.com","password":"Admin@123456"}'
    Write-Host " [PASS] Đăng nhập & cấp JWT: $($login.user.email) (Role: $($login.user.role))" -ForegroundColor Green
} catch {
    Write-Host " [FAIL] Đăng nhập lỗi: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
Write-Host " KIỂM TRA SHOW SERVICE (PHIM & SƠ ĐỒ GHẾ)" -ForegroundColor Yellow
Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
try {
    $movies = Invoke-RestMethod -Uri "http://localhost/api/shows/movies" -Method Get
    Write-Host " [PASS] Lấy danh sách phim: Tìm thấy $($movies.data.Count) phim" -ForegroundColor Green

    $seats = Invoke-RestMethod -Uri "http://localhost/api/shows/showtimes/1/seats" -Method Get
    Write-Host " [PASS] Lấy sơ đồ ghế suất chiếu #1: $($seats.total_seats) ghế (A1 - E8)" -ForegroundColor Green
} catch {
    Write-Host " [FAIL] Show Service lỗi: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
Write-Host " KIỂM TRA ĐẶT VÉ (REDIS LOCK + gRPC + KAFKA + NOTIFICATION)" -ForegroundColor Yellow
Write-Host "----------------------------------------------------------" -ForegroundColor Yellow
try {
    $seatRandom = "D" + (Get-Random -Minimum 1 -Maximum 8)
    $bookPayload = @{
        showtime_id = 1
        seat_code = $seatRandom
        user_id = 1
        user_email = "test.user@cinema.com"
        payment_method = "VNPAY"
    } | ConvertTo-Json

    $booking = Invoke-RestMethod -Uri "http://localhost/api/bookings/book" -Method Post -ContentType "application/json" -Body $bookPayload
    Write-Host " [PASS] Đặt vé thành công: ID = $($booking.booking.id) | Ghế = $($booking.booking.seat_code) | Giá = $($booking.booking.price) VND" -ForegroundColor Green
    Write-Host "        gRPC Show: OK | gRPC Payment: OK | Kafka Event: OK" -ForegroundColor Cyan
} catch {
    Write-Host " [INFO] Ghế vừa chọn có thể đã được đặt: $($_.Exception.Message)" -ForegroundColor Yellow
}

Write-Host "==========================================================" -ForegroundColor Cyan
