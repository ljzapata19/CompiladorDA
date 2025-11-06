@extends('layouts.app')

@section('title', 'Resultados del Análisis')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">📈 Resultados del Análisis</h4>
                <div>
                    <a href="{{ route('index') }}" class="btn btn-outline-secondary">
                        🔄 Nuevo Análisis
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <h5>✅ Compilación Exitosa</h5>
                        <p>El código se compiló y ejecutó correctamente.</p>
                    </div>
                @endif

                

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>📊 Tabla de Resultados</h5>
                        
                        <!-- @if(session('graph_path'))
                            <a href="{{ route('show.graph', ['filename' => session('graph_path')]) }}" 
                            class="btn btn-primary" 
                            target="_blank">
                                📊 Ver Gráfico 
                            </a>
                        @endif -->
                        
                    </div>
                    <div class="card-body">
                        @if(!empty($resultData))
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            @foreach(array_keys($resultData[0] ?? []) as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultData as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No se encontraron datos en el archivo de resultados.
                            </div>
                        @endif
                    </div>
                </div>
                {{-- SECCIÓN DEL GRÁFICO --}}
                @if(session('graph_path'))
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>📊 Gráfico Generado</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset(session('graph_path')) }}" 
                            alt="Gráfico de resultados" 
                            class="img-fluid rounded shadow" 
                            style="max-height: 500px; border: 1px solid #dee2e6;">
                        <div class="mt-3">
                            <a href="{{ asset(session('graph_path')) }}" 
                            download 
                            class="btn btn-outline-primary btn-sm">
                                💾 Descargar Gráfico
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6>ℹ️ Información sobre Gráficos</h6>
                            <p class="mb-0">
                                No se generó un gráfico en esta ejecución. 
                                Asegúrate de incluir el comando <code>GRAFICAR</code> en tu código fuente.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                <div class="mt-4 text-center">
                    <a href="{{ route('index') }}" class="btn btn-success btn-lg">
                        🏠 Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection