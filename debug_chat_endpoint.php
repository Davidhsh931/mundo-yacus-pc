<?php

/**
 * Script de depuración para el endpoint del chat
 */

echo "=== DEPURACIÓN DEL ENDPOINT DEL CHAT ===\n\n";

// Simular el request directamente
echo "1. Probando ChatBasicController directamente...\n";

try {
    // Crear un mock request
    $mockRequest = new class {
        public function input($key, $default = null) {
            $data = [
                'message' => 'Hola, quiero información sobre cuyes',
                'session_id' => 'test_session_123'
            ];
            return $data[$key] ?? $default;
        }
        
        public function all() {
            return [
                'message' => 'Hola, quiero información sobre cuyes',
                'session_id' => 'test_session_123'
            ];
        }
    };
    
    // Intentar instanciar y probar el controller
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Inicializar el framework Laravel básico
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    // Crear instancia del controller
    $controller = new \App\Http\Controllers\ChatBasicController();
    
    echo "   Controller instanciado correctamente\n";
    
    // Probar el método sendMessage
    $result = $controller->sendMessage($mockRequest);
    
    echo "   Método sendMessage ejecutado\n";
    echo "   Result: " . json_encode($result) . "\n";
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 2: Probar ChatAIController directamente
echo "2. Probando ChatAIController directamente...\n";

try {
    $aiController = new \App\Http\Controllers\ChatAIController();
    
    $message = 'Hola, quiero información sobre cuyes';
    $sessionId = 'test_session_123';
    $context = [];
    
    echo "   Controller instanciado correctamente\n";
    
    $response = $aiController->generateAdvancedResponse($message, $sessionId, $context);
    
    echo "   Método generateAdvancedResponse ejecutado\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n";
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 3: Probar ChatTranscendentController
echo "3. Probando ChatTranscendentController...\n";

try {
    $transcendentController = new \App\Http\Controllers\ChatTranscendentController();
    
    $message = 'Hola, quiero información sobre cuyes';
    $sessionId = 'test_session_123';
    $context = [];
    
    echo "   Controller instanciado correctamente\n";
    
    $response = $transcendentController->generateTranscendentResponse($message, $sessionId, $context);
    
    echo "   Método generateTranscendentResponse ejecutado\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n";
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA DEPURACIÓN ===\n";

?>
