import React, { useState, useEffect } from 'react';
import api from '../../utils/api';
import { useAuth } from '../../context/AuthContext';
import './GenerarReportes.css';

const GenerarReportes = () => {
  const { user } = useAuth();
  const [tipoReporte, setTipoReporte] = useState('horarios_semanales');
  const [formato, setFormato] = useState('pdf');
  const [filtros, setFiltros] = useState({
    periodo_academico: '',
    docente_id: '',
    grupo_id: ''
  });
  const [cargando, setCargando] = useState(false);
  const [mensaje, setMensaje] = useState('');
  const [periodos, setPeriodos] = useState([]);
  const [docentes, setDocentes] = useState([]);
  const [grupos, setGrupos] = useState([]);
  const [datos, setDatos] = useState([]);
  const [mostrarPreview, setMostrarPreview] = useState(false);

  useEffect(() => {
    cargarDatos();
  }, []);

  const cargarDatos = async () => {
    try {
      const [periodosRes, docentesRes, gruposRes] = await Promise.all([
        api.get('/periodos-academicos').catch(() => ({ data: { data: [] } })),
        api.get('/docentes').catch(() => ({ data: { data: [] } })),
        api.get('/grupos').catch(() => ({ data: { data: [] } }))
      ]);

      setPeriodos(periodosRes.data.data || []);
      setDocentes(docentesRes.data.data || []);
      setGrupos(gruposRes.data.data || []);
    } catch (error) {
      console.error('Error al cargar datos:', error);
    }
  };

  const handleFiltroChange = (e) => {
    const { name, value } = e.target;
    setFiltros(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const previsualizarReporte = async () => {
    try {
      setCargando(true);
      setMensaje('');
      
      const params = {
        tipo_reporte: tipoReporte,
        formato: formato,
        previsualizar: true,
        ...filtros
      };

      const response = await api.post('/reportes/generar', params);
      
      if (response.data.success) {
        setDatos(response.data.data || []);
        setMostrarPreview(true);
        setMensaje('Previsualización cargada correctamente');
      } else {
        setMensaje('Error al cargar previsualización');
      }
    } catch (error) {
      console.error('Error:', error);
      setMensaje('Error al cargar previsualización: ' + (error.response?.data?.message || error.message));
    } finally {
      setCargando(false);
    }
  };

  const generarReporte = async () => {
    try {
      setCargando(true);
      setMensaje('');
      
      const params = {
        tipo_reporte: tipoReporte,
        formato: formato,
        ...filtros
      };

      const response = await api.post('/reportes/generar', params, {
        responseType: 'blob'
      });

      // Crear enlace de descarga
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `reporte_${tipoReporte}_${new Date().getTime()}.${formato === 'pdf' ? 'pdf' : 'csv'}`);
      document.body.appendChild(link);
      link.click();
      link.remove();

      setMensaje('Reporte generado y descargado exitosamente');
    } catch (error) {
      console.error('Error:', error);
      setMensaje('Error al generar reporte: ' + (error.response?.data?.message || error.message));
    } finally {
      setCargando(false);
    }
  };

  return (
    <div className="generar-reportes-container">
      {/* Header Principal */}
      <div className="reportes-header">
        <div className="header-content">
          <div className="header-icon">
            <i className="fas fa-file-chart"></i>
          </div>
          <div className="header-text">
            <h1>Generar Reportes</h1>
            <p>Exporta reportes académicos en formato PDF o Excel</p>
          </div>
        </div>
      </div>

      {/* Mensaje de Estado */}
      {mensaje && (
        <div className={`alert ${mensaje.includes('Error') || mensaje.includes('error') ? 'alert-error' : 'alert-success'}`}>
          <i className={`fas ${mensaje.includes('Error') || mensaje.includes('error') ? 'fa-exclamation-circle' : 'fa-check-circle'}`}></i>
          <span>{mensaje}</span>
        </div>
      )}

      <div className="reportes-grid">
        {/* Panel de Configuración */}
        <div className="config-panel">
          <div className="panel-section">
            <div className="section-header">
              <i className="fas fa-cog"></i>
              <h3>Configuración del Reporte</h3>
            </div>

            {/* Tipo de Reporte */}
            <div className="form-group">
              <label>
                <i className="fas fa-chart-bar"></i>
                Tipo de Reporte
              </label>
              <select
                value={tipoReporte}
                onChange={(e) => setTipoReporte(e.target.value)}
                className="form-control"
              >
                <option value="horarios_semanales">Horarios Semanales</option>
                <option value="asistencia_docente">Asistencia por Docente y Grupo</option>
                <option value="aulas_disponibles">Aulas Disponibles</option>
              </select>
            </div>

            {/* Formato de Exportación */}
            <div className="form-group">
              <label>
                <i className="fas fa-file-export"></i>
                Formato de Exportación
              </label>
              <div className="formato-cards">
                <div 
                  className={`formato-card ${formato === 'pdf' ? 'active' : ''}`}
                  onClick={() => setFormato('pdf')}
                >
                  <input
                    type="radio"
                    value="pdf"
                    checked={formato === 'pdf'}
                    onChange={(e) => setFormato(e.target.value)}
                  />
                  <div className="formato-icon pdf">
                    <i className="fas fa-file-pdf"></i>
                  </div>
                  <span>PDF</span>
                </div>
                <div 
                  className={`formato-card ${formato === 'excel' ? 'active' : ''}`}
                  onClick={() => setFormato('excel')}
                >
                  <input
                    type="radio"
                    value="excel"
                    checked={formato === 'excel'}
                    onChange={(e) => setFormato(e.target.value)}
                  />
                  <div className="formato-icon excel">
                    <i className="fas fa-file-excel"></i>
                  </div>
                  <span>Excel</span>
                </div>
              </div>
            </div>
          </div>

          {/* Panel de Filtros */}
          <div className="panel-section">
            <div className="section-header">
              <i className="fas fa-filter"></i>
              <h3>Filtros de Búsqueda</h3>
            </div>

            <div className="form-group">
              <label>
                <i className="fas fa-calendar-alt"></i>
                Período Académico
              </label>
              <select
                name="periodo_academico"
                value={filtros.periodo_academico}
                onChange={handleFiltroChange}
                className="form-control"
              >
                <option value="">Todos los períodos</option>
                {periodos.map((periodo, index) => (
                  <option key={index} value={periodo}>{periodo}</option>
                ))}
              </select>
            </div>

            {tipoReporte !== 'aulas_disponibles' && (
              <div className="form-group">
                <label>
                  <i className="fas fa-user-tie"></i>
                  Docente
                </label>
                <select
                  name="docente_id"
                  value={filtros.docente_id}
                  onChange={handleFiltroChange}
                  className="form-control"
                >
                  <option value="">Todos los docentes</option>
                  {docentes.map((docente) => (
                    <option key={docente.codigo_doc} value={docente.codigo_doc}>
                      {docente.nombre_completo || `Docente ${docente.codigo_doc}`}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {tipoReporte === 'asistencia_docente' && (
              <div className="form-group">
                <label>
                  <i className="fas fa-users"></i>
                  Grupo
                </label>
                <select
                  name="grupo_id"
                  value={filtros.grupo_id}
                  onChange={handleFiltroChange}
                  className="form-control"
                >
                  <option value="">Todos los grupos</option>
                  {grupos.map((grupo) => (
                    <option key={grupo.codigo_grupo} value={grupo.codigo_grupo}>
                      {grupo.codigo_grupo}
                    </option>
                  ))}
                </select>
              </div>
            )}
          </div>

          {/* Botones de Acción */}
          <div className="action-buttons">
            <button
              onClick={previsualizarReporte}
              disabled={cargando}
              className="btn btn-preview"
            >
              <i className="fas fa-eye"></i>
              {cargando ? 'Cargando...' : 'Previsualizar'}
            </button>
            <button
              onClick={generarReporte}
              disabled={cargando}
              className="btn btn-generate"
            >
              <i className="fas fa-download"></i>
              {cargando ? 'Generando...' : `Generar ${formato.toUpperCase()}`}
            </button>
          </div>
        </div>

        {/* Panel de Previsualización */}
        {mostrarPreview && datos.length > 0 && (
          <div className="preview-panel">
            <div className="preview-header">
              <div className="preview-title">
                <i className="fas fa-table"></i>
                <h3>Previsualización</h3>
              </div>
              <div className="preview-badge">
                {datos.length} registro{datos.length !== 1 ? 's' : ''}
              </div>
            </div>
            
            <div className="preview-content">
              <div className="table-wrapper">
                <table className="preview-table">
                  <thead>
                    <tr>
                      {Object.keys(datos[0]).map((key) => (
                        <th key={key}>{key}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {datos.slice(0, 10).map((row, index) => (
                      <tr key={index}>
                        {Object.values(row).map((value, i) => (
                          <td key={i}>{value}</td>
                        ))}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {datos.length > 10 && (
                <div className="preview-footer">
                  <i className="fas fa-info-circle"></i>
                  Mostrando 10 de {datos.length} registros
                </div>
              )}
            </div>
          </div>
        )}

        {/* Estado Vacío */}
        {!mostrarPreview && (
          <div className="empty-state">
            <div className="empty-icon">
              <i className="fas fa-chart-line"></i>
            </div>
            <h3>Sin Previsualización</h3>
            <p>Selecciona los filtros y haz clic en "Previsualizar" para ver los datos</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default GenerarReportes;
