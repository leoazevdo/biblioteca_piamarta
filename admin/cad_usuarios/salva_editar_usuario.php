<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

$db = new SQLite3('../../data/bibliotecario.db');

try {
    // Verifica se os dados foram enviados
    if (!isset($_POST['id']) || empty(trim($_POST['id'])) ||
        !isset($_POST['nome']) || empty(trim($_POST['nome'])) ||
        !isset($_POST['nivel']) || empty(trim($_POST['nivel'])) ||
        !isset($_POST['login']) || empty(trim($_POST['login']))) {
        echo json_encode(["status" => "error", "message" => "Os campos Nome, Nível, Login e ID são obrigatórios."]);
        exit();
    }

    // Recebe e limpa os dados
    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $fone = isset($_POST['fone']) ? trim($_POST['fone']) : null;
    $nivel = intval($_POST['nivel']);
    $turma = isset($_POST['turma']) ? trim($_POST['turma']) : null;
    $login = strtolower(trim($_POST['login']));

    // Validações adicionais
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "O e-mail informado é inválido."]);
            exit();
        }

        // Verifica se o e-mail já existe em outro registro
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM cad_usuario WHERE email = :email AND id != :id");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row['total'] > 0) {
            echo json_encode(["status" => "error", "message" => "O e-mail informado já está em uso por outro usuário."]);
            exit();
        }
    }

    // Verifica se o login já existe em outro registro
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cad_usuario WHERE login = :login AND id != :id");
    $stmt->bindValue(':login', $login, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['total'] > 0) {
        echo json_encode(["status" => "error", "message" => "O login informado já está em uso por outro usuário."]);
        exit();
    }

    // Atualiza os dados no banco
    $stmt = $db->prepare(
        "UPDATE cad_usuario SET nome = :nome, email = :email, fone = :fone, nivel = :nivel, turma = :turma, login = :login WHERE id = :id"
    );
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':fone', $fone, SQLITE3_TEXT);
    $stmt->bindValue(':nivel', $nivel, SQLITE3_INTEGER);
    $stmt->bindValue(':turma', $turma, SQLITE3_TEXT);
    $stmt->bindValue(':login', $login, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Usuário atualizado com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar o usuário."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
