<html>

</html>   

<?php
// update_claim_code.php

// Generate a random 7-character uppercase code (A-Z only)
$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$code = '';
for ($i = 0; $i < 7; $i++) {
    $code .= $characters[rand(0, strlen($characters) - 1)];
}

// Firebase Realtime Database URL for the "claim-code/code" node.
// The ".json" extension is required by the REST API.
$url = 'https://victoreumgames-drop-default-rtdb.asia-southeast1.firebasedatabase.app/claim-code/code.json';

// The data to update: simply the new code (as a JSON string)
$data = json_encode($code);

// Initialize a cURL session
$ch = curl_init();

// Set the required options for a PUT request
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Use PUT to write the new value
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Execute the request and capture the response
$response = curl_exec($ch);

// Check for errors if needed
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    echo "Firebase claim-code updated to: " . $code;
}

// Close the cURL session
curl_close($ch);
?>
