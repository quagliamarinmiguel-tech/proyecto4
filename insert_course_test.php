<?php
require_once __DIR__ . '/config/supabaseApi.php';

$result = SupabaseApi::post('course', [
    'name' => 'Ingles II',
    'type' => 'intensivo',
    'state' => 1
]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
