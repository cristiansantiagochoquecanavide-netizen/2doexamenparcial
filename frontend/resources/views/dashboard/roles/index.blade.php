<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Sistema de Carga Horaria</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Gestión de Roles</h2>
                <button @click="showForm = !showForm" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg">
                    + Nuevo Rol
                </button>
            </div>

            <!-- Formulario -->
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">@{{ editingId ? 'Editar Rol' : 'Crear Nuevo Rol' }}</h3>
                <form @submit.prevent="guardarRol" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input v-model="formData.nombre" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Ej: Administrador, Docente">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea v-model="formData.descripcion" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" rows="3" placeholder="Descripción del rol"></textarea>
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
                            <th class="px-6 py-3 text-left">Nombre</th>
                            <th class="px-6 py-3 text-left">Descripción</th>
                            <th class="px-6 py-3 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="rol in roles" :key="rol.id_rol" class="hover:bg-gray-50">
                            <td class="px-6 py-3">@{{ rol.id_rol }}</td>
                            <td class="px-6 py-3">@{{ rol.nombre }}</td>
                            <td class="px-6 py-3">@{{ rol.descripcion || '-' }}</td>
                            <td class="px-6 py-3">
                                <button @click="editarRol(rol)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-2">
                                    Editar
                                </button>
                                <button @click="eliminarRol(rol.id_rol)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="roles.length === 0" class="text-center py-8 text-gray-500">
                    No hay roles registrados
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
                    roles: [],
                    showForm: false,
                    editingId: null,
                    loading: false,
                    formData: {
                        nombre: '',
                        descripcion: ''
                    }
                };
            },
            mounted() {
                this.cargarRoles();
            },
            methods: {
                async cargarRoles() {
                    try {
                        this.loading = true;
                        const token = localStorage.getItem('token');
                        const response = await axios.get('http://127.0.0.1:8000/api/roles', {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            }
                        });
                        this.roles = response.data;
                    } catch (error) {
                        console.error('Error al cargar roles:', error);
                        alert('Error al cargar los roles');
                    } finally {
                        this.loading = false;
                    }
                },
                async guardarRol() {
                    try {
                        if (!this.formData.nombre.trim()) {
                            alert('El nombre del rol es requerido');
                            return;
                        }

                        const token = localStorage.getItem('token');
                        
                        if (this.editingId) {
                            // Actualizar rol existente
                            const response = await axios.put(`http://127.0.0.1:8000/api/roles/${this.editingId}`, this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            
                            // Actualizar en la lista
                            const index = this.roles.findIndex(r => r.id_rol === this.editingId);
                            if (index !== -1) {
                                this.roles[index] = response.data.rol || response.data;
                            }
                            alert('Rol actualizado exitosamente');
                        } else {
                            // Crear nuevo rol
                            const response = await axios.post('http://127.0.0.1:8000/api/roles', this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            this.roles.push(response.data.rol || response.data);
                            alert('Rol creado exitosamente');
                        }
                        
                        this.cancelarFormulario();
                    } catch (error) {
                        console.error('Error al guardar rol:', error);
                        alert('Error al guardar el rol: ' + (error.response?.data?.message || error.message));
                    }
                },
                async eliminarRol(id) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este rol?')) return;
                    
                    try {
                        const token = localStorage.getItem('token');
                        await axios.delete(`http://127.0.0.1:8000/api/roles/${id}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        this.roles = this.roles.filter(r => r.id_rol !== id);
                        alert('Rol eliminado exitosamente');
                    } catch (error) {
                        console.error('Error al eliminar rol:', error);
                        alert('Error al eliminar el rol');
                    }
                },
                editarRol(rol) {
                    this.editingId = rol.id_rol;
                    this.formData = { 
                        nombre: rol.nombre,
                        descripcion: rol.descripcion || ''
                    };
                    this.showForm = true;
                },
                cancelarFormulario() {
                    this.formData = { nombre: '', descripcion: '' };
                    this.editingId = null;
                    this.showForm = false;
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
