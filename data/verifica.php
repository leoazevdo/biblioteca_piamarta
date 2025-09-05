<?php
// Cria o banco de dados se ele não existir
$mysqli = new SQLite3('bibliotecario.db');

// Função para evitar SQL injection
function limparEntrada($entrada) {
    global $mysqli;
    return $mysqli->escapeString($entrada);
}

// Cria a tabela cad_usuario se ela não existir
$createTableQuery = "
CREATE TABLE IF NOT EXISTS cad_usuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    login TEXT NOT NULL,
    nivel INTEGER NOT NULL,
    turma TEXT,
    fone TEXT
)";
$mysqli->exec($createTableQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtenha as credenciais do formulário
    $login = limparEntrada($_POST["login"]);

    // Consulta SQL para verificar as credenciais no banco de dados
    $sql = "SELECT * FROM cad_usuario WHERE login = '$login'";
    $result = $mysqli->query($sql);

    if ($result) {
        $dados = $result->fetchArray(SQLITE3_ASSOC);
        if ($dados) {
            $id_usuario = $dados['id'];
            $nivel_usuario = $dados['nivel'];

            // Login bem-sucedido
            session_start();
            /*
            nivel 1 = Administrador
            nivel 2 = Usuário
             
            */
            $_SESSION['user_id'] = $id_usuario;
            if ($nivel_usuario == "1") {
                echo "admin/painel.php";
            } elseif ($nivel_usuario == "2") {
                echo "usuarios/painel.php";
            } elseif ($nivel_usuario == "3") {
                echo "professor/painel.php";
            } elseif ($nivel_usuario == "4") {
                echo "aluno/painel.php";
            }
            exit();
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
}
?>
