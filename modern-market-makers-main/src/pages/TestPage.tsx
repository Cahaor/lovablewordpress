import { useState } from 'react';

export default function TestPage() {
  const [count, setCount] = useState(0);
  
  return (
    <div style={{ padding: '40px', fontFamily: 'Arial' }}>
      <h1>¡Hola! La página funciona ✅</h1>
      <p>Si ves esto, el routing está funcionando correctamente.</p>
      <button 
        onClick={() => setCount(count + 1)}
        style={{ 
          padding: '10px 20px', 
          background: '#667eea', 
          color: 'white', 
          border: 'none', 
          borderRadius: '5px',
          cursor: 'pointer',
          marginTop: '20px'
        }}
      >
        Clicks: {count}
      </button>
      <div style={{ marginTop: '40px' }}>
        <a href="/" style={{ color: '#667eea' }}>← Volver al inicio</a>
      </div>
    </div>
  );
}
