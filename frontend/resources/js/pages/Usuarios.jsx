import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import UsuarioForm from '../components/UsuarioForm';

// Función helper para obtener nombre completo
const getNombreCompleto = (usuario) => {
    if (usuario.nombre_completo && usuario.nombre_completo.trim()) {
        return usuario.nombre_completo;
    }
    
    const nombre = usuario.persona?.nombre || '';
    const apellidoPaterno = usuario.persona?.apellido_paterno || '';
    const apellidoMaterno = usuario.persona?.apellido_materno || '';
    
    const nombreCompleto = [nombre, apellidoPaterno, apellidoMaterno]
        .filter(part => part && part.trim())
        .join(' ');
    
    return nombreCompleto || 'Sin nombre';
};

function Usuarios() {
    const [usuarios, setUsuarios] = useState([]);
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingUsuario, setEditingUsuario] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterRol, setFilterRol] = useState('');
    const [filterEstado, setFilterEstado] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    useEffect(() => {
        setCurrentPage(1); // Resetear a página 1 cuando cambian los filtros
    }, [filterRol, filterEstado]);

    useEffect(() => {
        fetchUsuarios();
        fetchRoles();
    }, [currentPage, filterRol, filterEstado]);

    const fetchUsuarios = async () => {
        console.log('🔄 fetchUsuarios llamado');
        try {
            setLoading(true);
            const params = {
                page: currentPage,
                ...(filterRol && { id_rol: filterRol })
            };
            console.log('📤 Parámetros de petición:', params);
            console.log('📡 URL completa:', import.meta.env.VITE_API_URL + '/usuarios');
            
            const response = await api.get('/usuarios', { params });
            
            console.log('✅ Response completa:', response);
            console.log('✅ Response.data:', response.data);
            console.log('✅ Response.data.data:', response.data.data);
            console.log('✅ Cantidad de usuarios recibidos:', response.data.data?.length);
            
            setUsuarios(response.data.data || []);
            setTotalPages(response.data.last_page || 1);
            
            console.log('✅ Estado actualizado - usuarios:', response.data.data?.length);
        } catch (error) {
            console.error('❌ Error al cargar usuarios:', error);
            console.error('❌ Error details:', error.response);
            console.error('❌ Error message:', error.message);
            alert('ERROR: ' + (error.response?.data?.message || error.message));
            setUsuarios([]);
            setTotalPages(1);
        } finally {
            setLoading(false);
            console.log('✅ Loading = false');
        }
    };

    const fetchRoles = async () => {
        try {
            const response = await api.get('/roles');
            console.log('📋 Roles response:', response.data);
            
            // Asegurarse de que roles sea un array
            let rolesData = response.data.data || response.data;
            if (!Array.isArray(rolesData)) {
                console.warn('⚠️ Roles no es un array:', rolesData);
                rolesData = [];
            }
            
            setRoles(rolesData);
            console.log('✅ Roles cargados:', rolesData.length);
        } catch (error) {
            console.error('Error al cargar roles:', error);
            setRoles([]);
        }
    };

    const handleCreate = () => {
        setEditingUsuario(null);
        setShowModal(true);
    };

    const handleEdit = (usuario) => {
        setEditingUsuario(usuario);
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (!confirm('¿Está seguro de eliminar este usuario?')) return;

        try {
            console.log('🗑️ Intentando eliminar usuario:', id);
            const response = await api.delete(`/usuarios/${id}`);
            console.log('✅ Respuesta de eliminación:', response);
            alert('Usuario eliminado exitosamente');
            fetchUsuarios();
        } catch (error) {
            console.error('❌ Error al eliminar usuario:', error);
            console.error('❌ Error response:', error.response);
            console.error('❌ Error response data:', error.response?.data);
            const errorMsg = error.response?.data?.message || error.message || 'Error al eliminar usuario';
            alert('Error al eliminar usuario: ' + errorMsg);
        }
    };

    const handleToggleEstado = async (usuario) => {
        try {
            // Convertir estado a booleano si viene como string
            const estadoActual = typeof usuario.estado === 'string' 
                ? usuario.estado === 'A' 
                : usuario.estado !== false;
            
            await api.patch(`/usuarios/${usuario.id_usuario}`, {
                estado: !estadoActual  // true = Activo, false = Inactivo
            });
            alert('Estado actualizado exitosamente');
            fetchUsuarios();
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            alert('Error al cambiar estado');
        }
    };

    const handleSave = async (data) => {
        try {
            console.log('💾 Guardando usuario:', { editingUsuario, data });
            
            if (editingUsuario) {
                console.log('📝 Actualizando usuario ID:', editingUsuario.id_usuario);
                const response = await api.put(`/usuarios/${editingUsuario.id_usuario}`, data);
                console.log('✅ Respuesta de actualización:', response);
                alert('Usuario actualizado exitosamente');
            } else {
                console.log('➕ Creando nuevo usuario');
                const response = await api.post('/usuarios', data);
                console.log('✅ Respuesta de creación:', response);
                alert('Usuario creado exitosamente');
            }
            setShowModal(false);
            fetchUsuarios();
        } catch (error) {
            console.error('❌ Error al guardar usuario:', error);
            console.error('❌ Error response:', error.response);
            console.error('❌ Error data:', error.response?.data);
            alert('Error al guardar usuario: ' + (error.response?.data?.message || error.message));
            throw error;
        }
    };

    const filteredUsuarios = usuarios.filter(usuario => {
        const searchLower = searchTerm.toLowerCase();
        const matchesSearch = 
            usuario.ci_persona?.toLowerCase().includes(searchLower) ||
            usuario.ci?.toLowerCase().includes(searchLower) ||
            usuario.nombre_completo?.toLowerCase().includes(searchLower) ||
            usuario.persona?.nombre?.toLowerCase().includes(searchLower) ||
            usuario.persona?.apellido_paterno?.toLowerCase().includes(searchLower);
        
        // Filtro de rol
        let matchesRol = true;
        if (filterRol) {
            matchesRol = usuario.id_rol === parseInt(filterRol);
        }
        
        // Filtro de estado
        let matchesEstado = true;
        if (filterEstado === 'A') {
            matchesEstado = usuario.estado === true;
        } else if (filterEstado === 'I') {
            matchesEstado = usuario.estado === false;
        }
        
        return matchesSearch && matchesRol && matchesEstado;
    });

    console.log('Total usuarios cargados:', usuarios.length);
    console.log('Usuarios filtrados:', filteredUsuarios.length);
    console.log('Filtros activos:', { searchTerm, filterRol, filterEstado });

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-500">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-800 flex items-center">
                            <svg className="w-8 h-8 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Gestión de Usuarios
                        </h1>
                        <p className="text-gray-600 mt-1">Administra los usuarios del sistema</p>
                    </div>
                    <button
                        onClick={handleCreate}
                        className="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition duration-200 shadow-lg hover:shadow-xl"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        <span className="font-semibold">Nuevo Usuario</span>
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-xl shadow-lg p-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <div className="relative">
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder="Buscar por CI o nombre..."
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            />
                            <svg className="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Filtrar por Rol</label>
                        <select
                            value={filterRol}
                            onChange={(e) => setFilterRol(e.target.value)}
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                            <option value="">Todos los roles</option>
                            {Array.isArray(roles) && roles.map(rol => (
                                <option key={rol.id_rol} value={rol.id_rol}>{rol.nombre}</option>
                            ))}
                        </select>
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

            {/* Table */}
            <div className="bg-white rounded-xl shadow-lg overflow-hidden">
                {loading ? (
                    <div className="flex justify-center items-center py-12">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-4 border-orange-500"></div>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-sm font-semibold">ID</th>
                                        <th className="px-6 py-4 text-left text-sm font-semibold">CI</th>
                                        <th className="px-6 py-4 text-left text-sm font-semibold">Nombre Completo</th>
                                        <th className="px-6 py-4 text-left text-sm font-semibold">Rol</th>
                                        <th className="px-6 py-4 text-left text-sm font-semibold">Estado</th>
                                        <th className="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {filteredUsuarios.length === 0 ? (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-8 text-center text-gray-500">
                                                No se encontraron usuarios
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredUsuarios.map((usuario, index) => (
                                            <tr key={usuario.id_usuario} className={`hover:bg-orange-50 transition duration-150 ${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}`}>
                                                <td className="px-6 py-4 text-sm font-semibold text-gray-600">{usuario.id_usuario}</td>
                                                <td className="px-6 py-4 text-sm font-medium text-gray-900">{usuario.ci_persona || usuario.ci || '-'}</td>
                                                <td className="px-6 py-4 text-sm text-gray-900">
                                                    {getNombreCompleto(usuario)}
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    <span className="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                        {usuario.rol?.nombre}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    <button
                                                        onClick={() => handleToggleEstado(usuario)}
                                                        className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                                            usuario.estado === true || usuario.estado === 'A' || usuario.estado === 'Activo'
                                                                ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                                                : 'bg-red-100 text-red-800 hover:bg-red-200'
                                                        } transition duration-200`}
                                                    >
                                                        {usuario.estado === true || usuario.estado === 'A' || usuario.estado === 'Activo' ? 'Activo' : 'Inactivo'}
                                                    </button>
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    <div className="flex justify-center space-x-2">
                                                        <button
                                                            onClick={() => handleEdit(usuario)}
                                                            className="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200"
                                                            title="Editar"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(usuario.id_usuario)}
                                                            className="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200"
                                                            title="Eliminar"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                                <button
                                    onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                                    disabled={currentPage === 1}
                                    className="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <span className="text-sm text-gray-700">
                                    Página {currentPage} de {totalPages}
                                </span>
                                <button
                                    onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                                    disabled={currentPage === totalPages}
                                    className="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>

            {/* Modal */}
            {showModal && (
                <UsuarioForm
                    usuario={editingUsuario}
                    roles={roles}
                    onClose={() => setShowModal(false)}
                    onSave={handleSave}
                />
            )}
        </div>
    );
}

export default Usuarios;
