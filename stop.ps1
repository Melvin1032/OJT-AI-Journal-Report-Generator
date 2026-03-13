# Stop the OJT AI Journal Report Generator server
# Run this in PowerShell to stop the development server

Write-Host "Stopping OJT AI Journal Report Generator server..." -ForegroundColor Cyan

# Find and stop PHP processes
$phpProcesses = Get-Process php -ErrorAction SilentlyContinue

if ($phpProcesses) {
    foreach ($process in $phpProcesses) {
        Write-Host "Stopping PHP process (PID: $($process.Id))..." -ForegroundColor Yellow
        Stop-Process -Id $process.Id -Force
    }
    Write-Host "Server stopped successfully!" -ForegroundColor Green
} else {
    Write-Host "No PHP server running" -ForegroundColor Gray
}
