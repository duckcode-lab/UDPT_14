# Script Phục hồi Cơ sở dữ liệu tự động cho Hệ thống Phân tán (Database-per-Service)
param (
    [string]$BackupFolder
)

if (-not $BackupFolder) {
    # Lấy thư mục backup mới nhất
    $latestBackup = Get-ChildItem (Join-Path $PSScriptRoot "..\backups") -Directory | Sort-Object CreationTime -Descending | Select-Object -First 1
    if (-not $latestBackup) {
        Write-Error "Không tìm thấy thư mục sao lưu nào trong thư mục 'backups/'."
        exit 1
    }
    $BackupFolder = $latestBackup.FullName
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " PHỤC HỒI DỮ LIỆU TẬP TRUNG (DATABASE-PER-SERVICE) " -ForegroundColor Cyan
Write-Host " Nguồn phục hồi: $BackupFolder " -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

$databases = @(
    @{ Container = "auth-db"; DB = "auth_db" },
    @{ Container = "show-db"; DB = "show_db" },
    @{ Container = "booking-db"; DB = "booking_db" },
    @{ Container = "payment-db"; DB = "payment_db" },
    @{ Container = "notification-db"; DB = "notification_db" }
)

foreach ($item in $databases) {
    $sqlFile = Join-Path $BackupFolder "$($item.DB).sql"
    if (Test-Path $sqlFile) {
        Write-Host " [*] Đang phục hồi $($item.Container) từ $($item.DB).sql..." -NoNewline
        try {
            Get-Content $sqlFile -Raw | docker exec -i $($item.Container) mariadb -u root -proot_secret_password $($item.DB)
            Write-Host " [XONG]" -ForegroundColor Green
        } catch {
            Write-Host " [LỖI] $($_.Exception.Message)" -ForegroundColor Red
        }
    } else {
        Write-Host " [BỎ QUA] Không tìm thấy tệp: $sqlFile" -ForegroundColor Yellow
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " PHỤC HỒI HOÀN TẤT! " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
