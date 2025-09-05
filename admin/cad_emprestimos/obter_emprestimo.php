<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID do empréstimo não fornecido.']);
    exit();
}

$idEmprestimo = intval($_GET['id']);

// Conexão com o banco de dados SQLite3
try {
    $db = new SQLite3('../../data/bibliotecario.db');
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados.']);
    exit();
}

// Consulta para obter os dados do empréstimo
$query = "
    SELECT e.*, u.nome as nome_usuario, u.fone as fone_usuario, a.titulo as titulo_acervo, a.capa
    FROM emprestimos e
    LEFT JOIN cad_usuario u ON e.user_id = u.id
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    WHERE e.id = :id
";

$stmt = $db->prepare($query);
$stmt->bindValue(':id', $idEmprestimo, SQLITE3_INTEGER);

$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
    echo json_encode([
        'status' => 'success',
        'emprestimo' => [
            'id' => $row['id'],
            'nome_usuario' => $row['nome_usuario'],
            'fone_usuario' => $row['fone_usuario'],
            'titulo_acervo' => $row['titulo_acervo'],
            'capa' => $row['capa'] ?: '../img/book.png',
            'data_atual' => $row['data_atual'],
            'data_devolucao' => $row['data_devolucao'],
        ],
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Empréstimo não encontrado.']);
}
?>
