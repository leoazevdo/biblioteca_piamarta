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
    if (!isset($_POST['nome']) || empty(trim($_POST['nome'])) ||
        !isset($_POST['nivel']) || empty(trim($_POST['nivel']))) {
        echo json_encode(["status" => "error", "message" => "Os campos Nome e Nível são obrigatórios."]);
        exit();
    }

    // Recebe e limpa os dados
    $nome = trim($_POST['nome']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $fone = isset($_POST['fone']) ? trim($_POST['fone']) : null;
    $nivel = intval($_POST['nivel']);
    $turma = isset($_POST['turma']) ? trim($_POST['turma']) : null;

    // Validações adicionais (como email)
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "O e-mail informado é inválido."]);
        exit();
    }

    // Gera um login único baseado no primeiro nome e 4 números aleatórios
    $primeiro_nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $nome)[0]));
    $primeiro_nome = preg_replace('/[^a-z]/', '', $primeiro_nome); // remove qualquer caractere que não seja letra

    $login_gerado = '';

    do {
        $numero_aleatorio = rand(1000, 9999);
        $login_gerado = $primeiro_nome . $numero_aleatorio;

        // Verifica se o login já existe
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM cad_usuario WHERE login = :login");
        $stmt->bindValue(':login', $login_gerado, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
    } while ($row['total'] > 0);

    // Insere os dados no banco
    $stmt = $db->prepare(
        "INSERT INTO cad_usuario (nome, email, fone, nivel, turma, login) VALUES (:nome, :email, :fone, :nivel, :turma, :login)"
    );
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':fone', $fone, SQLITE3_TEXT);
    $stmt->bindValue(':nivel', $nivel, SQLITE3_INTEGER);
    $stmt->bindValue(':turma', $turma, SQLITE3_TEXT);
    $stmt->bindValue(':login', $login_gerado, SQLITE3_TEXT);

    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Usuário cadastrado com sucesso. Login gerado: $login_gerado"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar o usuário."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
