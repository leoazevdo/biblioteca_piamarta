<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

// Conexão com o banco de dados
$db = new SQLite3('../../data/bibliotecario.db');

// Recebe os dados do formulário
$id = htmlspecialchars($_POST['id']);
$titulo = htmlspecialchars($_POST['titulo']);
$autor = htmlspecialchars($_POST['autor']);
$editora = htmlspecialchars($_POST['editora']);
$ano = htmlspecialchars($_POST['ano']);
$categoria = intval($_POST['categoria']);
$sinopse = htmlspecialchars($_POST['sinopse']);

// Validação básica
if (empty($titulo) || empty($categoria)) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos obrigatórios.']);
    exit();
}

// Diretórios de destino
$uploadDirArquivo = '../../uploads/arquivos/';
$uploadDirImagem = '../../uploads/imagens/';

// Cria os diretórios se não existirem
if (!is_dir($uploadDirArquivo)) {
    mkdir($uploadDirArquivo, 0777, true);
}
if (!is_dir($uploadDirImagem)) {
    mkdir($uploadDirImagem, 0777, true);
}

// Recupera os caminhos atuais do banco de dados
$currentDataQuery = "SELECT arquivo_caminho, imagem_caminho FROM cad_acervo_digital WHERE id = :id";
$currentStmt = $db->prepare($currentDataQuery);
$currentStmt->bindValue(':id', $id, SQLITE3_INTEGER);
$currentData = $currentStmt->execute()->fetchArray(SQLITE3_ASSOC);

$arquivoCaminhoAtual = $currentData['arquivo_caminho'] ?? null;
$imagemCaminhoAtual = $currentData['imagem_caminho'] ?? null;

// Processa o arquivo PDF se enviado
$arquivoNome = $arquivoCaminhoAtual;
if (isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['tmp_name'])) {
    $arquivoTmp = $_FILES['arquivo']['tmp_name'];
    $arquivoNome = uniqid('arquivo_') . '.pdf';
    $arquivoCaminho = $uploadDirArquivo . $arquivoNome;

    if (!move_uploaded_file($arquivoTmp, $arquivoCaminho)) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o arquivo PDF.']);
        exit();
    }

    // Exclui o arquivo anterior se existir
    if ($arquivoCaminhoAtual && file_exists($uploadDirArquivo . $arquivoCaminhoAtual)) {
        unlink($uploadDirArquivo . $arquivoCaminhoAtual);
    }
}

// Processa a imagem da capa se enviada
$imagemNome = $imagemCaminhoAtual;
if (!empty($_POST['capa'])) {
    $capaBase64 = $_POST['capa'];

    if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capaBase64, $matches)) {
        $ext = $matches[1];
        $imagemNome = uniqid('imagem_') . '.' . $ext;
        $imagemCaminho = $uploadDirImagem . $imagemNome;

        $imagemData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $capaBase64));
        if (!file_put_contents($imagemCaminho, $imagemData)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a imagem da capa.']);
            exit();
        }

        // Exclui a imagem anterior se existir
        if ($imagemCaminhoAtual && file_exists($uploadDirImagem . $imagemCaminhoAtual)) {
            unlink($uploadDirImagem . $imagemCaminhoAtual);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagem inválido.']);
        exit();
    }
}

// Atualiza os dados no banco de dados
$query = "
    UPDATE cad_acervo_digital
    SET titulo = :titulo, autor = :autor, editora = :editora, ano = :ano, categoria = :categoria,
        sinopse = :sinopse, arquivo_caminho = :arquivo_caminho, imagem_caminho = :imagem_caminho
    WHERE id = :id
";

$stmt = $db->prepare($query);
$stmt->bindValue(':titulo', $titulo, SQLITE3_TEXT);
$stmt->bindValue(':autor', $autor, SQLITE3_TEXT);
$stmt->bindValue(':editora', $editora, SQLITE3_TEXT);
$stmt->bindValue(':ano', $ano, SQLITE3_TEXT);
$stmt->bindValue(':categoria', $categoria, SQLITE3_INTEGER);
$stmt->bindValue(':sinopse', $sinopse, SQLITE3_TEXT);
$stmt->bindValue(':arquivo_caminho', $arquivoNome, SQLITE3_TEXT);
$stmt->bindValue(':imagem_caminho', $imagemNome, SQLITE3_TEXT);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Atualização realizada com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o banco de dados.']);
}
?>