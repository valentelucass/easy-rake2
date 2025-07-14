<?php
function sendResponse($success, $message, $status = 200, $data = null) {
    http_response_code($status);
    $resp = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    echo json_encode($resp);
    exit;
}

function sendUnauthorized($message = 'Não autorizado') {
    sendResponse(false, $message, 401);
}

function sendForbidden($message = 'Acesso proibido') {
    sendResponse(false, $message, 403);
}

function sendError($message = 'Erro interno do servidor', $status = 500) {
    sendResponse(false, $message, $status);
}

function sendValidationError($message = 'Erro de validação') {
    sendResponse(false, $message, 422);
}

function sendNotFound($message = 'Não encontrado') {
    sendResponse(false, $message, 404);
}

function sendConflict($message = 'Conflito de dados') {
    sendResponse(false, $message, 409);
}

function sendCreated($message = 'Criado com sucesso', $data = null) {
    sendResponse(true, $message, 201, $data);
}

function sendInternalError($message = 'Erro interno do servidor') {
    sendResponse(false, $message, 500);
}

function sendList($data, $total = null) {
    sendResponse(true, 'Listagem realizada com sucesso', 200, [
        'total' => $total ?? count($data),
        'itens' => $data
    ]);
}
?> 