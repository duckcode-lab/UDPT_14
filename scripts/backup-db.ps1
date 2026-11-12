# Script Sao lưu Cơ sở dữ liệu tự động cho Hệ thống Phân tán (Database-per-Service)
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupDir = Join-Path $PSScriptRoot "..\backups\$timestamp"

if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " TIẾN HÀNH SAO LƯU DỮ LIỆU TẬP TRUNG (DATABASE-PER-SERVICE) " -ForegroundColor Cyan
Write-Host " Thư mục lưu trữ: $backupDir " -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

$databases = @(
    @{ Container = "auth-db"; DB = "auth_db" },
    @{ Container = "show-db"; DB = "show_db" },
    @{ Container = "booking-db"; DB = "booking_db" },
    @{ Container = "payment-db"; DB = "payment_db" },
    @{ Container = "notification-db"; DB = "notification_db" }
)

foreach ($item in $databases) {
    $outFile = Join-Path $backupDir "$($item.DB).sql"
    Write-Host " [*] Đang sao lưu $($item.Container) -> $($item.DB)..." -NoNewline
    try {
        docker exec $($item.Container) mariadb-dump -u root -proot_secret_password $($item.DB) | Out-File -FilePath $outFile -Encoding utf8
        $fileSize = (Get-Item $outFile).Length
        Write-Host " [XONG] ($fileSize bytes)" -ForegroundColor Green
    } catch {
        Write-Host " [LỖI] $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " SAO LƯU HOÀN TẤT VÀO: $backupDir" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
