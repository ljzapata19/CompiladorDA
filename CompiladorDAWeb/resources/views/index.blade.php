@extends('layouts.app')

@section('title', 'Compilador de Lenguaje Natural')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Compilador de Lenguaje Natural para Análisis de Datos</h4>
            </div>
            <div class="card-body">
                @if($errors->has('compile_error'))
                    <div class="alert alert-danger">
                        <h5>❌ Error en la Consulta</h5>
                        <p class="mb-3">{!! nl2br(e(getFriendlyErrorMessage($errors->first('compile_error')))) !!}</p>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6>💡 Sugerencias:</h6>
                            <ul class="mb-0">
                                <li>Verifica que todos los comandos estén completos</li>
                                <li>Revisa la ortografía y sintaxis</li>
                                <li>Asegúrate de usar los comandos en el orden correcto</li>
                                <li>Consulta el ejemplo de uso para ver la sintaxis correcta</li>
                            </ul>
                        </div>
                        
                        {{-- Mostrar detalles técnicos solo en modo debug --}}
                        @if(config('app.debug'))
                            @if($errors->has('compiler_output'))
                                <details class="mt-3">
                                    <summary class="btn btn-sm btn-outline-secondary">Ver detalles técnicos</summary>
                                    <h6 class="mt-2">Salida del Compilador:</h6>
                                    <pre class="output bg-dark text-light p-3 small">{{ $errors->first('compiler_output') }}</pre>
                                </details>
                            @endif
                        @endif
                    </div>
                @endif

                <form action="{{ route('compile') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="csv_file" class="form-label">📊 Archivo CSV con Datos</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <div class="form-text">Sube el archivo CSV que contiene los datos a analizar.</div>
                    </div>

                    <div class="mb-4">
                        <label for="source_code" class="form-label">📝 Código Fuente en Lenguaje Natural</label>
                        <textarea class="form-control code-area" id="source_code" name="source_code" rows="12" 
                                  placeholder="Escribe tus comandos en lenguaje natural aquí..." required>
{{ old('source_code', 'ANALIZAR DATOS
FILTRAR DONDE edad > 25
AGRUPAR POR departamento
CALCULAR promedio(salario), suma(ventas)
GRAFICAR tipo=barras eje_x=departamento eje_y=salario
GUARDAR COMO "resultado.csv"') }}</textarea>
                        <div class="form-text">
                            <strong>Comandos disponibles:</strong> ANALIZAR DATOS, FILTRAR DONDE, AGRUPAR POR, CALCULAR, GRAFICAR, GUARDAR COMO
                        </div>
                    </div> 

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            🚀 Compilar y Ejecutar
                        </button>
                    </div>
                </form>

                <div class="mt-4">
                    <h5>📚 Ejemplo de Uso:</h5>
                    <div class="card">
                        <div class="card-body">
                            <h6>Archivo CSV (datos.csv):</h6>
                            <pre class="code-area">nombre,edad,departamento,salario,ventas
Juan,30,Ventas,50000,150000
Maria,28,Marketing,45000,120000
Carlos,35,Ventas,55000,200000
Ana,22,Marketing,40000,80000
Luis,40,Ventas,60000,250000</pre>
                            
                            <h6 class="mt-3">Comandos (source.txt):</h6>
                            <pre class="code-area">ANALIZAR DATOS 
FILTRAR DONDE edad > 25
AGRUPAR POR departamento
CALCULAR promedio(salario), suma(ventas)
GRAFICAR tipo=barras eje_x=departamento eje_y=salario
GUARDAR COMO "resultado.csv"</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@php
function getFriendlyErrorMessage($error) {
    $error = htmlspecialchars($error);
    
    // Traducciones simples
    if (str_contains($error, 'POR expected')) {
        return '❌ Error de sintaxis: Falta <code>POR</code> después de <code>AGRUPAR</code>. Ejemplo: <code>AGRUPAR POR departamento</code>';
    }
    
    if (str_contains($error, 'ident expected') && str_contains($error, 'FILTRAR')) {
        return '❌ Filtro incompleto: Después de <code>FILTRAR DONDE</code> debe seguir una condición. Ejemplo: <code>FILTRAR DONDE edad > 25</code>';
    }
    
    if (str_contains($error, 'EOF expected')) {
        return '❌ Comando incompleto: La consulta está truncada. Verifica que todos los comandos estén completos.';
    }
    
    if (str_contains($error, '??')) {
        return '⚠️ Elemento inesperado: Revisa la sintaxis del comando.';
    }
    
    // Mensaje por defecto
    return '❌ Error de compilación: Revisa la sintaxis de tu consulta.';
}
@endphp