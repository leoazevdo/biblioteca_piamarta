<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
} else {
    // Cria a conexão com o banco de dados SQLite3
    $db = new SQLite3('../data/bibliotecario.db'); // Certifique-se de que o arquivo do banco de dados existe e está no caminho correto

    $id_usuario = $_SESSION['user_id'];
    // Consulta combinada para obter dados do usuário e configurações do sistema
    $stmt = $db->prepare("SELECT 
        u.id AS user_id, 
        u.nivel AS user_level, 
        u.nome AS user_name, 
        u.foto AS user_photo, 
        c.nome_instituicao AS institution_name, 
        c.logo_instituicao AS institution_logo, 
        c.endereco_instituicao AS institution_address
    FROM cad_usuario u
    LEFT JOIN cad_configuracoes c ON 1=1
    WHERE u.id = :id");
    $stmt->bindValue(':id', $id_usuario, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if ($result) {
        $dados = $result->fetchArray(SQLITE3_ASSOC); // Pega os dados em formato associativo
    
        if ($dados) {
            $id_usuario = $dados['user_id'];
            $nivel_usuario = $dados['user_level'];
            $nome_usuario = $dados['user_name'];
            $foto_usuario = $dados['user_photo'];
            $nome_instituicao = $dados['institution_name'];
            $logo_instituicao = $dados['institution_logo'];
            $endereco_instituicao = $dados['institution_address'];
    
            // Configuração da foto do usuário
            if (empty($foto_usuario)) {
                $foto_usuario = '../img/default.jpg';
            } else {
                $foto_usuario = '../uploads/imagens/' . $foto_usuario;
            }
    
            // Verifica o nível do usuário
            $nivel = "";
            if ($nivel_usuario != "1") {
                header("Location: ../index.php");
                exit();
            } else {
                $nivel = 'Administrador';
            }
        } else {
            // Usuário não encontrado, redirecionar para o login
            header("Location: ../index.php");
            exit();
        }
    }

}

$totals = [];
$tables = [
    'cad_acervo' => 'total_acervos',
    'cad_usuario' => 'total_usuarios',
    'emprestimos' => 'total_emprestimos',
    'cad_acervo_digital' => 'total_ebooks'
];

foreach ($tables as $table => $variable) {
    $stmt = $db->prepare("SELECT COUNT(id) as total FROM $table");
    $result = $stmt->execute();
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $totals[$variable] = $row['total'];
    } else {
        $totals[$variable] = 0; // Valor padrão caso a consulta falhe
    }
}

// Atribui os valores às variáveis
$total_acervos = $totals['total_acervos'];
$total_usuarios = $totals['total_usuarios'];
$total_emprestimos = $totals['total_emprestimos'];
$total_ebooks = $totals['total_ebooks'];



?>

<div class="page-title" >
    <div class="title_left" >
        <img src="<?php echo $logo_instituicao ?>" style="width: 90px; margin-bottom: 10px;"  alt="">
    </div>
    <div class="title_right" >
        <h3 class="text-right" style="margin-right: 30px; margin-top: 30px;"><?php echo $nome_instituicao ?>
    </h3>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Sistema Para Gestão de Biblioteca</h2>                    
                <div class="clearfix"></div>
            </div>
        <div class="x_content" >

            <div  class="row">
                <div  class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div  class="tile-stats">
                    <div  style="color: #017CBC " class="icon"><i class="fa fa-book"></i>
                    </div>
                    <div class="count"><?php echo $total_acervos ?></div>

                    <h3  style="color: #017CBC ">Livros</h3>
                    <p>Cadastrados no Sistema.</p>
                    </div>
                </div>
                <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="tile-stats">
                    <div style="color: #017CBC " class="icon"><i class="fa fa-user"></i>
                    </div>
                    <div class="count"><?php echo $total_usuarios ?></div>

                    <h3  style="color: #017CBC ">Usuários</h3>
                    <p>Registrados.</p>
                    </div>
                </div>
                <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="tile-stats">
                    <div  style="color: #017CBC " class="icon"><i class="fa fa-exchange"></i>
                    </div>
                    <div class="count"><?php echo $total_emprestimos ?></div>

                    <h3  style="color: #017CBC ">Empréstimos</h3>
                    <p>Atendimentos ao Público.</p>
                    </div>
                </div>
                <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="tile-stats">
                    <div  style="color: #017CBC " class="icon"><i class="fa fa-tablet"></i>
                    </div>
                    <div class="count"><?php echo $total_ebooks ?></div>

                    <h3  style="color: #017CBC ">E-Books</h3>
                    <p>Cadastrados.</p>
                    </div>
                </div>
            </div>

               
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
      
    })
</script>