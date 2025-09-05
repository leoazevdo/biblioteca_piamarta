<?php
// Conexão com o banco de dados
$mysqli = new SQLite3('../../../data/bibliotecario.db');

// Função para evitar SQL injection
function limparEntrada($entrada) {
    global $mysqli;
    return $mysqli->escapeString($entrada);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtenha os dados do formulário
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $descricao = limparEntrada($_POST["descricao"]);

    
        // Atualiza o registro existente
        $updateQuery = "UPDATE cad_tipo SET descricao = '$descricao' WHERE id = '$id'";
        $result = $mysqli->exec($updateQuery);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Cadastro atualizado com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao atualizar cadastro."]);
        }
   
} else {
    echo json_encode(["status" => "error", "message" => "Método de requisição inválido."]);
}
?>
