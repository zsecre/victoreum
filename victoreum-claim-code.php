<?php
// Firebase service account credentials (replace with your actual service account JSON)
$serviceAccountFile = 'victoreumgames-drop-firebase-adminsdk-fbsvc-855c1653be.json';
$firebaseConfig = [
    'apiKey' => 'AIzaSyBB3gfZtcvPBs1G4fbezMU9EdeK6u261To',
    'projectId' => 'victoreumgames-drop'
];

// Generate random 7-character code
function generateCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < 7; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Get Firebase access token using service account
function getFirebaseAccessToken($serviceAccountPath) {
    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/datastore',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signature = '';
    openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, $serviceAccount['private_key'], 'SHA256');
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true)['access_token'];
}

// Update Firestore document
try {
    $code = generateCode();
    $accessToken = getFirebaseAccessToken($serviceAccountFile);
    
    $url = "https://firestore.googleapis.com/v1/projects/{$firebaseConfig['projectId']}/databases/(default)/documents/claim-code/code";
    
    $data = [
        'fields' => [
            'code' => ['stringValue' => $code]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new Exception("Firestore update failed: " . $response);
    }

    // Output result
    echo "<div id='code'>$code</div>";
    echo "<p>Code successfully updated!</p>";

} catch (Exception $e) {
    echo "<div style='color: red'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
