<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado']);
    exit();
}

$db = new SQLite3('../../data/bibliotecario.db');

// Recebe os dados do formulário
$listaUsuarios = $_POST['lista'] ?? '';
$turma = $_POST['turma'] ?? '';
$nivel = 2;

if (empty($listaUsuarios) || empty($turma)) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos obrigatórios']);
    exit();
}


$usuarios = explode("\n", trim($listaUsuarios));
foreach ($usuarios as $usuarioNome) {
    $usuarioNome = trim($usuarioNome);

    if (!empty($usuarioNome)) {
        // Geração do login
        //$primeiro_nome = explode(' ', $usuarioNome)[0];
        //$primeiro_nome = strtolower(str_replace($acentos, $sem_acentos, $primeiro_nome));
        //$primeiro_nome = preg_replace('/[^a-z]/', '', $primeiro_nome);

        do {
            $login = rand(100, 9999);
            $verifica = $db->querySingle("SELECT COUNT(*) FROM cad_usuario WHERE login = '$login'");
        } while ($verifica > 0);

        $stmt = $db->prepare("INSERT INTO cad_usuario (nome, login, nivel, turma) VALUES (:nome, :login, :nivel, :turma)");
        $stmt->bindValue(':nome', $usuarioNome, SQLITE3_TEXT);
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':nivel', $nivel, SQLITE3_INTEGER);
        $stmt->bindValue(':turma', $turma, SQLITE3_TEXT);

        $resultado = $stmt->execute();

        if (!$resultado) {
            echo json_encode(['status' => 'error', 'message' => "Erro ao inserir o usuário $usuarioNome."]);
            exit();
        }
    }
}

echo json_encode(['status' => 'success', 'message' => 'Usuários cadastrados com sucesso.']);
