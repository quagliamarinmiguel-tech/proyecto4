<?php

require_once __DIR__ . '/supabase.env.php';

class SupabaseClient
{
    private string $url;
    private string $key;
    private int $timeout;

    public function __construct(string $url = SUPABASE_URL, string $key = SUPABASE_KEY, int $timeout = API_TIMEOUT_SECONDS)
    {
        $this->url = rtrim($url, '/');
        $this->key = $key;
        $this->timeout = $timeout;
    }

    public function get(string $table, string $select = '*', array $filters = []): array
    {
        $endpoint = $this->url . '/rest/v1/' . ltrim($table, '/');
        $query = ['select=' . urlencode($select)];

        foreach ($filters as $field => $value) {
            $query[] = urlencode($field) . '=' . urlencode((string) $value);
        }

        $endpoint .= '?' . implode('&', $query);

        return $this->request('GET', $endpoint);
    }

    public function post(string $table, array $payload): array
    {
        $endpoint = $this->url . '/rest/v1/' . ltrim($table, '/');
        return $this->request('POST', $endpoint, $payload, ['Prefer: return=representation']);
    }

    public function put(string $table, array $payload, array $filters = []): array
    {
        $endpoint = $this->url . '/rest/v1/' . ltrim($table, '/');

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->request('PUT', $endpoint, $payload, ['Prefer: return=representation']);
    }

    public function delete(string $table, array $filters = []): array
    {
        $endpoint = $this->url . '/rest/v1/' . ltrim($table, '/');

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, ?array $payload = null, array $extraHeaders = []): array
    {
        $ch = curl_init($endpoint);

        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        foreach ($extraHeaders as $header) {
            $headers[] = $header;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_errno($ch);
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
            'data' => is_array($decoded) ? $decoded : null,
        ];
    }
}
