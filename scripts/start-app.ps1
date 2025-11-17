# Script para iniciar la aplicacion completa
# Verifica que el backend este levantado antes de levantar el frontend

Write-Host "================================" -ForegroundColor Cyan
Write-Host "Iniciando aplicacion completa" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan

# Verificar si el backend ya esta corriendo
Write-Host "[1/3] Verificando si el backend esta disponible..." -ForegroundColor Yellow
$backendReady = $false
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -ErrorAction Stop -TimeoutSec 2
    if ($response.StatusCode -eq 200) {
        $backendReady = $true
        Write-Host "Backend ya esta disponible en http://127.0.0.1:8000" -ForegroundColor Green
    }
}
catch {
    Write-Host "Backend no esta disponible. Iniciando..." -ForegroundColor Yellow
}

# Si el backend no esta corriendo, iniciarlo
if (-not $backendReady) {
    Write-Host "[2/3] Iniciando backend en primer plano..." -ForegroundColor Yellow
    cd D:\ProgramasXamp\htdocs\appwebcargahoraria\backend
    php artisan serve --host=127.0.0.1 --port=8000
    
    # Esperar a que el backend este listo
    Write-Host "Esperando a que el backend este disponible..." -ForegroundColor Yellow
    Start-Sleep -Seconds 3
    $maxAttempts = 20
    $attempt = 0
    
    while ($attempt -lt $maxAttempts -and -not $backendReady) {
        $attempt++
        try {
            $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -ErrorAction Stop -TimeoutSec 2
            if ($response.StatusCode -eq 200) {
                $backendReady = $true
                Write-Host "Backend esta disponible en http://127.0.0.1:8000" -ForegroundColor Green
            }
        }
        catch {
            Write-Host "  Intento $attempt/$maxAttempts - Backend no disponible aun..." -ForegroundColor Gray
            Start-Sleep -Seconds 1
        }
    }
    
    if (-not $backendReady) {
        Write-Host "Error: El backend no se levanto correctamente" -ForegroundColor Red
        Write-Host "Verifica los logs del backend y intenta nuevamente" -ForegroundColor Red
        exit 1
    }
}

# Iniciar frontend en una nueva ventana
Write-Host "[3/3] Abriendo frontend en nueva ventana..." -ForegroundColor Yellow
$frontendScript = @"
cd D:\ProgramasXamp\htdocs\appwebcargahoraria\frontend
npm run dev
"@
Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -Command `"$frontendScript`"" -NoNewWindow:$false
