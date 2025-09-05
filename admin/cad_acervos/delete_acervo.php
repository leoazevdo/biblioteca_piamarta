<?php
$mysqli = new SQLite3('../../data/bibliotecario.db');

// Recebe o id do acervo a ser deletado
$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID é obrigatório.']);
    exit;
}

// Primeiro, verificamos se o acervo está emprestado na tabela "emprestimos"
$emprestimoQuery = "SELECT COUNT(*) as total FROM emprestimos WHERE acervo_id = ? AND devolvido = 'Não'";
$emprestimoStmt = $mysqli->prepare($emprestimoQuery);
$emprestimoStmt->bindValue(1, $id, SQLITE3_INTEGER);
$emprestimoResult = $emprestimoStmt->execute();
$emprestimoData = $emprestimoResult->fetchArray(SQLITE3_ASSOC);

if ($emprestimoData['total'] > 0) {
    // Se o acervo estiver emprestado, não permitir a exclusão
    echo json_encode(['status' => 'error', 'message' => 'O acervo está emprestado e não pode ser deletado.']);
    exit;
}

// Caso não esteja emprestado, buscar o registro a ser deletado para pegar o código e a imagem
$query = "SELECT codigo, capa FROM cad_acervo WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
    $codigo = $row['codigo']; // Pegamos o código para atualizar os outros registros
    $capa = $row['capa'];

    

    // Deletamos o registro do banco de dados
    $deleteQuery = "DELETE FROM cad_acervo WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteQuery);
    $deleteStmt->bindValue(1, $id, SQLITE3_INTEGER);

    if ($deleteStmt->execute()) {
        // Agora, atualizamos todos os registros com o mesmo código
        // Reduzindo a quantidade em 1
        $updateQuery = "UPDATE cad_acervo SET quantidade = quantidade - 1 WHERE codigo = ? AND quantidade > 0";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bindValue(1, $codigo, SQLITE3_TEXT); // Usa SQLITE3_TEXT para o tipo TEXT

        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registro deletado e quantidade atualizada com sucesso.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registro deletado, mas erro ao atualizar a quantidade.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar o registro.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registro não encontrado.']);
}
?>
