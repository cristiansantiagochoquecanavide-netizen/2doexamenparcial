import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../utils/api';
import './Dashboard.css';

const Dashboard = () => {
  const { user, isAuthenticated, isCoordinador } = useAuth();
  const [indicadores, setIndicadores] = useState({
    total_carga_asignada: 0,
    total_asistencias: 0,
    total_conflictos: 0,
  });
  const [cargaPorDocente, setCargaPorDocente] = useState([]);
  const [asistenciaPorPeriodo, setAsistenciaPorPeriodo] = useState([]);
  const [conflictosResumido, setConflictosResumido] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [periodoSeleccionado, setPeriodoSeleccionado] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    cargarDatos();
    cargarPeriodos();
  }, []);

  useEffect(() => {
    if (periodoSeleccionado) {
      cargarDatos();
    }
  }, [periodoSeleccionado]);

  const cargarPeriodos = async () => {
    try {
      const { data } = await api.get('/dashboard/periodos');
      setPeriodos(data.periodos || []);
    } catch (err) {
      console.error('Error al cargar períodos:', err);
    }
  };

  const cargarDatos = async () => {
    setLoading(true);
    setError('');
    try {
      const params = periodoSeleccionado ? { periodo_academico: periodoSeleccionado } : {};
      const { data } = await api.get('/dashboard', { params });

      setIndicadores(data.indicadores);
      setCargaPorDocente(data.carga_por_docente || []);
      setAsistenciaPorPeriodo(data.asistencia_por_periodo || []);
      setConflictosResumido(data.conflictos_resumido || []);
    } catch (err) {
      if (err.response?.status === 403) {
        setError('No tienes permiso para acceder al dashboard.');
      } else {
        setError('Error al cargar los datos del dashboard.');
      }
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  // ==========================================
  // Métodos implementados pero no visibles en UI
  // ==========================================

  /**
   * Obtener KPIs (Control de KPIs)
   * Métricas: Porcentaje de carga, Tasa de asistencia, Resolución de conflictos
   */
  const obtenerKPIs = async () => {
    try {
      const params = periodoSeleccionado ? { periodo_academico: periodoSeleccionado } : {};
      const response = await api.get('/dashboard/kpis', { params });
      console.log('KPIs obtenidos:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error al obtener KPIs:', error);
      return null;
    }
  };

  /**
   * Obtener Coordinación de Horario
   * Información coordinada de asignaciones, docentes, grupos y aulas
   */
  const obtenerCoordinacionHorario = async () => {
    try {
      const params = periodoSeleccionado ? { periodo_academico: periodoSeleccionado } : {};
      const response = await api.get('/dashboard/coordinacion', { params });
      console.log('Coordinación de horario obtenida:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error al obtener coordinación de horario:', error);
      return null;
    }
  };

  /**
   * Obtener Bitácora del Sistema
   * Solo disponible para Administradores
   */
  const obtenerBitacora = async (limite = 100, tipo = null) => {
    try {
      const params = {
        limite: limite
      };
      if (tipo) {
        params.tipo = tipo;
      }
      const response = await api.get('/dashboard/bitacora', { params });
      console.log('Bitácora obtenida:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error al obtener bitácora:', error);
      if (error.response?.status === 403) {
        console.error('No tienes permiso para acceder a la bitácora');
      }
      return null;
    }
  };

  if (error) {
    return (
      <div className="dashboard-container">
        <div className="dashboard-error">
          <strong>Error:</strong> {error}
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return (
      <div className="dashboard-container">
        <div className="dashboard-error">
          <strong>Error:</strong> No estás autenticado. Por favor, inicia sesión.
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="dashboard-container">
        <div className="loading">
          <div className="spinner"></div>
          Cargando indicadores...
        </div>
      </div>
    );
  }

  return (
    <div className="dashboard-container">
      <div className="dashboard-header">
        <h1>CU16 - Visualizar Dashboard</h1>
        <p>Indicadores de planificación, asistencia y conflictos</p>
      </div>

      <div className="dashboard-filtros">
        <label>
          <span>Filtrar por Período Académico:</span>
          <select
            value={periodoSeleccionado}
            onChange={(e) => setPeriodoSeleccionado(e.target.value)}
            className="filtro-select"
          >
            <option value="">Todos los períodos</option>
            {periodos.map((periodo, i) => (
              <option key={i} value={periodo}>
                {periodo}
              </option>
            ))}
          </select>
        </label>
      </div>

      {/* Tarjetas de indicadores */}
      <div className="indicadores-grid">
            <div className="indicador-card carga">
              <div className="indicador-icon">📊</div>
              <div className="indicador-content">
                <h3>Carga Asignada</h3>
                <p className="indicador-valor">{indicadores.total_carga_asignada}</p>
                <span className="indicador-label">asignaciones activas</span>
              </div>
            </div>

            <div className="indicador-card asistencia">
              <div className="indicador-icon">✅</div>
              <div className="indicador-content">
                <h3>Asistencias</h3>
                <p className="indicador-valor">{indicadores.total_asistencias}</p>
                <span className="indicador-label">registradas</span>
              </div>
            </div>

            <div className="indicador-card conflictos">
              <div className="indicador-icon">⚠️</div>
              <div className="indicador-content">
                <h3>Conflictos</h3>
                <p className="indicador-valor">{indicadores.total_conflictos}</p>
                <span className="indicador-label">detectados</span>
              </div>
            </div>
          </div>

          {/* Sección de gráficos y detalles */}
          <div className="dashboard-grid">
            {/* Carga por docente */}
            <div className="dashboard-card">
              <h2>Asignaciones de Carga Horaria</h2>
              {cargaPorDocente.length > 0 ? (
                <table className="dashboard-table">
                  <thead>
                    <tr>
                      <th>ID Asignación</th>
                      <th>Código Docente</th>
                      <th>Docente</th>
                      <th>Estado</th>
                      <th>Período</th>
                    </tr>
                  </thead>
                  <tbody>
                    {cargaPorDocente.map((item, i) => (
                      <tr key={i}>
                        <td className="font-bold">{item.id_asignacion}</td>
                        <td>{item.codigo_doc}</td>
                        <td>{item.docente?.usuario?.persona?.nombre || 'N/A'}</td>
                        <td>
                          <span className={`badge badge-${item.estado === 'ACTIVO' ? 'success' : 'warning'}`}>
                            {item.estado}
                          </span>
                        </td>
                        <td>{item.periodo_academico}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              ) : (
                <p className="no-data">No hay asignaciones disponibles</p>
              )}
            </div>

            {/* Asistencia por período */}
            <div className="dashboard-card">
              <h2>Asistencia por Período</h2>
              {asistenciaPorPeriodo.length > 0 ? (
                <table className="dashboard-table">
                  <thead>
                    <tr>
                      <th>Año</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    {asistenciaPorPeriodo.map((item, i) => (
                      <tr key={i}>
                        <td>{item.año}</td>
                        <td className="text-center">{item.total}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              ) : (
                <p className="no-data">No hay datos disponibles</p>
              )}
            </div>

            {/* Conflictos resumen */}
            <div className="dashboard-card full-width">
              <h2>Estado de Conflictos</h2>
              {conflictosResumido.length > 0 ? (
                <div className="conflictos-grid">
                  {conflictosResumido.map((item, i) => (
                    <div key={i} className={`conflicto-badge ${item.estado.toLowerCase()}`}>
                      <span className="badge-label">{item.estado}</span>
                      <span className="badge-valor">{item.total}</span>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="no-data">No hay conflictos registrados</p>
              )}
            </div>
          </div>
    </div>
  );
};

export default Dashboard;

