<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Carga Horaria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        [v-cloak] { display: none; }
    </style>
</head>
<body class="bg-gray-50">
    <div id="app" v-cloak>
        <!-- Navbar -->
        <nav class="bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <a href="/dashboard" class="flex items-center gap-2 hover:opacity-80">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h1 class="text-xl font-bold">Sistema de Carga Horaria</h1>
                        </a>
                    </div>
                    <div class="flex items-center gap-4">
                        <form action="/logout" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-sm">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Gestión de Usuarios</h2>
                <button @click="showForm = !showForm" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg">
                    + Nuevo Usuario
                </button>
            </div>

            <!-- Búsqueda y Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input v-model="searchTerm" type="text" placeholder="Buscar por CI o nombre..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Rol</label>
                    <select v-model="selectedRole" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Todos los roles</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Docente">Docente</option>
                        <option value="Estudiante">Estudiante</option>
                    </select>
                </div>
            </div>

            <!-- Formulario -->
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">@{{ editingId ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}</h3>
                <form @submit.prevent="guardarUsuario" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CI <span v-if="!editingId" class="text-red-500">*</span></label>
                            <input v-model="formData.ci_persona" type="text" :required="!editingId" :readonly="!!editingId" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" :class="{ 'bg-gray-100 cursor-not-allowed': editingId }" placeholder="Ej: 12345678">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                            <input v-model="formData.nombre_completo" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Nombre del usuario">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                            <input v-model="formData.email" type="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="usuario@ejemplo.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contraseña *</label>
                            <input v-model="formData.password" type="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Contraseña">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rol *</label>
                            <select v-model="formData.rol" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                                <option value="">Seleccionar rol</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Docente">Docente</option>
                                <option value="Estudiante">Estudiante</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select v-model="formData.estado" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            @{{ editingId ? 'Actualizar' : 'Guardar' }}
                        </button>
                        <button type="button" @click="cancelarFormulario" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-orange-500 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">ID</th>
                            <th class="px-6 py-3 text-left">CI</th>
                            <th class="px-6 py-3 text-left">Nombre Completo</th>
                            <th class="px-6 py-3 text-left">Rol</th>
                            <th class="px-6 py-3 text-left">Estado</th>
                            <th class="px-6 py-3 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="usuario in filteredUsuarios" :key="usuario.id_usuario" class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-semibold text-gray-600">@{{ usuario.id_usuario }}</td>
                            <td class="px-6 py-3">@{{ usuario.ci_persona }}</td>
                            <td class="px-6 py-3">@{{ usuario.nombre_completo ? usuario.nombre_completo : (usuario.persona?.nombre || '') + ' ' + (usuario.persona?.apellido_paterno || '') + ' ' + (usuario.persona?.apellido_materno || '') }}</td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-sm" :class="usuario.rol === 'Administrador' ? 'bg-red-100 text-red-800' : usuario.rol === 'Docente' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                    @{{ usuario.rol }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span :class="usuario.estado === true || usuario.estado === 'A' || usuario.estado === 'Activo' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                    @{{ usuario.estado === true || usuario.estado === 'A' || usuario.estado === 'Activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <button @click="editarUsuario(usuario)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-2">
                                    Editar
                                </button>
                                <button @click="eliminarUsuario(usuario.id_usuario)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="usuarios.length === 0" class="text-center py-8 text-gray-500">
                    No hay usuarios registrados
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    usuarios: [],
                    showForm: false,
                    editingId: null,
                    loading: false,
                    searchTerm: '',
                    selectedRole: '',
                    formData: {
                        ci_persona: '',
                        nombre_completo: '',
                        email: '',
                        password: '',
                        rol: '',
                        estado: true  // true = Activo, false = Inactivo
                    }
                };
            },
            computed: {
                filteredUsuarios() {
                    return this.usuarios.filter(usuario => {
                        const matchesSearch = usuario.ci_persona.includes(this.searchTerm) || 
                                            usuario.nombre_completo.toLowerCase().includes(this.searchTerm.toLowerCase());
                        const matchesRole = this.selectedRole === '' || usuario.rol === this.selectedRole;
                        return matchesSearch && matchesRole;
                    });
                }
            },
            mounted() {
                this.cargarUsuarios();
            },
            methods: {
                async cargarUsuarios() {
                    try {
                        this.loading = true;
                        const token = localStorage.getItem('token');
                        const response = await axios.get('http://127.0.0.1:8000/api/usuarios', {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            }
                        });
                        this.usuarios = response.data;
                    } catch (error) {
                        console.error('Error al cargar usuarios:', error);
                        // Simular datos si la API no responde
                        this.usuarios = [
                            { id_usuario: 1, ci_persona: '12345678', nombre_completo: 'Admin Sistema', rol: 'Administrador', estado: true, email: 'admin@sistema.com' },
                            { id_usuario: 2, ci_persona: '87654321', nombre_completo: 'Super Administrador', rol: 'Administrador', estado: true, email: 'super@admin.com' }
                        ];
                    } finally {
                        this.loading = false;
                    }
                },
                async guardarUsuario() {
                    try {
                        // CI solo es requerido cuando es creación (nuevo usuario)
                        if (!this.editingId && !this.formData.ci_persona.trim()) {
                            alert('CI es requerido');
                            return;
                        }
                        if (!this.formData.nombre_completo.trim()) {
                            alert('Nombre completo es requerido');
                            return;
                        }

                        const token = localStorage.getItem('token');
                        
                        if (this.editingId) {
                            const response = await axios.put(`http://127.0.0.1:8000/api/usuarios/${this.editingId}`, this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            
                            const index = this.usuarios.findIndex(u => u.id_usuario === this.editingId);
                            if (index !== -1) {
                                this.usuarios[index] = response.data.usuario || response.data;
                            }
                            alert('Usuario actualizado exitosamente');
                        } else {
                            const response = await axios.post('http://127.0.0.1:8000/api/usuarios', this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            this.usuarios.push(response.data.usuario || response.data);
                            alert('Usuario creado exitosamente');
                        }
                        
                        this.cancelarFormulario();
                    } catch (error) {
                        console.error('Error al guardar usuario:', error);
                        alert('Error al guardar el usuario: ' + (error.response?.data?.message || error.message));
                    }
                },
                async eliminarUsuario(id) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) return;
                    
                    try {
                        const token = localStorage.getItem('token');
                        await axios.delete(`http://127.0.0.1:8000/api/usuarios/${id}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        this.usuarios = this.usuarios.filter(u => u.id_usuario !== id);
                        alert('Usuario eliminado exitosamente');
                    } catch (error) {
                        console.error('Error al eliminar usuario:', error);
                        alert('Error al eliminar el usuario');
                    }
                },
                editarUsuario(usuario) {
                    this.editingId = usuario.id_usuario;
                    // Convertir estado a booleano si viene como string
                    const estadoValue = typeof usuario.estado === 'string' 
                        ? usuario.estado === 'A' 
                        : usuario.estado !== false;
                    
                    this.formData = { 
                        ci_persona: usuario.ci_persona,
                        nombre_completo: usuario.nombre_completo,
                        email: usuario.email || '',
                        password: '',
                        rol: usuario.rol,
                        estado: estadoValue  // true = Activo, false = Inactivo
                    };
                    this.showForm = true;
                },
                cancelarFormulario() {
                    this.formData = {
                        ci_persona: '',
                        nombre_completo: '',
                        email: '',
                        password: '',
                        rol: '',
                        estado: true  // true = Activo, false = Inactivo
                    };
                    this.editingId = null;
                    this.showForm = false;
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
