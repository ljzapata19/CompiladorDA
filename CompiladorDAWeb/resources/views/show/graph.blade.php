@extends('layouts.app')

@section('title', 'Resultados del Análisis')

@section('content')
<div class="container">
        <div class="graph-container">
            {{-- Header --}}
            <div class="graph-header text-center">
                <h1 class="mb-0">📊 Gráfico Generado</h1>
                <p class="mb-0 mt-2 opacity-75">Visualización de resultados del análisis</p>
            </div>

            {{-- Contenido --}}
            <div class="p-4">
                <div class="row">
                    {{-- Columna del gráfico --}}
                    <div class="col-md-8">
                        <div class="text-center p-3 border rounded bg-light">
                            <img src="{{ asset($graphPath) }}" 
                                 alt="Gráfico de resultados" 
                                 class="graph-image img-fluid rounded shadow"
                                 style="max-height: 70vh;">
                        </div>
                    </div>

                    {{-- Columna de información --}}
                    <div class="col-md-4">
                        <h4 class="mb-4">📋 Información del Gráfico</h4>
                        
                        <div class="info-card">
                            <h6>📄 Archivo</h6>
                            <p class="mb-0 text-muted">{{ $fileName }}</p>
                        </div>

                        <div class="info-card">
                            <h6>📏 Dimensiones</h6>
                            <p class="mb-0 text-muted">{{ $dimensions }}</p>
                        </div>

                        <div class="info-card">
                            <h6>💾 Tamaño</h6>
                            <p class="mb-0 text-muted">{{ $fileSize }}</p>
                        </div>

                        <div class="info-card">
                            <h6>📊 Tipo</h6>
                            <p class="mb-0 text-muted">Gráfico de barras generado por matplotlib</p>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ asset($graphPath) }}" 
                               download 
                               class="btn-download text-center">
                                💾 Descargar Gráfico
                            </a>
                            
                            <a href="javascript:window.print()" 
                               class="btn btn-outline-primary text-center">
                                🖨️ Imprimir
                            </a>
                            
                            <a href="{{ url()->previous() }}" 
                               class="btn-back text-center">
                                ↩️ Volver a Resultados
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Información adicional --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6>💡 Información</h6>
                            <p class="mb-0">
                                Este gráfico fue generado automáticamente por el sistema de análisis de datos 
                                a partir de los comandos en lenguaje natural proporcionados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection