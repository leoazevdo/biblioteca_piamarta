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
    SELECT a.id, a.capa, a.categoria, a.titulo, a.autor, a.editora, a.isbn, c.titulo AS titulo_categoria, t.descricao AS tipo, a.quantidade, a.prateleira, a.estante, a.setor, a.codigo
    FROM cad_acervo a
    LEFT JOIN cad_categoria c ON a.categoria = c.id
    LEFT JOIN cad_tipo t ON a.tipo = t.id    
    ORDER BY a.titulo   
    
";
$result = $db->query($query);

$resultPreferencias = $db->querySingle("SELECT preferencias FROM cad_configuracoes ", true);
// Transforme a string de volta em um array
$colunas_selecionadas = explode(',', $resultPreferencias['preferencias']);

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
<h3>Tabela de Acervos Cadastrados</h3>

<div class="row">
        <div class="col-lg-4"> 
           <br> <br>
            <div class="row">
                <div class="col-lg-12"> 
                                  
                    <button type="button" class="btn btn-warning btn-icon-text gerar_etiquetas">
                        
                        Gerar Etiquetas
                    </button> 
                </div>
                
            </div>
        </div>
</div>
<div class="table-responsive">
<table class="table table-bordered table-striped" id="acervosTable">
    <thead>
        <tr>
            <th>#</th>
            <th>
            <input type="checkbox" id="selectAll">
            </th>
            <th>Capa</th>
            <th>Título</th>
            <th>Categoria</th>
            <th>Setor</th>
            <th>Estante</th>
            <th>Prateleira</th>
            <?php
            // Adiciona as colunas selecionadas ao cabeçalho da tabela
            foreach ($colunas_selecionadas as $coluna) {
                echo "<th>" . htmlspecialchars($coluna) . "</th>";
            }
            ?>
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

            echo "<tr>";
            echo "<td>$id</td>";
           echo "<td style='color:black'><input type='checkbox' class='row-select' data-id='$id'></td>";
            echo "<td><img src='$caminhoFinal' width='50' ></td>";
            echo "<td>$titulo</td>";
            echo "<td data-categoria-id='$categoria_id'>$categoria</td>";
            echo "<td>$setor</td>";
            echo "<td>$estante</td>";
            echo "<td>$prateleira</td>";
           
            // Para cada coluna selecionada, exibe o valor correspondente
                foreach ($colunas_selecionadas as $coluna) {
                    if (isset($row[$coluna])) {
                        echo "<td>" . htmlspecialchars($row[$coluna]) . "</td>";
                    } else {
                        echo "<td>-</td>"; // Se a coluna não existir, exibe um marcador vazio
                    }
                }
            echo "<td class='action-buttons text-center no-print' style='white-space: nowrap;'>
                    <button type='button' class='btn btn-warning btn-rounded btn-icon edita-btn' data-id='$id'>Editar</button>
                    <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id='$id'>Excluir</button>
                    <button type='button' class='btn btn-primary btn-rounded btn-icon ver-btn' data-id='$id'><i class='fa fa-eye'></i></button>
                  </td>";
            echo "</tr>";
            $ordem++;
        }
        ?>
    </tbody>

</table>
</div>
<!-- Modal para Etiquetas -->
<div id="modalEtiquetas" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 1024px;"> <!-- Largura de A4 -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEtiquetasLabel">Etiquetas de Livros</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="etiquetas-container row">
                    <!-- As etiquetas serão geradas dinamicamente aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="imprimirEtiquetas">Imprimir</button>
            </div>
        </div>
    </div>
