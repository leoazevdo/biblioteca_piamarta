<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado']);
    exit();
}

try {
    // Conexão com o banco de dados
    $db = new SQLite3('../../data/bibliotecario.db');

    // Receber os dados do AJAX
    $emprestimoId = $_POST['id'] ?? '';
    $dataAtual = $_POST['data_atual'] ?? '';
    $dataDevolucao = $_POST['data_devolucao'] ?? '';

    if (empty($emprestimoId) || empty($dataAtual) || empty($dataDevolucao)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos obrigatórios não preenchidos']);
        exit();
    }

    // Atualizar os dados no banco de dados
    $query = "UPDATE emprestimos SET data_atual = :data_atual, data_devolucao = :data_devolucao WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':data_atual', $dataAtual, SQLITE3_TEXT);
    $stmt->bindValue(':data_devolucao', $dataDevolucao, SQLITE3_TEXT);
    $stmt->bindValue(':id', $emprestimoId, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Empréstimo atualizado com sucesso.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o empréstimo.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>
