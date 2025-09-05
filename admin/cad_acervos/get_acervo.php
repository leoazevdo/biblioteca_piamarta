<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

if (!isset($_POST['id']) || empty(trim($_POST['id']))) {
    echo json_encode(["status" => "error", "message" => "ID do acervo não fornecido."]);
    exit();
}

$id = intval($_POST['id']);

try {
    $db = new SQLite3('../../data/bibliotecario.db');

    // Busca os dados do acervo
    $stmt = $db->prepare("
        SELECT a.*, c.titulo AS categoria, t.descricao AS tipo
        FROM cad_acervo a
        LEFT JOIN cad_categoria c ON a.categoria = c.id
        LEFT JOIN cad_tipo t ON a.tipo = t.id
        WHERE a.id = :id
    ");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $acervo = $result->fetchArray(SQLITE3_ASSOC);

    if ($acervo) {
        echo json_encode(["status" => "success", "data" => $acervo]);
    } else {
        echo json_encode(["status" => "error", "message" => "Acervo não encontrado."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
}
