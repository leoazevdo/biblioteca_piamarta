<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

$db = new SQLite3('../../data/bibliotecario.db');

try {
    // Verifica se os dados foram enviados
    if (!isset($_POST['id']) || !isset($_POST['turma'])) {
        echo json_encode(["status" => "error", "message" => "Dados incompletos enviados."]);
        exit();
    }

    $id = intval($_POST['id']);
    $turma = trim($_POST['turma']);

    if (empty($turma)) {
        echo json_encode(["status" => "error", "message" => "O nome da turma não pode estar vazio."]);
        exit();
    }

    // Atualiza os dados da turma no banco de dados
    $stmt = $db->prepare("UPDATE cad_turma SET nome = :nome WHERE id = :id");
    $stmt->bindValue(':nome', $turma, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Turma atualizada com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar a turma."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
