<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

// Conexão com o banco de dados
$db = new SQLite3('../../data/bibliotecario.db');

// Verifica se o arquivo PDF foi enviado
if (!isset($_FILES['arquivo'])) {
    echo json_encode(['status' => 'error', 'message' => 'Arquivo PDF não enviado.']);
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

// Processa o arquivo PDF
$arquivoTmp = $_FILES['arquivo']['tmp_name'];
$arquivoNome = uniqid('arquivo_') . '.pdf';
$arquivoCaminho = $uploadDirArquivo . $arquivoNome;

if (!move_uploaded_file($arquivoTmp, $arquivoCaminho)) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o arquivo PDF.']);
    exit();
}

// Processa a imagem da capa
$capaBase64 = $_POST['capa']; // Imagem enviada como base64
$imagemNome = null;

if (!empty($capaBase64)) {
    // Verifica se o valor é uma string base64 válida
    if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capaBase64, $matches)) {
        $ext = $matches[1]; // Extensão do arquivo extraída do base64
        $imagemNome = uniqid('imagem_') . '.' . $ext;
        $imagemCaminho = $uploadDirImagem . $imagemNome;

        // Decodifica e salva a imagem
        $imagemData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $capaBase64));
        if (!file_put_contents($imagemCaminho, $imagemData)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a imagem da capa.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagem inválido.']);
        exit();
    }
}

// Recebe os dados do formulário
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

// Insere os dados no banco de dados
$query = "
    INSERT INTO cad_acervo_digital (titulo, autor, editora, ano, categoria, sinopse, arquivo_caminho, imagem_caminho)
    VALUES (:titulo, :autor, :editora, :ano, :categoria, :sinopse, :arquivo_caminho, :imagem_caminho)
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

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Cadastro realizado com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar no banco de dados.']);
}
?>
