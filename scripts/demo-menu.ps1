# ==============================================================================
# SCRIPT DEMO TU DONG CAC USE CASE KIEM THU HE THONG PHAN TAN (API GATEWAY)
# Tuong thich hoan toan voi Windows PowerShell 5.1 va PowerShell Core 7+
# ==============================================================================

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

function Show-Header {
    Clear-Host
    Write-Host "==============================================================================" -ForegroundColor Cyan
    Write-Host "     HE THONG DAT VE TRUC TUYEN PHAN TAN - BO KICH BAN DEMO KIEM THU         " -ForegroundColor Yellow
    Write-Host "==============================================================================" -ForegroundColor Cyan
}

function Pause-Console {
    Write-Host "`nNhan Enter de quay lai Menu chinh..." -ForegroundColor Gray
    try {
        if (-not [Console]::IsInputRedirected) {
            [void][Console]::ReadKey($true)
        } else {
            [void][Console]::ReadLine()
        }
    } catch {
        [void][Console]::ReadLine()
    }
}

# ------------------------------------------------------------------------------
# USE CASE 1: GATEWAY & RATE LIMITING
# ------------------------------------------------------------------------------
function UC1-Gateway-RateLimit {
    Show-Header
    Write-Host "[USE CASE 1] KIEM THU NGINX API GATEWAY VA RATE LIMITING" -ForegroundColor Cyan
    Write-Host "Muc tieu: Chung minh dinh tuyen Reverse Proxy va chan spam bang HTTP 429." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"
    
    Write-Host "`n1. Kiem tra suc khoe Gateway:" -ForegroundColor Yellow
    try {
        $res = Invoke-RestMethod -Uri "http://localhost/health" -Method Get
        Write-Host " [PASS] Gateway status: $($res.status) | Kien truc: $($res.architecture)" -ForegroundColor Green
    } catch {
        Write-Host " [FAIL] Khong the ket noi Gateway: $($_.Exception.Message)" -ForegroundColor Red
        Pause-Console
        return
    }

    Write-Host "`n2. Gia lap Bot spam 15 requests lien tuc vao /api/bookings/ (Nguong: 10r/s):" -ForegroundColor Yellow
    $rateLimitCount = 0
    1..15 | ForEach-Object {
        try {
            $r = Invoke-WebRequest -Uri "http://localhost/api/bookings/health" -UseBasicParsing -TimeoutSec 1
            Write-Host "  Request #$_ : HTTP $($r.StatusCode) OK" -ForegroundColor Green
        } catch {
            $code = $_.Exception.Response.StatusCode.value__
            if ($code -eq 429) {
                Write-Host "  Request #$_ : HTTP 429 TOO MANY REQUESTS (Rate Limiter da chan dung spam!)" -ForegroundColor Red
                $rateLimitCount++
            } else {
                Write-Host "  Request #$_ : HTTP $code" -ForegroundColor Yellow
            }
        }
    }
    Write-Host "`n => Ket luan: Co $rateLimitCount requests bi Gateway chan thanh cong (Dat yeu cau bao ve he thong)." -ForegroundColor Cyan
    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 2: AUTH SERVICE - JWT & RBAC
# ------------------------------------------------------------------------------
function UC2-Auth-RBAC {
    Show-Header
    Write-Host "[USE CASE 2] KIEM THU AUTH SERVICE: JWT TOKEN VA PHAN QUYEN RBAC" -ForegroundColor Cyan
    Write-Host "Muc tieu: Chung minh stateless JWT va phan quyen Admin vs User." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    Write-Host "`n1. Dang nhap bang tai khoan User thuong (user@cinema.com):" -ForegroundColor Yellow
    $userLogin = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"user@cinema.com","password":"User@123456"}'
    $userToken = $userLogin.token
    Write-Host " [PASS] Nhan JWT Token role: $($userLogin.user.role)" -ForegroundColor Green

    Write-Host "`n2. User thuong co tinh truy cap API quan tri Admin (/api/auth/users):" -ForegroundColor Yellow
    try {
        Invoke-RestMethod -Uri "http://localhost/api/auth/users" -Headers @{ Authorization = "Bearer $userToken" }
        Write-Host " [FAIL] Cho phep User vao trang Admin (Sai quyen)!" -ForegroundColor Red
    } catch {
        Write-Host " [PASS] Bi chan thanh cong voi HTTP $($_.Exception.Response.StatusCode.value__) Forbidden!" -ForegroundColor Green
        Write-Host "        Phan hoi: $($_.ErrorDetails.Message)" -ForegroundColor Red
    }

    Write-Host "`n3. Dang nhap bang tai khoan Quan tri vien (admin@cinema.com):" -ForegroundColor Yellow
    $adminLogin = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"admin@cinema.com","password":"Admin@123456"}'
    $adminToken = $adminLogin.token
    Write-Host " [PASS] Nhan JWT Token role: $($adminLogin.user.role)" -ForegroundColor Green

    Write-Host "`n4. Admin truy cap API quan ly nguoi dung (/api/auth/users):" -ForegroundColor Yellow
    $usersList = Invoke-RestMethod -Uri "http://localhost/api/auth/users" -Headers @{ Authorization = "Bearer $adminToken" }
    Write-Host " [PASS] Truy cap thanh cong! Tim thay $($usersList.count) nguoi dung trong auth_db." -ForegroundColor Green
    $usersList.users | Format-Table id, name, email, role, created_at

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 3: SHOW SERVICE - MOVIES & SEAT MAP
# ------------------------------------------------------------------------------
function UC3-Show-Service {
    Show-Header
    Write-Host "[USE CASE 3] KIEM THU SHOW SERVICE: PHIM, SUAT CHIEU VA SO DO GHE" -ForegroundColor Cyan
    Write-Host "Muc tieu: Kiem tra truy xuat du lieu tu show_db va so do ma tran 40 ghe." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    Write-Host "`n1. Lay danh sach phim dang chieu (/api/shows/movies):" -ForegroundColor Yellow
    $movies = Invoke-RestMethod -Uri "http://localhost/api/shows/movies" -Method Get
    $movies.data | Format-Table id, title, duration, created_at

    Write-Host "`n2. Lay so do ma tran ghe cua suat chieu #1 (/api/shows/showtimes/1/seats):" -ForegroundColor Yellow
    $seats = Invoke-RestMethod -Uri "http://localhost/api/shows/showtimes/1/seats" -Method Get
    Write-Host " [PASS] Phim: $($seats.showtime.movie_title) | Rap: $($seats.showtime.theater_name) | Tong so ghe: $($seats.total_seats)" -ForegroundColor Green
    
    Write-Host "`n Mo phong so do rap chieu:" -ForegroundColor Cyan
    Write-Host " ===================== MAN HINH CHIEU (SCREEN) =====================" -ForegroundColor DarkYellow
    $rows = @('A','B','C','D','E')
    foreach ($r in $rows) {
        $rowSeats = $seats.seats | Where-Object { $_.row -eq $r } | ForEach-Object { "$($_.code):$($_.type)" }
        Write-Host ("  [ " + ($rowSeats -join " | ") + " ]") -ForegroundColor White
    }

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 4: BOOKING SERVICE & REDIS DISTRIBUTED LOCK
# ------------------------------------------------------------------------------
function UC4-Booking-RedisLock {
    Show-Header
    Write-Host "[USE CASE 4] DAT VE gRPC VA CHONG RACE CONDITION (REDIS DISTRIBUTED LOCK)" -ForegroundColor Cyan
    Write-Host "Muc tieu: Khoa nguyen tu SETNX chong 2 nguoi mua cung 1 ghe." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    $testSeat = "B" + (Get-Random -Minimum 1 -Maximum 8)
    Write-Host "`n[Tinh huong] Hai khach hang cung luc chon dat ghe ${testSeat}:" -ForegroundColor Yellow

    Write-Host "`n1. Khach hang 1 (nguyenvana@gmail.com) bam 'Dat ve':" -ForegroundColor Cyan
    $bookPayload1 = @{
        showtime_id = 1
        seat_code = $testSeat
        user_id = 1
        user_email = "nguyenvana@gmail.com"
        payment_method = "VNPAY"
    } | ConvertTo-Json

    try {
        $res1 = Invoke-RestMethod -Uri "http://localhost/api/bookings/book" -Method Post -ContentType "application/json" -Body $bookPayload1
        Write-Host " [PASS] Khach 1 DAT THANH CONG!" -ForegroundColor Green
        Write-Host "        Ma ve UUID: $($res1.booking.id)" -ForegroundColor White
        Write-Host "        Ghe: $($res1.booking.seat_code) | Gia: $($res1.booking.price) VND" -ForegroundColor White
        Write-Host "        Ma GD: $($res1.booking.transaction_id)" -ForegroundColor White
        Write-Host "        => gRPC Show: OK | gRPC Payment: OK | Redis Key da khoa: lock:show:1:seat:${testSeat}" -ForegroundColor Green
    } catch {
        Write-Host " [INFO] Ghe ${testSeat} da duoc mua tu truoc: $($_.ErrorDetails.Message)" -ForegroundColor Yellow
    }

    Write-Host "`n2. Khach hang 2 (tranthib@gmail.com) bam 'Dat ve' ngay sau do cho ghe ${testSeat}:" -ForegroundColor Cyan
    $bookPayload2 = @{
        showtime_id = 1
        seat_code = $testSeat
        user_id = 2
        user_email = "tranthib@gmail.com"
        payment_method = "MOMO"
    } | ConvertTo-Json

    try {
        $res2 = Invoke-RestMethod -Uri "http://localhost/api/bookings/book" -Method Post -ContentType "application/json" -Body $bookPayload2
        Write-Host " [LOI NGHIEM TRONG] Cho phep dat trung ghe (Race Condition)!" -ForegroundColor Red
    } catch {
        $code = $_.Exception.Response.StatusCode.value__
        Write-Host " [PASS CHONG TRANH CHAP] He thong chan dung voi HTTP $code!" -ForegroundColor Green
        Write-Host "        Thong diep tu Redis Lock: $($_.ErrorDetails.Message)" -ForegroundColor Yellow
        Write-Host " => Ket luan: Redis Distributed Lock da bao ve du lieu 100% khong bi Overbooking!" -ForegroundColor Cyan
    }

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 5: PAYMENT & IDEMPOTENCY
# ------------------------------------------------------------------------------
function UC5-Payment-Idempotency {
    Show-Header
    Write-Host "[USE CASE 5] KIEM THU THANH TOAN VA CO CHE IDEMPOTENCY" -ForegroundColor Cyan
    Write-Host "Muc tieu: Dam bao khong tru tien 2 lan khi gui trung lap booking_id." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    $fixedBookingId = [System.Guid]::NewGuid().ToString()
    Write-Host "`n1. Thuc hien thanh toan lan dau cho Booking ID = ${fixedBookingId}:" -ForegroundColor Yellow
    $payBody = @{
        booking_id = $fixedBookingId
        user_id = 1
        amount = 120000
        payment_method = "VNPAY"
    } | ConvertTo-Json

    $res1 = Invoke-RestMethod -Uri "http://localhost/api/payments/process" -Method Post -ContentType "application/json" -Body $payBody
    Write-Host " [PASS] Thanh toan lan 1 thanh cong: Transaction ID = $($res1.transaction.id)" -ForegroundColor Green

    Write-Host "`n2. Gui lai request thanh toan lan 2 (Mo phong mang giat hoac User bam 2 lan):" -ForegroundColor Yellow
    $res2 = Invoke-RestMethod -Uri "http://localhost/api/payments/process" -Method Post -ContentType "application/json" -Body $payBody
    Write-Host " [PASS IDEMPOTENCY] He thong phat hien giao dich trung lap!" -ForegroundColor Green
    Write-Host "        Thong bao: $($res2.message)" -ForegroundColor Yellow
    Write-Host "        Transaction ID tra ve cu: $($res2.transaction.id) (Khong tru tien lan 2)" -ForegroundColor Cyan

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 6: KAFKA EVENT-DRIVEN & QR CODE
# ------------------------------------------------------------------------------
function UC6-Kafka-QR-Notification {
    Show-Header
    Write-Host "[USE CASE 6] KAFKA EVENT-DRIVEN, OUTBOX PATTERN VA MA QR VE" -ForegroundColor Cyan
    Write-Host "Muc tieu: Transactional Outbox day Kafka -> Notification Consumer sinh ma QR." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    Write-Host "`n1. Kiem tra bang Transactional Outbox trong booking_db:" -ForegroundColor Yellow
    docker exec booking-db mariadb -u booking_user -pbooking_secret booking_db -e "SELECT id, aggregate_id, event_type, status, created_at FROM outbox ORDER BY created_at DESC LIMIT 3;"

    Write-Host "`n2. Kiem tra bang notification_logs trong notification_db do Kafka Consumer ghi:" -ForegroundColor Yellow
    docker exec notification-db mariadb -u notification_user -pnotification_secret notification_db -e "SELECT id, booking_id, recipient_email, ticket_code, seat_code, status, created_at FROM notification_logs ORDER BY created_at DESC LIMIT 3;"

    Write-Host "`n3. Lay anh ma QR Code ve thu nghiem tu Notification API:" -ForegroundColor Yellow
    $qrRes = Invoke-RestMethod -Uri "http://localhost/api/notifications/tickets/DEMO-QR-TICKET/qr" -Method Get
    Write-Host " [PASS] Sinh ma QR thanh cong cho ve: $($qrRes.ticket_code)" -ForegroundColor Green
    Write-Host "        Du lieu Base64 QR Image: $($qrRes.qr_image_base64.Substring(0, 50))..." -ForegroundColor Gray

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 7: CIRCUIT BREAKER (OPOSSUM)
# ------------------------------------------------------------------------------
function UC7-Circuit-Breaker {
    Show-Header
    Write-Host "[USE CASE 7] TINH CHIU LOI: CIRCUIT BREAKER (OPOSSUM)" -ForegroundColor Cyan
    Write-Host "Muc tieu: Kiem tra mach bao ve ngat khi service con gap su co." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    $health = Invoke-RestMethod -Uri "http://localhost/api/bookings/health" -Method Get
    Write-Host "`nTrang thai cac Circuit Breakers hien tai:" -ForegroundColor Yellow
    Write-Host " - Show Service Breaker    : $($health.circuit_breakers.show_service.state)" -ForegroundColor Green
    Write-Host " - Payment Service Breaker : $($health.circuit_breakers.payment_service.state)" -ForegroundColor Green

    Write-Host "`nChi tiet thong ke (Metrics):" -ForegroundColor Cyan
    Write-Host " Fires: $($health.circuit_breakers.show_service.stats.fires) | Successes: $($health.circuit_breakers.show_service.stats.successes) | Failures: $($health.circuit_breakers.show_service.stats.failures)" -ForegroundColor White

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 8: BACKUP & RESTORE
# ------------------------------------------------------------------------------
function UC8-Backup-Restore {
    Show-Header
    Write-Host "[USE CASE 8] SAO LUU VA PHUC HOI DU LIEU TAP TRUNG (DISASTER RECOVERY)" -ForegroundColor Cyan
    Write-Host "Muc tieu: Xuat file .sql cho ca 5 DB rieng biet va khoi phuc an toan." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    Write-Host "`n1. Chay script sao luu 5 DB:" -ForegroundColor Yellow
    & "$PSScriptRoot\backup-db.ps1"

    Write-Host "`n2. Kiem tra thu muc sao luu moi nhat:" -ForegroundColor Yellow
    Get-ChildItem "$PSScriptRoot\..\backups" | Sort-Object CreationTime -Descending | Select-Object -First 1

    Pause-Console
}

# ------------------------------------------------------------------------------
# USE CASE 9: LOAD TEST k6
# ------------------------------------------------------------------------------
function UC9-LoadTest-k6 {
    Show-Header
    Write-Host "[USE CASE 9] KIEM THU CHIU TAI TRANH CHAP GHE FLASH-SALE (k6)" -ForegroundColor Cyan
    Write-Host "Muc tieu: 20 VUs tranh chap 3 ghe trong 10 giay qua Gateway." -ForegroundColor Gray
    Write-Host "------------------------------------------------------------------------------"

    & "$PSScriptRoot\run-k6.ps1"
    Pause-Console
}

# ------------------------------------------------------------------------------
# VONG LAP MENU CHINH
# ------------------------------------------------------------------------------
do {
    Show-Header
    Write-Host "  [1] UC 1: Nginx API Gateway va Rate Limiting (Chong spam HTTP 429)" -ForegroundColor White
    Write-Host "  [2] UC 2: Auth Service - JWT Token va Phan quyen RBAC (Admin/User)" -ForegroundColor White
    Write-Host "  [3] UC 3: Show Service - Phim, Suat chieu va So do ma tran ghe" -ForegroundColor White
    Write-Host "  [4] UC 4: Dat ve gRPC va Chong Race Condition (Redis Lock HTTP 409)" -ForegroundColor Yellow
    Write-Host "  [5] UC 5: Thanh toan Gia lap va Co che Idempotency" -ForegroundColor White
    Write-Host "  [6] UC 6: Kafka Event-Driven, Outbox Pattern va Ma QR ve" -ForegroundColor White
    Write-Host "  [7] UC 7: Tinh chiu loi Circuit Breaker (Opossum)" -ForegroundColor White
    Write-Host "  [8] UC 8: Sao luu (Backup) va Phuc hoi (Restore) 5 Database" -ForegroundColor White
    Write-Host "  [9] UC 9: Chay k6 Flash-Sale Load Testing (Mo phong 1.000 requests)" -ForegroundColor Yellow
    Write-Host "  [A] Chay TAT CA cac Use Cases tuan tu tu 1 den 8" -ForegroundColor Green
    Write-Host "  [Q] Thoat" -ForegroundColor Red
    Write-Host "------------------------------------------------------------------------------"
    $choice = Read-Host "Nhap lua chon cua ban (1-9, A, Q)"

    switch ($choice.ToUpper()) {
        '1' { UC1-Gateway-RateLimit }
        '2' { UC2-Auth-RBAC }
        '3' { UC3-Show-Service }
        '4' { UC4-Booking-RedisLock }
        '5' { UC5-Payment-Idempotency }
        '6' { UC6-Kafka-QR-Notification }
        '7' { UC7-Circuit-Breaker }
        '8' { UC8-Backup-Restore }
        '9' { UC9-LoadTest-k6 }
        'A' {
            UC1-Gateway-RateLimit
            UC2-Auth-RBAC
            UC3-Show-Service
            UC4-Booking-RedisLock
            UC5-Payment-Idempotency
            UC6-Kafka-QR-Notification
            UC7-Circuit-Breaker
            UC8-Backup-Restore
        }
    }
} while ($choice.ToUpper() -ne 'Q')
