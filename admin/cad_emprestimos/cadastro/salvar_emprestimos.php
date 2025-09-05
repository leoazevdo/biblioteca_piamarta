<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado']);
    exit();
}

try {
    // Conexão com o banco de dados SQLite
    $db = new SQLite3('../../../data/bibliotecario.db');

    // Receber os dados enviados via POST
    $emprestimos = json_decode($_POST['emprestimos'], true);

    if (empty($emprestimos)) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido']);
        exit();
    }

    // Iniciar transação
    $db->exec('BEGIN');

    // Inserir os empréstimos no banco
    foreach ($emprestimos as $emprestimo) {
        $stmt = $db->prepare('
            INSERT INTO emprestimos (user_id, acervo_id, data_atual, data_devolucao, devolvido, total_dias, dia_semana) 
            VALUES (:user_id, :acervo_id, :data_atual, :data_devolucao, :devolvido, :total_dias, :dia_semana)
        ');
        $stmt->bindValue(':user_id', $emprestimo['user_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':acervo_id', $emprestimo['acervo_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':data_atual', $emprestimo['data_atual'], SQLITE3_TEXT);
        $stmt->bindValue(':data_devolucao', $emprestimo['data_devolucao'], SQLITE3_TEXT);
        $stmt->bindValue(':devolvido', $emprestimo['devolvido'], SQLITE3_TEXT);
        $stmt->bindValue(':total_dias', $emprestimo['total_dias'], SQLITE3_INTEGER);
        $stmt->bindValue(':dia_semana', $emprestimo['dia_semana'], SQLITE3_TEXT);
        $stmt->execute();
    }

    // Finalizar transação
    $db->exec('COMMIT');
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $db->exec('ROLLBACK');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
