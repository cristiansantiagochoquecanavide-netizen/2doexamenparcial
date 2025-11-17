# Script para levantar el servidor Laravel en segundo plano
# Puerto: 8000
# Host: 127.0.0.1

$backendPath = "D:\ProgramasXamp\htdocs\appwebcargahoraria\backend"
$phpPath = "D:\ProgramasXamp\php\php.exe"
$logFile = "$backendPath\server.log"

# Cambiar al directorio del backend
Set-Location $backendPath

# Iniciar el servidor en segundo plano
$process = Start-Process -FilePath $phpPath `
    -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" `
    -RedirectStandardOutput $logFile `
    -RedirectStandardError "$backendPath\server-error.log" `
    -WindowStyle Hidden `
    -PassThru

# Guardar el ID del proceso
$processId = $process.Id
"$processId" | Out-File -FilePath "$backendPath\server.pid" -Encoding UTF8

Write-Host "Servidor backend iniciado en segundo plano"
Write-Host "Puerto: 8000"
Write-Host "Host: 127.0.0.1"
Write-Host "URL: http://127.0.0.1:8000"
Write-Host "ID de proceso: $processId"
Write-Host "Logs: $logFile"
