<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

if (!isset($_POST['categoria_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID da categoria não fornecido.']);
    exit();
}

$categoria_id = intval($_POST['categoria_id']);

try {
    $db = new SQLite3('../../data/bibliotecario.db');

    // Consulta a cor da categoria
    $stmt = $db->prepare('SELECT cor FROM cad_categoria WHERE id = :id');
    $stmt->bindValue(':id', $categoria_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
        echo json_encode(['status' => 'success', 'cor' => $row['cor']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Categoria não encontrada.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
