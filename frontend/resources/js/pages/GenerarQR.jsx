import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './GenerarQR.css';

const GenerarQR = () => {
  const [asignaciones, setAsignaciones] = useState([]);
  const [selectedAsignacion, setSelectedAsignacion] = useState('');
  const [duracion, setDuracion] = useState(60);
  const [loading, setLoading] = useState(false);
  const [qrGenerado, setQrGenerado] = useState(null);
  const [error, setError] = useState('');
  const [sesiones, setSesiones] = useState([]);

  useEffect(() => {
    cargarAsignaciones();
    cargarSesionesActivas();
  }, []);

  const cargarAsignaciones = async () => {
    try {
      const { data } = await api.get('/asignaciones');
      setAsignaciones(data.data || data);
    } catch (err) {
      console.error('Error al cargar asignaciones:', err);
    }
  };

  const cargarSesionesActivas = async () => {
    try {
      const { data } = await api.get('/asistencia/sesiones-activas');
      setSesiones(data.data || []);
    } catch (err) {
      console.error('Error al cargar sesiones:', err);
    }
  };

  const handleGenerarQR = async (e) => {
    e.preventDefault();
    
    if (!selectedAsignacion) {
      setError('Selecciona una asignación');
      return;
    }

    try {
      setLoading(true);
      setError('');
      
      const { data } = await api.post('/asistencia/generar-qr', {
        id_asignacion: parseInt(selectedAsignacion),
        duracion_minutos: duracion
      });

      if (data.success) {
        setQrGenerado(data.data);
        cargarSesionesActivas();
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error al generar QR');
    } finally {
      setLoading(false);
    }
  };

  const handleDescargarQR = () => {
    if (!qrGenerado) return;

    const link = document.createElement('a');
    link.href = qrGenerado.qr_base64;
    link.download = `asistencia-qr-${qrGenerado.token}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const handleCopiarEnlace = () => {
    if (!qrGenerado) return;
    navigator.clipboard.writeText(qrGenerado.url_registro);
    alert('Enlace copiado al portapapeles');
  };

  const handleCerrarSesion = async (idSesion) => {
    if (window.confirm('¿Deseas cerrar esta sesión?')) {
      try {
        await api.post('/asistencia/cerrar-sesion', { id_sesion: idSesion });
        cargarSesionesActivas();
      } catch (err) {
        alert('Error al cerrar la sesión');
      }
    }
  };

  const getAsignacionInfo = (id) => {
    return asignaciones.find(a => a.id_asignacion == id);
  };

  return (
    <div className="generar-qr">
      <div className="container">
        <h1>Generar QR de Asistencia</h1>
        <p className="subtitle">Crea un código QR para que tus estudiantes registren asistencia</p>

        {error && <div className="alert alert-error">{error}</div>}

        <div className="content-grid">
          {/* Formulario */}
          <div className="form-section">
            <form onSubmit={handleGenerarQR} className="form">
              <div className="form-group">
                <label htmlFor="asignacion">Selecciona la Asignación *</label>
                <select
                  id="asignacion"
                  value={selectedAsignacion}
                  onChange={(e) => setSelectedAsignacion(e.target.value)}
                  className="form-control"
                  required
                >
                  <option value="">-- Selecciona una asignación --</option>
                  {asignaciones.map((asig) => (
                    <option key={asig.id_asignacion} value={asig.id_asignacion}>
                      {asig.grupo?.materia?.nombre_materia} - Grupo {asig.codigo_grupo} (Aula {asig.nro_aula})
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="duracion">Duración de Sesión (minutos)</label>
                <div className="input-group">
                  <input
                    id="duracion"
                    type="number"
                    value={duracion}
                    onChange={(e) => setDuracion(Math.min(480, Math.max(5, parseInt(e.target.value) || 5)))}
                    min="5"
                    max="480"
                    className="form-control"
                  />
                  <span className="help-text">{Math.floor(duracion / 60)}h {duracion % 60}m</span>
                </div>
              </div>

              <button 
                type="submit" 
                className="btn btn-primary"
                disabled={loading}
              >
                {loading ? 'Generando...' : '📲 Generar QR'}
              </button>
            </form>
          </div>

          {/* QR Generado */}
          {qrGenerado && (
            <div className="qr-section">
              <div className="qr-card">
                <h3>Código QR Generado</h3>
                <img 
                  src={qrGenerado.qr_base64} 
                  alt="QR Code" 
                  className="qr-image"
                />
                <div className="qr-info">
                  <p><strong>Token:</strong> {qrGenerado.token}</p>
                  <p><strong>Válido hasta:</strong> {new Date(qrGenerado.fecha_expiracion).toLocaleString()}</p>
                </div>
                <div className="qr-actions">
                  <button 
                    onClick={handleDescargarQR}
                    className="btn btn-secondary"
                  >
                    ⬇️ Descargar QR
                  </button>
                  <button 
                    onClick={handleCopiarEnlace}
                    className="btn btn-secondary"
                  >
                    🔗 Copiar Enlace
                  </button>
                </div>
                <div className="enlace-registro">
                  <p className="label">Enlace de Registro:</p>
                  <code>{qrGenerado.url_registro}</code>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Sesiones Activas */}
        {sesiones.length > 0 && (
          <div className="sesiones-section">
            <h2>Sesiones Activas</h2>
            <div className="sesiones-grid">
              {sesiones.map((sesion) => {
                const asig = getAsignacionInfo(sesion.id_asignacion);
                const tiempoRestante = new Date(sesion.fecha_expiracion) - new Date();
                const minutos = Math.floor(tiempoRestante / 60000);
                
                return (
                  <div key={sesion.id_sesion} className="sesion-card">
                    <div className="sesion-header">
                      <h4>{asig?.grupo?.materia?.nombre_materia}</h4>
                      <span className={`badge ${minutos > 10 ? 'badge-active' : 'badge-expiring'}`}>
                        {minutos}m restantes
                      </span>
                    </div>
                    <div className="sesion-info">
                      <p><strong>Grupo:</strong> {asig?.codigo_grupo}</p>
                      <p><strong>Aula:</strong> {asig?.nro_aula}</p>
                      <p><strong>Token:</strong> {sesion.token.substring(0, 8)}...</p>
                    </div>
                    <button 
                      onClick={() => handleCerrarSesion(sesion.id_sesion)}
                      className="btn btn-small btn-danger"
                    >
                      Cerrar Sesión
                    </button>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        <div className="info-box">
          <h4>ℹ️ Instrucciones de Uso:</h4>
          <ol>
            <li>Selecciona la asignación para la cual deseas generar el QR</li>
            <li>Establece la duración de la sesión (de 5 minutos a 8 horas)</li>
            <li>Haz clic en "Generar QR"</li>
            <li>Descarga el QR o copia el enlace para compartir con tus estudiantes</li>
            <li>Los estudiantes escanean el QR o usan el enlace para registrar su asistencia</li>
            <li>Puedes cerrar la sesión manualmente en cualquier momento</li>
          </ol>
        </div>
      </div>
    </div>
  );
};

export default GenerarQR;
