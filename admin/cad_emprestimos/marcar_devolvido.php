<?php
$mysqli = new SQLite3('../../data/bibliotecario.db');

// Verifica se o ID foi enviado
$id = $_POST['id'] ?? '';
if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID é obrigatório.']);
    exit;
}

$pagina = $_POST['pagina'] ?? '';

// Obtém a data atual no formato YYYY-MM-DD
$data_entregue = date('Y-m-d');

// Verifica se a página foi informada e ajusta a query
if ($pagina != '') {
    // Se a página for fornecida, marca o empréstimo como não devolvido e limpa a data de entrega
    $query = "
        UPDATE emprestimos 
        SET devolvido = 'Não', data_entregue = NULL
        WHERE id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
} else {
    // Se a página não for fornecida, marca como devolvido e atualiza a data de entrega
    $query = "
        UPDATE emprestimos 
        SET devolvido = 'Sim', data_entregue = ?
        WHERE id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bindValue(1, $data_entregue, SQLITE3_TEXT);
    $stmt->bindValue(2, $id, SQLITE3_INTEGER);
}

// Executa a query e verifica o resultado
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Empréstimo atualizado com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o empréstimo.']);
}
?>
