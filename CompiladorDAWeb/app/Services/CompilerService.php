<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompilerService
{
    public function compile(string $csvContent, string $sourceCode)
{
    try {
        // Generar UN timestamp para todos los archivos
        $timestamp = now()->timestamp;
        $csvFilename = "data_{$timestamp}.csv";
        $sourceFilename = "source_{$timestamp}.txt";
        $outputFilename = "output_{$timestamp}.py";
        $resultFilename = "resultado_{$timestamp}.csv";
        $graphFilename = "grafico_{$timestamp}.png"; // ← Mismo timestamp

        // Rutas completas
        $csvPath = storage_path("app/uploads/{$csvFilename}");
        $sourcePath = storage_path("app/uploads/{$sourceFilename}");
        $outputPath = storage_path("app/uploads/{$outputFilename}");
        $resultPath = storage_path("app/uploads/{$resultFilename}");
        $graphPath = storage_path("app/uploads/{$graphFilename}");

        // Guardar archivos temporales
        file_put_contents($csvPath, $csvContent);
        file_put_contents($sourcePath, $sourceCode);

        // Reemplazar "datos.csv" en el código fuente
        $sourceCode = str_replace('"datos.csv"', '"' . $csvFilename . '"', $sourceCode);
        $sourceCode = str_replace("'datos.csv'", "'" . $csvFilename . "'", $sourceCode);
        file_put_contents($sourcePath, $sourceCode);

        Log::info("Archivos temporales generados con timestamp: {$timestamp}");

        // Ejecutar compilador C#
        $compilerResult = $this->runCompiler($sourcePath, $timestamp); // ← Pasar timestamp
        
        if (!$compilerResult['success']) {
            return [
                'success' => false,
                'error' => 'Error en compilación: ' . $compilerResult['error'],
                'compiler_output' => $compilerResult['output']
            ];
        }

        // Buscar output.py generado
        $outputPath = $this->findOutputFile($outputPath, $timestamp);

        // Reemplazar también en el código Python generado
        if (file_exists($outputPath)) {
            $pythonCode = file_get_contents($outputPath);
            
            // ✅ REEMPLAZAR TODOS LOS NOMBRES DE ARCHIVO
            $pythonCode = str_replace('"datos.csv"', '"' . $csvFilename . '"', $pythonCode);
            $pythonCode = str_replace("'datos.csv'", "'" . $csvFilename . "'", $pythonCode);
            $pythonCode = str_replace('"resultado.csv"', '"' . $resultFilename . '"', $pythonCode);
            $pythonCode = str_replace("'resultado.csv'", "'" . $resultFilename . "'", $pythonCode);
            $pythonCode = str_replace('"grafico.png"', '"' . $graphFilename . '"', $pythonCode);
            $pythonCode = str_replace("'grafico.png'", "'" . $graphFilename . "'", $pythonCode);
            
            file_put_contents($outputPath, $pythonCode);
            
            Log::info("✅ Nombres de archivo reemplazados en código Python");
        }

        // Ejecutar Python
        $pythonResult = $this->runPython($outputPath, $resultPath, $graphPath, $timestamp);
        
        if (!$pythonResult['success']) {
            return [
                'success' => false,
                'error' => 'Error en ejecución Python: ' . $pythonResult['error'],
                'python_output' => $pythonResult['output']
            ];
        }

        // Leer resultado
        $resultData = $this->readCsvResult($resultPath);

        return [
            'success' => true,
            'result_data' => $resultData,
            'result_file' => $resultFilename,
            'graph_file' => $graphFilename, // ← Incluir nombre del gráfico
            'compiler_output' => $compilerResult['output'],
            'python_output' => $pythonResult['output'],
            'timestamp' => $timestamp // ← Para referencia
        ];

    } catch (\Exception $e) {
        Log::error('Error en compilación: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Error del sistema: ' . $e->getMessage()
        ];
    }
}
//     public function compile($csvContent, $sourceCode)
// {
//     try {
//         Generar nombres únicos para archivos temporales
//         $timestamp = now()->timestamp;
//         $csvFilename = "data_{$timestamp}.csv";
//         $sourceFilename = "source_{$timestamp}.txt";
//         $outputFilename = "output_{$timestamp}.py";
//         $resultFilename = "resultado_{$timestamp}.csv";

//         Rutas completas
//         $csvPath = storage_path("app/uploads/{$csvFilename}");
//         $sourcePath = storage_path("app/uploads/{$sourceFilename}");
//         $outputPath = storage_path("app/uploads/{$outputFilename}");
//         $resultPath = storage_path("app/uploads/{$resultFilename}");

//         Guardar archivos temporales
//         file_put_contents($csvPath, $csvContent);
//         file_put_contents($sourcePath, $sourceCode);

//         ✅ PASO 1: Reemplazar en el código fuente ANTES de compilar
//         $sourceCode = str_replace('"datos.csv"', '"' . $csvFilename . '"', $sourceCode);
//         $sourceCode = str_replace("'datos.csv'", "'" . $csvFilename . "'", $sourceCode);
//         file_put_contents($sourcePath, $sourceCode);

//         Log::info("Código fuente procesado. Archivo CSV: {$csvFilename}");

//         Ejecutar compilador C#
//         $compilerResult = $this->runCompiler($sourcePath);
        
//         if (!$compilerResult['success']) {
//             return [
//                 'success' => false,
//                 'error' => 'Error en compilación: ' . $compilerResult['error'],
//                 'compiler_output' => $compilerResult['output']
//             ];
//         }

//         Buscar output.py generado
//         $outputPath = $this->findOutputFile($outputPath);

//         ✅ PASO 2: Reemplazar también en el código Python generado (por si acaso)
//         if (file_exists($outputPath)) {
//             $pythonCode = file_get_contents($outputPath);
            
//             Hacer múltiples reemplazos para cubrir todos los casos
//             $pythonCode = str_replace('"datos.csv"', '"' . $csvFilename . '"', $pythonCode);
//             $pythonCode = str_replace("'datos.csv'", "'" . $csvFilename . "'", $pythonCode);
//             $pythonCode = str_replace('datos.csv', $csvFilename, $pythonCode);
            
//             file_put_contents($outputPath, $pythonCode);
            
//             Log::info("✅ Reemplazado 'datos.csv' por '{$csvFilename}' en el código Python");
//             Log::info("Contenido Python después del reemplazo:");
//             Log::info($pythonCode);
//         } else {
//             Log::warning("❌ output.py no encontrado para reemplazo");
//         }

//         Ejecutar Python
//         $pythonResult = $this->runPython($outputPath, $resultPath);
        
//         if (!$pythonResult['success']) {
//             return [
//                 'success' => false,
//                 'error' => 'Error en ejecución Python: ' . $pythonResult['error'],
//                 'python_output' => $pythonResult['output']
//             ];
//         }

//         Leer resultado
//         $resultData = $this->readCsvResult($resultPath);

//         return [
//             'success' => true,
//             'result_data' => $resultData,
//             'result_file' => $resultFilename,
//             'compiler_output' => $compilerResult['output'],
//             'python_output' => $pythonResult['output']
//         ];

//     } catch (\Exception $e) {
//         Log::error('Error en compilación: ' . $e->getMessage());
//         return [
//             'success' => false,
//             'error' => 'Error del sistema: ' . $e->getMessage()
//         ];
//     }
// }
    private function runCompiler($sourcePath, $timestamp)
{
    $compilerPath = config('services.compiler.path');

    if (!file_exists($compilerPath)) {
        throw new \Exception("El compilador no se encuentra en: {$compilerPath}");
    }

    $command = "\"{$compilerPath}\" \"{$sourcePath}\"";

    Log::info("Ejecutando compilador: {$command}");
    
    $process = Process::timeout(30)->run($command);

    $expectedOutputPath = storage_path("app/uploads/output_{$timestamp}.py");
    $outputPath = $this->findOutputFile($expectedOutputPath, $timestamp);

    return [
        'success' => $process->successful() && file_exists($outputPath),
        'output' => $process->output() . "\n" . $process->errorOutput(),
        'error' => $process->errorOutput(),
        'output_file' => $outputPath
    ];
}

private function findOutputFile($expectedPath, $timestamp)
{
    $possiblePaths = [
        $expectedPath,
        storage_path('app/uploads/output.py'),
        storage_path("app/uploads/output_{$timestamp}.py"),
        getcwd() . '/output.py',
        'output.py'
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            Log::info("✅ output.py encontrado en: {$path}");
            
            if ($path !== $expectedPath) {
                copy($path, $expectedPath);
                Log::info("✅ output.py copiado a: {$expectedPath}");
            }
            
            return $expectedPath;
        }
    }

    Log::warning("❌ output.py NO encontrado en ninguna ubicación");
    return $expectedPath;
}
    // private function runCompiler($sourcePath)
    // {
    //     $compilerPath = config('services.compiler.path');
    //     $projectPath = config('services.compiler.project');

    //     // Verificar si el archivo .exe existe
    //     if (!file_exists($compilerPath) && !Str::contains(strtolower($compilerPath), 'dotnet')) {
    //         throw new \Exception("El compilador no se encuentra en: {$compilerPath}");
    //     }

    //     // Opción 1: Usar .exe directo
    //     if (Str::contains(strtolower($compilerPath), '.exe')) {
    //         $command = "\"{$compilerPath}\" \"{$sourcePath}\"";
    //     }
    //     // Opción 2: Usar dotnet run
    //     elseif (Str::contains(strtolower($compilerPath), 'dotnet') && $projectPath) {
    //         $command = "{$compilerPath} run --project \"{$projectPath}\" \"{$sourcePath}\"";
    //     } 
    //     else {
    //         throw new \Exception("Configuración del compilador inválida");
    //     }

    //     \Log::info("Ejecutando compilador: {$command}");
        
    //     $process = Process::timeout(30)->run($command);

    //     return [
    //         'success' => $process->successful(),
    //         'output' => $process->output() . "\n" . $process->errorOutput(),
    //         'error' => $process->errorOutput()
    //     ];
    // }
    /**
     * Encuentra la ruta de Python
     */
    private function findPythonPath()
    {
        $pythonPath = config('services.python.path');
        
        // Si ya es una ruta completa y existe, usarla
        if (file_exists($pythonPath)) {
            return $pythonPath;
        }
        
        // Buscar Python en ubicaciones comunes de Windows
        $commonPaths = [
            'C:\\Python311\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python39\\python.exe',
            'C:\\Python38\\python.exe',
            'C:\\Program Files\\Python311\\python.exe',
            'C:\\Program Files\\Python310\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\anaconda3\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\miniconda3\\python.exe',
            'python.exe', // Último intento con el PATH
            'python',     // Último intento con el PATH
        ];
        
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                Log::info("Python encontrado en: {$path}");
                return $path;
            }
        }
        
        throw new \Exception("No se pudo encontrar Python. Verifica que esté instalado.");
    }

    private function runPython($outputPath, $resultPath, $graphPath, $timestamp)
{
    $pythonPath = $this->findPythonPath();
    
    $workingDir = storage_path('app/uploads');
    
    $env = [
        'HOME' => $workingDir,
        'USERPROFILE' => $workingDir,
        'HOMEPATH' => $workingDir,
        'MATPLOTLIBRC' => $workingDir,
        'MPLCONFIGDIR' => $workingDir,
        'PYTHONIOENCODING' => 'utf-8',
    ];

    $command = "cd \"{$workingDir}\" && \"{$pythonPath}\" \"" . basename($outputPath) . "\"";

    Log::info("Ejecutando Python: {$command}");
    
    $process = Process::timeout(60)
                     ->path($workingDir)
                     ->env($env)
                     ->run($command);

    $output = mb_convert_encoding($process->output(), 'UTF-8', 'UTF-8');
    $errorOutput = mb_convert_encoding($process->errorOutput(), 'UTF-8', 'UTF-8');

    // ✅ VERIFICAR AMBOS ARCHIVOS CON EL TIMESTAMP CORRECTO
    $hasResult = file_exists($resultPath);
    $hasGraph = file_exists($graphPath);
    $pythonSuccess = $process->successful() && ($hasResult || $hasGraph);

    Log::info("Verificación de archivos:");
    Log::info("- Resultado: " . ($hasResult ? "✅ {$resultPath}" : "❌ No encontrado"));
    Log::info("- Gráfico: " . ($hasGraph ? "✅ {$graphPath}" : "❌ No encontrado"));

    return [
        'success' => $pythonSuccess,
        'output' => $output . "\n" . $errorOutput,
        'error' => $errorOutput,
        'has_result' => $hasResult,
        'has_graph' => $hasGraph
    ];
}
    private function readCsvResult($filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    private function cleanup($files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}