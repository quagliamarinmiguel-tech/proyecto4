<?php

require_once __DIR__ . '/supabase.env.php';
require_once __DIR__ . '/SupabaseClient.php';

// Ejemplo rápido de uso
$supabase = new SupabaseClient(SUPABASE_URL, SUPABASE_KEY);

$result = $supabase->get('usuario', '*', [
    'id' => 'gt.0'
]);

print_r($result);
