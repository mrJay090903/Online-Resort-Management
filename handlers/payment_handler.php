<?php
require_once __DIR__ . '/../config/env.php';

// Use the constants for PayMongo API calls
$secretKey = PAYMONGO_SECRET_KEY;
$publicKey = PAYMONGO_PUBLIC_KEY;

// Set up PayMongo API headers
$headers = [
    'Authorization: Basic ' . base64_encode($secretKey),
    'Content-Type: application/json',
    'Accept: application/json'
];

// Example PayMongo API call function
function createPaymentIntent($amount, $currency = 'PHP') {
    global $headers;
    
    $data = [
        'data' => [
            'attributes' => [
                'amount' => $amount * 100, // Convert to cents
                'payment_method_allowed' => ['card', 'gcash'],
                'currency' => $currency,
            ]
        ]
    ];

    $ch = curl_init('https://api.paymongo.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Your PayMongo API calls here... 