<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

// Conexão com o banco de dados
$db = new SQLite3('../../data/bibliotecario.db');

// Recebe os dados do formulário
$id = intval($_POST['id']);
$titulo = htmlspecialchars($_POST['titulo']);
$autor = htmlspecialchars($_POST['autor']);
$editora = htmlspecialchars($_POST['editora']);
$categoria = intval($_POST['categoria']);
$tipo = intval($_POST['tipo']);
$quantidade = intval($_POST['quantidade']);
$prateleira = htmlspecialchars($_POST['prateleira']);
$estante = htmlspecialchars($_POST['estante']);
$setor = htmlspecialchars($_POST['setor']);
$sinopse = htmlspecialchars($_POST['sinopse']);
$isbn = htmlspecialchars($_POST['isbn']);
$codigo = htmlspecialchars($_POST['codigo']);

// Verifica se uma nova capa foi enviada
$capaNova = isset($_FILES['capa']) && $_FILES['capa']['tmp_name'] != '';
$capa = '';

if ($capaNova) {
    $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('capa_') . '.' . $ext;
    $filePath = '../../uploads/imagens/' . $fileName;

    if (move_uploaded_file($_FILES['capa']['tmp_name'], $filePath)) {
        $capa = $fileName;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a imagem da capa.']);
        exit();
    }
} else {
    // Se nenhuma nova imagem for enviada, mantém a capa existente
    $queryCapaAtual = "SELECT capa FROM cad_acervo WHERE id = :id";
    $stmtCapa = $db->prepare($queryCapaAtual);
    $stmtCapa->bindValue(':id', $id, SQLITE3_INTEGER);
    $resultCapa = $stmtCapa->execute();
    $rowCapa = $resultCapa->fetchArray(SQLITE3_ASSOC);
    $capa = $rowCapa['capa'];
}

// Recupera a quantidade antiga
$queryQuantidade = "SELECT COUNT(*) AS total FROM cad_acervo WHERE codigo = :codigo";
$stmtQuantidade = $db->prepare($queryQuantidade);
$stmtQuantidade->bindValue(':codigo', $codigo, SQLITE3_TEXT);

$result = $stmtQuantidade->execute();
$registro = $result->fetchArray(SQLITE3_ASSOC);
$quantidadeAntiga = $registro['total'];

if($quantidade < $quantidadeAntiga){
    $quantidade = $quantidadeAntiga;
}

// Atualiza o registro principal
$queryAtualizar = "
    UPDATE cad_acervo 
    SET titulo = :titulo, autor = :autor, editora = :editora, categoria = :categoria, tipo = :tipo,
        quantidade = :quantidade, prateleira = :prateleira, estante = :estante, setor = :setor, 
        sinopse = :sinopse, isbn = :isbn, capa = :capa
    WHERE codigo = :codigo
";
$stmtAtualizar = $db->prepare($queryAtualizar);
$stmtAtualizar->bindValue(':codigo', $codigo, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':titulo', $titulo, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':autor', $autor, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':editora', $editora, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':categoria', $categoria, SQLITE3_INTEGER);
$stmtAtualizar->bindValue(':tipo', $tipo, SQLITE3_INTEGER);
$stmtAtualizar->bindValue(':quantidade', $quantidade, SQLITE3_INTEGER);
$stmtAtualizar->bindValue(':prateleira', $prateleira, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':estante', $estante, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':setor', $setor, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':sinopse', $sinopse, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':isbn', $isbn, SQLITE3_TEXT);
$stmtAtualizar->bindValue(':capa', $capa, SQLITE3_TEXT);

if (!$stmtAtualizar->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o registro principal.']);
    exit();
}

// Calcula a diferença de quantidade e insere novos registros, se necessário
if ($quantidade > $quantidadeAntiga) {
    $diferenca = $quantidade - $quantidadeAntiga;

    $queryInserir = "
        INSERT INTO cad_acervo (titulo, autor, editora, categoria, tipo, quantidade, prateleira, estante, setor, sinopse, isbn, capa, codigo)
        VALUES (:titulo, :autor, :editora, :categoria, :tipo, :quantidade, :prateleira, :estante, :setor, :sinopse, :isbn, :capa, :codigo)
    ";

    $stmtInserir = $db->prepare($queryInserir);
    $stmtInserir->bindValue(':titulo', $titulo, SQLITE3_TEXT);
    $stmtInserir->bindValue(':autor', $autor, SQLITE3_TEXT);
    $stmtInserir->bindValue(':editora', $editora, SQLITE3_TEXT);
    $stmtInserir->bindValue(':categoria', $categoria, SQLITE3_INTEGER);
    $stmtInserir->bindValue(':tipo', $tipo, SQLITE3_INTEGER);
    $stmtInserir->bindValue(':quantidade', $quantidade, SQLITE3_INTEGER);
    $stmtInserir->bindValue(':prateleira', $prateleira, SQLITE3_TEXT);
    $stmtInserir->bindValue(':estante', $estante, SQLITE3_TEXT);
    $stmtInserir->bindValue(':setor', $setor, SQLITE3_TEXT);
    $stmtInserir->bindValue(':sinopse', $sinopse, SQLITE3_TEXT);
    $stmtInserir->bindValue(':isbn', $isbn, SQLITE3_TEXT);
    $stmtInserir->bindValue(':capa', $capa, SQLITE3_TEXT);
    $stmtInserir->bindValue(':codigo', $codigo, SQLITE3_TEXT);

    for ($i = 0; $i < $diferenca; $i++) {
        if (!$stmtInserir->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao inserir novos registros.']);
            exit();
        }
    }
}

echo json_encode(['status' => 'success', 'message' => 'Registro atualizado com sucesso.']);
