import React from 'react';

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { 
      hasError: false, 
      error: null,
      errorInfo: null 
    };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    console.error('🔴 ErrorBoundary capturó error:', error);
    console.error('🔴 Error Info:', errorInfo);
    this.setState({
      error,
      errorInfo
    });
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-boundary">
          <div className="error-container">
            <h2>❌ Algo salió mal</h2>
            <p>Ha ocurrido un error al renderizar el componente.</p>
            
            <div className="error-details">
              <h3>Detalles del error:</h3>
              <pre>{this.state.error && this.state.error.toString()}</pre>
              
              {this.state.errorInfo && (
                <>
                  <h3>Stack Trace:</h3>
                  <pre>{this.state.errorInfo.componentStack}</pre>
                </>
              )}
            </div>

            <button 
              onClick={() => window.location.reload()}
              className="btn btn-primary"
            >
              Recargar página
            </button>
          </div>

          <style>{`
            .error-boundary {
              padding: 20px;
              background-color: #f8d7da;
              border: 1px solid #f5c6cb;
              border-radius: 4px;
            }

            .error-container {
              max-width: 800px;
              margin: 0 auto;
            }

            .error-container h2 {
              color: #721c24;
              margin-top: 0;
            }

            .error-details {
              background-color: #fff;
              border: 1px solid #dee2e6;
              border-radius: 4px;
              padding: 15px;
              margin: 15px 0;
              overflow-x: auto;
            }

            .error-details pre {
              background-color: #f5f5f5;
              padding: 10px;
              border-radius: 3px;
              font-size: 12px;
              overflow-x: auto;
              color: #721c24;
            }

            .btn {
              padding: 10px 20px;
              border: none;
              border-radius: 4px;
              cursor: pointer;
              font-size: 14px;
            }

            .btn-primary {
              background-color: #007bff;
              color: white;
            }

            .btn-primary:hover {
              background-color: #0056b3;
            }
          `}</style>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
