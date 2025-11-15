import '../css/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import Layout from './components/Layout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
// P1: Autenticación y Control de Acceso
import Usuarios from './pages/Usuarios';
import Roles from './pages/Roles';
// P2: Gestión de Catálogos Académicos
import Docentes from './pages/Docentes';
import Materias from './pages/Materias';
import Grupos from './pages/Grupos';
import Aulas from './pages/Aulas';
import Infraestructura from './pages/Infraestructura';
// P3: Planificación Académica
import CargaHoraria from './pages/CargaHoraria';
import ConfiguracionMalla from './pages/ConfiguracionMalla';
// P4: Asistencia Docente
import Asistencias from './pages/Asistencias';
import GestionInasistencias from './pages/GestionInasistencias';
import RegistroAsistencia from './pages/RegistroAsistencia';
import ConflictosHorario from './pages/ConflictosHorario';
import DisponibilidadAulas from './pages/planificacion/DisponibilidadAulas';
// P5: Monitoreo y Reportes
import Monitoreo from './pages/monitoreo/Monitoreo';
// P6: Auditoría y Trazabilidad
import Bitacora from './pages/Bitacora';

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/" element={<Layout />}>
            <Route index element={<Dashboard />} />
            {/* P1: Autenticación y Control de Acceso */}
            <Route path="/usuarios" element={<Usuarios />} />
            <Route path="/roles" element={<Roles />} />
            {/* P2: Gestión de Catálogos Académicos */}
            <Route path="/docentes" element={<Docentes />} />
            <Route path="/materias" element={<Materias />} />
            <Route path="/grupos" element={<Grupos />} />
            <Route path="/aulas" element={<Aulas />} />
            <Route path="/infraestructura" element={<Infraestructura />} />
            {/* P3: Planificación Académica */}
            <Route path="/carga-horaria" element={<CargaHoraria />} />
            <Route path="/configuracion-malla" element={<ConfiguracionMalla />} />
            {/* P4: Asistencia Docente */}
            <Route path="/asistencias" element={<Asistencias />} />
            <Route path="/gestionar-inasistencias" element={<GestionInasistencias />} />
            <Route path="/asistencia/registro/:token" element={<RegistroAsistencia />} />
            {/* CU12: Gestionar Conflictos de Horario */}
            <Route path="/conflictos-horarios" element={<ConflictosHorario />} />
            {/* CU13: Consultar disponibilidad de aulas */}
            <Route path="/disponibilidad-aulas" element={<DisponibilidadAulas />} />
            {/* P5: Monitoreo y Reportes */}
            <Route path="/monitoreo" element={<Monitoreo />} />
            {/* P6: Auditoría y Trazabilidad */}
            <Route path="/bitacora" element={<Bitacora />} />
          </Route>
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}

const rootEl = document.getElementById('root');
if (rootEl) {
  ReactDOM.createRoot(rootEl).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  );
}

