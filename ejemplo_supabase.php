<?php

require_once __DIR__ . '/config/supabaseApi.php';

$result = SupabaseApi::get('course', ['select' => '*']);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
