<?php
function send_json_response($success, $message, $data = [], $dev_message = null, $error_code = 500) {
    $response = [
        'success' => $success,
        'message' => $message,
    ];
    if (!empty($data)) $response['data'] = $data;
    if ($dev_message && getenv('APP_ENV') === 'development') {
        $response['dev_message'] = $dev_message;
    }
    if (!$success) {
        http_response_code($error_code);
        $response['error_code'] = $error_code;
    } else {
        http_response_code(200);
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}