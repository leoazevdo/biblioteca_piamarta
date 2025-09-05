<?php
// Cria o banco de dados se ele não existir
$mysqli = new SQLite3('../../../data/bibliotecario.db');

// Função para evitar SQL injection
function limparEntrada($entrada) {
    global $mysqli;
    return $mysqli->escapeString($entrada);
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtenha os dados do formulário
    $descricao = limparEntrada($_POST["descricao"]);

    // Insere os dados na tabela cad_turma
    $insertQuery = "INSERT INTO cad_tipo (descricao) VALUES ('$descricao')";
    $result = $mysqli->exec($insertQuery);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Tipo de acervo salvo com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao salvar Tipo de Acervo."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método de requisição inválido."]);
}
?>
