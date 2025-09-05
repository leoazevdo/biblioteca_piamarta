<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

if (!isset($_POST['id']) || empty(trim($_POST['id']))) {
    echo json_encode(["status" => "error", "message" => "ID do usuário não fornecido."]);
    exit();
}

$id = intval($_POST['id']);

try {
    $db = new SQLite3('../../data/bibliotecario.db');

    // Verifica se o usuário existe
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cad_usuario WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['total'] === 0) {
        echo json_encode(["status" => "error", "message" => "Usuário não encontrado."]);
        exit();
    }

    // Exclui o usuário
    $stmt = $db->prepare("DELETE FROM cad_usuario WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Usuário excluído com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao excluir o usuário."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
