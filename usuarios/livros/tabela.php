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

// Consulta com Limite, Offset e Filtros
$query = "
    SELECT a.id, a.capa, a.categoria, a.titulo, a.autor, a.editora, c.titulo AS titulo_categoria, t.descricao AS tipo, a.quantidade, a.prateleira, a.estante, a.setor, a.codigo, e.devolvido AS devolvido
    FROM cad_acervo a
    LEFT JOIN cad_categoria c ON a.categoria = c.id
    LEFT JOIN emprestimos e ON a.id = e.acervo_id
    LEFT JOIN cad_tipo t ON a.tipo = t.id        
    ORDER BY a.titulo   
    
";
$result = $db->query($query);


?>
<style>
    .dt-buttons {
        display: flex;
        justify-content: center; /* Centraliza os botões horizontalmente */
        gap: 10px; /* Adiciona espaçamento entre os botões */
        margin-bottom: 10px; /* Espaçamento abaixo dos botões */
    }
</style>
<br>



<table class="table table-bordered table-striped" id="acervosTable">
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Capa</th>
            <th class="sticky-col">Titulo</th>
            <th>Área de Conhecimento</th>
            <th>Situação</th>
            <th>Local (S/E/P)</th>
            <th class='text-center no-print'>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $ordem = 1;
        // Verifica se há registros e os exibe
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $id = htmlspecialchars($row['id']);
            $capa = htmlspecialchars($row['capa']);
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



            $titulo = htmlspecialchars($row['titulo']);
            $autor = htmlspecialchars($row['autor']);
            $prateleira = htmlspecialchars($row['prateleira']);
            $estante = htmlspecialchars($row['estante']);
            $editora = htmlspecialchars($row['editora']);
            $categoria = htmlspecialchars($row['titulo_categoria']);
            $categoria_id = htmlspecialchars($row['categoria']);
            $tipo = htmlspecialchars($row['tipo']);
            $setor = htmlspecialchars($row['setor']);
            $quantidade = htmlspecialchars($row['quantidade']);
            $codigo = htmlspecialchars($row['codigo']);
            $situacao = htmlspecialchars($row['devolvido']);

            $cor = '';
            if($situacao == 'Sim'){
                $situacao = 'Disponível';
                $cor = 'success';
            }else{
                $situacao = 'Indisponível';
                $cor = 'danger';
            }

            

            echo "<tr>";
            echo "<td>$ordem</td>";
            echo "<td >$id</td>";
            echo "<td><img src='$caminhoFinal' width='50' ></td>";
            echo "<td>$titulo</td>";
            echo "<td data-categoria-id='$categoria_id'>$categoria</td>";
            echo "<td><button class='btn btn-$cor btn-xs'>$situacao</btn></td>";
            echo "<td>Setor: $setor<br> Estante: $estante<br> Prateleira: $prateleira</td>";
            echo "<td class='action-buttons text-center no-print' style='white-space: nowrap;'>
                    
                    <button type='button' class='btn btn-primary btn-rounded btn-icon ver-btn' data-id='$id'><i class='fa fa-eye'></i></button>
                  </td>";
            echo "</tr>";
            $ordem++;
        }
        ?>
    </tbody>

</table>



<!-- Modal para Visualizar Acervo -->
<div id="modalVisualizarAcervo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalVisualizarAcervoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarAcervoLabel">Detalhes do Acervo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="acervoCapa" src="" alt="Capa do Livro" class="img-fluid" style="max-height: 300px; margin-bottom: 15px;">
                    </div>
                    <div class="col-md-8">
                        <p><strong>Título:</strong> <span id="acervoTitulo"></span></p>
                        <p><strong>Autor:</strong> <span id="acervoAutor"></span></p>
                        <p><strong>Editora:</strong> <span id="acervoEditora"></span></p>
                        <p><strong>ISBN:</strong> <span id="acervoISBN"></span></p>
                        <p><strong>Categoria:</strong> <span id="acervoCategoria"></span></p>
                        <p><strong>Tipo:</strong> <span id="acervoTipo"></span></p>
                        <p><strong>Resumo:</strong></p>
                        <p id="acervoSinopse" style="text-align: justify;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>


<script>
     $(document).ready(function() {
        $('#acervosTable').DataTable({
            language: {
                url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
            },
            pageLength: 10, // Define o número de registros exibidos na primeira página
            dom: 'lfrtip', // Ativa o uso dos botões
           
            columnDefs: [
            {
                targets: 0, // Primeira coluna
                orderable: false, // Desabilita a ordenação nesta coluna
                searchable: false, // Desabilita a pesquisa nesta coluna
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Calcula o índice da linha (começa em 0) e soma 1
                }
            }
        ],
        order: [[3, 'asc']] // Define a terceira coluna como padrão para ordenação
        });

   

    // Evento de clique no botão ver-btn
    $('.ver-btn').on('click', function () {
        const id = $(this).data('id'); // Obtém o ID do acervo
        Swal.fire({
            title: 'Carregando detalhes...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        // Chamada AJAX para buscar os detalhes do acervo
        $.ajax({
            url: '../admin/cad_acervos/get_acervo.php', // Endpoint para buscar os dados do acervo
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                Swal.close(); // Fecha o loading
                if (response.status === 'success') {
                    const data = response.data;

                    let capaFinal;

                        // Verifica se o valor de data.capa é uma imagem base64
                        if (/^data:image\/(jpeg|png|gif|bmp|webp);base64,/.test(data.capa)) {
                            capaFinal = data.capa; // Mantém o valor atual de data.capa
                        } else if (data.capa === '../master/images/book.png' || !data.capa) {
                            capaFinal = '../img/book.png'; // Define um caminho padrão para a imagem
                        } else {
                            // Altera para o caminho padrão no diretório de uploads
                            capaFinal = '../uploads/imagens/' + data.capa;
                        }

                    // Preenche os dados no modal
                    $('#acervoCapa').attr('src', capaFinal);
                    $('#acervoTitulo').text(data.titulo || 'Não informado');
                    $('#acervoAutor').text(data.autor || 'Não informado');
                    $('#acervoEditora').text(data.editora || 'Não informado');
                    $('#acervoISBN').text(data.isbn || 'Não informado');
                    $('#acervoCategoria').text(data.categoria || 'Não informado');
                    $('#acervoTipo').text(data.tipo || 'Não informado');
                    $('#acervoSinopse').text(data.sinopse || 'Sinopse não disponível.');

                    // Exibe o modal
                    $('#modalVisualizarAcervo').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao buscar os dados do acervo.',
                    });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível carregar os dados do acervo.',
                });
            },
        });
    });

   
})
</script>