</div>

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
                        <p><strong>Sinopse:</strong></p>
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
  const table = $('#acervosTable').DataTable({
    language: {
      url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
    },
    pageLength: 50, // valor inicial ao carregar
    lengthMenu: [[50, 100, 1000, 2000, -1], [50, 100, 1000, 2000, "Todos"]], // adiciona 1000, 2000 e "Todos"
    dom: '<"dt-buttons-container"B>lfrtip', // mantém seus botões
    buttons: [
                {
                    extend: "print",
                    text: "Imprimir",
                    className: "btn-info"
                },
                {
                    extend: "excelHtml5", // Extensão para exportação em Excel
                    text: "Salvar Excel",
                    className: "btn-success"
                }
            ],
            columnDefs: [
                {
                    targets: 0, // Primeira coluna
                    orderable: false, // Desabilita a ordenação nesta coluna
                    searchable: false, // Desabilita a pesquisa nesta coluna
                            }
            ],
            order: [[3, 'asc']] // Define a terceira coluna como padrão para ordenação
        });


//Função de selecionar Todos 
// Mapa para armazenar seleções
let selectedRowsMap = {};

// Clique no "Selecionar Todos"
$('#selectAll').on('change', function () {
  const checked = this.checked;

  // Marca todos na página atual
  $('#acervosTable tbody input.row-select').prop('checked', checked);

  // Marca/desmarca todas as linhas (todas as páginas)
  table.rows().every(function () {
    const data = this.data();
    const id = $(data[0]).text() || $(this.node()).find('td:first').text().trim();
    selectedRowsMap[id] = checked;
  });
});

// Quando o usuário marca/desmarca individualmente
$('#acervosTable tbody').on('change', 'input.row-select', function () {
  const row = $(this).closest('tr');
  const id = row.find('td').eq(0).text().trim();
  selectedRowsMap[id] = this.checked;
});

// Ao mudar de página, mantém o estado
table.on('draw', function () {
  table.rows().every(function () {
    const row = $(this.node());
    const id = row.find('td').eq(0).text().trim();
    row.find('input.row-select').prop('checked', !!selectedRowsMap[id]);
  });
});

