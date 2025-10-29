# 📚 Documentación de API - Sistema de Carga Horaria

## 🔐 Base URL
```
http://localhost:8000/api
```

## 🔑 Autenticación
La API utiliza **Laravel Sanctum** para autenticación basada en tokens.

### Headers requeridos para rutas protegidas:
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## 📋 ENDPOINTS

### 🔐 AUTENTICACIÓN Y CONTROL DE ACCESO

#### **Autenticación**

**POST** `/api/auth/login`
- Descripción: Iniciar sesión (CU1)
- Público: ✅ Sí
- Body:
```json
{
  "ci_persona": "12345678",
  "contrasena": "password123"
}
```
- Response: `{ token, usuario }`

**POST** `/api/auth/logout`
- Descripción: Cerrar sesión (CU2)
- Protegido: 🔒 Sí

**GET** `/api/auth/me`
- Descripción: Obtener usuario autenticado
- Protegido: 🔒 Sí

**POST** `/api/auth/cambiar-contrasena`
- Descripción: Cambiar contraseña
- Protegido: 🔒 Sí
- Body:
```json
{
  "contrasena_actual": "old_password",
  "contrasena_nueva": "new_password",
  "contrasena_nueva_confirmation": "new_password"
}
```

---

#### **Usuarios (CU3)**

**GET** `/api/usuarios`
- Listar usuarios
- Query params: `?search=nombre&estado=1&per_page=15`

**POST** `/api/usuarios`
- Crear usuario
- Body:
```json
{
  "ci": "12345678",
  "nombre": "Juan Pérez",
  "telefono": "12345678",
  "email": "juan@example.com",
  "direccion": "Calle 123",
  "contrasena": "password123",
  "id_rol": 1
}
```

**GET** `/api/usuarios/{id}`
- Mostrar usuario

**PUT/PATCH** `/api/usuarios/{id}`
- Actualizar usuario

**DELETE** `/api/usuarios/{id}`
- Eliminar usuario

**POST** `/api/usuarios/{id}/toggle-estado`
- Activar/Desactivar usuario

---

#### **Roles (CU4)**

**GET** `/api/roles`
- Listar roles

**POST** `/api/roles`
- Crear rol
- Body:
```json
{
  "nombre": "Administrador",
  "descripcion": "Rol con todos los permisos",
  "permisos": [1, 2, 3]
}
```

**GET** `/api/roles/{id}`
- Mostrar rol

**PUT/PATCH** `/api/roles/{id}`
- Actualizar rol

**DELETE** `/api/roles/{id}`
- Eliminar rol

**GET** `/api/permisos`
- Listar todos los permisos

**POST** `/api/roles/{id}/permisos`
- Asignar permisos a un rol

---

### 🎓 GESTIÓN DE CATÁLOGOS ACADÉMICOS

#### **Docentes (CU5)**

**GET** `/api/docentes`
- Listar docentes
- Query params: `?search=nombre&titulo=PhD`

**POST** `/api/docentes`
- Crear docente
- Body:
```json
{
  "titulo": "Licenciado en Informática",
  "correo_institucional": "docente@universidad.edu",
  "carga_horaria_max": 40,
  "id_usuario": 1
}
```

**GET** `/api/docentes/{id}`
- Mostrar docente

**PUT/PATCH** `/api/docentes/{id}`
- Actualizar docente

**DELETE** `/api/docentes/{id}`
- Eliminar docente

**GET** `/api/docentes/{id}/carga-horaria`
- Obtener carga horaria del docente
- Query params: `?periodo_academico=2024-1`

---

#### **Materias (CU6)**

**GET** `/api/materias`
- Listar materias
- Query params: `?search=Matemáticas&nivel=1&tipo=Obligatoria`

**POST** `/api/materias`
- Crear materia
- Body:
```json
{
  "codigo_mat": "MAT101",
  "nombre_mat": "Matemáticas I",
  "nivel": 1,
  "horas_semanales": 6,
  "tipo": "Obligatoria"
}
```

