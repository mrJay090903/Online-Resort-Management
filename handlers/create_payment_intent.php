<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['amount']) || !isset($data['booking_data'])) {
            throw new Exception("Missing required data");
        }

        // Convert amount to cents and ensure it's an integer
        $amount = intval($data['amount'] * 100);
        $booking_data = $data['booking_data'];

        // Ensure minimum amount (100 PHP = 10000 cents)
        if ($amount < 10000) {
            throw new Exception("Amount must be at least PHP 100");
        }

        // Build PayMongo request
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
        
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'description' => 'Booking payment for ' . $booking_data['booking_number'],
                    'currency' => 'PHP',
                    'statement_descriptor' => 'Casita De Grands',
                    'type' => 'gcash',
                    'redirect' => [
                        'success' => $base_url . '/Online-Resort-Management/customer/payment_success.php',
                        'failed' => $base_url . '/Online-Resort-Management/customer/payment_failed.php'
                    ]
                ]
            ]
        ];

        error_log("PayMongo Request: " . json_encode($payload));

        // Make PayMongo API request
        $ch = curl_init('https://api.paymongo.com/v1/sources');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'accept: application/json',
                'Authorization: Basic ' . base64_encode('sk_test_PLPKHXfcCfZFc5xNHpSDZi9b')
            ]
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        error_log("PayMongo HTTP Code: " . $httpcode);
        error_log("PayMongo Response: " . $response);
        
        if ($err) {
            throw new Exception("Payment provider connection error: " . $err);
        }

        if ($httpcode !== 200) {
            throw new Exception("Payment provider returned error code: " . $httpcode);
        }

        $result = json_decode($response, true);
        if (!$result || !isset($result['data']) || !isset($result['data']['attributes']['redirect']['checkout_url'])) {
            throw new Exception("Invalid response format from payment provider");
        }

        echo json_encode([
            'success' => true,
            'source_id' => $result['data']['id'],
            'checkout_url' => $result['data']['attributes']['redirect']['checkout_url']
        ]);

    } catch (Exception $e) {
        error_log("Payment Error: " . $e->getMessage());
        error_log("Request Data: " . print_r($data ?? [], true));
        error_log("Response: " . ($response ?? 'No response'));
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 