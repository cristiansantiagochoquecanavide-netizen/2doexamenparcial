import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../../context/AuthContext';
import './GenerarReportes.css';

// Configurar axios con la URL base del API
const createApi = (token) => {
  return axios.create({
    baseURL: window.location.origin + '/api',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` })
    }
  });
};

const GenerarReportes = () => {
  const { user } = useAuth();
  const [api, setApi] = useState(null);
  
  const [tipoReporte, setTipoReporte] = useState('asignaciones');
  const [filtros, setFiltros] = useState({
    periodo_academico: '',
    docente_id: '',
    grupo_id: '',
    estado: '',
    desde: '',
    hasta: ''
  });
  const [formatos, setFormatos] = useState({
    pdf: true,
    excel: false
  });
  const [cargando, setCargando] = useState(false);
  const [mensaje, setMensaje] = useState('');
  const [tipoMensaje, setTipoMensaje] = useState('');
  const [periodos, setPeriodos] = useState([]);
  const [docentes, setDocentes] = useState([]);
  const [grupos, setGrupos] = useState([]);
  const [datos, setDatos] = useState(null);
  const [mostrarPreview, setMostrarPreview] = useState(false);

  // Configurar axios cuando el componente monta o cuando cambia el token
  useEffect(() => {
    const token = localStorage.getItem('token');
    console.log('🔐 Configurando axios - Token presente:', !!token);
    const newApi = createApi(token);
    setApi(newApi);
  }, []);

  // Cargar datos cuando el api está listo
  useEffect(() => {
    if (api) {
      console.log('✅ GenerarReportes component mounted');
      console.log('📍 API Base URL:', api.defaults.baseURL);
      console.log('👤 Usuario:', user?.usuario);
      cargarDatos();
    }
  }, [api, user]);

  const cargarDatos = async () => {
    if (!api) {
      console.warn('⚠️ API no configurado aún');
      return;
    }
    
    try {
      console.log('📥 Iniciando carga de datos...');
      console.log('🔗 URL base:', api.defaults.baseURL);
      console.log('🔐 Headers con auth:', api.defaults.headers);
      
      // Crear instancia sin autenticación para periodos-academicos (ruta pública)
      const apiPublic = axios.create({
        baseURL: window.location.origin + '/api',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      const [periodos, docentes, grupos] = await Promise.all([
        // Períodos es ruta pública
        apiPublic.get('/periodos-academicos')
          .then(r => {
            console.log('✅ Períodos - Status:', r.status);
            console.log('✅ Períodos - Datos:', r.data);
            const data = r.data.data || r.data || [];
            return Array.isArray(data) ? data : [];
          })
          .catch(err => {
            console.error('❌ Error períodos - Status:', err.response?.status);
            console.error('❌ Error períodos - Message:', err.message);
            console.error('❌ Error períodos - Response:', err.response?.data);
            console.error('❌ Error períodos - URL intentada:', apiPublic.defaults.baseURL + '/periodos-academicos');
            return [];
          }),
        // Docentes requiere autenticación
        api.get('/docentes')
          .then(r => {
            console.log('✅ Docentes - Status:', r.status);
            console.log('✅ Docentes - Datos:', r.data);
            const data = r.data.data || r.data || [];
            return Array.isArray(data) ? data : [];
          })
          .catch(err => {
            console.error('❌ Error docentes - Status:', err.response?.status);
            console.error('❌ Error docentes - Message:', err.message);
            console.error('❌ Error docentes - Response:', err.response?.data);
            console.error('❌ Headers enviados:', api.defaults.headers);
            return [];
          }),
        // Grupos requiere autenticación
        api.get('/grupos')
          .then(r => {
            console.log('✅ Grupos - Status:', r.status);
            console.log('✅ Grupos - Datos:', r.data);
            const data = r.data.data || r.data || [];
            return Array.isArray(data) ? data : [];
          })
          .catch(err => {
            console.error('❌ Error grupos - Status:', err.response?.status);
            console.error('❌ Error grupos - Message:', err.message);
            console.error('❌ Error grupos - Response:', err.response?.data);
            console.error('❌ Headers enviados:', api.defaults.headers);
            return [];
          })
      ]);
      
      console.log('📊 Resumen - Períodos:', periodos.length);
      console.log('📊 Resumen - Docentes:', docentes.length);
      console.log('📊 Resumen - Grupos:', grupos.length);
      
      if (periodos.length === 0) console.warn('⚠️ No hay períodos cargados');
      if (docentes.length === 0) console.warn('⚠️ No hay docentes cargados');
      if (grupos.length === 0) console.warn('⚠️ No hay grupos cargados');
      
      setPeriodos(periodos);
      setDocentes(docentes);
      setGrupos(grupos);
      console.log('✅ Datos cargados exitosamente');
    } catch (error) {
      console.error('❌ Error general al cargar datos:', error);
      console.error('❌ Stack trace:', error.stack);
    }
  };

  const handleFiltroChange = (e) => {
    const { name, value } = e.target;
    setFiltros(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFormatoChange = (e) => {
    const { name, checked } = e.target;
    setFormatos(prev => ({
      ...prev,
      [name]: checked
    }));
  };

  const previsualizarReporte = async () => {
    console.log('🔍 Iniciando previsualización...');
    console.log('📌 API disponible:', !!api);
    console.log('📌 Tipo reporte:', tipoReporte);
    console.log('📌 Filtros:', filtros);
    
    if (!api) {
      console.error('❌ API no configurado');
      setMensaje('API no configurado. Recarga la página');
      setTipoMensaje('error');
      return;
    }

    if (!formatos.pdf && !formatos.excel) {
      setMensaje('Selecciona al menos un formato (PDF o Excel)');
      setTipoMensaje('error');
      return;
    }

    try {
      setCargando(true);
      setMensaje('');
      const params = {
        tipo_reporte: tipoReporte,
        ...filtros,
        previsualizar: true
      };

      console.log('📤 Enviando params:', params);
      const response = await api.post('/reportes/generar', params);
      
      console.log('✅ Response recibido:', response.data);
      
      if (response.data.success) {
        const datosArray = response.data.data || [];
        console.log('📊 Datos a mostrar:', datosArray);
        setDatos(datosArray);
        setMostrarPreview(true);
        setMensaje(`Previsualización cargada: ${datosArray.length} registros`);
        setTipoMensaje('success');
      } else {
        const errorMsg = response.data.message || 'Error al generar previsualización';
        console.error('❌ Error en respuesta:', errorMsg);
        setMensaje(errorMsg);
        setTipoMensaje('error');
      }
    } catch (error) {
      console.error('❌ Error completo:', error);
      console.error('❌ Error response:', error.response?.data);
      console.error('❌ Error message:', error.message);
      const errorMsg = error.response?.data?.message || error.message || 'Error al generar previsualización';
      setMensaje(errorMsg);
      setTipoMensaje('error');
    } finally {
      setCargando(false);
    }
  };

  const generarReporte = async (formatoSeleccionado) => {
    console.log('📊 Iniciando generación de reporte...');
    console.log('📌 API disponible:', !!api);
    console.log('📌 Formato:', formatoSeleccionado);
    
    if (!api) {
      console.error('❌ API no configurado');
      setMensaje('API no configurado. Recarga la página');
      setTipoMensaje('error');
      return;
    }

    try {
      setCargando(true);
      setMensaje('');
      
      const params = {
        tipo_reporte: tipoReporte,
        formato: formatoSeleccionado,
        periodo_academico: filtros.periodo_academico || null,
        filtros: {
          codigo_doc: filtros.docente_id || null,
          codigo_grupo: filtros.grupo_id || null,
          estado: filtros.estado || null
        }
      };

      console.log('📤 Enviando petición a /reportes/generar con parámetros:', params);
      const response = await api.post('/reportes/generar', params);
      console.log('✅ Respuesta recibida:', response.data);

      if (response.data.success) {
        // Si es JSON, mostrar datos en preview
        if (formatoSeleccionado === 'json' && response.data.datos) {
          const datosArray = response.data.datos || [];
          setDatos(datosArray);
          setMostrarPreview(true);
          setMensaje(`Reporte generado: ${datosArray.length} registros`);
          setTipoMensaje('success');
        } else if (response.data.descarga_url) {
          // Si hay URL de descarga, descargar archivo
          console.log('📥 Descargando archivo desde:', response.data.descarga_url);
          const link = document.createElement('a');
          link.href = response.data.descarga_url;
          link.setAttribute('download', response.data.filename || `reporte_${tipoReporte}`);
          document.body.appendChild(link);
          link.click();
          link.parentNode.removeChild(link);
          
          setMensaje(`Reporte descargado: ${response.data.filename}`);
          setTipoMensaje('success');
        } else {
          setMensaje(`Reporte generado exitosamente: ${response.data.registros} registros`);
          setTipoMensaje('success');
        }

        // Registrar en bitácora
        await api.post('/bitacora/registrar', {
          accion: `Generar reporte ${tipoReporte}`,
          modulo: 'Reportes',
          detalles: { tipo_reporte: tipoReporte, formato: formatoSeleccionado, registros: response.data.registros }
        }).catch(err => {
          console.warn('⚠️ Bitácora no registrada:', err.message);
        });
      }
    } catch (error) {
      console.error('❌ Error completo:', error);
      console.error('❌ Error response:', error.response?.data);
      console.error('❌ Error message:', error.message);
      const errorMsg = error.response?.data?.message || error.message || `Error al generar reporte`;
      setMensaje(errorMsg);
      setTipoMensaje('error');
    } finally {
      setCargando(false);
    }
  };

  const compartirReporte = async () => {
    try {
      setCargando(true);
      setMensaje('');

      // Determinar formato para compartir
      let formato = 'pdf';
      if (formatos.excel) formato = 'excel';

      const response = await api.post('/reportes/compartir', {
        tipo_reporte: tipoReporte,
        formato: formato,
        destinatarios: [],  // Se puede actualizar con destinatarios reales
        mensaje: `Se comparte reporte de ${tipoReporte} generado ${new Date().toLocaleDateString()}`
      });

      if (response.data.success) {
        setMensaje(`Reporte compartido. Token: ${response.data.token_descarga.substring(0, 10)}...`);
        setTipoMensaje('success');
        
        // Copiar URL al portapapeles
        if (response.data.url_descarga) {
          navigator.clipboard.writeText(response.data.url_descarga);
          setMensaje(`URL copiada al portapapeles. Válido por ${response.data.expira_en}`);
        }
      }
    } catch (error) {
      console.error('Error:', error);
      setMensaje(error.response?.data?.message || 'Error al compartir reporte');
      setTipoMensaje('error');
    } finally {
      setCargando(false);
    }
  };

  const renderizarDatos = () => {
    try {
      if (!datos) {
        console.warn('⚠️ No hay datos para renderizar');
        return <p>No hay datos disponibles</p>;
      }

      if (!Array.isArray(datos)) {
        console.error('❌ Datos no es un array:', typeof datos);
        return <p>Error: formato de datos inválido</p>;
      }

      if (datos.length === 0) {
        return <p>No hay registros para mostrar</p>;
      }

      switch (tipoReporte) {
        case 'asignaciones':
          return (
            <table className="tabla-preview">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Docente</th>
                  <th>Materia</th>
                  <th>Grupo</th>
                  <th>Aula</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                {datos.map((row, idx) => (
                  <tr key={idx}>
                    <td>{row.id_asignacion}</td>
                    <td>{row.docente}</td>
                    <td>{row.materia}</td>
                    <td>{row.grupo}</td>
                    <td>{row.aula}</td>
                    <td><span className={`badge badge-${row.estado}`}>{row.estado}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          );
        
        case 'asistencia':
          return (
            <table className="tabla-preview">
              <thead>
                <tr>
                  <th>Docente</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Hora Entrada</th>
                  <th>Observaciones</th>
                </tr>
              </thead>
              <tbody>
                {datos.map((row, idx) => (
                  <tr key={idx}>
                    <td>{row.docente}</td>
                    <td>{row.fecha}</td>
                    <td><span className={`badge badge-${row.estado}`}>{row.estado}</span></td>
                    <td>{row.hora_entrada}</td>
                    <td>{row.observaciones}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          );
        
        case 'inasistencias':
          return (
            <table className="tabla-preview">
              <thead>
                <tr>
                  <th>Docente</th>
                  <th>Fecha</th>
                  <th>Motivo</th>
                  <th>Estado</th>
                  <th>Tipo</th>
                </tr>
              </thead>
              <tbody>
                {datos.map((row, idx) => (
                  <tr key={idx}>
                    <td>{row.docente}</td>
                    <td>{row.fecha}</td>
                    <td>{row.motivo}</td>
                    <td><span className={`badge badge-${row.estado}`}>{row.estado}</span></td>
                    <td>{row.tipo}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          );

        case 'ocupacion_aulas':
          return (
            <table className="tabla-preview">
              <thead>
                <tr>
                  <th>Aula</th>
                  <th>Capacidad</th>
                  <th>Ocupación</th>
                  <th>Porcentaje</th>
                </tr>
              </thead>
              <tbody>
                {datos.map((row, idx) => (
                  <tr key={idx}>
                    <td>{row.aula}</td>
                    <td>{row.capacidad}</td>
                    <td>{row.ocupacion}</td>
                    <td>
                      <div className="progress-bar">
                        <div 
                          className="progress-fill" 
                          style={{ width: `${row.porcentaje}%` }}
                        ></div>
                      </div>
                      {row.porcentaje}%
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          );

        default:
          console.warn('⚠️ Tipo de reporte desconocido:', tipoReporte);
          return <p>Tipo de reporte desconocido</p>;
      }
    } catch (error) {
      console.error('❌ Error renderizando datos:', error);
      return <p>Error al renderizar datos: {error.message}</p>;
    }
  };

  return (
    <div className="generar-reportes-container">
      <div className="reportes-header">
        <h2>📊 CU17 - Generar Reportes (PDF/Excel)</h2>
        <p>Genera reportes operacionales/gerenciales de asignaciones, asistencia, inasistencias y ocupación de aulas</p>
      </div>

      {mensaje && (
        <div className={`alert alert-${tipoMensaje}`}>
          {tipoMensaje === 'success' && '✅ '}
          {tipoMensaje === 'error' && '❌ '}
          {mensaje}
        </div>
      )}

      <div className="reportes-content">
        {/* Sección de configuración */}
        <div className="seccion-formulario">
          <h3>1️⃣ Selecciona el tipo de reporte</h3>
          <div className="form-group">
            <label>Tipo de Reporte *</label>
            <select 
              value={tipoReporte} 
              onChange={(e) => setTipoReporte(e.target.value)}
              className="form-control"
            >
              <option value="asignaciones">Asignaciones de Carga Horaria</option>
              <option value="asistencia">Asistencia Docente</option>
              <option value="inasistencias">Inasistencias y Justificaciones</option>
              <option value="ocupacion_aulas">Ocupación de Aulas</option>
            </select>
          </div>

          <h3>2️⃣ Aplicar filtros (opcional)</h3>
          <div className="filtros-grid">
            <div className="form-group">
              <label>Período Académico</label>
              <select 
                name="periodo_academico"
                value={filtros.periodo_academico}
                onChange={handleFiltroChange}
                className="form-control"
              >
                <option value="">Todos</option>
                {periodos.map(p => (
                  <option key={p} value={p}>{p}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Docente</label>
              <select 
                name="docente_id"
                value={filtros.docente_id}
                onChange={handleFiltroChange}
                className="form-control"
              >
                <option value="">Todos</option>
                {docentes.map(d => (
                  <option key={d.codigo_doc} value={d.codigo_doc}>
                    {d.nombre_completo || `${d.usuario?.persona?.nombre} ${d.usuario?.persona?.apellido_paterno}`}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Grupo</label>
              <select 
                name="grupo_id"
                value={filtros.grupo_id}
                onChange={handleFiltroChange}
                className="form-control"
              >
                <option value="">Todos</option>
                {grupos.map(g => (
                  <option key={g.codigo_grupo} value={g.codigo_grupo}>
                    {g.codigo_grupo} - {g.materia?.nombre_mat}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Estado</label>
              <select 
                name="estado"
                value={filtros.estado}
                onChange={handleFiltroChange}
                className="form-control"
              >
                <option value="">Todos</option>
                <option value="ACTIVO">Activo</option>
                <option value="INACTIVO">Inactivo</option>
                <option value="PENDIENTE">Pendiente</option>
              </select>
            </div>

            <div className="form-group">
              <label>Desde</label>
              <input 
                type="date"
                name="desde"
                value={filtros.desde}
                onChange={handleFiltroChange}
                className="form-control"
              />
            </div>

            <div className="form-group">
              <label>Hasta</label>
              <input 
                type="date"
                name="hasta"
                value={filtros.hasta}
                onChange={handleFiltroChange}
                className="form-control"
              />
            </div>
          </div>

          <h3>3️⃣ Selecciona formato(s) de salida</h3>
          <div className="formatos-checkbox">
            <label className="checkbox-label">
              <input 
                type="checkbox"
                name="pdf"
                checked={formatos.pdf}
                onChange={handleFormatoChange}
              />
              <span>📄 PDF</span>
            </label>
            <label className="checkbox-label">
              <input 
                type="checkbox"
                name="excel"
                checked={formatos.excel}
                onChange={handleFormatoChange}
              />
              <span>📊 Excel</span>
            </label>
          </div>

          <div className="botones-acciones">
            <button 
              className="btn btn-secondary"
              onClick={previsualizarReporte}
              disabled={cargando}
            >
              👁️ Previsualizar
            </button>
            {formatos.pdf && (
              <button 
                className="btn btn-primary"
                onClick={() => generarReporte('pdf')}
                disabled={cargando}
              >
                {cargando ? '⏳ Generando...' : '📥 Descargar PDF'}
              </button>
            )}
            {formatos.excel && (
              <button 
                className="btn btn-success"
                onClick={() => generarReporte('excel')}
                disabled={cargando}
              >
                {cargando ? '⏳ Generando...' : '📥 Descargar Excel'}
              </button>
            )}
          </div>
        </div>

        {/* Sección de previsualización */}
        {mostrarPreview && datos && (
          <div className="seccion-previsualizar">
            <h3>📋 Previsualización del Reporte</h3>
            <div className="preview-container">
              {renderizarDatos()}
            </div>
            <div className="botones-preview">
              <button 
                className="btn btn-secondary"
                onClick={compartirReporte}
                disabled={cargando}
              >
                🔗 Compartir Reporte
              </button>
              <button 
                className="btn btn-warning"
                onClick={() => setMostrarPreview(false)}
              >
                ❌ Cerrar Previsualización
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Información útil */}
      <div className="info-box">
        <h4>ℹ️ Información sobre los reportes:</h4>
        <ul>
          <li><strong>Asignaciones:</strong> Detalle de docentes asignados a grupos, aulas y horarios</li>
          <li><strong>Asistencia:</strong> Registro de asistencias diarias y estados de los docentes</li>
          <li><strong>Inasistencias:</strong> Justificativos presentados y estado de resolución</li>
          <li><strong>Ocupación de Aulas:</strong> Análisis de utilización y disponibilidad de infraestructura</li>
        </ul>
      </div>
    </div>
  );
};

export default GenerarReportes;
