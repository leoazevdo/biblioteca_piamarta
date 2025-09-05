<?php
session_start();

header('Content-Type: application/json');

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

// Verifica se o ID do usuário foi enviado
if (!isset($_POST['id']) || empty(trim($_POST['id']))) {
    echo json_encode(["status" => "error", "message" => "ID do usuário não fornecido."]);
    exit();
}

$id = intval($_POST['id']);

try {
    $db = new SQLite3('../../data/bibliotecario.db');

    // Consulta para buscar os dados do usuário e o nome da turma
    $stmt = $db->prepare(
        "SELECT 
            u.id, 
            u.nome, 
            u.turma, 
            t.nome AS turma_nome, 
            u.fone,
            u.foto, 
            u.login, 
            u.nivel 
         FROM 
            cad_usuario u
         LEFT JOIN 
            cad_turma t 
         ON 
            CAST(u.turma AS INTEGER) = t.id
         WHERE 
            u.id = :id"
    );
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        // Formata o nível do usuário
        $user['nivel'] = $user['nivel'] == 1 ? 'Administrador(a)' : 'Usuário';

        // Retorna o nome da turma ou uma mensagem padrão
        $user['turma_nome'] = $user['turma_nome'] ?? 'Turma/Setor não encontrado';

        // Retorna os dados no formato JSON
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "Usuário não encontrado ou não associado a uma turma."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