// Botão para gerar etiquetas
$('.gerar_etiquetas').on('click', function () {
  // Coleta IDs selecionados
  const selectedIds = Object.keys(selectedRowsMap).filter(id => selectedRowsMap[id]);

  if (selectedIds.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Nenhuma seleção',
      text: 'Por favor, selecione pelo menos um item para gerar as etiquetas.',
    });
    return;
  }

  const etiquetasContainer = $('.etiquetas-container').empty();

  selectedIds.forEach(id => {
    const rowNode = table.rows().nodes().to$().filter(function () {
      return $(this).find('td').eq(0).text().trim() === id;
    });
    const titulo = rowNode.find('td').eq(3).text().trim();
    const categoria = rowNode.find('td').eq(4).text().trim();
    const setor = rowNode.find('td').eq(5).text().trim();
    const estante = rowNode.find('td').eq(6).text().trim();
    const prateleira = rowNode.find('td').eq(7).text().trim();

    let local = [setor, estante, prateleira].filter(Boolean).join(' - ');

    etiquetasContainer.append(`
      <div style="width:30%;padding:5px;float:left;margin-left:10px;">
        <div class="etiqueta" style="border:1px solid #000;padding:10px;">
          <h3>ID: ${id}</h3>
          <p><strong>Título:</strong> ${titulo}<br>
          <strong>Categoria:</strong> ${categoria}<br>
          <strong>Local:</strong> ${local || 'Não informado'}</p>
        </div>
      </div>
    `);
  });
});



    // Função para editar um registro
    $('.edita-btn').on('click', function() {
        var id = $(this).data('id');
                // Lógica para editar o registro (você pode redirecionar para outra página ou abrir um modal)
        $('#tabela').load("cad_acervos/editar_acervo.php?id="+id)
    });

 // Função para excluir um registro
 $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                var row = $(this).closest('tr'); // captura a linha atual
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'POST',
                            url: 'cad_acervos/delete_acervo.php',
                            data: { id: id },
                            success: function(response) {
                                var res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire(
                                        'Excluído!',
                                        res.message,
                                        'success'
                                    ).then(() => {
                                        row.fadeOut(300, function() {
                                            $(this).remove(); // remove a linha atual
                                        });
                                    });
                                } else {
                                    Swal.fire(
                                        'Erro!',
                                        res.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire(
                                    'Erro!',
                                    'Erro ao excluir o acervo.',
                                    'error'
                                );
                                console.error(xhr);
                            }
                        });
                    }
                });
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
            url: 'cad_acervos/get_acervo.php', // Endpoint para buscar os dados do acervo
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



    
     // Evento para gerar etiquetas
     $('.gerar_etiquetas').on('click', function () {
        const selectedRows = $('#acervosTable tbody input[type="checkbox"]:checked').closest('tr');

        if (selectedRows.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nenhuma seleção',
                text: 'Por favor, selecione pelo menos um item para gerar as etiquetas.',
            });
            return;
        }

        const etiquetasContainer = $('.etiquetas-container');
        etiquetasContainer.empty(); // Limpa etiquetas anteriores

        const promises = [];

        selectedRows.each(function () {
            const row = $(this);
            const id = row.find('td').eq(0).text().trim(); // Obtém o ID do acervo
            const titulo = row.find('td').eq(3).text().trim(); // Obtém o título (fixo na quarta coluna)
            const categoria = row.find('td').eq(4).text().trim(); // Obtém a categoria
            const categoriaId = row.find('td').eq(4).data('categoria-id'); // Obtém o ID da categoria

            // Captura Setor, Estante e Prateleira corretamente
            const setor = row.find('td').eq(5).text().trim();
            const estante = row.find('td').eq(6).text().trim();
            const prateleira = row.find('td').eq(7).text().trim();

            // Concatena o local corretamente
            let local = "";
            if (setor) local += setor;
            if (estante) local += (local ? " - " : "") + estante;
            if (prateleira) local += (local ? " - " : "") + prateleira;

            // Cria uma promessa para buscar a cor da categoria
            const promise = $.ajax({
                url: 'cad_acervos/get_categoria_cor.php',
                method: 'POST',
                data: { categoria_id: categoriaId },
                dataType: 'json',
            }).then(function (response) {
                if (response.status === 'success') {
                    const color = response.cor || '#cccccc'; // Cor padrão, caso não haja
                    etiquetasContainer.append(`
                        <div style="width: 30%; padding: 5px; float: left; margin-left: 10px;">
                            <div class="etiqueta" style="border: 1px solid #000; padding: 10px;">
                                <table style="width: 100%">
                                    <tr>
                                        <td>
                                            <div style="background-color: ${color}; height: 10px; border: 5px solid ${color}; border-radius: 3px; margin-bottom: 10px;"></div> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap; text-align: center;">
                                            <h1><strong>ID:</strong> ${id}</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Título:</strong> ${titulo}<br>
                                            <strong>Categoria:</strong> ${categoria}<br>
                                            <strong>Local:</strong> ${local || "Não informado"}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    `);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: `Erro ao buscar a cor da categoria: ${response.message}`,
                    });
                }
            });

            promises.push(promise);
        });

        // Após todas as promessas serem resolvidas, exibe o modal
        Promise.all(promises).then(() => {
            $('#modalEtiquetas').modal('show');
        });
    });

    // Evento para imprimir etiquetas
    $('#imprimirEtiquetas').on('click', function () {
        $('.etiquetas-container').print({
            iframe: true, // Usa um iframe para impressão
            mediaPrint: false,
            noPrintSelector: ".avoid-this",
            prepend: `
                <style>
                    .etiqueta div {
                        background-color: inherit !important;
                    }
                    .etiqueta {
                        border: 1px solid #000;
                        padding: 10px;
                        border-radius: 5px;
                    }
                    .etiqueta div {
                        height: 10px;
                        border-radius: 3px;
                        margin-bottom: 10px;
                    }
                </style>
            `,
            append: ""
        });
    });
})
</script>
