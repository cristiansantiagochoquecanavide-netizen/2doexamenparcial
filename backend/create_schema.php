<?php
try {
    $conn = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=cargahoraria', 'postgres', 'CAMPEON');
    $conn->exec('CREATE SCHEMA IF NOT EXISTS carga_horaria');
    $conn->exec('ALTER SCHEMA carga_horaria OWNER TO postgres');
    echo "Esquema creado exitosamente\n";
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
