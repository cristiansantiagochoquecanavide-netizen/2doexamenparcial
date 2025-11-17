# Script para detener el servidor Laravel
$backendPath = "D:\ProgramasXamp\htdocs\appwebcargahoraria\backend"
$pidFile = "$backendPath\server.pid"

if (Test-Path $pidFile) {
    $processId = Get-Content $pidFile -Raw
    $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
    
    if ($process) {
        Stop-Process -Id $processId -Force
        Write-Host "Servidor backend detenido (PID: $processId)"
        Remove-Item $pidFile -ErrorAction SilentlyContinue
    } else {
        Write-Host "El proceso con PID $processId no está en ejecución"
    }
} else {
    Write-Host "Archivo de PID no encontrado"
    # Intentar detener cualquier proceso artisan serve
    Get-Process | Where-Object {$_.ProcessName -eq "php"} | Stop-Process -Force -ErrorAction SilentlyContinue
    Write-Host "Se han terminado todos los procesos PHP"
}
