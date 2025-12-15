<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                    })->toArray(),
                ]);
            }

            // Transformar los datos para incluir las relaciones en la respuesta JSON
            $data = $docentes->map(function ($docente) {
                // Obtener nombre completo (Nombre + Apellido Paterno + Apellido Materno)
                $nombreCompleto = 'Desconocido';
                $ci = '';
                
                // Debug: Verificar si usuario existe
                if ($docente->usuario) {
                    if ($docente->usuario->persona) {
                        $persona = $docente->usuario->persona;
                        $nombreCompleto = trim("{$persona->nombre} {$persona->apellido_paterno} {$persona->apellido_materno}");
                        $ci = $persona->ci;
                    } else {
                        // Log si persona no existe
                        \Log::warning('Docente sin persona', ['id_usuario' => $docente->usuario->id_usuario, 'ci_persona' => $docente->usuario->ci_persona]);
                    }
                } else {
                    \Log::warning('Docente sin usuario', ['codigo_doc' => $docente->codigo_doc]);
                }

                return [
                    'codigo_doc' => $docente->codigo_doc,
                    'ci' => $ci,
                    'nombre_completo' => $nombreCompleto,
                    'titulo' => $docente->titulo,
                    'correo_institucional' => $docente->correo_institucional,
                    'carga_horaria_max' => $docente->carga_horaria_max,
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
            'estado' => 'nullable|in:A,I',
        ]);

        try {
            \DB::beginTransaction();

            // Obtener el estado - por defecto es 'A' (Activo)
            $estadoParam = $request->input('estado', 'A');
            $estadoBoolean = ($estadoParam === 'I') ? false : true; // 'A' o null = true, 'I' = false

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

            \Log::info('Persona creada correctamente', ['ci' => $persona->ci]);

            // 2. Obtener el rol "Docente"
            $rolDocente = \DB::table('rol')->where('nombre', 'Docente')->first();
            if (!$rolDocente) {
                throw new \Exception('El rol Docente no existe en la base de datos');
            }

            \Log::info('Rol Docente obtenido correctamente', ['id_rol' => $rolDocente->id_rol]);

            // 3. Crear usuario con rol de Docente
            $usuario = Usuario::create([
                'ci_persona' => $persona->ci,
                'contrasena' => Hash::make($persona->ci), // Usar CI como contraseña inicial
                'estado' => $estadoBoolean,
                'id_rol' => $rolDocente->id_rol,
            ]);

            \Log::info('Usuario creado correctamente', ['id_usuario' => $usuario->id_usuario]);

            // 4. Crear docente con los campos que realmente existen en la tabla
            $docente = Docente::create([
                'titulo' => $request->especialidad, // Usar especialidad como título
                'correo_institucional' => $request->email, // Usar email como correo institucional
                'carga_horaria_max' => $request->carga_horaria_max,
                'id_usuario' => $usuario->id_usuario,
            ]);

            \Log::info('Docente creado correctamente', ['codigo_doc' => $docente->codigo_doc]);

            \DB::commit();

            Bitacora::registrar('Gestión de Docentes', "Docente creado: {$persona->nombre}");

            // Cargar las relaciones necesarias
            $docente->load('usuario.persona', 'usuario.rol');

            return response()->json([
                'message' => 'Docente creado exitosamente',
                'docente' => [
                    'codigo_doc' => $docente->codigo_doc,
                    'ci' => $persona->ci,
                    'nombre_completo' => $persona->nombre,
                    'titulo' => $docente->titulo,
                    'correo_institucional' => $docente->correo_institucional,
                    'carga_horaria_max' => $docente->carga_horaria_max,
                ],
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
     * CU5: Gestionar Docentes - Actualizar
     */
    public function update(Request $request, $id)
    {
        try {
            // Obtener el docente primero
            $docente = Docente::findOrFail($id);
            $ciActual = $docente->usuario->ci_persona;
            
            // Permitir actualización parcial - solo validar los campos que vienen en la request
            $rules = [];
            
            // NOTA: El CI no se puede editar después de crear el docente (es la clave única)
            if ($request->has('nombre_completo')) {
                $rules['nombre_completo'] = 'string|max:200';
            }
            if ($request->has('fecha_nacimiento')) {
                $rules['fecha_nacimiento'] = 'nullable|date';
            }
            if ($request->has('sexo')) {
                $rules['sexo'] = 'nullable|in:M,F';
            }
            if ($request->has('telefono')) {
                $rules['telefono'] = 'nullable|string|max:20';
            }
            if ($request->has('email')) {
                $rules['email'] = 'nullable|email|max:100';
            }
            if ($request->has('direccion')) {
                $rules['direccion'] = 'nullable|string|max:255';
            }
            if ($request->has('especialidad')) {
                $rules['especialidad'] = 'nullable|string|max:100';
            }
            if ($request->has('carga_horaria_max')) {
                $rules['carga_horaria_max'] = 'integer|min:1';
            }
            if ($request->has('estado')) {
                $rules['estado'] = 'nullable|in:A,I,Activo,Inactivo,true,false,1,0';
            }
            
            $request->validate($rules);

            \DB::beginTransaction();

            $persona = $docente->usuario->persona;

            // Actualizar persona solo si se envían datos
            if ($request->has('nombre_completo') || $request->has('fecha_nacimiento') || 
                $request->has('sexo') || $request->has('telefono') || $request->has('email') || $request->has('direccion')) {
                
                $personaData = [];
                // NO incluir CI - no se puede cambiar después de creado
                if ($request->has('nombre_completo')) $personaData['nombre'] = $request->nombre_completo;
                if ($request->has('fecha_nacimiento')) $personaData['fecha_nacimiento'] = $request->fecha_nacimiento;
                if ($request->has('sexo')) $personaData['sexo'] = $request->sexo;
                if ($request->has('telefono')) $personaData['telefono'] = $request->telefono;
                if ($request->has('email')) $personaData['email'] = $request->email;
                if ($request->has('direccion')) $personaData['direccion'] = $request->direccion;
                
                $persona->update($personaData);
                \Log::info('Persona actualizada correctamente', ['ci' => $persona->ci]);
            }

            // Actualizar docente solo si se envían datos
            if ($request->has('especialidad') || $request->has('carga_horaria_max')) {
                $docenteData = [];
                if ($request->has('especialidad')) $docenteData['titulo'] = $request->especialidad;
                if ($request->has('carga_horaria_max')) $docenteData['carga_horaria_max'] = $request->carga_horaria_max;
                if ($request->has('email')) $docenteData['correo_institucional'] = $request->email;
                
                $docente->update($docenteData);
            }

            // Actualizar el usuario (estado)
            if ($request->has('estado')) {
                $estadoValue = $request->estado;
                
                // Convertir múltiples formatos a boolean
                if ($estadoValue === 'A' || $estadoValue === 'Activo' || $estadoValue === 'true' || $estadoValue === '1' || $estadoValue === 1 || $estadoValue === true) {
                    $estadoBool = true;
                } else {
                    $estadoBool = false;
                }
                
                $docente->usuario->update([
                    'estado' => $estadoBool,
                ]);
            }

            \Log::info('Docente actualizado correctamente', ['codigo_doc' => $docente->codigo_doc]);

            \DB::commit();

            Bitacora::registrar('Gestión de Docentes', "Docente actualizado: {$persona->nombre}");

            // Recargar relaciones
            $docente->load('usuario.persona', 'usuario.rol');

            return response()->json([
                'message' => 'Docente actualizado exitosamente',
                'docente' => [
                    'codigo_doc' => $docente->codigo_doc,
                    'ci' => $persona->ci,
                    'nombre_completo' => $persona->nombre,
                    'titulo' => $docente->titulo,
                    'correo_institucional' => $docente->correo_institucional,
                    'carga_horaria_max' => $docente->carga_horaria_max,
                    'estado' => $docente->usuario->estado ? 'Activo' : 'Inactivo',
                ],
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al actualizar docente: ' . $e->getMessage());
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
        $usuario = $docente->usuario;

        DB::beginTransaction();
        try {
            // Eliminar todas las asignaciones de horario del docente (sin importar el estado)
            if ($docente->asignaciones()->exists()) {
                $docente->asignaciones()->delete();
            }

            // Eliminar el registro de docente
            $docente->delete();

            // Si el usuario asociado tiene rol de Docente (id_rol = 5), eliminarlo también
            // Esto elimina el usuario y en cascada la persona
            if ($usuario && $usuario->id_rol == 5) {
                DB::statement('DELETE FROM carga_horaria.usuario WHERE id_usuario = ?', [$usuario->id_usuario]);
            }

            Bitacora::registrar('Gestión de Docentes', "Docente eliminado: {$nombreDocente}");

            DB::commit();

            return response()->json([
                'message' => 'Docente eliminado exitosamente',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar docente',
                'error' => $e->getMessage(),
            ], 500);
        }
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
