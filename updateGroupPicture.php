<?php
// Get the group ID from the URL parameter
if (!isset($_GET['user_id'])) {
    die("Error: user_id parameter is missing.");
}

$group_id = $_GET['user_id'];

// API endpoint
$api_url = "https://whatsapp-waha.brandon.my/api/default/groups/" . urlencode($group_id) . "/picture";

// Image details
$image_data = [
    "file" => [
        "mimetype" => "image/jpeg",
        "filename" => "filename.jpg",
        "url" => "https://cloud.webbypage.com/index.php/s/KHseyQxcmRYxXBM/download"
    ]
];

// cURL initialization
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($image_data));

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
} else {
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "Response Code: $http_code\n";
    echo "Response Body: $response";
}

// Close cURL session
curl_close($ch);
?>