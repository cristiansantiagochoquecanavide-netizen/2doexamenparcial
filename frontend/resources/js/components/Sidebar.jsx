import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const menuModules = [
    {
        id: 0,
        name: 'Dashboard',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4h4" />
            </svg>
        ),
        items: [
            { name: 'Inicio', path: '/' }
        ],
        visibleForRoles: ['all']
    },
    {
        id: 1,
        name: 'Autenticación y Control de Acceso',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        ),
        items: [
            { name: 'CU3 - Gestionar Usuarios', path: '/usuarios', cu: 'CU3', visibleForRoles: ['Administrador'] },
            { name: 'CU4 - Gestionar Roles', path: '/roles', cu: 'CU4', visibleForRoles: ['Administrador'] }
        ],
        visibleForRoles: ['Administrador']
    },
    {
        id: 2,
        name: 'Gestión de Catálogos Académicos',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        ),
        items: [
            { name: 'CU5 - Gestionar Docentes', path: '/docentes', cu: 'CU5', visibleForRoles: ['Administrador'] },
            { name: 'CU6 - Gestionar Materias', path: '/materias', cu: 'CU6', visibleForRoles: ['Coordinador Académico', 'Administrador'] },
            { name: 'CU7 - Gestionar Grupos', path: '/grupos', cu: 'CU7', visibleForRoles: ['Coordinador Académico', 'Administrador'] },
            { name: 'CU8 - Gestionar Aulas', path: '/aulas', cu: 'CU8', visibleForRoles: ['Administrador'] },
            { name: 'CU9 - Gestionar Infraestructura', path: '/infraestructura', cu: 'CU9', visibleForRoles: ['Administrador'] }
        ],
        visibleForRoles: ['Coordinador Académico', 'Administrador']
    },
    {
        id: 3,
        name: 'Planificación Académica',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        ),
        items: [
            { name: 'CU10 - Configurar Malla Horaria', path: '/configuracion-malla', cu: 'CU10', visibleForRoles: ['Coordinador Académico', 'Administrador'] },
            { name: 'CU11 - Asignar Carga Horaria', path: '/carga-horaria', cu: 'CU11', visibleForRoles: ['Coordinador Académico', 'Administrador'] },
            { name: 'CU12 - Gestionar Conflictos', path: '/conflictos-horarios', cu: 'CU12', visibleForRoles: ['Coordinador Académico', 'Administrador'] },
            { name: 'CU13 - Consultar disponibilidad de aulas', path: '/disponibilidad-aulas', cu: 'CU13', visibleForRoles: ['Coordinador Académico', 'Administrador'] }
        ],
        visibleForRoles: ['Coordinador Académico', 'Administrador']
    },
    {
        id: 4,
        name: 'Asistencia Docente',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
        items: [
            { name: 'CU14 - Registrar Asistencia', path: '/asistencias', cu: 'CU14' },
            { name: 'CU15 - Gestionar Inasistencias y Justificaciones', path: '/gestionar-inasistencias', cu: 'CU15' }
        ],
        visibleForRoles: ['Administrador', 'Coordinador Académico']
    },
    {
        id: 6,
        name: 'Auditoría y Trazabilidad',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        ),
        items: [
            { name: 'CU18 - Registrar Bitácora', path: '/bitacora', cu: 'CU18' }
        ],
        visibleForRoles: ['Administrador']
    },
    {
        id: 7,
        name: 'Monitoreo y Reportes',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        ),
        items: [
            { name: 'CU16 - Visualizar Dashboard', path: '/monitoreo', cu: 'CU16' },
            { name: 'CU17 - Generar Reportes', path: '/monitoreo', cu: 'CU17' }
        ],
        visibleForRoles: ['Administrador', 'Coordinador Académico']
    }
];

function Sidebar({ isOpen, onClose }) {
    const [openModules, setOpenModules] = useState([0, 1, 4]); // Dashboard, Planificación y Asistencia abiertos por defecto
    const location = useLocation();
    const { isCoordinador, user } = useAuth();

    const toggleModule = (moduleId) => {
        setOpenModules(prev => 
            prev.includes(moduleId) 
                ? prev.filter(id => id !== moduleId)
                : [...prev, moduleId]
        );
    };

    const isActive = (path) => location.pathname === path;

    // Determinar el rol actual
    const userRole = user?.rol?.nombre || 'Usuario';

    // Log temporal para debug
    if (user) {
        console.log('DEBUG Sidebar:', {
            userFullName: user.nombre_persona,
            userRole: userRole,
            userRolObject: user.rol,
            allUserData: user
        });
    }

    // Filtrar módulos visibles según el rol
    const canViewModule = (module) => {
        if (module.visibleForRoles.includes('all')) return true;
        return module.visibleForRoles.includes(userRole);
    };

    // Filtrar items del módulo según el rol
    const canViewItem = (item) => {
        if (!item.visibleForRoles) return true; // Si no especifica, mostrar a todos
        return item.visibleForRoles.includes(userRole);
    };

    return (
        <>
            {/* Overlay para móviles */}
            {isOpen && (
                <div 
                    className="fixed inset-0 bg-black/50 z-40 lg:hidden"
                    onClick={onClose}
                ></div>
            )}

            {/* Sidebar */}
            <aside
                className={`fixed top-16 left-0 bottom-0 w-72 bg-white shadow-2xl transform transition-transform duration-300 z-40 overflow-y-auto ${
                    isOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <nav className="p-4 space-y-2">
                    {menuModules.filter(canViewModule).map((module) => (
                        <div key={module.id} className="border-b border-gray-100 pb-2">
                            {/* Module Header */}
                            <button
                                onClick={() => toggleModule(module.id)}
                                className="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 transition duration-200 group"
                            >
                                <div className="flex items-center space-x-3">
                                    <div className="text-orange-500 group-hover:text-red-600 transition duration-200">
                                        {module.icon}
                                    </div>
                                    <span className="font-semibold text-gray-700 text-sm group-hover:text-orange-600 transition duration-200">
                                        {module.name}
                                    </span>
                                </div>
                                <svg
                                    className={`w-5 h-5 text-gray-400 transition-transform duration-200 ${
                                        openModules.includes(module.id) ? 'rotate-180' : ''
                                    }`}
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {/* Module Items */}
                            {openModules.includes(module.id) && (
                                <div className="mt-2 ml-8 space-y-1">
                                    {module.items.filter(item => canViewItem(item)).map((item, index) => (
                                        <Link
                                            key={index}
                                            to={item.path}
                                            onClick={onClose}
                                            className={`block px-4 py-2 rounded-lg text-sm transition duration-200 flex items-center space-x-2 ${
                                                isActive(item.path)
                                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold shadow-md'
                                                    : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600'
                                            }`}
                                        >
                                            {item.cu && (
                                                <span className={`text-xs font-bold px-2 py-0.5 rounded ${
                                                    isActive(item.path) 
                                                        ? 'bg-white/20' 
                                                        : 'bg-orange-100 text-orange-700'
                                                }`}>
                                                    {item.cu}
                                                </span>
                                            )}
                                            <span>{item.name}</span>
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}
                </nav>

                {/* Footer del Sidebar */}
                <div className="p-4 border-t border-gray-200">
                    <div className="bg-gradient-to-r from-orange-50 to-red-50 p-3 rounded-lg">
                        <p className="text-xs text-gray-600 text-center">
                            Sistema de Carga Horaria v1.0
                        </p>
                        <p className="text-xs text-gray-500 text-center mt-1">
                            © 2025
                        </p>
                    </div>
                </div>
            </aside>
        </>
    );
}

export default Sidebar;
