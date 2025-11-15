import React, { useState, useEffect } from 'react';
import api from '../utils/api';

function UsuarioForm({ usuario, roles, onClose, onSave }) {
    const [formData, setFormData] = useState({
        ci: '',
        contrasena: '',
        confirmar_contrasena: '',
        id_rol: '',
        estado: true, // true = Activo, false = Inactivo
        // Datos de persona
        nombre_completo: '',
        fecha_nacimiento: '',
        sexo: 'M',
        telefono: '',
        email: '',
        direccion: ''
    });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (usuario) {
            // Convertir estado a booleano si viene de la BD
            const estadoValue = typeof usuario.estado === 'string' 
                ? usuario.estado === 'A' 
                : usuario.estado !== false; // true si no es false
            
            // Obtener nombre completo del campo nombre en persona
            const nombreCompleto = usuario.persona?.nombre || '';
            
            setFormData({
                ci: usuario.ci_persona || usuario.ci || '',
                contrasena: '',
                confirmar_contrasena: '',
                id_rol: usuario.id_rol || '',
                estado: estadoValue, // true = Activo, false = Inactivo
                nombre_completo: nombreCompleto,
                fecha_nacimiento: usuario.persona?.fecha_nacimiento || '',
                sexo: usuario.persona?.sexo || 'M',
                telefono: usuario.persona?.telefono || '',
                email: usuario.email || usuario.persona?.email || '',
                direccion: usuario.persona?.direccion || ''
            });
        }
    }, [usuario]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: '' }));
        }
    };

    const validateForm = () => {
        const newErrors = {};

        // CI solo es requerido si es creación (nuevo usuario)
        if (!usuario && !formData.ci.trim()) newErrors.ci = 'El CI es requerido';
        if (!usuario && !formData.contrasena) newErrors.contrasena = 'La contraseña es requerida';
        if (formData.contrasena && formData.contrasena !== formData.confirmar_contrasena) {
            newErrors.confirmar_contrasena = 'Las contraseñas no coinciden';
        }
        if (!formData.id_rol) newErrors.id_rol = 'El rol es requerido';
        if (!formData.nombre_completo.trim()) newErrors.nombre_completo = 'El nombre completo es requerido';
        if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = 'Email inválido';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) return;

        setLoading(true);
        try {
            const dataToSend = { ...formData };
            if (!dataToSend.contrasena) {
                delete dataToSend.contrasena;
                delete dataToSend.confirmar_contrasena;
            }
            await onSave(dataToSend);
        } catch (error) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            } else {
                alert(error.response?.data?.message || 'Error al guardar usuario');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                {/* Header */}
                <div className="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                    <h2 className="text-2xl font-bold flex items-center">
                        <svg className="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {usuario ? 'Editar Usuario' : 'Nuevo Usuario'}
                    </h2>
                    <button
                        onClick={onClose}
                        className="p-2 hover:bg-white/20 rounded-lg transition duration-200"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="p-6 space-y-6">
                    {/* Datos de Cuenta */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg className="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Datos de Cuenta
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    CI <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="ci"
                                    value={formData.ci}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.ci ? 'border-red-500' : 'border-gray-300'}`}
                                    disabled={!!usuario}
                                />
                                {errors.ci && <p className="text-red-500 text-xs mt-1">{errors.ci}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Rol <span className="text-red-500">*</span>
                                </label>
                                <select
                                    name="id_rol"
                                    value={formData.id_rol}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.id_rol ? 'border-red-500' : 'border-gray-300'}`}
                                >
                                    <option value="">Seleccione un rol</option>
                                    {roles.map(rol => (
                                        <option key={rol.id_rol} value={rol.id_rol}>{rol.nombre}</option>
                                    ))}
                                </select>
                                {errors.id_rol && <p className="text-red-500 text-xs mt-1">{errors.id_rol}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    {usuario ? 'Nueva Contraseña' : 'Contraseña'} {!usuario && <span className="text-red-500">*</span>}
                                </label>
                                <input
                                    type="password"
                                    name="contrasena"
                                    value={formData.contrasena}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.contrasena ? 'border-red-500' : 'border-gray-300'}`}
                                    placeholder={usuario ? 'Dejar en blanco para no cambiar' : ''}
                                />
                                {errors.contrasena && <p className="text-red-500 text-xs mt-1">{errors.contrasena}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Confirmar Contraseña {!usuario && <span className="text-red-500">*</span>}
                                </label>
                                <input
                                    type="password"
                                    name="confirmar_contrasena"
                                    value={formData.confirmar_contrasena}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.confirmar_contrasena ? 'border-red-500' : 'border-gray-300'}`}
                                />
                                {errors.confirmar_contrasena && <p className="text-red-500 text-xs mt-1">{errors.confirmar_contrasena}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select
                                    name="estado"
                                    value={formData.estado ? 'true' : 'false'}
                                    onChange={(e) => handleChange({ target: { name: 'estado', value: e.target.value === 'true' } })}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                                >
                                    <option value="true">Activo</option>
                                    <option value="false">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Datos Personales */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg className="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Datos Personales
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="md:col-span-1">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Completo <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="nombre_completo"
                                    value={formData.nombre_completo}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.nombre_completo ? 'border-red-500' : 'border-gray-300'}`}
                                />
                                {errors.nombre_completo && <p className="text-red-500 text-xs mt-1">{errors.nombre_completo}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento</label>
                                <input
                                    type="date"
                                    name="fecha_nacimiento"
                                    value={formData.fecha_nacimiento}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                                <select
                                    name="sexo"
                                    value={formData.sexo}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                                >
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input
                                    type="text"
                                    name="telefono"
                                    value={formData.telefono}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                                />
                            </div>

                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 ${errors.email ? 'border-red-500' : 'border-gray-300'}`}
                                />
                                {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
                            </div>

                            <div className="md:col-span-3">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                                <textarea
                                    name="direccion"
                                    value={formData.direccion}
                                    onChange={handleChange}
                                    rows="2"
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Buttons */}
                    <div className="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-6 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition duration-200 disabled:opacity-50 flex items-center space-x-2"
                        >
                            {loading ? (
                                <>
                                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Guardar</span>
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default UsuarioForm;
