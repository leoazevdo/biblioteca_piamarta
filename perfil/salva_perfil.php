<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

$db = new SQLite3('../data/bibliotecario.db');

$id_usuario = $_SESSION['user_id'];

try {
    $nome = $_POST['nome'] ?? '';
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $fone = $_POST['fone'] ?? '';

    $foto = null;

    // Upload de foto, se houver
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = '../uploads/imagens/';
        $foto_name = basename($_FILES['foto']['name']);
        $foto_path = $upload_dir . $foto_name;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
            $foto = $foto_name;
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao salvar a foto."]);
            exit();
        }
    }

    // Atualiza os dados do usuário
    $query = "UPDATE cad_usuario SET nome = :nome, login = :login, fone = :fone";

    if (!empty($senha)) {
        $query .= ", senha = :senha";
    }

    if ($foto) {
        $query .= ", foto = :foto";
    }

    $query .= " WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':login', $login, SQLITE3_TEXT);
    $stmt->bindValue(':fone', $fone, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id_usuario, SQLITE3_INTEGER);

    if (!empty($senha)) {
        $stmt->bindValue(':senha', password_hash($senha, PASSWORD_DEFAULT), SQLITE3_TEXT);
    }

    if ($foto) {
        $stmt->bindValue(':foto', $foto, SQLITE3_TEXT);
    }

    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Perfil atualizado com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar o perfil."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
} finally {
    $db->close();
}
?>
