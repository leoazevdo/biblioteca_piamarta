<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit();
}

try {
    // Conexão com o banco de dados
    $db = new SQLite3('../../data/bibliotecario.db');

    // Dados do formulário
    $titulo = $_POST['titulo'] ?? '';
    $categoria = intval($_POST['categoria'] ?? 0);
    $tipo = intval($_POST['tipo'] ?? 0);
    $isbn = $_POST['isbn'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $editora = $_POST['editora'] ?? '';
    $setor = $_POST['setor'] ?? '';
    $quantidade = intval($_POST['quantidade'] ?? 1);
    $estante = $_POST['estante'] ?? '';
    $prateleira = $_POST['prateleira'] ?? '';
    $sinopse = $_POST['sinopse'] ?? '';
    $capaBase64 = $_POST['capa'] ?? ''; // A imagem em Base64 recebida do formulário

    // Validação básica
    if (empty($titulo) || $quantidade <= 0) {
        echo json_encode(["status" => "error", "message" => "Título ou quantidade inválida."]);
        exit();
    }

    // Processa a imagem Base64 se existir
    $caminhoCapa = '';
    if (!empty($capaBase64)) {
        $caminhoPasta = '../../uploads/imagens/';
        if (!is_dir($caminhoPasta)) {
            mkdir($caminhoPasta, 0777, true);
        }

        // Gera um nome único para a imagem
        $nomeImagem = uniqid() . '.jpg';

        // Remove o cabeçalho "data:image/jpeg;base64," se existir
        $capaBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $capaBase64);
        $capaBase64 = base64_decode($capaBase64);

        if ($capaBase64 === false) {
            echo json_encode(["status" => "error", "message" => "Erro ao processar a imagem."]);
            exit();
        }

        // Salva a imagem no diretório
        $caminhoCompleto = $caminhoPasta . $nomeImagem;
        file_put_contents($caminhoCompleto, $capaBase64);
        $caminhoCapa = $nomeImagem;
    }

    // Insere os registros na tabela `cad_acervo`
    $stmt = $db->prepare("
        INSERT INTO cad_acervo (titulo, categoria, tipo, isbn, autor, editora, setor, quantidade, estante, prateleira, sinopse, capa, codigo)
        VALUES (:titulo, :categoria, :tipo, :isbn, :autor, :editora, :setor, :quantidade, :estante, :prateleira, :sinopse, :capa, :codigo)
    ");

    // Liga os valores aos parâmetros
    $stmt->bindValue(':titulo', $titulo, SQLITE3_TEXT);
    $stmt->bindValue(':categoria', $categoria, SQLITE3_INTEGER);
    $stmt->bindValue(':tipo', $tipo, SQLITE3_INTEGER);
    $stmt->bindValue(':isbn', $isbn, SQLITE3_TEXT);
    $stmt->bindValue(':autor', $autor, SQLITE3_TEXT);
    $stmt->bindValue(':editora', $editora, SQLITE3_TEXT);
    $stmt->bindValue(':setor', $setor, SQLITE3_TEXT);
    $stmt->bindValue(':quantidade', $quantidade, SQLITE3_INTEGER);
    $stmt->bindValue(':estante', $estante, SQLITE3_TEXT);
    $stmt->bindValue(':prateleira', $prateleira, SQLITE3_TEXT);
    $stmt->bindValue(':sinopse', $sinopse, SQLITE3_TEXT);
    $stmt->bindValue(':capa', $caminhoCapa, SQLITE3_TEXT);
    $stmt->bindValue(':codigo', $titulo, SQLITE3_TEXT);

    for ($i = 0; $i < $quantidade; $i++) {
        $stmt->execute();
    }

    echo json_encode(["status" => "success", "message" => "Acervo cadastrado com sucesso!"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
}
?>
