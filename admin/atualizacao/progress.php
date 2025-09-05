<?php
session_start();

header('Content-Type: application/json');

$response = [
    'progress' => $_SESSION['migration_progress'] ?? 0,
    'message' => $_SESSION['migration_message'] ?? 'Aguardando início...'
];

echo json_encode($response);
?>