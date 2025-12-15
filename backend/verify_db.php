<?php
$conn = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=cargahoraria', 'postgres', 'CAMPEON');
$stmt = $conn->query('SELECT u.id_usuario, u.ci_persona, u.estado, p.nombre, d.codigo_doc FROM usuario u LEFT JOIN persona p ON u.ci_persona = p.ci LEFT JOIN docente d ON d.id_usuario = u.id_usuario ORDER BY u.id_usuario DESC LIMIT 10');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== Verificación de datos ===\n";
foreach ($results as $row) {
    echo "ID Usuario: " . $row['id_usuario'] . ", Código Docente: " . ($row['codigo_doc'] ?: 'null') . ", CI: " . $row['ci_persona'] . ", Estado: " . ($row['estado'] ? 'true (Activo)' : 'false (Inactivo)') . ", Nombre: " . $row['nombre'] . "\n";
}
