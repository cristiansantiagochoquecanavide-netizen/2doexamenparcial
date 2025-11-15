// Mapeo de días numéricos a nombres en español
const DIAS_MAP = {
  '1': 'Lunes',
  '2': 'Martes',
  '3': 'Miércoles',
  '4': 'Jueves',
  '5': 'Viernes',
  '6': 'Sábado',
  '7': 'Domingo'
};
import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import './DisponibilidadAulas.css';
import api from '../../utils/api';

const filtrosIniciales = {
  periodo: '',
  dia: '',
  franja: '',
  capacidad: '',
  sede: ''
};

const DisponibilidadAulas = () => {
  const { isCoordinador } = useAuth();
  const [filtros, setFiltros] = useState(filtrosIniciales);
  const [estadosSeleccionados, setEstadosSeleccionados] = useState(['Disponible']);
  const ESTADOS_AULA = ['Disponible', 'Ocupado', 'Mantenimiento'];
  const [resultado, setResultado] = useState([]);
  const [mensaje, setMensaje] = useState('');
  const [loading, setLoading] = useState(false);
  // Opciones dinámicas
  const [periodos, setPeriodos] = useState([]);
  const [dias, setDias] = useState([]);
  const [franjas, setFranjas] = useState([]);
  const [capacidades, setCapacidades] = useState([]);
  const [sedes, setSedes] = useState([]);

  useEffect(() => {
    // Periodos desde endpoint público
    api.get('/periodos-academicos').then(res => {
      setPeriodos(res.data.data || []);
    });
    // Días y franjas desde malla horaria
    api.get('/horarios').then(res => {
      const horarios = res.data && res.data.data ? res.data.data : (Array.isArray(res.data) ? res.data : []);
      // Días únicos (ej: Lunes, Martes, ...)
  const diasUnicos = [...new Set(horarios.map(h => h.dias_semana).filter(Boolean))];
  // Mapear a nombres si es número
  const diasMostrados = diasUnicos.map(d => DIAS_MAP[d] || d);
  setDias(diasMostrados);
      // Franjas filtradas por día seleccionado
      if (filtros.dia) {
        const franjasPorDia = horarios
          .filter(h => h.dias_semana === filtros.dia)
          .map(h => `${h.hora_inicio} - ${h.hora_fin}`);
        setFranjas([...new Set(franjasPorDia)]);
      } else {
        // Si no hay día seleccionado, mostrar todas las franjas
        const todasFranjas = horarios.map(h => `${h.hora_inicio} - ${h.hora_fin}`);
        setFranjas([...new Set(todasFranjas)]);
      }
    });
    // Capacidades desde aulas
    api.get('/aulas').then(res => {
      // Soportar respuesta paginada o array directo
      const aulas = res.data && res.data.data ? res.data.data : (Array.isArray(res.data) ? res.data : []);
      // Solo capacidades numéricas válidas y únicas
      const capacidadesUnicas = [...new Set(aulas.map(a => parseInt(a.capacidad, 10)).filter(c => !isNaN(c) && c > 0))];
      setCapacidades(capacidadesUnicas);
    });
    // Sedes desde infraestructuras
    api.get('/infraestructura').then(res => {
      // Soportar respuesta paginada o array directo
      const infra = res.data && res.data.data ? res.data.data : (Array.isArray(res.data) ? res.data : []);
      const sedesUnicas = [...new Set(infra.map(i => i.nombre_infr).filter(Boolean))];
      setSedes(sedesUnicas);
    });
  }, []);

  const handleChange = e => {
    const { name, value, type, checked } = e.target;
    if (name === 'estadoAula') {
      setEstadosSeleccionados(prev =>
        checked ? [...prev, value] : prev.filter(est => est !== value)
      );
      return;
    }
    setFiltros(prev => {
      const nuevosFiltros = { ...prev, [name]: value };
      // Si cambia el día, actualizar franjas para ese día
      if (name === 'dia') {
        api.get('/horarios').then(res => {
          const horarios = res.data && res.data.data ? res.data.data : (Array.isArray(res.data) ? res.data : []);
          // Buscar el valor numérico correspondiente al nombre seleccionado
          const diaNum = Object.keys(DIAS_MAP).find(k => DIAS_MAP[k] === value) || value;
          const franjasPorDia = horarios
            .filter(h => h.dias_semana == diaNum)
            .map(h => `${h.hora_inicio} - ${h.hora_fin}`);
          setFranjas([...new Set(franjasPorDia)]);
        });
        nuevosFiltros.franja = '';
      }
      return nuevosFiltros;
    });
  };

  const consultarDisponibilidad = async () => {
    setLoading(true);
    setMensaje('');
    setResultado([]);
    try {
      const { data } = await api.post('/disponibilidad-aulas/consultar-aulas', filtros);
      if (data && data.aulas && Array.isArray(data.aulas)) {
        setResultado(data.aulas);
        if (data.aulas.length === 0) setMensaje('No se encontraron aulas para los filtros seleccionados.');
      } else {
        setMensaje('No se pudo obtener la disponibilidad.');
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.message) {
        setMensaje(err.response.data.message);
      } else {
        setMensaje('Error al consultar la disponibilidad.');
      }
    } finally {
      setLoading(false);
    }
  };


  // Métodos adicionales conectados a la API
  const solicitarDisponibilidad = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/solicitar-disponibilidad', filtros);
      setMensaje('Disponibilidad solicitada.');
      setResultado(data.disponibilidad || []);
    } catch (err) {
      setMensaje('Error al solicitar disponibilidad.');
    } finally {
      setLoading(false);
    }
  };

  const consultarEstadoAulas = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const filtrosConEstados = { ...filtros, estados: estadosSeleccionados };
      const { data } = await api.post('/disponibilidad-aulas/consultar-estado', filtrosConEstados);
      setMensaje('Estado de aulas consultado.');
      setResultado(data.estados || data.aulas || []);
    } catch (err) {
      setMensaje('Error al consultar estado de aulas.');
    } finally {
      setLoading(false);
    }
  };

  const aulasDisponibles = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/aulas-disponibles', filtros);
      setMensaje('Aulas disponibles consultadas.');
      setResultado(data.aulas_disponibles || []);
    } catch (err) {
      setMensaje('Error al consultar aulas disponibles.');
    } finally {
      setLoading(false);
    }
  };

  const registrarConsulta = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/registrar-consulta', filtros);
      setMensaje(data.registrado ? 'Consulta registrada en bitácora.' : 'No se pudo registrar la consulta.');
    } catch (err) {
      setMensaje('Error al registrar consulta.');
    } finally {
      setLoading(false);
    }
  };

  const confirmarRegistro = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/confirmar-registro', filtros);
      setMensaje(data.confirmado ? 'Registro confirmado en bitácora.' : 'No se pudo confirmar el registro.');
    } catch (err) {
      setMensaje('Error al confirmar registro.');
    } finally {
      setLoading(false);
    }
  };

  const actualizarDisponibilidad = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/actualizar-disponibilidad', filtros);
      setMensaje(data.actualizado ? 'Disponibilidad actualizada.' : 'No se pudo actualizar la disponibilidad.');
    } catch (err) {
      setMensaje('Error al actualizar disponibilidad.');
    } finally {
      setLoading(false);
    }
  };

  const mostrarResultados = async () => {
    setLoading(true);
    setMensaje('');
    try {
      const { data } = await api.post('/disponibilidad-aulas/mostrar-resultados', filtros);
      setMensaje('Resultados consultados.');
      setResultado(data.resultados || []);
    } catch (err) {
      setMensaje('Error al mostrar resultados.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="disponibilidad-aulas-container">
      <h2>CU13 - Consultar disponibilidad de aulas</h2>
      <form className="filtros-form" onSubmit={e => { e.preventDefault(); consultarDisponibilidad(); }}>
        <label>
          <span>Periodo</span>
          <select name="periodo" value={filtros.periodo} onChange={handleChange} required>
            <option value="">Seleccione</option>
            {periodos.map((p, i) => <option key={i} value={p}>{p}</option>)}
          </select>
        </label>
        <label>
          <span>Día</span>
          <select name="dia" value={filtros.dia} onChange={handleChange} required>
            <option value="">Seleccione</option>
            {dias.map((d, i) => <option key={i} value={d}>{d}</option>)}
          </select>
        </label>
        <label>
          <span>Franja</span>
          <select name="franja" value={filtros.franja} onChange={handleChange} required>
            <option value="">Seleccione</option>
            {franjas.map((f, i) => <option key={i} value={f}>{f}</option>)}
          </select>
        </label>
        <label>
          <span>Capacidad</span>
          <select name="capacidad" value={filtros.capacidad} onChange={handleChange}>
            <option value="">Seleccione</option>
            {capacidades.map((c, i) => <option key={i} value={c}>{c}</option>)}
          </select>
        </label>
        <label>
          <span>Sede</span>
          <select name="sede" value={filtros.sede} onChange={handleChange}>
            <option value="">Seleccione</option>
            {sedes.map((s, i) => <option key={i} value={s}>{s}</option>)}
          </select>
        </label>
        <button type="submit" disabled={loading}>Consultar disponibilidad</button>
      </form>
      <div className="botones-cu13">
        {!isCoordinador && (
          <>
            <button className="btn-cu13" type="button" onClick={solicitarDisponibilidad} disabled={loading}>Solicitar Disponibilidad</button>
            <button className="btn-cu13" type="button" onClick={registrarConsulta} disabled={loading}>Registrar Consulta</button>
            <button className="btn-cu13" type="button" onClick={confirmarRegistro} disabled={loading}>Confirmar Registro</button>
            <button className="btn-cu13" type="button" onClick={actualizarDisponibilidad} disabled={loading}>Actualizar Disponibilidad</button>
            <button className="btn-cu13" type="button" onClick={mostrarResultados} disabled={loading}>Mostrar Resultados</button>
          </>
        )}
        <div style={{ display: 'inline-block', marginRight: 16 }}>
          {ESTADOS_AULA.map(estado => (
            <label key={estado} style={{ marginRight: 8 }}>
              <input
                type="checkbox"
                name="estadoAula"
                value={estado}
                checked={estadosSeleccionados.includes(estado)}
                onChange={handleChange}
              />{' '}{estado}
            </label>
          ))}
        </div>
        <button className="btn-cu13" type="button" onClick={consultarEstadoAulas} disabled={loading}>Consultar Estado Aulas</button>
      </div>
      {mensaje === 'Estado de aulas consultado.' && (
        <div className="mensaje-exito">
          {mensaje}
        </div>
      )}
      {resultado.length > 0 && (
        <div className="resultado">
          <h3>Resultado</h3>
          {/* Mostrar tabla diferente según la acción (campos presentes) */}
          {/* Mostrar solo aulas con los estados seleccionados */}
          {(() => {
            const filtradas = resultado.filter(r => estadosSeleccionados.includes((r.estado || '').toString()));
            if (filtradas.length === 0) return <div>No hay aulas con los estados seleccionados.</div>;
            return (
              <table>
                <thead>
                  <tr>
                    <th>Aula</th>
                    <th>Estado</th>
                    <th>Capacidad</th>
                    <th>Sede</th>
                  </tr>
                </thead>
                <tbody>
                  {filtradas.map((r, i) => (
                    <tr key={i}>
                      <td>{r.nro_aula || r.aula || r.nombre || '-'}</td>
                      <td>{r.estado || '-'}</td>
                      <td>{r.capacidad || '-'}</td>
                      <td>{r.sede || '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            );
          })()}
        </div>
      )}
    </div>
  );
};

export default DisponibilidadAulas;
