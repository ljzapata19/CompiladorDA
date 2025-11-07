<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompilerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            // ✅ INICIALIZAR LA VARIABLE ANTES DEL IF
            $graphPublicFilename = null;
            
            // Manejar el gráfico generado
            if (isset($result['graph_file'])) {
                $graphTempPath = storage_path("app/uploads/{$result['graph_file']}");
                if (file_exists($graphTempPath)) {
                    $graphPublicFilename = 'grafico_public_' . $result['timestamp'] . '.png';
                    $graphPublicPath = public_path($graphPublicFilename);
                    
                    copy($graphTempPath, $graphPublicPath);
                    Log::info("Gráfico copiado a: " . $graphPublicPath);
                }
            }

            // ✅ AGREGAR LOG PARA DEBUG (AHORA FUNCIONARÁ)
            Log::info("Redirigiendo a result con timestamp: " . $result['timestamp']);
            Log::info("Graph path: " . ($graphPublicFilename ?? 'null'));

            return redirect()->route('result')->with([
                'success' => true,
                'result_data' => $result['result_data'],
                'result_file' => $result['result_file'],
                'graph_path' => $graphPublicFilename, // ✅ Ya está inicializada
                'compiler_output' => $result['compiler_output'],
                'python_output' => $result['python_output'],
                'timestamp' => $result['timestamp']
            ]);
            
        } else {
            Log::error("Error en compilación " . $result['error']);
            return back()->withErrors([
                'compile_error' => $result['error'],
                'compiler_output' => $result['compiler_output'] ?? '',
                'python_output' => $result['python_output'] ?? ''
            ])->withInput();
        }

    } catch (\Exception $e) {
        Log::error('Error en compile: ' . $e->getMessage());
        return back()->withErrors([
            'compile_error' => 'Error del sistema: ' . $e->getMessage()
        ])->withInput();
    }
}
    // public function compile(Request $request)
    // {
    //     $request->validate([
    //         'csv_file' => 'required|file|mimes:csv,txt',
    //         'source_code' => 'required|string',
    //     ]);
        

    //     try {
    //         // Obtener contenido del CSV
    //         $csvContent = file_get_contents($request->file('csv_file')->getRealPath());
            
    //         // Obtener código fuente
    //         $sourceCode = $request->input('source_code');

    //         // Ejecutar compilación
    //         $result = $this->compilerService->compile($csvContent, $sourceCode);

    //         if ($result['success']) {
    //             // === AGREGAR ESTO: Manejar el gráfico generado ===
    //             $graphPath = null;
    //             $graphPublicPath = null;
                
                
                
    //             // Manejar el gráfico generado
    //             if (isset($result['graph_file'])) {
    //                 $graphTempPath = storage_path("app/uploads/{$result['graph_file']}");
    //                 if (file_exists($graphTempPath)) {
    //                     $graphPublicFilename = 'grafico_public_' . $result['timestamp'] . '.png';
    //                     $graphPublicPath = public_path($graphPublicFilename);
                        
    //                     copy($graphTempPath, $graphPublicPath);
                        
    //                 }
    //             }

    //             return redirect()->route('result')->with([
    //                 'success' => true,
    //                 'result_data' => $result['result_data'],
    //                 'result_file' => $result['result_file'],
    //                 'graph_path' => $graphPublicFilename ?? null,
    //                 'compiler_output' => $result['compiler_output'],
    //                 'python_output' => $result['python_output'],
    //                 'timestamp' => $result['timestamp']
    //             ]);
                
    //         } else {
    //             return back()->withErrors([
    //                 'compile_error' => $result['error'],
    //                 'compiler_output' => $result['compiler_output'] ?? '',
    //                 'python_output' => $result['python_output'] ?? ''
    //             ])->withInput();
    //         }

    //     } catch (\Exception $e) {
    //         return back()->withErrors([
    //             'compile_error' => 'Error del sistema: ' . $e->getMessage()
    //         ])->withInput();
    //     }
    // }

