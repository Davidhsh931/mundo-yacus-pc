<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VisionController extends Controller
{
    public function analyze(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:2048'
            ]);

            // 1. Guardar la imagen
            $path = $request->file('image')->store('temp_vision', 'local');
            $fullPath = Storage::disk('local')->path($path);

            // 2. Ejecutar el script (OJO: Espacio después de python)
            $scriptPath = base_path('scripts/vision_analyzer.py');
            
            // CORRECCIÓN: "python " con espacio. Usamos python porque es Windows.
            $process = Process::timeout(60)->run("python " . escapeshellarg($scriptPath) . " " . escapeshellarg($fullPath));
            
            $output = $process->output();
            $error = $process->errorOutput();

            // 3. Limpiar el archivo temporal
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            // 4. PROCESAR LA RESPUESTA (La "limpieza" del JSON)
            $start = strpos($output, '{');
            $end = strrpos($output, '}');

            if ($start !== false && $end !== false) {
                $jsonStr = substr($output, $start, $end - $start + 1);
                $data = json_decode($jsonStr, true);
                
                if ($data && isset($data['status'])) {
                    // Retornamos el JSON real que viene de Python (Cerdo, Gallina, etc.)
                    return response()->json($data);
                }
            }

            // Si llegamos aquí, algo falló en la comunicación con Python
            Log::error("Error de salida Python: " . $output);
            Log::error("Error de consola Python: " . $error);

            return response()->json([
                'status' => 'error',
                'raza_detectada' => 'Error de Detección',
                'confianza' => '0%',
                'sugerencia' => 'La IA no respondió correctamente. Verifica los logs.'
            ]);

        } catch (\Exception $e) {
            Log::error("Vision Controller Exception: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'raza_detectada' => 'Error Crítico',
                'confianza' => '0%',
                'sugerencia' => $e->getMessage()
            ], 500);
        }
    }
    
    public function index()
{
    return Inertia::render('Admin/VisionScanner');
}
}