**GET** `/api/materias/{codigo}`
- Mostrar materia

**PUT/PATCH** `/api/materias/{codigo}`
- Actualizar materia

**DELETE** `/api/materias/{codigo}`
- Eliminar materia

**GET** `/api/materias/nivel/{nivel}`
- Listar materias por nivel

---

#### **Grupos (CU7)**

**GET** `/api/grupos`
- Listar grupos
- Query params: `?search=A&codigo_mat=MAT101`

**POST** `/api/grupos`
- Crear grupo
- Body:
```json
{
  "codigo_grupo": "1A",
  "capacidad_de_grupo": 35,
  "codigo_mat": "MAT101"
}
```

**GET** `/api/grupos/{codigo}`
- Mostrar grupo

**PUT/PATCH** `/api/grupos/{codigo}`
- Actualizar grupo

**DELETE** `/api/grupos/{codigo}`
- Eliminar grupo

**POST** `/api/grupos/{codigo}/materias`
- Asignar materias al grupo
- Body:
```json
{
  "materias": ["MAT101", "FIS101", "QUI101"]
}
```

---

#### **Infraestructura (CU9)**

**GET** `/api/infraestructuras`
- Listar infraestructura

**POST** `/api/infraestructuras`
- Crear infraestructura
- Body:
```json
{
  "nombre_infr": "Edificio A",
  "ubicacion": "Campus Central",
  "estado": "ACTIVO"
}
```

**GET** `/api/infraestructuras/{id}`
- Mostrar infraestructura

**PUT/PATCH** `/api/infraestructuras/{id}`
- Actualizar infraestructura

**DELETE** `/api/infraestructuras/{id}`
- Eliminar infraestructura

---

#### **Aulas (CU8)**

**GET** `/api/aulas`
- Listar aulas
- Query params: `?tipo=Laboratorio&estado=DISPONIBLE`

**POST** `/api/aulas`
- Crear aula
- Body:
```json
{
  "nro_aula": "A101",
  "tipo": "Aula normal",
  "capacidad": 40,
  "estado": "DISPONIBLE",
  "id_infraestructura": 1
}
```

**GET** `/api/aulas/{nro_aula}`
- Mostrar aula

**PUT/PATCH** `/api/aulas/{nro_aula}`
- Actualizar aula

**DELETE** `/api/aulas/{nro_aula}`
- Eliminar aula

**POST** `/api/aulas/{nro_aula}/verificar-disponibilidad`
- Verificar disponibilidad del aula
- Body:
```json
{
  "id_horario": 1,
  "periodo_academico": "2024-1"
}
```

---

### 📅 PLANIFICACIÓN ACADÉMICA

#### **Horarios (CU10)**

**GET** `/api/horarios`
- Listar horarios
- Query params: `?dias_semana=Lunes&turno=Mañana`

**POST** `/api/horarios`
- Crear horario
- Body:
```json
{
  "dias_semana": "Lunes",
  "hora_inicio": "08:00",
  "hora_fin": "10:00",
  "turno": "Mañana"
}
```

**GET** `/api/horarios/{id}`
- Mostrar horario

**PUT/PATCH** `/api/horarios/{id}`
- Actualizar horario

**DELETE** `/api/horarios/{id}`
- Eliminar horario

**GET** `/api/horarios/dia/{dia}`
- Listar horarios por día

**GET** `/api/horarios/turno/{turno}`
- Listar horarios por turno

---

#### **Asignaciones de Horario**

**GET** `/api/asignaciones`
- Listar asignaciones
- Query params: `?periodo_academico=2024-1&estado=ACTIVO&codigo_doc=1`

**POST** `/api/asignaciones`
- Crear asignación (con validación de conflictos)
- Body:
```json
{
  "periodo_academico": "2024-1",
  "codigo_doc": 1,
  "codigo_grupo": "1A",
  "nro_aula": "A101",
  "id_horario": 1,
  "estado": "ACTIVO"
}
```

