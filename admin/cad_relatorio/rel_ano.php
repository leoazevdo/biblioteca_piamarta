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

$meses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
$atendimentos = array_fill(1, 12, 0);

// Consulta SQL para atendimentos
$stmt = $db->prepare("
    SELECT CAST(strftime('%m', data_atual) AS INTEGER) AS mes, COUNT(*) AS total
    FROM emprestimos
    WHERE strftime('%Y', data_atual) = :ano
    GROUP BY mes
    ORDER BY mes ASC
");
$stmt->bindValue(':ano', $ano, SQLITE3_TEXT);
$result = $stmt->execute();

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $atendimentos[$row['mes']] = $row['total'];
}

$mes_atual = date('m');
$ano_atual = date('Y');
$dia = date('d');

$i = $mes_atual;


    if($i == '1'){
        $mes_atual = 'JANEIRO';
    }elseif($i == 2){
        $mes_atual = 'FEVEREIRO';
    }elseif($i == 3){
        $mes_atual = 'MARÇO';
    }elseif($i == 4){
        $mes_atual = 'ABRIL';
    }elseif($i == 5){
        $mes_atual = 'MAIO';
    }elseif($i == 6){
        $mes_atual = 'JUNHO';
    }elseif($i == 7){
        $mes_atual = 'JULHO';
    }elseif($i == 8){
        $mes_atual = 'AGOSTO';
    }elseif($i == 9){
        $mes_atual = 'SETEMBRO';
    }elseif($i == 10){
        $mes_atual = 'OUTUBRO';
    }elseif($i == 11){
        $mes_atual = 'NOVEMBRO';
    }elseif($i == 12){
        $mes_atual = 'DEZEMBRO';
    }
    


?>
<style>

.mx-custom {
    margin-left: 20px;
    margin-right: 20px;
}
        .a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 1mm;
            margin: auto;
            background: white;
            font-family: "Arial", "Helvetica", sans-serif;
            font-size: 11pt; /* Tamanho ligeiramente menor */
            line-height: 1.5;
            color: #000;
           
        }

        .a4 h1, .a4 h2, .a4 h3, .a4 h5, .text-center{
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
 
@media print {
        #foto img {
            max-width: 100px; /* Defina a largura máxima desejada (porcentagem ou valor em pixels) */
            height: auto; /* Mantém a proporção original da imagem */
        }
    }

    .intro-text {
        text-align: justify; /* Adiciona justificação ao texto */
        margin-bottom: 20px; /* Adiciona um espaçamento inferior */
    }


</style>
<script src="../arquivos/scripts/html2canvas.js"></script>

<br>
<h3>RELATÓRIO DE GESTÃO DE BIBLIOTECA ANO: <?php echo $ano ?></h3>

<div class="container mt-4">
    <div class="no-print text-right">
        <button class="btn btn-lg btn-primary" id="imprimir">Imprimir</button>    
    </div>
    <div class="a4" id="document">
        <div class="text-center">
            <img src="<?php echo $logo_instituicao ?>" alt="" style="width: 200px;">
        </div>
        <div contenteditable="true" class="seletor">
        <h3 contenteditable="true">RELATÓRIO CONSOLIDADO DE ATENDIMENTOS</h3>
        <h5 contenteditable="true" style="margin-top: -10px;">
            NOME DA INSTITUIÇÃO (click e edite) <br><br>
           <strong> PERÍODO: <?php echo $ano ?>   </strong>    
        </h5>
        </div>
        <br>
        <!-- inserir o texto introdutório abaixo -->
        <div contenteditable="true" class="intro-text seletor">
            <p>
                <strong>Relatório Consolidado de Atividades - Ano <?php echo $ano; ?></strong>
            </p>
            <p>
                Este relatório apresenta uma visão geral das atividades realizadas pelo sistema de gestão de bibliotecas durante o ano de <strong><?php echo $ano; ?></strong>. 
                Ele consolida os resultados alcançados, destacando os atendimentos mensais realizados ao longo do período, bem como o impacto gerado no suporte ao acesso e 
                utilização dos acervos disponíveis na instituição.
            </p>
            <p>
                Ao longo do ano, foi possível observar a relevância do sistema na organização e gestão das operações bibliotecárias, garantindo maior eficiência no controle 
                de empréstimos e devoluções. Além disso, o sistema contribuiu significativamente para o aumento da acessibilidade dos recursos educacionais oferecidos aos usuários.
            </p>
            <p>
                Este documento visa proporcionar uma análise detalhada dos dados coletados, apresentados em tabelas e gráficos para facilitar a visualização e interpretação 
                das informações. Esperamos que as informações aqui consolidadas sirvam como base para decisões estratégicas e planejamento de melhorias contínuas para os 
                próximos períodos.
            </p>
        </div>
