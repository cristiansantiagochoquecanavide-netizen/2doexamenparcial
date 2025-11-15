import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './GestionInasistencias.css';

const GestionInasistencias = () => {
  console.log('🔍 GestionInasistencias componente montado');
  const [inasistencias, setInasistencias] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selectedInasistencia, setSelectedInasistencia] = useState(null);
  const [modalAbierto, setModalAbierto] = useState(false);
  const [filtros, setFiltros] = useState({
    estado: 'PENDIENTE',
    desde: '',
    hasta: ''
  });
  const [formResolucion, setFormResolucion] = useState({
    decision: '',
    tipo_accion: '',
    descripcion: '',
    comentario: ''
  });

  useEffect(() => {
    console.log('⚡ useEffect activado, cargando inasistencias...');
    cargarInasistencias();
  }, [filtros]);

  const cargarInasistencias = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams(filtros);
      const { data } = await api.get(`/inasistencias?${params}`);
      setInasistencias(data.data.data || []);
    } catch (err) {
      console.error('Error cargando inasistencias:', err);
    } finally {
      setLoading(false);
    }
  };

  const cargarDetalle = async (id) => {
    try {
      const { data } = await api.get(`/inasistencias/${id}`);
      setSelectedInasistencia(data.data);
      setModalAbierto(true);
    } catch (err) {
      console.error('Error cargando detalle:', err);
    }
  };

  const handleRevision = async () => {
    if (!formResolucion.decision || !formResolucion.tipo_accion) {
      alert('Complete los campos requeridos');
      return;
    }

    try {
      const { data } = await api.post(`/inasistencias/${selectedInasistencia.id_inasistencia}/revisar`, {
        ...formResolucion,
        id_inasistencia: selectedInasistencia.id_inasistencia
      });

      alert('Inasistencia resuelta exitosamente');
      setModalAbierto(false);
      setFormResolucion({});
      cargarInasistencias();
    } catch (err) {
      console.error('Error:', err);
      alert('Error al resolver inasistencia');
    }
  };

  return (
    <div className="gestion-inasistencias">
      <div className="header">
        <h1>📋 Gestión de Inasistencias y Justificaciones</h1>
        <p>Revisar y procesar justificativos de docentes</p>
      </div>

      {/* Filtros */}
      <div className="filtros-section">
        <h3>Filtros</h3>
        <div className="filtros-grid">
          <div className="form-group">
            <label>Estado</label>
            <select
              value={filtros.estado}
              onChange={(e) => setFiltros({ ...filtros, estado: e.target.value })}
            >
              <option value="">Todos</option>
              <option value="PENDIENTE">Pendiente</option>
              <option value="EN_REVISION">En Revisión</option>
              <option value="RESUELTA">Resuelta</option>
              <option value="RECHAZADA">Rechazada</option>
            </select>
          </div>
          <div className="form-group">
            <label>Desde</label>
            <input
              type="date"
              value={filtros.desde}
              onChange={(e) => setFiltros({ ...filtros, desde: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Hasta</label>
            <input
              type="date"
              value={filtros.hasta}
              onChange={(e) => setFiltros({ ...filtros, hasta: e.target.value })}
            />
          </div>
        </div>
      </div>

      {/* Tabla de Inasistencias */}
      <div className="tabla-section">
        {loading ? (
          <div className="loading">Cargando...</div>
        ) : inasistencias.length === 0 ? (
          <div className="sin-datos">No hay inasistencias para mostrar</div>
        ) : (
          <table className="tabla-inasistencias">
            <thead>
              <tr>
                <th>Docente</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {inasistencias.map((inasistencia) => (
                <tr key={inasistencia.id_inasistencia}>
                  <td>{inasistencia.codigo_doc || '-'}</td>
                  <td>{new Date(inasistencia.fecha).toLocaleDateString()}</td>
                  <td>{inasistencia.motivo_aparente || '-'}</td>
                  <td>
                    <span className={`badge estado-${inasistencia.estado.toLowerCase()}`}>
                      {inasistencia.estado}
                    </span>
                  </td>
                  <td>{inasistencia.tipo_inasistencia}</td>
                  <td>
                    <button
                      className="btn btn-small"
                      onClick={() => cargarDetalle(inasistencia.id_inasistencia)}
                    >
                      Revisar
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Modal de Revisión */}
      {modalAbierto && selectedInasistencia && (
        <div className="modal-overlay" onClick={() => setModalAbierto(false)}>
          <div className="modal-contenido" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Revisar Inasistencia</h2>
              <button className="close" onClick={() => setModalAbierto(false)}>✕</button>
            </div>

            <div className="modal-body">
              {/* Información del Docente */}
              <div className="info-section">
                <h3>Información del Docente</h3>
                <p><strong>Código:</strong> {selectedInasistencia.codigo_doc}</p>
                <p><strong>Fecha:</strong> {new Date(selectedInasistencia.fecha).toLocaleDateString()}</p>
                <p><strong>Motivo:</strong> {selectedInasistencia.motivo_aparente || '-'}</p>
              </div>

              {/* Justificativos */}
              {selectedInasistencia.justificativos && selectedInasistencia.justificativos.length > 0 && (
                <div className="justificativos-section">
                  <h3>Justificativos Presentados</h3>
                  {selectedInasistencia.justificativos.map((just) => (
                    <div key={just.id_justificativo} className="justificativo-card">
                      <p><strong>Motivo:</strong> {just.motivo_justificacion}</p>
                      <p><strong>Archivo:</strong> {just.archivo_nombre_original}</p>
                      <p><strong>Tipo:</strong> {just.archivo_tipo} - {(just.archivo_tamaño / 1024).toFixed(2)} KB</p>
                      <button className="btn btn-small">
                        📥 Descargar
                      </button>
                    </div>
                  ))}
                </div>
              )}

              {/* Formulario de Resolución */}
              <div className="resolucion-section">
                <h3>Resolución de Inasistencia</h3>
                
                <div className="form-group">
                  <label>Decisión *</label>
                  <select
                    value={formResolucion.decision}
                    onChange={(e) => setFormResolucion({ ...formResolucion, decision: e.target.value })}
                    className="form-control"
                  >
                    <option value="">Seleccione decisión</option>
                    <option value="APROBADA">✅ Aprobada</option>
                    <option value="RECHAZADA">❌ Rechazada</option>
                  </select>
                </div>

                <div className="form-group">
                  <label>Tipo de Acción *</label>
                  <select
                    value={formResolucion.tipo_accion}
                    onChange={(e) => setFormResolucion({ ...formResolucion, tipo_accion: e.target.value })}
                    className="form-control"
                  >
                    <option value="">Seleccione acción</option>
                    <option value="REPOSICION">Reposición de clase</option>
                    <option value="AJUSTE">Ajuste de calificación</option>
                    <option value="CONDONACION">Condonación</option>
                    <option value="NINGUNA">Ninguna</option>
                  </select>
                </div>

                <div className="form-group">
                  <label>Descripción de la Acción</label>
                  <textarea
                    value={formResolucion.descripcion}
                    onChange={(e) => setFormResolucion({ ...formResolucion, descripcion: e.target.value })}
                    placeholder="Detalles de la acción a ejecutar..."
                    rows="3"
                    className="form-control"
                  />
                </div>

                <div className="form-group">
                  <label>Comentario (para el justificativo)</label>
                  <textarea
                    value={formResolucion.comentario}
                    onChange={(e) => setFormResolucion({ ...formResolucion, comentario: e.target.value })}
                    placeholder="Observaciones sobre el justificativo..."
                    rows="2"
                    className="form-control"
                  />
                </div>
              </div>
            </div>

            <div className="modal-footer">
              <button className="btn btn-secondary" onClick={() => setModalAbierto(false)}>
                Cancelar
              </button>
              <button className="btn btn-primary" onClick={handleRevision}>
                Guardar Resolución
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default GestionInasistencias;
