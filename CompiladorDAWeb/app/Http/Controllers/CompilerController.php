<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompilerService;
use Illuminate\Support\Facades\Storage;

class CompilerController extends Controller
{
    protected $compilerService;

    public function __construct(CompilerService $compilerService)
    {
        $this->compilerService = $compilerService;
    }

    public function index()
    {
        return view('index');
    }

    public function compile(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'source_code' => 'required|string',
        ]);
        

        try {
            // Obtener contenido del CSV
            $csvContent = file_get_contents($request->file('csv_file')->getRealPath());
            
            // Obtener código fuente
            $sourceCode = $request->input('source_code');

            // Ejecutar compilación
            $result = $this->compilerService->compile($csvContent, $sourceCode);

            if ($result['success']) {
                // === AGREGAR ESTO: Manejar el gráfico generado ===
                $graphPath = null;
                $graphPublicPath = null;
                
                
                
                // Manejar el gráfico generado
                if (isset($result['graph_file'])) {
                    $graphTempPath = storage_path("app/uploads/{$result['graph_file']}");
                    if (file_exists($graphTempPath)) {
                        $graphPublicFilename = 'grafico_public_' . $result['timestamp'] . '.png';
                        $graphPublicPath = public_path($graphPublicFilename);
                        
                        copy($graphTempPath, $graphPublicPath);
                        
                    }
                }

                return redirect()->route('result')->with([
                    'success' => true,
                    'result_data' => $result['result_data'],
                    'result_file' => $result['result_file'],
                    'graph_path' => $graphPublicFilename ?? null,
                    'compiler_output' => $result['compiler_output'],
                    'python_output' => $result['python_output'],
                    'timestamp' => $result['timestamp']
                ]);
            } else {
                return back()->withErrors([
                    'compile_error' => $result['error'],
                    'compiler_output' => $result['compiler_output'] ?? '',
                    'python_output' => $result['python_output'] ?? ''
                ])->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors([
                'compile_error' => 'Error del sistema: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function result()
{
    if (!session()->has('result_data')) {
        return redirect()->route('index');
    }

    return view('result', [
        'resultData' => session('result_data', []),
        'resultFile' => session('result_file'),
        'compilerOutput' => session('compiler_output', ''),
        'pythonOutput' => session('python_output', ''),
        'graphPath' => session('graph_path')  // ← Agregar esto
    ]);
}

    public function showGraphs($filename)
{
    try {
        // Verificar que el archivo existe en la carpeta pública
        $graphPath = public_path($filename);
        
        if (!file_exists($graphPath)) {
            abort(404, 'Gráfico no encontrado');
        }

        // Obtener información del archivo
        $fileInfo = pathinfo($graphPath);
        $fileSize = filesize($graphPath);
        $imageInfo = getimagesize($graphPath);

        return view('graph', [
            'graphPath' => $filename,
            'fileName' => $filename,
            'fileSize' => $this->formatBytes($fileSize),
            'imageInfo' => $imageInfo,
            'dimensions' => $imageInfo ? "{$imageInfo[0]}x{$imageInfo[1]} px" : 'N/A'
        ]);

    } catch (\Exception $e) {
        abort(404, 'Error al cargar el gráfico: ' . $e->getMessage());
    }
}

// Método auxiliar para formatear bytes
    private function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
}