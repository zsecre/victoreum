<?php
// update_claim_code.php

// Generate a random 7-character uppercase code
$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$code = '';
for ($i = 0; $i < 7; $i++) {
    $code .= $characters[rand(0, strlen($characters) - 1)];
}

// Firestore REST API endpoint to update the document.
// This URL targets the document in the collection "claim-code" with ID "code"
// and uses an update mask to update the field named "code".
$url = 'https://firestore.googleapis.com/v1/projects/victoreumgames-drop/databases/(default)/documents/claim-code/code?updateMask.fieldPaths=code';

// Build the JSON body for the PATCH request.
// Firestore documents require fields to be nested under a "fields" object.
$data = json_encode([
    "fields" => [
        "code" => [
            "stringValue" => $code
        ]
    ]
]);

// Initialize cURL session
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// If you require authentication, add an Authorization header with your access token.
// For example:
// curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     'Content-Type: application/json',
//     'Authorization: Bearer YOUR_ACCESS_TOKEN'
// ]);

// Execute the request and capture the response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
} else {
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "Firestore document updated successfully to: " . $code;
    } else {
        echo "Error updating Firestore. HTTP Code: " . $httpCode . "\nResponse: " . $response;
    }
}

// Close the cURL session
curl_close($ch);
?>
