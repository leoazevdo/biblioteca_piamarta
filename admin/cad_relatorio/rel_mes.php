<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Conexão com o banco de dados SQLite
$db = new SQLite3('../../data/bibliotecario.db');
if (!$db) {
    die("Erro ao conectar ao banco de dados SQLite.");
}

$id_usuario = $_SESSION['user_id'];

// Consulta combinada para dados do usuário e da instituição
$stmt = $db->prepare("
    SELECT u.id AS user_id, u.nivel AS user_level, u.nome AS user_name, u.foto AS user_photo, 
           c.nome_instituicao AS institution_name, c.logo_instituicao AS institution_logo, 
           c.endereco_instituicao AS institution_address
    FROM cad_usuario u
    LEFT JOIN cad_configuracoes c ON 1=1
    WHERE u.id = :id
");
$stmt->bindValue(':id', $id_usuario, SQLITE3_INTEGER);
$result = $stmt->execute();
$dados = $result->fetchArray(SQLITE3_ASSOC);

if (!$dados) {
    header("Location: ../../index.php");
    exit();
}

$foto_usuario = $dados['user_photo'] ?: '../../img/default.jpg';
$logo_instituicao = $dados['institution_logo'] ?: '../../img/default-logo.png';
$nivel_usuario = $dados['user_level'];
$nome_usuario = $dados['user_name'];

if ($nivel_usuario != 1) {
    header("Location: ../../index.php");
    exit();
}

$ano = filter_input(INPUT_GET, 'ano', FILTER_SANITIZE_NUMBER_INT);
if (!$ano || strlen($ano) !== 4) {
    die("Ano inválido.");
}

$mes = filter_input(INPUT_GET, 'mes', FILTER_SANITIZE_NUMBER_INT);
if (!$mes || $mes < 1 || $mes > 12) {
    die("Mês inválido.");
}

// Array de meses
$mesesNomes = [
    1 => 'JANEIRO', 2 => 'FEVEREIRO', 3 => 'MARÇO', 4 => 'ABRIL',
    5 => 'MAIO', 6 => 'JUNHO', 7 => 'JULHO', 8 => 'AGOSTO',
    9 => 'SETEMBRO', 10 => 'OUTUBRO', 11 => 'NOVEMBRO', 12 => 'DEZEMBRO'
];

$mesNome = $mesesNomes[$mes] ?? 'MÊS INVÁLIDO';

$mes_atual = date('m');
$ano_atual = date('Y');
$dia = date('d');

// Formata o nome do mês atual
$mes_atual = $mesesNomes[(int)$mes_atual] ?? 'MÊS INVÁLIDO';

// Consulta SQL para atendimentos
$stmt = $db->prepare("
    SELECT e.*, e.id as id_emprestimo, u.id as user_id, u.nome as nome_usuario, a.id as acervo_id, a.titulo as titulo_acervo 
    FROM emprestimos e
    LEFT JOIN cad_usuario u ON e.user_id = u.id
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    WHERE strftime('%Y', data_atual) = :ano
    AND strftime('%m', data_atual) = :mes
");
$stmt->bindValue(':ano', $ano, SQLITE3_TEXT);
$stmt->bindValue(':mes', str_pad($mes, 2, '0', STR_PAD_LEFT), SQLITE3_TEXT);
$result = $stmt->execute();

// Funções auxiliares para formatação de datas
function formatDateToCompare($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d ? $d->format('Y-m-d') : $date;
}

function formatDateToDisplay($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}

?>

<style>
    .a4 {
        width: 210mm;
        min-height: 297mm;
        padding: 1mm;
        margin: auto;
        background: white;
        font-family: "Arial", "Helvetica", sans-serif;
        font-size: 11pt;
        line-height: 1.5;
        color: #000;
    }

    .a4 h1, .a4 h2, .a4 h3, .a4 h5 {
        text-align: center;
        margin-bottom: 20px;
    }

    .a4 img {
        max-width: 300px;
        height: auto;
    }

    @media print {
        .a4, .a4 * {
            visibility: visible;
        }

        .a4 {
            margin: 0;
            box-shadow: none;
        }

        .no-print {
            display: none;
        }
    }

    .intro-text {
        text-align: justify;
        margin-bottom: 20px;
    }

    .bg-verde {
        background-color: #c8e6c9;
        color: #1b5e20;
    }

    .bg-vermelho {
        background-color: #ffcdd2;
        color: #b71c1c;
    }

    .bg-amarelo {
        background-color: #fff9c4;
        color: #000;
    }
</style>

<br>
<h3>RELATÓRIO DE GESTÃO DE BIBLIOTECA ANO: <?php echo $ano ?> MÊS: <?php echo $mesNome ?></h3>

<div class="container mt-4">
    <div class="no-print text-right">
        <button class="btn btn-lg btn-primary" id="imprimir">Imprimir</button>    
    </div>
    <div class="a4" id="document">
        <div class="text-center">
            <img src="<?php echo $logo_instituicao ?>" alt="Logo da Instituição" style="width: 200px;">
        </div>
        <div contenteditable="true" class="seletor">
            <h3>TABELA CONSOLIDADA DE ATENDIMENTOS</h3>
            <h5>
                <?php echo htmlspecialchars($dados['institution_name'] ?? 'NOME DA INSTITUIÇÃO'); ?> <br>
                <strong> PERÍODO: <?php echo $mesNome . ' / ' . $ano ?> </strong>
            </h5>
        </div>
        <div contenteditable="true" class="intro-text seletor">
            <p>
                Este relatório apresenta os resultados dos atendimentos realizados pela biblioteca no período de <?php echo $mesNome ?> de <?php echo $ano ?>.
                Abaixo estão os dados detalhados dos empréstimos realizados e os respectivos status de devolução.
            </p>
        </div>
        <table class="table table-bordered table-striped" id="emprestimos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>Título</th>
                    <th>ID</th>
                    <th>Retirada</th>
                    <th>Devolução</th>
                    <th class="text-center">Devolvido?</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ordem = 1;
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $dataDevolucao = formatDateToCompare($row['data_devolucao']);
                    $currentDate = date('Y-m-d');
                    $rowClass = strtolower($row['devolvido']) === 'sim' ? 'bg-verde' : ($dataDevolucao < $currentDate ? 'bg-vermelho' : 'bg-amarelo');
                    echo "<tr class='{$rowClass}'>";
                    echo "<td>{$ordem}</td>";
                    echo "<td>" . htmlspecialchars($row['nome_usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['titulo_acervo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['id_emprestimo']) . "</td>";
                    echo "<td>" . formatDateToDisplay($row['data_atual']) . "</td>";
                    echo "<td>" . formatDateToDisplay($row['data_devolucao']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($row['devolvido']) . "</td>";
                    echo "</tr>";
                    $ordem++;
                }
                ?>
            </tbody>
        </table>
        <br>
        <div contenteditable="true" class="seletor">
        <div contenteditable="true"  class="text-right" style="padding-left: 20px; padding-right: 20px;">
            Cidade, <?php echo $dia.' de '.$mes_atual.' de '. $ano_atual ?>
            
        </div>
        <br><br>
        <p contenteditable="true" class="text-center">
                        _________________________________________________<br>
                        <strong><?php echo $nome_usuario?></strong><br>
                        Responsável Pela Biblioteca

        </p>
        </div>
        <br><br><br><br>
    </div>
</div>

<script>
$(function(){
    $('#imprimir').on('click', function () {        

            $('#document').print({
                        iframe : false,
                        mediaPrint : false,
                        noPrintSelector : ".avoid-this",
                        //add título 
                        prepend : "",
                        append : ""
            });
          
    });

})
tinymce.init({
        selector: '.seletor', // ID do elemento editável
        inline: true, // Ativa o modo inline
        plugins: "code",

        toolbar: 'undo redo | alignleft aligncenter alignright alignjustify | bold italic underline | bullist numlist | link image | code',
        menubar: false, // Remove o menu superior
});
</script>
