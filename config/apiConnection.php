<?php

class ApiConnection
{
    private static ?string $baseUrl = null;

    public static function baseUrl(): string
    {
        if (self::$baseUrl === null) {
            self::$baseUrl = getenv('API_BASE_URL') ?: 'http://localhost:8000';
        }

        return rtrim(self::$baseUrl, '/');
    }

    public static function request(string $method, string $path, array $data = [], array $headers = []): array
    {
        $url = self::baseUrl() . '/' . ltrim($path, '/');

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if (!empty($data)) {
            $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_errno($ch);
        $curlErrMsg = curl_error($ch);

        curl_close($ch);

        if ($curlErr !== 0) {
            return [
                'ok' => false,
                'status' => 0,
                'message' => $curlErrMsg,
                'data' => null,
            ];
        }

        $decoded = json_decode($response, true);

        return [
            'ok' => $httpCode >= 200 && $httpCode < 300,
            'status' => $httpCode,
            'message' => $decoded['message'] ?? 'OK',
            'data' => is_array($decoded) ? $decoded : $response,
        ];
    }

    public static function get(string $path, array $query = []): array
    {
        if (!empty($query)) {
            $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
        }

        return self::request('GET', $path, [], ['Accept: application/json']);
    }

    public static function post(string $path, array $data = []): array
    {
        return self::request('POST', $path, $data, ['Accept: application/json']);
    }

    public static function put(string $path, array $data = []): array
    {
        return self::request('PUT', $path, $data, ['Accept: application/json']);
    }
}
