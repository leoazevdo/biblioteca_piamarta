<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
} else {
    // Cria a conexão com o banco de dados SQLite3
    $db = new SQLite3('../../data/bibliotecario.db'); // Certifique-se de que o arquivo do banco de dados existe e está no caminho correto

    $id_usuario = $_SESSION['user_id'];

}
// Consulta para obter os empréstimos com informações do usuário e do acervo

$query = "
    SELECT e.*, e.id as id_emprestimo, u.id as user_id, u.nome as nome_usuario, a.id as acervo_id, a.titulo as titulo_acervo 
    FROM emprestimos e
    LEFT JOIN cad_usuario u ON e.user_id = u.id
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    WHERE e.user_id = '$id_usuario'
    ORDER BY e.data_devolucao ASC
";

$result = $db->query($query);


// Função para converter data do formato DD/MM/YYYY para YYYY-MM-DD para comparação
function formatDateToCompare($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d ? $d->format('Y-m-d') : $date;
}

// Função para converter data do formato YYYY-MM-DD para DD/MM/YYYY para exibição
function formatDateToDisplay($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}

// Data atual para comparação
$currentDate = date('Y-m-d');
$ano = date('Y');
?>
<style>
    .dt-buttons {
        display: flex;
        justify-content: center; /* Centraliza os botões horizontalmente */
        gap: 10px; /* Adiciona espaçamento entre os botões */
        margin-bottom: 10px; /* Espaçamento abaixo dos botões */
    }
    
    
    /* Remover estilo de fundo padrão do DataTable */
table.dataTable tbody tr {
    background-color: initial !important; /* Usa o estilo padrão ou inicial */
}

/* Garante que estilos personalizados prevaleçam */
table.dataTable tbody tr.bg-amarelo {
    background-color: #fff9c4 !important;
    color: #000 !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}

table.dataTable tbody tr.bg-vermelho {
    background-color: #ffcdd2 !important;
    color: #b71c1c !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}

table.dataTable tbody tr.bg-verde {
    background-color: #c8e6c9 !important;
    color: #1b5e20 !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}



</style>
<br>
<h3>Tabela Geral de Empréstimos</h3>
<table class="table table-bordered table-striped" class="display" id="emprestimos">
    <thead>
        <tr>
            <th>#</th>
            <th>Usuário</th> <!-- Simplificado para "Usuário" -->
            
            <th>Título do Item</th> <!-- Substitui "Acervo" por "Item" para maior clareza -->
            <th>ID Acervo</th> <!-- Mais específico -->
            <th>Data de Empréstimo</th> <!-- Linguagem mais formal -->
            <th>Data de Devolução</th> <!-- Consistente com o anterior -->
            <th>Devolvido?</th> <!-- Indica que é uma pergunta (sim/não) -->
            <th>Situação</th>
            
        </tr>
    </thead>

    <tbody>
        <?php
        $ordem = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // Convertendo a data de devolução e data atual para formato de comparação
            $dataDevolucao = formatDateToCompare($row['data_devolucao']);
            $currentDate = date('Y-m-d');

            $situacao = "";
            
           
            
            // Inicializa a variável de classe CSS da linha
            $rowClass = '';

            // Definir classes de linha baseadas no status da devolução
            if (strtolower($row['devolvido']) === 'sim') {
                $rowClass = 'bg-verde'; // Acervo devolvido  
                $situacao = "Em dia";              
            } elseif ($dataDevolucao < $currentDate) {
                $rowClass = 'bg-vermelho'; // Data de devolução é anterior à data atual
                $situacao = "Pendente";
            } elseif ($dataDevolucao == date('Y-m-d', strtotime('+1 day', strtotime($currentDate))) || $dataDevolucao == $currentDate ) {
                $rowClass = 'bg-amarelo'; // Data de devolução é um dia após a data atual
                $situacao = "Em dia";
            }else{
                $situacao = "Em dia";
            }

            // Exibe as datas no formato DD/MM/YYYY
            $dataAtual = formatDateToDisplay($row['data_atual']);
            $dataDevolucao = formatDateToDisplay($row['data_devolucao']);
            $dataEntregue = !empty($row['data_entregue']) ? formatDateToDisplay($row['data_entregue']) : '';

            //style='text-decoration:none; color: black;'
            echo "<tr class='{$rowClass}'>";
            echo "<td>" . htmlspecialchars($ordem) . "</td>";
            echo "<td><b><a href='#'  class='user-info' data-id='" . htmlspecialchars($row['user_id']) . "'>" . htmlspecialchars($row['nome_usuario']) . "</a></b></td>";

            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['titulo_acervo']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['id']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($dataAtual) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($dataDevolucao) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['devolvido']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($situacao) . "</span></td>";
           
            echo "</tr>";
            $ordem++;
        }
        ?>
       

    </tbody>


</table>


<!-- Modal para edição do empréstimo -->
<div class="modal fade" id="modalEditarEmprestimo" tabindex="-1" role="dialog" aria-labelledby="modalEditarEmprestimoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarEmprestimoLabel">Editar Empréstimo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditarEmprestimo">
          <input type="hidden" id="emprestimoId" name="emprestimoId">
          <div class="row">
            <div class="col-md-4 text-center">
              <img id="acervoCapa" src="../img/book.png" alt="Capa do Acervo" style="max-width: 100%; height: auto; border: 1px solid #ddd;">
            </div>
            <div class="col-md-8">
              <h5>Usuário</h5>
              <p id="usuarioNome"></p>
              <p id="usuarioFone"></p>
              <h5>Título do Acervo</h5>
              <p id="acervoTitulo"></p>
              <div class="form-group">
                <label for="dataAtual">Data de Empréstimo</label>
                <input type="date" class="form-control" id="dataAtual" name="dataAtual">
              </div>
              <div class="form-group">
                <label for="dataDevolucao">Data de Devolução</label>
                <input type="date" class="form-control" id="dataDevolucao" name="dataDevolucao">
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary" id="salvarEdicaoEmprestimo">Salvar</button>
      </div>
    </div>
  </div>
</div>



<script>
$(function(){

    // Define um tipo de dados personalizado para datas no formato DD/MM/YYYY
    $.fn.dataTable.ext.type.order['date-uk-pre'] = function (date) {
        if (!date || date === '') {
            return 0; // Retorna 0 para células vazias
        }
        const parts = date.split('/'); // Divide a data no formato DD/MM/YYYY
        return new Date(parts[2], parts[1] - 1, parts[0]).getTime(); // Retorna timestamp para ordenação
    };

        $('#emprestimos').DataTable({
            columnDefs: [
                {
                    targets: [4, 5], // Índice da coluna com as datas (ajuste conforme necessário)
                    type: 'date-uk', // Usa o tipo de ordenação personalizado
                },
                {
                    targets: 0, // Primeira coluna
                    orderable: false, // Desabilita a ordenação nesta coluna
                    searchable: false, // Desabilita a pesquisa nesta coluna
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Calcula o índice da linha (começa em 0) e soma 1
                    },
                },
            ],
            stripeClasses: [], // Remove as classes que alternam as cores das linhas
            language: {
                url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
            },
            pageLength: 10, // Define o número de registros exibidos na primeira página
            dom: 'lfrtip', // Ativa o uso dos botões
           
           
        order: [[5, 'asc']], // Define a sexta coluna como padrão para ordenação
        
        });

    
    });





</script>