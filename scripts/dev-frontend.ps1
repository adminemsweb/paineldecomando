$ErrorActionPreference = 'Stop'

$workspace = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$frontend = Join-Path $workspace 'frontend'
$listener = Get-NetTCPConnection -State Listen -LocalPort 5173 -ErrorAction SilentlyContinue

foreach ($connection in $listener) {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId=$($connection.OwningProcess)"
    $belongsToProject = $process.Name -eq 'node.exe' -and $process.CommandLine -like "*$frontend*"
    if (-not $belongsToProject) {
        throw "A porta 5173 está ocupada por outro programa (PID $($connection.OwningProcess))."
    }
    Write-Host "[DEV] Encerrando frontend anterior (PID $($connection.OwningProcess))..." -ForegroundColor Yellow
    Stop-Process -Id $connection.OwningProcess -Force
}

Start-Sleep -Milliseconds 500
Write-Host '[DEV] Frontend: http://127.0.0.1:5173' -ForegroundColor Cyan
Set-Location -LiteralPath $frontend
& npm.cmd run dev -- --host 127.0.0.1 --strictPort
exit $LASTEXITCODE
