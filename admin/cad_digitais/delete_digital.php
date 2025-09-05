<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

// Conexão com o banco de dados
$db = new SQLite3('../../data/bibliotecario.db');

// Recebe o ID do registro a ser excluído
$id = htmlspecialchars($_POST['id']);

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID não fornecido.']);
    exit();
}

// Recupera os caminhos dos arquivos no banco de dados
$query = "SELECT arquivo_caminho, imagem_caminho FROM cad_acervo_digital WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Registro não encontrado.']);
    exit();
}

$arquivoCaminho = $result['arquivo_caminho'] ?? null;
$imagemCaminho = $result['imagem_caminho'] ?? null;

// Diretórios de arquivos
$uploadDirArquivo = '../../uploads/arquivos/';
$uploadDirImagem = '../../uploads/imagens/';

// Exclui o arquivo PDF, se existir
if ($arquivoCaminho && file_exists($uploadDirArquivo . $arquivoCaminho)) {
    unlink($uploadDirArquivo . $arquivoCaminho);
}

// Exclui a imagem, se existir
if ($imagemCaminho && file_exists($uploadDirImagem . $imagemCaminho)) {
    unlink($uploadDirImagem . $imagemCaminho);
}

// Exclui o registro do banco de dados
$deleteQuery = "DELETE FROM cad_acervo_digital WHERE id = :id";
$deleteStmt = $db->prepare($deleteQuery);
$deleteStmt->bindValue(':id', $id, SQLITE3_INTEGER);

if ($deleteStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Registro excluído com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir o registro do banco de dados.']);
}
?>
