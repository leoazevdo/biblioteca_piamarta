<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit();
}

try {
    // Conexão com o banco de dados SQLite3
    $db = new SQLite3('../../../data/bibliotecario.db');

    // Consulta para listar todos os usuários
    $query = "SELECT id, nome FROM cad_usuario ORDER BY nome";
    $result = $db->query($query);

    $usuarios = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $usuarios[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
        ];
    }

    // Retorna os usuários como JSON
    echo json_encode($usuarios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao listar usuários.']);
}
?>
