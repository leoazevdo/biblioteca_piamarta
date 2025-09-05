<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexão com o banco de dados
    $mysqli = new SQLite3('../../data/bibliotecario.db');

    // Função para evitar SQL injection
    function limparEntrada($entrada) {
        global $mysqli;
        return $mysqli->escapeString($entrada);
    }

    // Obtenha o ID da turma a ser excluída
    $id = limparEntrada($_POST["id"]);

    // Exclui o registro da tabela cad_turma
    $deleteQuery = "DELETE FROM cad_turma WHERE id = '$id'";
    $result = $mysqli->exec($deleteQuery);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Turma excluída com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao excluir turma."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método de requisição inválido."]);
}
?>
