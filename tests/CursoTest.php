<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../config/supabaseApi.php';

class CursoTest extends TestCase
{
    public function testObtenerCursosState1()
    {
        // Llamamos a la API de Supabase para obtener la tabla 'course' donde 'state' sea igual a 1
        $result = SupabaseApi::get('course', [
            'state' => 'eq.1'
        ]);

        // 1. Verificar que la petición fue exitosa
        $this->assertTrue($result['ok'], "La petición a Supabase falló: " . $result['message']);

        // 2. Verificar que los datos devueltos sean un array
        $this->assertIsArray($result['data'], "Los datos devueltos no son un array válido.");

        // 3. (Opcional) Verificar que los cursos devueltos realmente tengan el state en 1
        foreach ($result['data'] as $curso) {
            $this->assertEquals(1, $curso['state'], "El curso '{$curso['name']}' no tiene state 1.");
        }
        
        // Imprimir en consola la cantidad de cursos obtenidos para verlo al correr las pruebas (solo para debug)
        echo "\nSe encontraron " . count($result['data']) . " cursos activos (state=1).\n";
    }
}
