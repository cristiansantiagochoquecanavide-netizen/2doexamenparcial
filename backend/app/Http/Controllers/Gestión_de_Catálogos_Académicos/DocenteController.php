<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    /**
     * CU5: Gestionar Docentes - Listar
     */
    public function index(Request $request)
    {
        // Log explícito para registrar cualquier acceso a /docentes
        try {
            $usuario = auth('sanctum')->user();
            $ip = $request->ip();
            \Log::info('ACCESO /docentes', [
                'ci' => $usuario ? $usuario->ci_persona : null,
                'rol' => $usuario && $usuario->rol ? $usuario->rol->nombre : null,
                'id_usuario' => $usuario ? $usuario->id_usuario : null,
                'ip' => $ip,
                'user_agent' => $request->header('User-Agent'),
                'full_url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('No se pudo obtener usuario en /docentes: ' . $e->getMessage());
        }

        // Log de depuración antes y después de la consulta
        \Log::info('DocenteController@index - Iniciando consulta de docentes');

        try {
            $query = Docente::with(['usuario.persona', 'usuario.rol', 'asignaciones.grupo.materia']);
            // Sin filtro por rol, devolver todos los docentes para cualquier usuario autenticado

            // Búsqueda por término
            if ($request->search) {
                $search = $request->search;
                $query->whereHas('usuario.persona', function ($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('ci', 'ILIKE', "%{$search}%");
                })->orWhere('titulo', 'ILIKE', "%{$search}%");
            }

            // Búsqueda por título
            if ($request->titulo) {
                $query->where('titulo', 'ILIKE', "%{$request->titulo}%");
            }

            $docentes = $query->paginate($request->per_page ?? 1000); // Aumentar el límite para combos

            // Log especial para depuración: solo para Coordinador Académico
            $usuario = auth('sanctum')->user();
            if ($usuario && $usuario->rol && $usuario->rol->nombre === 'Coordinador Académico') {
                \Log::info('DEBUG DOCENTES Coordinador', [
                    'total' => $docentes->count(),
                    'docentes' => $docentes->map(function($d) {
                        return [
                            'codigo_doc' => $d->codigo_doc,
                            'nombre' => $d->usuario && $d->usuario->persona ? $d->usuario->persona->nombre : null,
                            'id_usuario' => $d->id_usuario,
                            'usuario_estado' => $d->usuario ? $d->usuario->estado : null,
                            'usuario_rol' => $d->usuario && $d->usuario->rol ? $d->usuario->rol->nombre : null,
                        ];
                    })
                ]);
            }

            // Transformar los datos para incluir las relaciones en la respuesta JSON
            $data = $docentes->map(function ($docente) {
                // Obtener nombre completo (Nombre + Apellido Paterno + Apellido Materno)
                $nombreCompleto = 'Desconocido';
                if ($docente->usuario && $docente->usuario->persona) {
                    $persona = $docente->usuario->persona;
                    $nombre = trim($persona->nombre ?? '');
                    $apellidoPaterno = trim($persona->apellido_paterno ?? '');
                    $apellidoMaterno = trim($persona->apellido_materno ?? '');
                    
                    // Construir: Nombre Apellido_Paterno Apellido_Materno
                    $parts = array_filter([$nombre, $apellidoPaterno, $apellidoMaterno]);
                    $nombreCompleto = !empty($parts) ? implode(' ', $parts) : 'Desconocido';
                }

                // Obtener materias asignadas (sin duplicados)
                $materias = [];
                try {
                    if ($docente->asignaciones && count($docente->asignaciones) > 0) {
                        $materiasCollection = collect($docente->asignaciones)
                            ->pluck('grupo.materia.nombre_mat')
                            ->filter()
                            ->unique()
                            ->values();
                        $materias = $materiasCollection->toArray();
                    }
                } catch (\Exception $e) {
                    // Si hay error al obtener materias, continuar sin ellas
                    $materias = [];
                }

                return [
                    'codigo_doc' => $docente->codigo_doc,
                    'titulo' => $docente->titulo,
                    'correo_institucional' => $docente->correo_institucional,
                    'carga_horaria_max' => $docente->carga_horaria_max,
                    'id_usuario' => $docente->id_usuario,
                    'nombre_completo' => $nombreCompleto,
                    'nombre_docente' => $nombreCompleto,
                    'materias' => $materias,
                    'usuario' => $docente->usuario ? [
                        'id_usuario' => $docente->usuario->id_usuario,
                        'estado' => $docente->usuario->estado,
                        'ci_persona' => $docente->usuario->ci_persona,
                        'id_rol' => $docente->usuario->id_rol,
                        'persona' => $docente->usuario->persona ? [
                            'ci' => $docente->usuario->persona->ci,
                            'nombre_completo' => $docente->usuario->persona->nombre_completo,
                            'apellido' => $docente->usuario->persona->apellido,
                            'nombre' => $docente->usuario->persona->nombre,
                        ] : null,
                    ] : null,
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $docentes->currentPage(),
                    'from' => $docentes->firstItem(),
                    'last_page' => $docentes->lastPage(),
                    'per_page' => $docentes->perPage(),
                    'to' => $docentes->lastItem(),
                    'total' => $docentes->total(),
                ],
                'links' => [
                    'first' => $docentes->url(1),
                    'last' => $docentes->url($docentes->lastPage()),
                    'prev' => $docentes->previousPageUrl(),
                    'next' => $docentes->nextPageUrl(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al listar docentes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al listar docentes: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * CU5: Gestionar Docentes - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            // Datos de persona
            'ci' => 'required|string|max:20|unique:persona,ci',
            'nombre_completo' => 'required|string|max:200',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
            // Datos de docente
            'especialidad' => 'nullable|string|max:100',
            'carga_horaria_max' => 'required|integer|min:1',
            'estado' => 'required|in:A,I',
        ]);

        try {
            \DB::beginTransaction();

            // 1. Crear persona con nombre completo
            $persona = \App\Models\Persona::create([
                'ci' => $request->ci,
                'nombre' => $request->nombre_completo,
                'apellido_paterno' => '',
                'apellido_materno' => '',
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo ?? 'M',
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
            ]);

            // 2. Obtener el rol "Docente"
            $rolDocente = \DB::table('rol')->where('nombre', 'Docente')->first();
            if (!$rolDocente) {
                throw new \Exception('El rol Docente no existe en la base de datos');
            }

            // 3. Crear usuario con rol de Docente
            $usuario = Usuario::create([
                'ci_persona' => $persona->ci,
                'id_rol' => $rolDocente->id_rol,
                'contrasena' => \Hash::make('password123'), // Contraseña temporal
                'estado' => $request->estado === 'A' ? true : false, // Convertir a booleano
            ]);

            // 4. Crear docente con los campos que realmente existen en la tabla
            $docente = Docente::create([
                'titulo' => $request->especialidad, // Usar especialidad como título
                'id_usuario' => $usuario->id_usuario,
                'carga_horaria_max' => $request->carga_horaria_max,
                'correo_institucional' => $request->email, // Usar email como correo institucional
            ]);

            \DB::commit();

            Bitacora::registrar('Gestión de Docentes', "Docente creado: {$persona->nombre}");

            return response()->json([
                'message' => 'Docente creado exitosamente',
                'docente' => $docente->load('usuario.persona'),
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear docente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear docente: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
        try {
            $query = Docente::with(['usuario.persona', 'usuario.rol', 'asignaciones.grupo.materia']);
            // Sin filtro por rol, devolver todos los docentes para cualquier usuario autenticado

            // Búsqueda por término
            if ($request->search) {
                $search = $request->search;
                $query->whereHas('usuario.persona', function ($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('ci', 'ILIKE', "%{$search}%");
                })->orWhere('titulo', 'ILIKE', "%{$search}%");
            }

            // Búsqueda por título
            if ($request->titulo) {
                $query->where('titulo', 'ILIKE', "%{$request->titulo}%");
            }

            $docentes = $query->paginate($request->per_page ?? 1000); // Aumentar el límite para combos

            \Log::info('DocenteController@index - Docentes encontrados', [
                'total' => $docentes->total(),
                'ids' => $docentes->pluck('codigo_doc')->toArray(),
            ]);

            // Transformar los datos para incluir las relaciones en la respuesta JSON
            $data = $docentes->map(function ($docente) {
                // Obtener nombre completo (Nombre + Apellido Paterno + Apellido Materno)
                $nombreCompleto = 'Desconocido';
                if ($docente->usuario && $docente->usuario->persona) {
                    $persona = $docente->usuario->persona;
                    $nombre = trim($persona->nombre ?? '');
                    $apellidoPaterno = trim($persona->apellido_paterno ?? '');
                    $apellidoMaterno = trim($persona->apellido_materno ?? '');
                    // Construir: Nombre Apellido_Paterno Apellido_Materno
                    $parts = array_filter([$nombre, $apellidoPaterno, $apellidoMaterno]);
                    $nombreCompleto = !empty($parts) ? implode(' ', $parts) : 'Desconocido';
                }

                // Obtener materias asignadas (sin duplicados)
                $materias = [];
                try {
                    if ($docente->asignaciones && count($docente->asignaciones) > 0) {
                        $materiasCollection = collect($docente->asignaciones)
                            ->pluck('grupo.materia.nombre_mat')
                            ->filter()
                            ->unique()
                            ->values();
                        $materias = $materiasCollection->toArray();
                    }
                } catch (\Exception $e) {
                    // Si hay error al obtener materias, continuar sin ellas
                    $materias = [];
                }

                return [
                    'codigo_doc' => $docente->codigo_doc,
                    'titulo' => $docente->titulo,
                    'correo_institucional' => $docente->correo_institucional,
                    'carga_horaria_max' => $docente->carga_horaria_max,
                    'id_usuario' => $docente->id_usuario,
                    'nombre_completo' => $nombreCompleto,
                    'nombre_docente' => $nombreCompleto,
                    'materias' => $materias,
                    'usuario' => $docente->usuario ? [
                        'id_usuario' => $docente->usuario->id_usuario,
                        'estado' => $docente->usuario->estado,
                        'ci_persona' => $docente->usuario->ci_persona,
                        'id_rol' => $docente->usuario->id_rol,
                        'persona' => $docente->usuario->persona ? [
                            'ci' => $docente->usuario->persona->ci,
                            'nombre_completo' => $docente->usuario->persona->nombre_completo,
                            'apellido' => $docente->usuario->persona->apellido,
                            'nombre' => $docente->usuario->persona->nombre,
                        ] : null,
                    ] : null,
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $docentes->currentPage(),
                    'from' => $docentes->firstItem(),
                    'last_page' => $docentes->lastPage(),
                    'per_page' => $docentes->perPage(),
                    'to' => $docentes->lastItem(),
                    'total' => $docentes->total(),
                ],
                'links' => [
                    'first' => $docentes->url(1),
                    'last' => $docentes->url($docentes->lastPage()),
                    'prev' => $docentes->previousPageUrl(),
                    'next' => $docentes->nextPageUrl(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en DocenteController@index: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudieron obtener los docentes'], 500);
        }
            return response()->json([
                'message' => 'Error al actualizar docente: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * CU5: Gestionar Docentes - Eliminar
     */
    public function destroy($id)
    {
        $docente = Docente::findOrFail($id);
        $nombreDocente = $docente->persona->nombre;

        // Verificar si tiene asignaciones activas
        if ($docente->asignaciones()->where('estado', 'ACTIVO')->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el docente porque tiene asignaciones activas',
            ], 422);
        }

        $docente->delete();

        Bitacora::registrar('Gestión de Docentes', "Docente eliminado: {$nombreDocente}");

        return response()->json([
            'message' => 'Docente eliminado exitosamente',
        ]);
    }

    /**
     * Obtener carga horaria actual del docente
     */
    public function cargaHoraria($id, Request $request)
    {
        $docente = Docente::findOrFail($id);
        
        $periodo = $request->periodo_academico ?? now()->format('Y-1');
        
        $asignaciones = $docente->asignaciones()
            ->with(['horario', 'grupo.materia', 'aula'])
            ->where('periodo_academico', $periodo)
            ->where('estado', 'ACTIVO')
            ->get();

        $horasAsignadas = $asignaciones->sum(function ($asignacion) {
            return $asignacion->grupo->materia->horas_semanales ?? 0;
        });

        return response()->json([
            'docente' => $docente->load('usuario.persona'),
            'periodo_academico' => $periodo,
            'carga_horaria_max' => $docente->carga_horaria_max,
            'horas_asignadas' => $horasAsignadas,
            'horas_disponibles' => $docente->carga_horaria_max - $horasAsignadas,
            'asignaciones' => $asignaciones,
        ]);
    }
}
