<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Docentes - Sistema de Carga Horaria</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Gestión de Docentes</h2>
                <button @click="showForm = !showForm" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg">
                    + Nuevo Docente
                </button>
            </div>

            <!-- Búsqueda y Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input v-model="searchTerm" type="text" placeholder="Buscar por CI, nombre o especialidad..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Estado</label>
                    <select v-model="selectedState" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Formulario -->
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">@{{ editingId ? 'Editar Docente' : 'Crear Nuevo Docente' }}</h3>
                <form @submit.prevent="guardarDocente" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CI *</label>
                            <input v-model="formData.ci_persona" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Ej: 12345678">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                            <input v-model="formData.nombre_completo" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Nombre del docente">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="formData.email" type="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="docente@ejemplo.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input v-model="formData.telefono" type="tel" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Ej: +1234567890">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Especialidad</label>
                            <input v-model="formData.especialidad" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Ej: Matemáticas, Física">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select v-model="formData.estado" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea v-model="formData.observaciones" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" rows="2" placeholder="Observaciones adicionales"></textarea>
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
                            <th class="px-6 py-3 text-left">CI</th>
                            <th class="px-6 py-3 text-left">Nombre Completo</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Especialidad</th>
                            <th class="px-6 py-3 text-left">Estado</th>
                            <th class="px-6 py-3 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="docente in filteredDocentes" :key="docente.id_docente" class="hover:bg-gray-50">
                            <td class="px-6 py-3">@{{ docente.ci_persona }}</td>
                            <td class="px-6 py-3">@{{ docente.nombre_completo }}</td>
                            <td class="px-6 py-3">@{{ docente.email || '-' }}</td>
                            <td class="px-6 py-3">@{{ docente.especialidad || '-' }}</td>
                            <td class="px-6 py-3">
                                <span :class="docente.estado === 'Activo' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                    @{{ docente.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <button @click="editarDocente(docente)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-2">
                                    Editar
                                </button>
                                <button @click="eliminarDocente(docente.id_docente)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="docentes.length === 0" class="text-center py-8 text-gray-500">
                    No se encontraron docentes
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
                    docentes: [],
                    showForm: false,
                    editingId: null,
                    loading: false,
                    searchTerm: '',
                    selectedState: '',
                    formData: {
                        ci_persona: '',
                        nombre_completo: '',
                        email: '',
                        telefono: '',
                        especialidad: '',
                        estado: 'Activo',
                        observaciones: ''
                    }
                };
            },
            computed: {
                filteredDocentes() {
                    return this.docentes.filter(docente => {
                        const matchesSearch = (docente.ci_persona || '').includes(this.searchTerm) || 
                                            (docente.nombre_completo || '').toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                                            (docente.especialidad || '').toLowerCase().includes(this.searchTerm.toLowerCase());
                        const matchesState = this.selectedState === '' || docente.estado === this.selectedState;
                        return matchesSearch && matchesState;
                    });
                }
            },
            mounted() {
                this.cargarDocentes();
            },
            methods: {
                async cargarDocentes() {
                    try {
                        this.loading = true;
                        const token = localStorage.getItem('token');
                        const response = await axios.get('http://127.0.0.1:8000/api/docentes', {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            }
                        });
                        this.docentes = response.data;
                    } catch (error) {
                        console.error('Error al cargar docentes:', error);
                        // Si no hay datos, mostrar lista vacía
                        this.docentes = [];
                    } finally {
                        this.loading = false;
                    }
                },
                async guardarDocente() {
                    try {
                        if (!this.formData.ci_persona.trim() || !this.formData.nombre_completo.trim()) {
                            alert('CI y Nombre completo son requeridos');
                            return;
                        }

                        const token = localStorage.getItem('token');
                        
                        if (this.editingId) {
                            const response = await axios.put(`http://127.0.0.1:8000/api/docentes/${this.editingId}`, this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            
                            const index = this.docentes.findIndex(d => d.id_docente === this.editingId);
                            if (index !== -1) {
                                this.docentes[index] = response.data.docente || response.data;
                            }
                            alert('Docente actualizado exitosamente');
                        } else {
                            const response = await axios.post('http://127.0.0.1:8000/api/docentes', this.formData, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                            this.docentes.push(response.data.docente || response.data);
                            alert('Docente creado exitosamente');
                        }
                        
                        this.cancelarFormulario();
                    } catch (error) {
                        console.error('Error al guardar docente:', error);
                        alert('Error al guardar el docente: ' + (error.response?.data?.message || error.message));
                    }
                },
                async eliminarDocente(id) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este docente?')) return;
                    
                    try {
                        const token = localStorage.getItem('token');
                        await axios.delete(`http://127.0.0.1:8000/api/docentes/${id}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        this.docentes = this.docentes.filter(d => d.id_docente !== id);
                        alert('Docente eliminado exitosamente');
                    } catch (error) {
                        console.error('Error al eliminar docente:', error);
                        alert('Error al eliminar el docente');
                    }
                },
                editarDocente(docente) {
                    this.editingId = docente.id_docente;
                    this.formData = { 
                        ci_persona: docente.ci_persona || '',
                        nombre_completo: docente.nombre_completo || '',
                        email: docente.email || '',
                        telefono: docente.telefono || '',
                        especialidad: docente.especialidad || '',
                        estado: docente.estado || 'Activo',
                        observaciones: docente.observaciones || ''
                    };
                    this.showForm = true;
                },
                cancelarFormulario() {
                    this.formData = {
                        ci_persona: '',
                        nombre_completo: '',
                        email: '',
                        telefono: '',
                        especialidad: '',
                        estado: 'Activo',
                        observaciones: ''
                    };
                    this.editingId = null;
                    this.showForm = false;
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
