import React, { useState } from 'react';
import '../pages/ConflictosHorario.css';
import api from '../utils/api';

const ConflictosHorario = () => {
	const [conflictos, setConflictos] = useState([]);
	const [resultado, setResultado] = useState('');
	const [loading, setLoading] = useState(false);

	// Simulación de detección de conflictos
	const detectarConflictos = async () => {
		setLoading(true);
		setResultado('');
		setConflictos([]);
		try {
			const { data } = await api.post('/conflictos-horario/detectar');
			if (data && data.conflictos && Array.isArray(data.conflictos)) {
				setConflictos(data.conflictos);
				setResultado(`Se detectaron ${data.conflictos.length} conflicto(s).`);
			} else {
				setResultado('No se pudo obtener la información de conflictos.');
			}
		} catch (err) {
			setResultado('Error al detectar conflictos.');
		} finally {
			setLoading(false);
		}
	};

	// Validación real de conflictos
	const validarConflictos = async () => {
		setLoading(true);
		setResultado('');
		try {
			const { data } = await api.post('/conflictos-horario/validar', { conflictos });
			if (data && typeof data.valido !== 'undefined') {
				setResultado(data.valido ? 'No hay conflictos por resolver.' : 'Validación completada. Hay conflictos por resolver.');
			} else {
				setResultado('No se pudo validar los conflictos.');
			}
		} catch (err) {
			setResultado('Error al validar los conflictos.');
		} finally {
			setLoading(false);
		}
	};

	// Resolución real de conflictos
	const resolverConflictos = async () => {
		setLoading(true);
		setResultado('');
		try {
			const { data } = await api.post('/conflictos-horario/resolver', { conflictos });
			if (data && data.resuelto) {
				setConflictos([]);
				setResultado('Todos los conflictos han sido resueltos y confirmados.');
			} else {
				setResultado('No se pudo resolver los conflictos.');
			}
		} catch (err) {
			setResultado('Error al resolver los conflictos.');
		} finally {
			setLoading(false);
		}
	};

		// Notificar resultado
		const notificarResultado = async () => {
			setLoading(true);
			setResultado('');
			try {
				const { data } = await api.post('/conflictos-horario/notificar-resultado', { conflictos });
				if (data && data.notificado) {
					setResultado('Resultado notificado correctamente.');
				} else {
					setResultado('No se pudo notificar el resultado.');
				}
			} catch (err) {
				setResultado('Error al notificar el resultado.');
			} finally {
				setLoading(false);
			}
		};

		return (
			<div className="conflictos-container">
				<h2>CU12 – Gestionar conflictos de horario</h2>
				<div className="acciones-conflictos">
					<button className="btn btn-primary" onClick={detectarConflictos} disabled={loading}>
						Detectar Conflictos
					</button>
					<button className="btn btn-warning" onClick={validarConflictos} disabled={loading || conflictos.length === 0}>
						Validar Conflictos
					</button>
					<button className="btn btn-success" onClick={resolverConflictos} disabled={loading || conflictos.length === 0}>
						Resolver y Confirmar
					</button>
					<button className="btn btn-info" onClick={notificarResultado} disabled={loading || conflictos.length === 0}>
						Notificar Resultado
					</button>
				</div>
				<div className="resultado-conflictos">
					{resultado && <p>{resultado}</p>}
				</div>
				<div className="lista-conflictos">
					<h4>Conflictos Detectados</h4>
					{conflictos.length === 0 ? (
						<p>No hay conflictos detectados.</p>
					) : (
						<ul>
							{conflictos.map((c) => (
								<li key={c.id}>{c.descripcion}</li>
							))}
						</ul>
					)}
				</div>
			</div>
		);
};

export default ConflictosHorario;