<br>
        
        <h5>Tabela: Atendimento Mensal</h5>
        
        <table class="table table-bordered table-striped" >
            <thead>
                <tr class="text-center">
                    <th>MESES</th>
                    <?php foreach ($meses as $mes) : ?>
                        <th><?= htmlspecialchars($mes) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center">
                    <td>Atendimentos</td>
                    <?php foreach ($atendimentos as $total) : ?>
                        <td><?= htmlspecialchars($total) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <br><br>
        <h5>Gráfico: Atendimentos por Mês</h5>
        <div style="height: 250px;">
        <canvas id="graficoAtendimentos" ></canvas>
        </div>
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

    // Dados do gráfico
    const meses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
    const atendimentos = <?= json_encode(array_values($atendimentos)) ?>; // PHP gera os valores em JSON

    // Configuração do gráfico
    const ctx = document.getElementById('graficoAtendimentos').getContext('2d');
    const graficoAtendimentos = new Chart(ctx, {
        type: 'bar', // Tipo de gráfico: barra
        data: {
            labels: meses, // Rótulos do eixo X
            datasets: [{
                label: 'Atendimentos Mensais',
                data: atendimentos, // Dados do eixo Y
                backgroundColor: 'rgba(54, 162, 235, 0.2)', // Cor de fundo das barras
                borderColor: 'rgba(54, 162, 235, 1)', // Cor da borda das barras
                borderWidth: 1 // Largura da borda
            }]
        },
        options: {
            responsive: true, // Responsivo
            maintainAspectRatio: false, // Não mantém proporção fixa
            scales: {
                y: {
                    beginAtZero: true // Iniciar o eixo Y no zero
                }
            },
            plugins: {
                legend: {
                    display: true, // Exibe a legenda
                    position: 'top'
                },
                tooltip: {
                    enabled: true // Exibe as informações no hover
                }
            }
        }
    });

    $('#imprimir').on('click', function () {
            // Seletores dos gráficos a serem transformados em imagens
            const grafico1 = document.querySelector("#graficoAtendimentos");
           
            // Função para transformar um gráfico em imagem
        function transformarGraficoEmImagem(grafico) {
                return new Promise((resolve) => {
                    html2canvas(grafico).then(canvas => {
                        const imgData = canvas.toDataURL("image/png");
                        const imgElement = document.createElement("img");
                        imgElement.src = imgData;
                        imgElement.style.width = "100%";
                        imgElement.style.height = "auto";
                        resolve({ original: grafico, imagem: imgElement });
                    });
                });
        }

            // Transformar os gráficos em imagens
        Promise.all([
                transformarGraficoEmImagem(grafico1)
               
        ]).then(graficosTransformados => {
            // Substituir os gráficos por imagens temporárias
            graficosTransformados.forEach(({ original, imagem }) => {
                original.parentElement.replaceChild(imagem, original);
            });

            $('#document').print({
                        iframe : false,
                        mediaPrint : false,
                        noPrintSelector : ".avoid-this",
                        //add título 
                        prepend : "",
                        append : ""
            });


            // Restaurar os gráficos originais após a impressão
            graficosTransformados.forEach(({ original, imagem }) => {
            imagem.parentElement.replaceChild(original, imagem);
        });
    });

})
})

tinymce.init({
        selector: '.seletor', // ID do elemento editável
        inline: true, // Ativa o modo inline
        plugins: "code",

        toolbar: 'undo redo | alignleft aligncenter alignright alignjustify | bold italic underline | bullist numlist | link image | code',
        menubar: false, // Remove o menu superior
    });
</script>