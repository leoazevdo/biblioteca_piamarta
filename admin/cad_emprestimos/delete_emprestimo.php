<?php
$mysqli = new SQLite3('../../data/bibliotecario.db');

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID é obrigatório.']);
    exit;
}

$query = "DELETE FROM emprestimos WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bindValue(1, $id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Empréstimo excluído com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir o empréstimo.']);
}
?>
