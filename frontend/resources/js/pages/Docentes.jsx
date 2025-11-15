import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import DocenteForm from '../components/DocenteForm';

function Docentes() {
    const [docentes, setDocentes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingDocente, setEditingDocente] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterEstado, setFilterEstado] = useState('');

    useEffect(() => {
        fetchDocentes();
    }, []);

    const fetchDocentes = async () => {
        try {
            setLoading(true);
            const response = await api.get('/docentes');
            setDocentes(response.data.data || response.data);
        } catch (error) {
            console.error('Error al cargar docentes:', error);
            alert('Error al cargar docentes');
        } finally {
            setLoading(false);
        }
    };

    // Permitir crear/editar/eliminar solo si es Administrador o Coordinador Académico
    const user = JSON.parse(localStorage.getItem('user'));
    const isAdminOrCoord = user?.rol?.nombre === 'Administrador' || user?.rol?.nombre === 'Coordinador Académico';

    const handleCreate = () => {
        if (!isAdminOrCoord) return;
        setEditingDocente(null);
        setShowModal(true);
    };

    const handleEdit = (docente) => {
        if (!isAdminOrCoord) return;
        setEditingDocente(docente);
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (!isAdminOrCoord) return;
        if (!confirm('¿Está seguro de eliminar este docente?')) return;
        try {
            await api.delete(`/docentes/${id}`);
            alert('Docente eliminado exitosamente');
            fetchDocentes();
        } catch (error) {
            console.error('Error al eliminar docente:', error);
            alert(error.response?.data?.message || 'Error al eliminar docente');
        }
    };

    const handleToggleEstado = async (docente) => {
        if (!isAdminOrCoord) return;
        try {
            await api.patch(`/docentes/${docente.codigo_doc}`, {
                estado: docente.usuario?.estado ? 'I' : 'A'
            });
            alert('Estado actualizado exitosamente');
            fetchDocentes();
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            alert('Error al cambiar estado');
        }
    };

    const handleSave = async (data) => {
        if (!isAdminOrCoord) return;
        try {
            if (editingDocente) {
                await api.put(`/docentes/${editingDocente.codigo_doc}`, data);
                alert('Docente actualizado exitosamente');
            } else {
                await api.post('/docentes', data);
                alert('Docente creado exitosamente');
            }
            setShowModal(false);
            fetchDocentes();
        } catch (error) {
            console.error('Error al guardar docente:', error);
            throw error;
        }
    };

    const filteredDocentes = docentes.filter(docente => {
        const searchLower = searchTerm.toLowerCase();
        const persona = docente.usuario?.persona;
        const matchesSearch = 
            persona?.ci?.toLowerCase().includes(searchLower) ||
            persona?.nombre?.toLowerCase().includes(searchLower) ||
            persona?.apellido_paterno?.toLowerCase().includes(searchLower) ||
            docente.titulo?.toLowerCase().includes(searchLower) ||
            docente.codigo_doc?.toString().includes(searchLower);
        
        // Filtro de estado: A = true (Activo), I = false (Inactivo)
        let matchesEstado = true;
        if (filterEstado === 'A') {
            matchesEstado = docente.usuario?.estado === true;
        } else if (filterEstado === 'I') {
            matchesEstado = docente.usuario?.estado === false;
        }
        
        return matchesSearch && matchesEstado;
    });

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-500">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-800 flex items-center">
                            <svg className="w-8 h-8 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Gestión de Docentes
                        </h1>
                        <p className="text-gray-600 mt-1">Administra el personal docente de la institución</p>
                    </div>
                    <button
                        onClick={handleCreate}
                        className="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition duration-200 shadow-lg hover:shadow-xl"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        <span className="font-semibold">Nuevo Docente</span>
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-xl shadow-lg p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <div className="relative">
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder="Buscar por CI, nombre o especialidad..."
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            />
                            <svg className="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Filtrar por Estado</label>
                        <select
                            value={filterEstado}
                            onChange={(e) => setFilterEstado(e.target.value)}
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                            <option value="">Todos los estados</option>
                            <option value="A">Activos</option>
                            <option value="I">Inactivos</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Cards Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {loading ? (
                    <div className="col-span-full flex justify-center items-center py-12">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-4 border-orange-500"></div>
                    </div>
                ) : filteredDocentes.length === 0 ? (
                    <div className="col-span-full text-center py-12">
                        <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p className="mt-4 text-gray-500">No se encontraron docentes</p>
                    </div>
                ) : (
                    filteredDocentes.map((docente) => (
                        <div
                            key={docente.codigo_doc}
                            className="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 overflow-hidden border border-gray-100"
                        >
                            {/* Card Header */}
                            <div className="bg-gradient-to-r from-orange-500 to-red-500 p-4">
                                <div className="flex items-center space-x-3">
                                    <div className="w-14 h-14 bg-white rounded-full flex items-center justify-center">
                                        <svg className="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div className="flex-1 text-white">
                                        <h3 className="font-bold text-lg">
                                            {docente.nombre_completo || `${docente.usuario?.persona?.nombre} ${docente.usuario?.persona?.apellido_paterno}`}
                                        </h3>
                                        <p className="text-sm opacity-90">CI: {docente.usuario?.persona?.ci}</p>
                                        <p className="text-sm opacity-90">Código: {docente.codigo_doc}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Card Body */}
                            <div className="p-4 space-y-3">
                                <div className="flex items-start space-x-2">
                                    <svg className="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p className="text-xs text-gray-500">Especialidad</p>
                                        <p className="text-sm font-semibold text-gray-800">{docente.titulo || 'No especificada'}</p>
                                    </div>
                                </div>

                                <div className="flex items-start space-x-2">
                                    <svg className="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p className="text-xs text-gray-500">Carga Horaria</p>
                                        <p className="text-sm font-semibold text-gray-800">
                                            {docente.carga_horaria_actual || 0} / {docente.carga_horaria_max} horas
                                        </p>
                                        <div className="mt-1 w-full bg-gray-200 rounded-full h-2">
                                            <div
                                                className="bg-gradient-to-r from-orange-400 to-red-500 h-2 rounded-full transition-all duration-300"
                                                style={{
                                                    width: `${((docente.carga_horaria_actual || 0) / docente.carga_horaria_max) * 100}%`
                                                }}
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                {docente.usuario?.persona?.telefono && (
                                    <div className="flex items-start space-x-2">
                                        <svg className="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <div>
                                            <p className="text-xs text-gray-500">Teléfono</p>
                                            <p className="text-sm font-semibold text-gray-800">{docente.usuario?.persona?.telefono}</p>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center justify-between pt-2 border-t border-gray-200">
                                    <button
                                        onClick={() => handleToggleEstado(docente)}
                                        className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                            docente.usuario?.estado
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                : 'bg-red-100 text-red-800 hover:bg-red-200'
                                        } transition duration-200`}
                                    >
                                        {docente.usuario?.estado ? 'Activo' : 'Inactivo'}
                                    </button>

                                    <div className="flex space-x-2">
                                        <button
                                            onClick={() => handleEdit(docente)}
                                            className="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200"
                                            title="Editar"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            onClick={() => handleDelete(docente.codigo_doc)}
                                            className="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200"
                                            title="Eliminar"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Modal */}
            {showModal && (
                <DocenteForm
                    docente={editingDocente}
                    onClose={() => setShowModal(false)}
                    onSave={handleSave}
                />
            )}
        </div>
    );
}

export default Docentes;
