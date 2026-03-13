# OJT AI Journal Report Generator - Launch Script
# Run this in PowerShell to start the development server

Write-Host "====================================" -ForegroundColor Cyan
Write-Host "  OJT AI Journal Report Generator" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Change to script directory
Set-Location $PSScriptRoot

# Check if PHP is installed
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: PHP is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install PHP or add it to your system PATH" -ForegroundColor Yellow
    pause
    exit
}

# Check PHP version
$phpVersion = php -v 2>&1 | Select-Object -First 1
Write-Host "PHP Version: $phpVersion" -ForegroundColor Green
Write-Host ""

# Check if .env exists
if (-not (Test-Path ".env")) {
    Write-Host "WARNING: .env file not found!" -ForegroundColor Yellow
    Write-Host "Please copy .env.example to .env and configure your API key" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Run: Copy-Item .env.example .env" -ForegroundColor Cyan
    Write-Host ""
    pause
}

# Check if db directory exists
if (-not (Test-Path "db")) {
    Write-Host "Creating db directory..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Path "db" | Out-Null
}

# Check if uploads directory exists
if (-not (Test-Path "uploads")) {
    Write-Host "Creating uploads directory..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Path "uploads" | Out-Null
}

Write-Host "Starting development server..." -ForegroundColor Green
Write-Host ""
Write-Host "Open your browser to: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Start the PHP development server
php -S localhost:8000
