<?php
// Cria o banco de dados se ele não existir
$mysqli = new SQLite3('../../data/bibliotecario.db');

// Função para evitar SQL injection
function limparEntrada($entrada) {
    global $mysqli;
    return $mysqli->escapeString($entrada);
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtenha os dados do formulário
    $titulo = limparEntrada($_POST["titulo"]);
    $cor = limparEntrada($_POST["cor"]);

    // Insere os dados na tabela cad_turma
    $insertQuery = "INSERT INTO cad_categoria (titulo,cor) VALUES ('$titulo','$cor')";
    $result = $mysqli->exec($insertQuery);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Área de Conhecimento salva com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao salvar Área de Conhecimento."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método de requisição inválido."]);
}
?>
