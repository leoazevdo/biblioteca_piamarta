<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

if (!isset($_POST['ids']) || empty($_POST['ids'])) {
    echo json_encode(["status" => "error", "message" => "Nenhum ID de usuário foi fornecido."]);
    exit();
}

$ids = $_POST['ids'];

if (!is_array($ids)) {
    echo json_encode(["status" => "error", "message" => "IDs de usuários inválidos."]);
    exit();
}

try {
    $db = new SQLite3('../../data/bibliotecario.db');

    // Converte o array de IDs para uma lista separada por vírgulas, escapando para evitar SQL injection
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $db->prepare(
        "SELECT 
            u.id, 
            u.nome, 
            t.nome AS turma_nome, 
            u.fone, 
            u.foto, 
            u.nivel 
         FROM cad_usuario u
         LEFT JOIN cad_turma t ON u.turma = t.id
         WHERE u.id IN ($placeholders)"
    );

    // Vincula os valores dos IDs
    foreach ($ids as $index => $id) {
        $stmt->bindValue($index + 1, intval($id), SQLITE3_INTEGER);
    }

    $result = $stmt->execute();

    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = [
            "id" => $row['id'],
            "nome" => $row['nome'],
            "turma_nome" => $row['turma_nome'] ?? 'Não Informada',
            "fone" => $row['fone'],
            "foto" => $row['foto'],
            "nivel" => $row['nivel'] == 1 ? 'Administrador(a)' : 'Usuário',
        ];
    }

    echo json_encode(["status" => "success", "data" => $users]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro ao buscar usuários: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