public function result()
{
    if (!session()->has('result_data') && !session()->has('result_file')) {
        return redirect()->route('index');
    }

    // ✅ Asegurar que tenemos timestamp
    if (session()->has('result_file') && !session()->has('timestamp')) {
        $resultFile = session('result_file');
        if (preg_match('/resultado_(\d+)\.csv/', $resultFile, $matches)) {
            session(['timestamp' => $matches[1]]);
        }
    }

    return view('result', [
        'resultData' => session('result_data', []),
        'resultFile' => session('result_file'),
        'compilerOutput' => session('compiler_output', ''),
        'pythonOutput' => session('python_output', ''),
        'graphPath' => session('graph_path'),
        'timestamp' => session('timestamp')
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
public function showPythonCode($timestamp = null)
{
    // ✅ PRIORIDAD: timestamp desde parámetro URL
    if (!$timestamp) {
        // ✅ SEGUNDA OPCIÓN: timestamp desde sesión
        $timestamp = session('timestamp');
        
        // ✅ TERCERA OPCIÓN: extraer de result_file en sesión
        if (!$timestamp && session('result_file')) {
            $resultFile = session('result_file');
            if (preg_match('/resultado_(\d+)\.csv/', $resultFile, $matches)) {
                $timestamp = $matches[1];
            }
        }
    }
    
    // Si aún no hay timestamp, mostrar error
    if (!$timestamp) {
        Log::warning("No se pudo encontrar timestamp para mostrar código Python");
        return redirect()->route('result')->withErrors([
            'error' => 'No se encontró información de la sesión actual. Por favor, ejecute un análisis primero.'
        ]);
    }

    Log::info("Buscando código Python con timestamp: " . $timestamp);

    $pythonFilePath = storage_path("app/uploads/output_{$timestamp}.py");
    Log::info("Buscando archivo Python en: " . $pythonFilePath);
    
    $pythonCode = '';
    if (file_exists($pythonFilePath)) {
        $pythonCode = file_get_contents($pythonFilePath);
        Log::info("✅ Archivo Python encontrado, tamaño: " . strlen($pythonCode) . " bytes");
    } else {
        Log::warning("❌ Archivo Python NO encontrado en: " . $pythonFilePath);
        
        // Intentar buscar el archivo sin timestamp
        $fallbackPath = storage_path('app/uploads/output.py');
        Log::info("Buscando archivo fallback en: " . $fallbackPath);
        
        if (file_exists($fallbackPath)) {
            $pythonCode = file_get_contents($fallbackPath);
            Log::info("✅ Archivo fallback encontrado");
        } else {
            Log::warning("❌ No se encontró ningún archivo Python");
            return redirect()->route('result')->withErrors([
                'error' => 'No se pudo encontrar el código Python generado. Los archivos temporales pueden haber sido limpiados.'
            ]);
        }
    }

    return view('python-code', [
        'pythonCode' => $pythonCode,
        'timestamp' => $timestamp
    ]);
}

public function downloadCSV($filename)
{
    try {
        $filePath = storage_path("app/uploads/{$filename}");
        
        if (!file_exists($filePath)) {
            abort(404, 'Archivo CSV no encontrado');
        }

        // Mejorar el nombre del archivo para descarga
        $downloadName = $this->generateDownloadName($filename);
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$downloadName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->download($filePath, $downloadName, $headers);

    } catch (\Exception $e) {
        Log::error("Error al descargar CSV {$filename}: " . $e->getMessage());
        return redirect()->route('result')->withErrors([
            'error' => 'Error al descargar el archivo: ' . $e->getMessage()
        ]);
    }
}

private function generateDownloadName($originalFilename)
{
    // Ejemplo: resultado_1762473125.csv → resultados_analisis.csv
    if (preg_match('/resultado_(\d+)\.csv/', $originalFilename)) {
        return 'resultados_analisis.csv';
    }
    
    return $originalFilename;
}
}