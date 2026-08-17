$ErrorActionPreference = 'Stop'

$workspace = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$backend = Join-Path $workspace 'backend'
$listener = Get-NetTCPConnection -State Listen -LocalPort 8080 -ErrorAction SilentlyContinue

foreach ($connection in $listener) {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId=$($connection.OwningProcess)"
    $belongsToProject = $process.Name -eq 'php.exe' -and $process.CommandLine -match '\-S\s+127\.0\.0\.1:8080'
    if (-not $belongsToProject) {
        throw "A porta 8080 está ocupada por outro programa (PID $($connection.OwningProcess))."
    }
    Write-Host "[DEV] Encerrando backend anterior (PID $($connection.OwningProcess))..." -ForegroundColor Yellow
    Stop-Process -Id $connection.OwningProcess -Force
}

Start-Sleep -Milliseconds 500
Write-Host '[DEV] Backend/API: http://127.0.0.1:8080' -ForegroundColor Cyan
Set-Location -LiteralPath $backend
& php.exe -S 127.0.0.1:8080 -t public public/router.php
exit $LASTEXITCODE
