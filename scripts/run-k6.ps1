# Script chay k6 Load Testing thong qua Docker
Write-Host "=========================================================="
Write-Host " KHOI CHAY KIEM THU TAI FLASH SALE TRANH CHAP GHE (k6) "
Write-Host " Mo phong Virtual Users dong thoi tranh chap ghe qua Gateway"
Write-Host "=========================================================="

docker run --rm --network udpt_14_cinema-network -v ${PWD}/tests/k6-flash-sale.js:/scripts/k6-flash-sale.js:ro -e API_URL=http://cinema-api-gateway grafana/k6:latest run /scripts/k6-flash-sale.js
