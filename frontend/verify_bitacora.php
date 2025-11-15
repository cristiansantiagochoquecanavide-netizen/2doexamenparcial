<?php
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bitacora;
use Illuminate\Support\Facades\DB;

try {
    // Contar registros
    $count = Bitacora::count();
    echo "📊 Total de registros en bitacora: $count\n\n";

    // Mostrar últimos 5 registros
    if ($count > 0) {
        echo "📋 Últimos 5 registros:\n";
        $bitacoras = Bitacora::with('usuario')
            ->orderByDesc('id_bit')
            ->limit(5)
            ->get();

        foreach ($bitacoras as $b) {
            $usuario = $b->usuario ? $b->usuario->nombre_usuario : 'Sistema';
            echo "ID: {$b->id_bit} | Usuario: $usuario | Acción: {$b->accion} | Módulo: {$b->modulo} | Fecha: {$b->fecha_accion}\n";
        }
    } else {
        echo "❌ No hay registros en la bitácora\n";
        
        // Verificar si la tabla existe
        $tableExists = DB::getSchemaBuilder()->hasTable('bitacora');
        echo "📐 ¿Tabla 'bitacora' existe? " . ($tableExists ? "Sí" : "No") . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    // echo "Stack: " . $e->getTraceAsString() . "\n";
}
?>
