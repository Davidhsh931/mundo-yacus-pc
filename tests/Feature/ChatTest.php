<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test suite completo para el asistente de chat
     * Prueba todas las funcionalidades del sistema de chat
     */
    public function test_chat_complete_functionality()
    {
        $testCases = [
            // Nivel 1: Funcionalidades Básicas
            [
                'message' => 'hola',
                'expected_keywords' => ['hola', 'ayudarte'],
                'category' => 'saludo'
            ],
            [
                'message' => '¿Cuánto cuesta el envío?',
                'expected_keywords' => ['envío', '15', 'perú'],
                'category' => 'envío'
            ],
            [
                'message' => '¿Aceptan Yape?',
                'expected_keywords' => ['yape', '999'],
                'category' => 'pago'
            ],
            [
                'message' => '¿Dónde están ubicados?',
                'expected_keywords' => ['huánuco', 'perú'],
                'category' => 'ubicación'
            ],
            [
                'message' => 'gracias',
                'expected_keywords' => ['nada', 'aquí'],
                'category' => 'despedida'
            ],

            // Nivel 2: Variaciones y Sinónimos
            [
                'message' => '¿Cuánto me cobra por mandar?',
                'expected_keywords' => ['envío', '15', 'perú'],
                'category' => 'sinónimos_envío'
            ],
            [
                'message' => '¿Hay disponibilidad de cuyes?',
                'expected_keywords' => ['stock', 'productos'],
                'category' => 'disponibilidad'
            ],
            [
                'message' => 'quiero comprar',
                'expected_keywords' => ['productos', 'comprar'],
                'category' => 'compra_intención'
            ],
            [
                'message' => '¿Qué formas de pago tienen?',
                'expected_keywords' => ['yape', 'plin', 'efectivo'],
                'category' => 'métodos_pago'
            ],
            [
                'message' => 'malo el servicio',
                'expected_keywords' => ['lamento', 'whatsapp'],
                'category' => 'queja'
            ],

            // Nivel 4: Datos Dinámicos
            [
                'message' => '¿Cuánto cuesta un cuy?',
                'expected_keywords' => ['precios', 's/.'],
                'category' => 'precios_dinámicos'
            ],
            [
                'message' => '¿Cuántos cuyes tienen disponibles?',
                'expected_keywords' => ['stock', 'disponibles'],
                'category' => 'stock_dinámico'
            ],

            // Nivel 5: Errores y Límites
            [
                'message' => '¿Cuánto cuesta un unicornio?',
                'expected_keywords' => ['unicornio', 'cuyes'],
                'category' => 'producto_no_existente'
            ],
            [
                'message' => 'xyz123',
                'expected_keywords' => ['reformula', 'whatsapp'],
                'category' => 'texto_incomprensible'
            ],
            [
                'message' => '¿Cuál es el sentido de la vida?',
                'expected_keywords' => ['profunda', 'productos'],
                'category' => 'pregunta_filosófica'
            ],

            // Nivel 6: Casos Complejos
            [
                'message' => 'Hola, quiero comprar un cuy Perú, ¿cuánto cuesta y hay stock?',
                'expected_keywords' => ['excelente', 'precios', 'stock'],
                'category' => 'consulta_compleja'
            ],
            [
                'message' => '¿Me lo pueden mandar a Lima y cuánto tarda?',
                'expected_keywords' => ['lima', 'envío', '2-3'],
                'category' => 'ubicación_envío'
            ],
            [
                'message' => '¿Es seguro comprar con ustedes?',
                'expected_keywords' => ['confiable', 'garantía'],
                'category' => 'seguridad_confianza'
            ],
            [
                'message' => 'No me gusta el precio, hay algo más barato?',
                'expected_keywords' => ['económicas', 'opciones'],
                'category' => 'alternativas_económicas'
            ],

            // Nivel 8: Edge Cases
            [
                'message' => '¿CuáNtO cUeStA eL eNvÍO?',
                'expected_keywords' => ['envío', '15'],
                'category' => 'mayúsculas_mixtas'
            ],
            [
                'message' => '¿cuanto cuesta???!!!',
                'expected_keywords' => ['revisa', 'productos'],
                'category' => 'múltiples_signos'
            ],
            [
                'message' => 'precio envío',
                'expected_keywords' => ['envío', '15'],
                'category' => 'palabras_sueltas'
            ],
            [
                'message' => 'envio gratis?',
                'expected_keywords' => ['envío', '15'],
                'category' => 'pregunta_directa'
            ]
        ];

        $results = [];
        $passed = 0;
        $failed = 0;

        foreach ($testCases as $index => $testCase) {
            $response = $this->postJson('/api/chat/message', [
                'message' => $testCase['message'],
                'session_id' => 'test_session_' . $index
            ]);

            $success = $response->assertStatus(200);
            $data = $response->json();
            
            $keywordsFound = $this->checkKeywords($data['response'], $testCase['expected_keywords']);
            $testPassed = $success && $keywordsFound;

            $results[] = [
                'test' => $index + 1,
                'message' => $testCase['message'],
                'category' => $testCase['category'],
                'response' => $data['response'],
                'keywords_expected' => $testCase['expected_keywords'],
                'keywords_found' => $keywordsFound,
                'passed' => $testPassed,
                'intent' => $data['intent'] ?? 'none'
            ];

            if ($testPassed) {
                $passed++;
            } else {
                $failed++;
            }
        }

        // Guardar resultados para análisis
        $this->saveTestResults($results, $passed, $failed);

        // Assert que al menos el 80% de las pruebas pasen
        $totalTests = count($testCases);
        $passRate = ($passed / $totalTests) * 100;
        
        $this->assertGreaterThan(80, $passRate, 
            "El chat solo pasó {$passRate}% de las pruebas ({$passed}/{$totalTests}). " .
            "Se requiere al menos 80% de aprobación."
        );

        return [
            'total_tests' => $totalTests,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => $passRate,
            'results' => $results
        ];
    }

    /**
     * Verifica si las palabras clave esperadas están en la respuesta
     */
    private function checkKeywords($response, $expectedKeywords)
    {
        $responseLower = strtolower($response);
        $foundKeywords = [];

        foreach ($expectedKeywords as $keyword) {
            if (str_contains($responseLower, strtolower($keyword))) {
                $foundKeywords[] = $keyword;
            }
        }

        return count($foundKeywords) >= count($expectedKeywords) * 0.6; // 60% de keywords encontradas
    }

    /**
     * Guarda los resultados de las pruebas para análisis posterior
     */
    private function saveTestResults($results, $passed, $failed)
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'summary' => [
                'total' => count($results),
                'passed' => $passed,
                'failed' => $failed,
                'pass_rate' => round(($passed / count($results)) * 100, 2)
            ],
            'failed_tests' => array_filter($results, fn($r) => !$r['passed']),
            'categories_performance' => $this->analyzeCategories($results)
        ];

        // Guardar en storage para revisión manual
        file_put_contents(
            storage_path('logs/chat_test_results.json'), 
            json_encode($report, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Analiza el rendimiento por categorías
     */
    private function analyzeCategories($results)
    {
        $categories = [];
        
        foreach ($results as $result) {
            $category = $result['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = ['total' => 0, 'passed' => 0];
            }
            $categories[$category]['total']++;
            if ($result['passed']) {
                $categories[$category]['passed']++;
            }
        }

        foreach ($categories as $category => &$data) {
            $data['pass_rate'] = round(($data['passed'] / $data['total']) * 100, 2);
        }

        return $categories;
    }

    /**
     * Prueba específica para contexto conversacional
     */
    public function test_conversational_context()
    {
        $sessionId = 'context_test_' . time();
        
        // Primer mensaje sobre productos
        $response1 = $this->postJson('/api/chat/message', [
            'message' => '¿Cuánto cuesta el cuy Perú?',
            'session_id' => $sessionId
        ]);

        $response1->assertStatus(200);
        $data1 = $response1->json();

        // Segundo mensaje contextual
        $response2 = $this->postJson('/api/chat/message', [
            'message' => '¿Y el más barato?',
            'session_id' => $sessionId
        ]);

        $response2->assertStatus(200);
        $data2 = $response2->json();

        // Verificar que la segunda respuesta mencione alternativas económicas
        $this->assertStringContainsStringIgnoringCase(
            'económ', 
            $data2['response'],
            'El chat debería mantener contexto y ofrecer alternativas económicas'
        );
    }

    /**
     * Prueba de rendimiento bajo carga
     */
    public function test_performance_under_load()
    {
        $startTime = microtime(true);
        $requests = 10;
        
        for ($i = 0; $i < $requests; $i++) {
            $response = $this->postJson('/api/chat/message', [
                'message' => 'hola',
                'session_id' => 'perf_test_' . $i
            ]);
            
            $response->assertStatus(200);
        }
        
        $endTime = microtime(true);
        $avgTime = (($endTime - $startTime) / $requests) * 1000; // en milisegundos
        
        // El promedio debería ser menor a 200ms por request
        $this->assertLessThan(200, $avgTime, 
            "El tiempo promedio de respuesta es {$avgTime}ms, debería ser menor a 200ms"
        );
    }
}
