<?php

class DeepSeekService {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        $config = require __DIR__ . '/../config/deepseek.php';
        $this->apiUrl = $config['deepseek_api_url'];
        $this->apiKey = $config['deepseek_api_key'];
    }

    public function getGuidance($input) {
        $response = $this->callApi('guidance', ['input' => $input]);
        return $response;
    }

    public function getExplanation($input) {
        $response = $this->callApi('explanation', ['input' => $input]);
        return $response;
    }

    private function callApi($endpoint, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}