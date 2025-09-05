<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

try {
    // Conexão com o banco de dados SQLite3
    $db = new SQLite3('../../data/bibliotecario.db');

    // Criação da tabela cad_configuracoes se não existir
    $db->exec("CREATE TABLE IF NOT EXISTS cad_configuracoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_instituicao TEXT NOT NULL,
        logo_instituicao TEXT,
        endereco_instituicao TEXT NOT NULL,
        preferencias TEXT NOT NULL
    )");

    // Recebe os dados do formulário
    $nome = isset($_POST['turma']) ? $_POST['turma'] : '';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : '';
    $colunas_selecionadas = isset($_POST['colunas']) ? $_POST['colunas'] : [];

    $colunas_string = '';
    if (!empty($colunas_selecionadas)) {
        // Converta as colunas selecionadas para uma string separada por vírgulas
        $colunas_string = implode(',', $colunas_selecionadas);
    }

    // Verifica e faz o upload do logo, se fornecido
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/imagens/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logoName = uniqid() . '_' . basename($_FILES['logo']['name']);
        $logoPath = $uploadDir . $logoName;

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao fazer upload do logo.']);
            exit();
        }
    }

    // Atualiza as configurações existentes (id = 1)
    $stmt = $db->prepare("UPDATE cad_configuracoes SET 
        nome_instituicao = :nome,
        logo_instituicao = CASE WHEN :logo IS NOT NULL THEN :logo ELSE logo_instituicao END,
        endereco_instituicao = :endereco,
        preferencias = :preferencias
    WHERE id = 1");

    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':logo', $logoPath, SQLITE3_TEXT);
    $stmt->bindValue(':endereco', $endereco, SQLITE3_TEXT);
    $stmt->bindValue(':preferencias', $colunas_string, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Configurações atualizadas com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar as configurações no banco de dados.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
