<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
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
     if ($nivel_usuario != "2") {
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

// Conexão com o banco de dados SQLite
$db = new SQLite3('../data/bibliotecario.db');
if (!$db) {
    die("Erro ao conectar ao banco de dados SQLite.");
}

// Consulta para "Mais Lidos"
$maisLidosQuery = "
    SELECT a.id, a.titulo, a.capa, a.autor, COUNT(e.acervo_id) AS total_emprestimos
    FROM emprestimos e
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    WHERE  e.user_id = '$id_usuario'
    GROUP BY e.acervo_id
    ORDER BY total_emprestimos DESC
    LIMIT 6
";
$maisLidosResult = $db->query($maisLidosQuery);

// Consulta para "Empreatado e Não devolvidos"
$naoDevolvidoQuery = "
    SELECT a.id, a.titulo, a.capa, a.autor, COUNT(e.acervo_id) AS total_emprestimos
    FROM emprestimos e
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    WHERE  e.user_id = '$id_usuario' AND e.devolvido = 'Não'
    GROUP BY e.acervo_id
    ORDER BY total_emprestimos DESC
    LIMIT 6
";
$naoDevolvidoResult = $db->query($naoDevolvidoQuery);

// Consulta para "Últimos Cadastros"
$ultimosCadastrosQuery = "
    SELECT id, titulo, capa, autor
    FROM cad_acervo
    ORDER BY id DESC
    LIMIT 6
";
$ultimosCadastrosResult = $db->query($ultimosCadastrosQuery);

// Consulta para "Literatura"
$literaturaQuery = "
    SELECT DISTINCT titulo, capa, autor
    FROM cad_acervo
    WHERE categoria = '1'
    ORDER BY titulo 
    LIMIT 6
";
$literaturaResult = $db->query($literaturaQuery);
?>


    <style>
        .book-card {
            margin-bottom: 20px;
            text-align: center;
        }
        .book-card img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .book-title {
            font-weight: bold;
            margin-top: 10px;
        }
        .book-author {
            font-style: italic;
            color: #777;
        }
    </style>

    <div class="container">
        <h1 class="text-center"><?php echo $nome_instituicao ?></h1>
        <hr>

        <div>
            <?php
                $naoDevolvido = [];
                // Executa a consulta e preenche o array
                while ($row = $naoDevolvidoResult->fetchArray(SQLITE3_ASSOC)) { 
                    $naoDevolvido[] = $row; // Adiciona os resultados em um array
                }
                if (empty($naoDevolvido)) {
                    
                }else{
                    echo '<div class="alert alert-danger" role="alert">Você possui empréstimos ainda pendentes de devolução.</div>';
                }

            ?>

        </div>

        <!-- Mais Lidos -->
        <h3>Minhas Leituras</h3>
        <div class="row">
        <?php 
            $maisLidos = [];

            // Executa a consulta e preenche o array
            while ($row = $maisLidosResult->fetchArray(SQLITE3_ASSOC)) { 
                $maisLidos[] = $row; // Adiciona os resultados em um array
            }

            // Verifica se o array está vazio
            if (empty($maisLidos)) {
                echo '<div class="alert alert-info" role="alert">Nenhum livro encontrado nos Mais Lidos.</div>';
            } else {
                foreach ($maisLidos as $row): 
                    $capa = htmlspecialchars($row['capa']);
                    $caminhoFinal = '';

                    if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capa)) {
                        // Mantém o valor atual de $capa
                        $caminhoFinal = $capa;
                    } elseif ($capa === '../master/images/book.png' || $capa === '') {
                        $caminhoFinal = '../img/book.png';
                    } else {
                        // Altera para o caminho padrão no diretório de uploads
                        $caminhoFinal = '../uploads/imagens/' . $capa;
                    }
            ?>
                    <div class="col-sm-2 book-card">
                        <img src="<?php echo $caminhoFinal ?>" alt="<?= htmlspecialchars($row['titulo']) ?>">
                        <div class="book-title"><?= htmlspecialchars($row['titulo']) ?></div>
                        <div class="book-author"><?= htmlspecialchars($row['autor']) ?></div>
                    </div>
            <?php 
                endforeach; 
            }
            ?>

        </div>
        <hr>

        <!-- Últimos Cadastros -->
        <h3>Últimos Cadastros</h3>
        <div class="row">
            <?php while ($row = $ultimosCadastrosResult->fetchArray(SQLITE3_ASSOC)) : 
                 $capa = htmlspecialchars($row['capa']);
                 $caminhoFinal = '';
     
                 if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capa) ) {
                     // Mantém o valor atual de $capa
                     $caminhoFinal = $capa;
                 }elseif($capa === '../master/images/book.png' || $capa === ''){
                     $caminhoFinal = '../img/book.png';
                 } else {
                     // Altera para o caminho padrão no diretório de uploads
                     $caminhoFinal = '../uploads/imagens/' . $capa;
                 }
                
            ?>
                
                <div class="col-sm-2 book-card">

                    <img src="<?php echo $caminhoFinal ?>" alt="<?= htmlspecialchars($row['titulo']) ?>">
                    <div class="book-title"><?= htmlspecialchars($row['titulo']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($row['autor']) ?></div>
                </div>
            <?php endwhile; ?>
        </div>
        <hr>

        <!-- Literatura -->
        <h3>Literatura</h3>
        <div class="row">
            <?php while ($row = $literaturaResult->fetchArray(SQLITE3_ASSOC)) : 
                $capa = htmlspecialchars($row['capa']);
                $caminhoFinal = '';
     
                if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capa) ) {
                    // Mantém o valor atual de $capa
                    $caminhoFinal = $capa;
                }elseif($capa === '../master/images/book.png' || $capa === ''){
                    $caminhoFinal = '../img/book.png';
                } else {
                    // Altera para o caminho padrão no diretório de uploads
                    $caminhoFinal = '../uploads/imagens/' . $capa;
                }
                ?>
                <div class="col-sm-2 book-card">
                    <img src="<?php echo $caminhoFinal ?>" alt="<?= htmlspecialchars($row['titulo']) ?>">
                    <div class="book-title"><?= htmlspecialchars($row['titulo']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($row['autor']) ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.filter-btn').on('click', function() {
                const category = $(this).data('category');
                $('.book-container').hide(); // Esconde todas as seções
                $(`[data-category="${category}"]`).fadeIn(); // Mostra apenas a seção correspondente
            });
        });
    </script>
