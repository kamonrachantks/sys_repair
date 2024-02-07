<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $message = $_POST['message'];

    $lineNotifyApi = "https://notify-api.line.me/api/notify";
    $data = http_build_query(['message' => $message]);
    

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Authorization: Bearer $token\r\n",
            'method' => 'POST',
            'content' => $data
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($lineNotifyApi, false, $context);

    // Handle the result as needed
    $resultData = json_decode($result, true);
    if ($resultData['status'] === 200) {
        echo "LINE Notify message sent successfully!";
    } else {
        echo "Error sending LINE Notify message: " . $resultData['message'];
    }
}
?>
