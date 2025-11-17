<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #ff5722;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            font-size: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #ff5722;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <div class="subtitle">Generado el: {{ $fecha }}</div>

    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($datos[0]) as $columna)
                        <th>{{ ucfirst(str_replace('_', ' ', $columna)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($datos as $fila)
                    <tr>
                        @foreach($fila as $valor)
                            <td>{{ $valor }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No hay datos disponibles para mostrar</p>
        </div>
    @endif

    <div class="footer">
        <p>Sistema de Carga Horaria &copy; {{ date('Y') }}</p>
        <p>Total de registros: {{ count($datos) }}</p>
    </div>
</body>
</html>
