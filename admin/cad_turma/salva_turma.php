<?php
session_start();
ob_start(); // Captura qualquer saída inesperada


header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

$db = new SQLite3('../../data/bibliotecario.db');

try {
    // Verifica se o nome da turma foi enviado
    if (!isset($_POST['turma']) || empty(trim($_POST['turma']))) {
        echo json_encode(["status" => "error", "message" => "O nome da turma é obrigatório."]);
        exit();
    }

    $turma = trim($_POST['turma']);

    // Insere os dados da turma no banco de dados
    $stmt = $db->prepare("INSERT INTO cad_turma (nome) VALUES (:nome)");
    $stmt->bindValue(':nome', $turma, SQLITE3_TEXT);

    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Turma cadastrada com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar a turma."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}


?>