**GET** `/api/asignaciones/{id}`
- Mostrar asignación

**PUT/PATCH** `/api/asignaciones/{id}`
- Actualizar asignación

**DELETE** `/api/asignaciones/{id}`
- Eliminar asignación

**GET** `/api/asignaciones/docente/{codigo_doc}`
- Obtener horario del docente
- Query params: `?periodo_academico=2024-1`

**GET** `/api/asignaciones/grupo/{codigo_grupo}`
- Obtener horario del grupo
- Query params: `?periodo_academico=2024-1`

---

### ✅ ASISTENCIA DOCENTE

**GET** `/api/asistencias`
- Listar asistencias
- Query params: `?fecha=2024-10-28&estado=PRESENTE&id_asignacion=1`

**POST** `/api/asistencias`
- Registrar asistencia
- Body:
```json
{
  "fecha": "2024-10-28",
  "tipo_registro": "MANUAL",
  "estado": "PRESENTE",
  "id_asignacion": 1
}
```

**GET** `/api/asistencias/{id}`
- Mostrar asistencia

**PUT/PATCH** `/api/asistencias/{id}`
- Actualizar asistencia

**DELETE** `/api/asistencias/{id}`
- Eliminar asistencia

**POST** `/api/asistencias/registrar-hoy`
- Registrar asistencia del día actual
- Body:
```json
{
  "id_asignacion": 1,
  "estado": "PRESENTE"
}
```

**GET** `/api/asistencias/reporte/docente/{codigo_doc}`
- Reporte de asistencias por docente
- Query params: `?fecha_inicio=2024-10-01&fecha_fin=2024-10-31`

**GET** `/api/asistencias/reporte/grupo/{codigo_grupo}`
- Reporte de asistencias por grupo
- Query params: `?fecha_inicio=2024-10-01&fecha_fin=2024-10-31`

---

### 🔍 AUDITORÍA Y TRAZABILIDAD

**GET** `/api/bitacora`
- Listar registros de bitácora
- Query params: `?modulo=Gestión de Usuarios&id_usuario=1&fecha_inicio=2024-10-01`

**GET** `/api/bitacora/{id}`
- Mostrar registro de bitácora

**GET** `/api/bitacora/reporte/usuario/{id_usuario}`
- Reporte de actividad por usuario
- Query params: `?fecha_inicio=2024-10-01&fecha_fin=2024-10-31`

**GET** `/api/bitacora/reporte/modulo/{modulo}`
- Reporte de actividad por módulo
- Query params: `?fecha_inicio=2024-10-01&fecha_fin=2024-10-31`

**GET** `/api/bitacora/estadisticas/general`
- Estadísticas generales de auditoría
- Query params: `?fecha_inicio=2024-10-01&fecha_fin=2024-10-31`

---

## 📊 Códigos de Respuesta HTTP

- `200 OK` - Solicitud exitosa
- `201 Created` - Recurso creado exitosamente
- `422 Unprocessable Entity` - Errores de validación
- `401 Unauthorized` - No autenticado
- `403 Forbidden` - Sin permisos
- `404 Not Found` - Recurso no encontrado
- `500 Internal Server Error` - Error del servidor

---

## 🚀 Ejemplo de uso con JavaScript (Fetch API)

```javascript
// Login
const login = async () => {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      ci_persona: '12345678',
      contrasena: 'password123'
    })
  });
  
  const data = await response.json();
  localStorage.setItem('token', data.token);
  return data;
};

// Obtener docentes (autenticado)
const getDocentes = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/docentes', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
};
```

---

## 📝 Notas Importantes

1. Todas las rutas excepto `/api/auth/login` requieren autenticación
2. Los tokens de Sanctum se generan al hacer login
3. La bitácora registra automáticamente todas las acciones
4. Las asignaciones de horario validan conflictos automáticamente
5. La carga horaria de docentes se valida al crear asignaciones
