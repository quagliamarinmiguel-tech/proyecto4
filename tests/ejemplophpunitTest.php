<?php

use PHPUnit\Framework\TestCase;

// El nombre de la clase debe coincidir con el nombre del archivo y terminar en "Test"
class ejemplophpunitTest extends TestCase
{
    // Los métodos de prueba DEBEN empezar con la palabra "test"
    public function testSumaBasica()
    {
        $resultado = 2 + 2;

        // Usamos aserciones (assertions) para verificar que el resultado es el esperado
        $this->assertEquals(4, $resultado);
    }

    public function testVerdaderoFalso()
    {
        $condicion = true;

        $this->assertTrue($condicion);
    }
}
