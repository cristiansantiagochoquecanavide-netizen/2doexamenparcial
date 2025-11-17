#!/usr/bin/env pwsh
# Script para iniciar el desarrollo de Carga Horaria

Write-Host "🚀 Iniciando Sistema de Carga Horaria..." -ForegroundColor Green
Write-Host ""

# Detener procesos anteriores
Write-Host "🛑 Deteniendo procesos anteriores..." -ForegroundColor Yellow
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep 1

# Funciones para iniciar servidores
function Start-Backend {
    Write-Host "📱 Iniciando Backend (Laravel)..." -ForegroundColor Cyan
    cd backend
    php artisan serve --host 127.0.0.1 --port 8000
}

function Start-Frontend {
    Write-Host "⚛️  Iniciando Frontend (React)..." -ForegroundColor Cyan
    cd frontend
    npm install --legacy-peer-deps 2>$null
    npm run dev -- --host 127.0.0.1 --port 5173
}

# Iniciar ambos en paralelo
Write-Host ""
Write-Host "✅ Servidores configurados:" -ForegroundColor Green
Write-Host "  • Backend:  http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "  • Frontend: http://127.0.0.1:5173" -ForegroundColor Green
Write-Host ""

# Abrir PowerShell para backend
$backendJob = Start-Job -ScriptBlock {
    Set-Location D:\ProgramasXamp\htdocs\appwebcargahoraria\backend
    php artisan serve --host 127.0.0.1 --port 8000
} -Name "backend"

Start-Sleep 2

# Abrir PowerShell para frontend
$frontendJob = Start-Job -ScriptBlock {
    Set-Location D:\ProgramasXamp\htdocs\appwebcargahoraria\frontend
    npm run dev -- --host 127.0.0.1 --port 5173
} -Name "frontend"

Write-Host "✨ Sistema iniciado correctamente" -ForegroundColor Green
Write-Host "   Presiona Ctrl+C para detener los servidores" -ForegroundColor Yellow
Write-Host ""

# Mantener abierto hasta que se presione Ctrl+C
Wait-Job $backendJob, $frontendJob
