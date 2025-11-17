<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Carga Horaria - Backend API</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            padding: 50px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .version {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .status {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .info h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .endpoints {
            list-style: none;
        }
        
        .endpoints li {
            padding: 10px 0;
            color: #555;
            border-bottom: 1px solid #ddd;
        }
        
        .endpoints li:last-child {
            border-bottom: none;
        }
        
        .endpoint-label {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .endpoint-url {
            color: #666;
            font-family: 'Courier New', monospace;
        }
        
        .section {
            margin: 30px 0;
        }
        
        .section h4 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .section ul {
            margin-left: 20px;
            color: #555;
        }
        
        .section li {
            margin: 8px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #999;
            font-size: 12px;
        }
        
        .cases-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .case-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .case-card h5 {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .case-card p {
            font-size: 12px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📚</div>
            <h1>Sistema de Carga Horaria</h1>
            <p class="version">Backend API v1.0.0</p>
            <span class="status">✓ Servidor En Funcionamiento</span>
        </div>
        
        <div class="info">
            <h3>📍 Información del Servidor</h3>
            <ul class="endpoints">
                <li><strong>Host:</strong> <span class="endpoint-url">127.0.0.1:3000</span></li>
                <li><strong>URL Base API:</strong> <span class="endpoint-url">http://127.0.0.1:3000/api</span></li>
                <li><strong>Base de Datos:</strong> <span class="endpoint-url">PostgreSQL - carga_horaria</span></li>
                <li><strong>Autenticación:</strong> <span class="endpoint-url">Laravel Sanctum</span></li>
            </ul>
        </div>
        
        <div class="info">
            <h3>🔧 Casos de Uso Implementados</h3>
            
            <div class="cases-grid">
                <div class="case-card">
                    <h5>P1: Autenticación y Control de Acceso</h5>
                    <p>Login, logout, gestión de roles y permisos</p>
                </div>
                <div class="case-card">
                    <h5>P2: Gestión de Catálogos Académicos</h5>
                    <p>Docentes, materias, grupos, aulas e infraestructura</p>
                </div>
                <div class="case-card">
                    <h5>P3: Planificación Académica</h5>
                    <p>Carga horaria, asignaciones y conflictos de horario</p>
                </div>
                <div class="case-card">
                    <h5>P4: Asistencia Docente</h5>
                    <p>Registro de asistencia y gestión de inasistencias</p>
                </div>
                <div class="case-card">
                    <h5>P5: Monitoreo y Reportes</h5>
                    <p>Dashboard, KPIs y generación de reportes</p>
                </div>
                <div class="case-card">
                    <h5>P6: Auditoría y Trazabilidad</h5>
                    <p>Bitácora del sistema y registro de cambios</p>
                </div>
            </div>
        </div>
        
        <div class="info">
            <h3>📋 Endpoints Principales</h3>
            <ul class="endpoints">
                <li><span class="endpoint-label">POST</span> <span class="endpoint-url">/api/auth/login</span></li>
                <li><span class="endpoint-label">POST</span> <span class="endpoint-url">/api/auth/logout</span></li>
                <li><span class="endpoint-label">GET</span> <span class="endpoint-url">/api/usuarios</span></li>
                <li><span class="endpoint-label">GET</span> <span class="endpoint-url">/api/docentes</span></li>
                <li><span class="endpoint-label">GET</span> <span class="endpoint-url">/api/materias</span></li>
                <li><span class="endpoint-label">GET</span> <span class="endpoint-url">/api/dashboard</span></li>
                <li><span class="endpoint-label">GET</span> <span class="endpoint-url">/api/bitacora</span></li>
            </ul>
        </div>
        
        <div class="info">
            <h3>🚀 Para Comenzar</h3>
            <div class="section">
                <h4>1. Autenticación</h4>
                <ul>
                    <li>Envía una petición POST a <code>/api/auth/login</code> con tu CI y contraseña</li>
                    <li>Recibirás un token que debes usar en el header <code>Authorization: Bearer {token}</code></li>
                </ul>
            </div>
            <div class="section">
                <h4>2. Consume los Endpoints</h4>
                <ul>
                    <li>Usa el token para acceder a los demás endpoints</li>
                    <li>Todos los endpoints están protegidos con autenticación</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 Sistema de Carga Horaria - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
