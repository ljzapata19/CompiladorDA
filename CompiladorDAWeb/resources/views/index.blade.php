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
                        <h5>❌ Error de Compilación</h5>
                        <p>{{ $errors->first('compile_error') }}</p>
                        
                        @if($errors->has('compiler_output'))
                            <h6>Salida del Compilador:</h6>
                            <pre class="output">{{ $errors->first('compiler_output') }}</pre>
                        @endif
                        
                        @if($errors->has('python_output'))
                            <h6>Salida de Python:</h6>
                            <pre class="output">{{ $errors->first('python_output') }}</pre>
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
                                  placeholder="Escribe tus comandos en lenguaje natural aquí..." required>{{ old('source_code', 'ANALIZAR DATOS "datos.csv"
                                    FILTRAR WHERE edad > 25
                                    AGRUPAR POR departamento
                                    CALCULAR promedio(salario), suma(ventas)
                                    GRAFICAR tipo=barras eje_x=departamento eje_y=salario
                                    GUARDAR COMO "resultado.csv"') }}</textarea>
                        <div class="form-text">
                            <strong>Comandos disponibles:</strong> ANALIZAR DATOS, FILTRAR WHERE, AGRUPAR POR, CALCULAR, GRAFICAR, GUARDAR COMO
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
                            <pre class="code-area">ANALIZAR DATOS "datos.csv"
                                FILTRAR WHERE edad > 25
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