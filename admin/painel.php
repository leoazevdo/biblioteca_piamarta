<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
} else {
    // Conectar ao banco de dados SQLite3
    $db = new SQLite3('../data/bibliotecario.db'); // Certifique-se de que o arquivo do banco existe e está correto

    $id_usuario = $_SESSION['user_id'];

    // Criar a tabela 'cad_configuracoes' se não existir
    $sql = "CREATE TABLE IF NOT EXISTS cad_configuracoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_instituicao TEXT,
        logo_instituicao TEXT,
        endereco_instituicao TEXT
    )";
    $db->exec($sql); // Executa a criação da tabela

    // Verificar se a coluna 'foto' já existe na tabela 'cad_usuario'
    $query = "PRAGMA table_info(cad_usuario)";
    $result = $db->query($query);
    
    $fotoExiste = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === 'foto') {
            $fotoExiste = true;
            break;
        }
    }

    // Se a coluna 'foto' não existir, adiciona
    if (!$fotoExiste) {
        $sql = "ALTER TABLE cad_usuario ADD COLUMN foto TEXT";
        if ($db->exec($sql)) {
            echo "Coluna 'foto' adicionada com sucesso!";
        } else {
            echo "Erro ao adicionar a coluna 'foto'.";
        }
    } 

    


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
     else {
        // Erro na execução da consulta
        header("Location: ../index.php");
        exit();
    }

    try {
      // Nome da tabela e do campo a ser adicionado
      $tabela = 'cad_usuario';
      $novo_campo = 'foto';
      $tipo_campo = 'TEXT';
  
      // Verifica se o campo já existe na tabela
      $stmt = $db->prepare("PRAGMA table_info($tabela)");
      $result = $stmt->execute();
  
      $campo_existe = false;
      while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
          if ($row['name'] === $novo_campo) {
              $campo_existe = true;
              break;
          }
      }
  
      // Adiciona o campo se ele não existir
      if (!$campo_existe) {
          $db->exec("ALTER TABLE $tabela ADD COLUMN $novo_campo $tipo_campo");
          //echo "Campo '$novo_campo' adicionado à tabela '$tabela'.";
      } else {
          //echo "O campo '$novo_campo' já existe na tabela '$tabela'.";
      }
  } catch (Exception $e) {
      echo "Erro: " . $e->getMessage();
  } finally {
     
  }

  // Criação da tabela cad_configuracoes se não existir
  $db->exec("CREATE TABLE IF NOT EXISTS cad_configuracoes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome_instituicao TEXT NOT NULL,
      logo_instituicao TEXT,
      endereco_instituicao TEXT NOT NULL
  )");

    $stmt->close(); // Fecha o statement
    $db->close(); // Fecha a conexão com o banco de dados
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>BIBLIOTECÁRIO</title>

    <!-- Bootstrap -->
    <link href="../arquivos/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../arquivos/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../arquivos/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../arquivos/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
	
    <!-- bootstrap-progressbar -->
    <link href="../arquivos/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../arquivos/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../arquivos/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../arquivos/build/css/custom.min.css" rel="stylesheet">
    <!-- Tema do Piamarta -->
    <link href="../arquivos/build/css/meu-tema.css" rel="stylesheet">

    <link href="../arquivos/vendors/fullcalendar/dist/fullcalendar.css" rel="stylesheet">

    <link href="../arquivos/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
   

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />

    <link href="../arquivos/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../arquivos/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../arquivos/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../arquivos/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../arquivos/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
    <!-- GoogleFonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    
  </head>

  <body  class="nav-md">
    <div  class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
           

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="<?php echo $foto_usuario ?>" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
                <span>Bem-vindo(a),</span>
                <h2><?php echo $nome_usuario ?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>MENU</h3>
                <ul class="nav side-menu">
                  <li>
                    <a><i class="fa fa-home"></i> Home <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#" class="painel_principal">Painel Principal</a></li>   
                      <li><a href="#" class="ver_perfil">Perfil</a></li>                   
                    </ul>
                  </li>
                  <li><a><i class="fa fa-edit"></i> Cadastros <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#" class="cad_turma">Setor/Turma</a></li>
                      <li><a href="#" class="cad_usuarios">Usuários</a></li>
                      <li><a href="#" class="cad_acervos">Acervos</a></li>
                      <li><a href="#" class="cad_digitais">Acervos Digitais</a></li>                      
                                    
                      
                    </ul>
                  </li>
                  <li><a><i class="fa fa-book"></i> Empréstimos <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#" class="cad_emprestimos">Painel</a></li>
                                            
                                     
                    </ul>
                  </li>
                  <li><a><i class="fa fa-book"></i> Configurações <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#" class="cad_configuracoes"> Painel</a></li>   
                      
                      
                    </ul>
                  </li>
                  <li><a><i class="fa fa-book"></i> Relatórios <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#" class="cad_relatorio"> Painel</a></li> 
                      
                    </ul>
                  </li>

                  
                 
                </ul>
              </div>
              

            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons 
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
             /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $foto_usuario ?>" alt=""><?php echo $nome_usuario ?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="#" class="ver_perfil"> Perfil</a></li>
                    
                    <li><a href="../sair.php"><i class="fa fa-sign-out pull-right"></i> Sair</a></li>
                  </ul>
                </li>

                
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main" >
          <div id="conteudo"></div>

          
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
           Directed by Departamento de TI - Colégio Piamarta Montese
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <script src="../arquivos/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../arquivos/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!--<script src="../arquivos/scripts/sweetalert/sweetalert.min.js" type="text/javascript"></script>-->
    <script src="../arquivos/scripts/sweetalert/sweetalert2.min.js" type="text/javascript"></script>
    <script src="../arquivos/scripts/jquery_mask/jquery_mask.js" type="text/javascript"></script>
    <script src="../arquivos/vendors/Chart.js/dist/Chart.js"></script>
    
    
    <script src="../arquivos/vendors/fullcalendar/dist/fullcalendar.js"></script>
    <!-- Adiciona o JavaScript do Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
    <!-- Parsley 
    <script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
    -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    
    <!-- Include jQuery Signature plugin -->
    
   
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="../arquivos/scripts/signature/js/jquery.signature.js"></script>
  <script src="../arquivos/scripts/printer/jQuery.print.js"></script> 

  <!-- DataTables Core -->
  <script src="../arquivos/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
 
  <script src="../arquivos/vendors/jszip/dist/jszip.min.js"></script>
  <!-- DataTables Buttons -->
  <script src="../arquivos/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>

  <!-- DataTables Extras -->
  <script src="../arquivos/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
  <script src="../arquivos/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
  <script src="../arquivos/vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>

  <!-- JSZip para exportação para Excel -->
  <script src="../arquivos/vendors/jszip/dist/jszip.min.js"></script>

  <!-- pdfMake para exportação para PDF -->
  <script src="../arquivos/vendors/pdfmake/build/pdfmake.min.js"></script>
  <script src="../arquivos/vendors/pdfmake/build/vfs_fonts.js"></script>

  <script src="../arquivos/scripts/tinymce/tinymce.min.js"></script>
  <script src="../arquivos/scripts/tinymce/langs/pt_BR.js"></script>

    

    <!-- Custom Theme Scripts -->
    <script src="../arquivos/build/js/custom.js"></script>
    <script>
        $(function(){
            $(".ver_perfil").click(function(e){
                e.preventDefault();
                $("#conteudo").load("../perfil/painel.php")

            })
            $(".cad_turma").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_turma/painel.php")
            })
            $(".cad_usuarios").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_usuarios/painel.php")
            })
            $(".cad_acervos").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_acervos/painel.php")
            })
            $(".cad_digitais").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_digitais/painel.php")
            })
            $(".cad_emprestimos").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_emprestimos/painel.php")
            })
            $(".cad_configuracoes").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_configuracoes/painel.php")
            })
            $(".cad_relatorio").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_relatorio/painel.php")
            })
            $(".cad_atendimentos").click(function(e){
                e.preventDefault();
                $("#conteudo").load("cad_atendimentos/painel.php")
            })
            $(".cad_consolidado_mes").click(function(e){
                e.preventDefault();
                $("#conteudo").load("consolidado_mes/painel.php")
            })
            $(".painel_principal").click(function(e){
                e.preventDefault();
                $("#conteudo").load("painel_principal.php")
            })
            $("#conteudo").load("painel_principal.php")
            
        })
    </script>
	
  </body>
</html>
