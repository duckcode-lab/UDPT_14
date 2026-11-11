# Script kiểm tra sức khỏe cụm Microservices & API Gateway
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " KIỂM TRA SỨC KHỎE CỤM HỆ THỐNG PHÂN TÁN (API GATEWAY) " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$endpoints = @(
    @{ Name = "Nginx API Gateway"; Url = "http://localhost/health" },
    @{ Name = "Auth Service (qua Gateway)"; Url = "http://localhost/api/auth/health" },
    @{ Name = "Show Service (qua Gateway)"; Url = "http://localhost/api/shows/health" },
    @{ Name = "Show Service Movies API"; Url = "http://localhost/api/shows/movies" },
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

Write-Host "==========================================================" -ForegroundColor Cyan
