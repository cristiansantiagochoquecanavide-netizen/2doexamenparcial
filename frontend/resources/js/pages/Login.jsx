import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

function Login() {
    const [ciPersona, setCiPersona] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const { login, isAuthenticated } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        if (isAuthenticated) {
            navigate('/', { replace: true });
        }
    }, [isAuthenticated, navigate]);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setLoading(true);

        const result = await login(ciPersona, password);

        if (result.success) {
            setTimeout(() => navigate('/', { replace: true }), 150);
        } else {
            setError(result.message);
        }

        setLoading(false);
    };

    return (
        <div className="min-h-screen bg-slate-950 text-white">
            <div className="relative min-h-screen flex flex-col lg:flex-row">
                {/* Backdrop glow */}
                <div className="absolute inset-0 bg-gradient-to-br from-orange-500/20 via-rose-500/20 to-amber-500/20 blur-3xl opacity-60 pointer-events-none" />

                {/* Left hero */}
                <section className="relative flex-1 hidden lg:flex flex-col overflow-hidden p-12 text-white">
                    <div className="absolute inset-0 bg-gradient-to-br from-orange-600 via-rose-600 to-amber-500 opacity-90 rounded-r-[4rem] blur-xl" />
                    <div className="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="h-12 w-12 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center">
                                    <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <p className="uppercase tracking-widest text-xs text-white/80">Universidad Boliviana</p>
                                    <h1 className="text-2xl font-bold">Sistema de Carga Horaria</h1>
                                </div>
                            </div>
                            <p className="text-lg text-white/90 max-w-md">
                                Visualiza y gestiona la planificación académica, asistencias y monitoreo institucional desde una sola plataforma.
                            </p>
                        </div>

                        <div className="space-y-4">
                            <div className="bg-white/10 backdrop-blur rounded-3xl p-6 border border-white/20">
                                <p className="text-sm text-white/70">Panel Ejecutivo</p>
                                <h3 className="text-3xl font-semibold">+ 150</h3>
                                <p className="text-sm text-white/70">Docentes coordinados diariamente</p>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                {[['Asistencias', 'Automatiza el registro'], ['Monitoreo', 'Alertas en tiempo real']].map(([title, description]) => (
                                    <div key={title} className="bg-white/10 rounded-2xl p-4 border border-white/10">
                                        <p className="text-sm text-white/70">{title}</p>
                                        <p className="text-base font-semibold">{description}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Right form */}
                <section className="relative flex-1 flex items-center justify-center px-6 py-12 lg:px-16">
                    <div className="w-full max-w-md bg-slate-900/80 border border-white/5 rounded-3xl shadow-2xl backdrop-blur-xl p-8 space-y-8">
                        <div>
                            <p className="text-sm uppercase tracking-[0.4em] text-orange-300">Bienvenido</p>
                            <h2 className="text-3xl font-semibold mt-2">Inicia sesión</h2>
                            <p className="text-sm text-white/60 mt-1">Ingresa tus credenciales para acceder al panel administrativo.</p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div className="space-y-2">
                                <label htmlFor="ci_persona" className="text-sm font-medium text-white/80">
                                    Cédula de Identidad
                                </label>
                                <div className="relative">
                                    <span className="absolute left-4 top-1/2 -translate-y-1/2 text-orange-300">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                    <input
                                        id="ci_persona"
                                        type="text"
                                        value={ciPersona}
                                        onChange={(event) => setCiPersona(event.target.value)}
                                        placeholder="Ej. 12345678"
                                        className="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label htmlFor="password" className="text-sm font-medium text-white/80">
                                    Contraseña
                                </label>
                                <div className="relative">
                                    <span className="absolute left-4 top-1/2 -translate-y-1/2 text-orange-300">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5.121 17.804A8.966 8.966 0 0112 15c2.357 0 4.5.906 6.121 2.381M15 11a3 3 0 013 3v2" />
                                        </svg>
                                    </span>
                                    <input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        placeholder="Ingresa tu contraseña"
                                        className="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-12 pr-12 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all"
                                        required
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword((prev) => !prev)}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white/80 transition"
                                    >
                                        {showPassword ? 'Ocultar' : 'Ver'}
                                    </button>
                                </div>
                            </div>

                            {error && (
                                <div className="flex items-center space-x-3 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{error}</span>
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-orange-500 to-rose-500 py-3 font-semibold text-white shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition disabled:opacity-60"
                            >
                                {loading ? (
                                    <>
                                        <svg className="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                        </svg>
                                        Iniciando sesión...
                                    </>
                                ) : (
                                    <>
                                        <span>Ingresar</span>
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                    </>
                                )}
                            </button>
                        </form>

                        <p className="text-xs text-white/40 text-center">
                            © 2025 Sistema de Carga Horaria. Todos los derechos reservados.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    );
}

export default Login;
