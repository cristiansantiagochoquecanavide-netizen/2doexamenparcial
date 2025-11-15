<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Carga Horaria</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h1 class="text-xl font-bold">Sistema de Carga Horaria</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm">{{ Auth::user()->persona->nombre ?? 'Usuario' }}</span>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card: Gestión de Roles -->
                <a href="{{ route('dashboard.roles') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Roles</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra los roles del sistema</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                </a>

                <!-- Card: Gestión de Materias -->
                <a href="{{ route('dashboard.materias') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Materias</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra las materias académicas</p>
                        </div>
                        <svg class="w-12 h-12 text-green-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </a>

                <!-- Card: Gestión de Grupos -->
                <a href="{{ route('dashboard.grupos') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Grupos</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra los grupos estudiantiles</p>
                        </div>
                        <svg class="w-12 h-12 text-purple-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM4.318 20H3v-2a6 6 0 019.182-5.437M9 10a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </a>

                <!-- Card: Gestión de Aulas -->
                <a href="{{ route('dashboard.aulas') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Aulas</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra las aulas disponibles</p>
                        </div>
                        <svg class="w-12 h-12 text-yellow-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </a>

                <!-- Card: Gestión de Infraestructura -->
                <a href="{{ route('dashboard.infraestructura') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Infraestructura</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra recursos e infraestructura</p>
                        </div>
                        <svg class="w-12 h-12 text-indigo-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0H9m6 0h2" />
                        </svg>
                    </div>
                </a>

                <!-- Card: Gestión de Horarios -->
                <a href="{{ route('dashboard.horarios') }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 border-l-4 border-pink-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Gestión de Horarios</h3>
                            <p class="text-gray-600 text-sm mt-1">Administra los horarios académicos</p>
                        </div>
                        <svg class="w-12 h-12 text-pink-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    usuario: null
                };
            },
            mounted() {
                // Obtener usuario del localStorage
                const userStr = localStorage.getItem('user');
                if (userStr) {
                    this.usuario = JSON.parse(userStr);
                }
                
                // Si no hay usuario, redirigir al login
                if (!this.usuario) {
                    window.location.href = 'http://127.0.0.1:8000/login';
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
