import React from 'react';
import ReactDOM from 'react-dom/client';

function TestApp() {
    return (
        <div style={{ padding: '20px', background: 'lightblue' }}>
            <h1>¡React está funcionando!</h1>
            <p>Si ves este mensaje, React se está cargando correctamente.</p>
        </div>
    );
}

const root = document.getElementById('app');
if (root) {
    ReactDOM.createRoot(root).render(<TestApp />);
} else {
    console.error('No se encontró el elemento con id="app"');
}
