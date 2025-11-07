@extends('layouts.app')

@section('title', 'Código Python Generado')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">🐍 Código Python Generado</h4>
                <div>
                    <a href="#" onclick="goBackToResults()" class="btn btn-outline-secondary">
                        ↩️ Volver a Resultados
                    </a>
                    <a href="{{ route('index') }}" class="btn btn-outline-primary">
                        🏠 Inicio
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(!empty($pythonCode))
                    <div class="alert alert-info">
                        <h6>📝 Información</h6>
                        <p class="mb-0">
                            Este es el código Python generado automáticamente a partir de tus comandos en lenguaje natural.
                            Puedes copiarlo y ejecutarlo en cualquier entorno Python.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6>output_{{$timestamp}}.py</h6>
                            <div>
                                
                                <button onclick="copyToClipboard(this)" class="btn btn-outline-success btn-sm">
                                    📋 Copiar Código
                                </button>
                                <a href="data:text/plain;charset=utf-8,{{ urlencode($pythonCode) }}" 
                                   download="output_{{$timestamp}}.py" 
                                   class="btn btn-outline-primary btn-sm">
                                    💾 Descargar
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <pre class="m-0" style="background: #f8f9fa; padding: 20px; border-radius: 5px; max-height: 600px; overflow-y: auto;"><code class="language-python" id="python-code" style="font-family: 'Courier New', monospace; font-size: 0.9em; line-height: 1.4;">{{ $pythonCode }}</code></pre>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h6>❌ Código no disponible</h6>
                        <p class="mb-0">
                            No se pudo encontrar el código Python generado. 
                            Esto puede deberse a que los archivos temporales fueron limpiados.
                        </p>
                    </div>
                @endif

                <div class="mt-4">
                    <h6>💡 ¿Cómo ejecutar este código?</h6>
                    <ul>
                        <li>Copie el código en un archivo <code>.py</code></li>
                        <li>Asegúrese de tener instalados: <code>pandas</code> y <code>matplotlib</code></li>
                        <li>Ejecute: <code>python nombre_del_archivo.py</code></li>
                        <li>Los archivos CSV necesarios deben estar en la misma carpeta</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function goBackToResults() {
    // Intentar volver atrás en el historial
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Si no hay historial, redirigir al result con timestamp
        window.location.href = "{{ route('result', ['timestamp' => $timestamp]) }}";
    }
}

function copyToClipboard(button) {
    const codeElement = document.getElementById('python-code');
    const code = codeElement.textContent;
    
    navigator.clipboard.writeText(code).then(function() {
        const originalText = button.innerHTML;
        button.innerHTML = '✅ Copiado!';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');
        }, 2000);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar el código. Por favor, selecciona y copia manualmente.');
    });
}

// ⚠️ ELIMINAR COMPLETAMENTE el resaltado de sintaxis
</script>
@endsection