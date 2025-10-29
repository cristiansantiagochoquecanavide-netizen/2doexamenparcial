import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';

const menuModules = [
    {
        id: 1,
        name: 'Autenticación y Control de Acceso',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        ),
        items: [
            { name: 'Usuarios', path: '/usuarios' },
            { name: 'Roles', path: '/roles' }
        ]
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
            { name: 'Docentes', path: '/docentes' },
            { name: 'Materias', path: '/materias' },
            { name: 'Grupos', path: '/grupos' },
            { name: 'Aulas', path: '/aulas' },
            { name: 'Infraestructuras', path: '/infraestructuras' }
        ]
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
            { name: 'Horarios', path: '/horarios' },
            { name: 'Asignaciones', path: '/asignaciones' }
        ]
    },
    {
        id: 4,
        name: 'Asistencia Docente',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        ),
        items: [
            { name: 'Asistencias', path: '/asistencias' }
        ]
    },
    {
        id: 5,
        name: 'Auditoría y Trazabilidad',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        ),
        items: [
            { name: 'Bitácora', path: '/bitacora' }
        ]
    },
    {
        id: 6,
        name: 'Dashboard',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        ),
        items: [
            { name: 'Inicio', path: '/dashboard' }
        ]
    }
];

function Sidebar({ isOpen, onClose }) {
    const [openModules, setOpenModules] = useState([6]); // Dashboard abierto por defecto
    const location = useLocation();

    const toggleModule = (moduleId) => {
        setOpenModules(prev => 
            prev.includes(moduleId) 
                ? prev.filter(id => id !== moduleId)
                : [...prev, moduleId]
        );
    };

    const isActive = (path) => location.pathname === path;

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
                    {menuModules.map((module) => (
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
                                    {module.items.map((item, index) => (
                                        <Link
                                            key={index}
                                            to={item.path}
                                            onClick={onClose}
                                            className={`block px-4 py-2 rounded-lg text-sm transition duration-200 ${
                                                isActive(item.path)
                                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold shadow-md'
                                                    : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600'
                                            }`}
                                        >
                                            {item.name}
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
