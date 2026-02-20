<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexão apenas para carregar as preferências de colunas
$db = new SQLite3('../../data/bibliotecario.db');
$resultPreferencias = $db->querySingle("SELECT preferencias FROM cad_configuracoes ", true);
$colunas_selecionadas = explode(',', $resultPreferencias['preferencias']);
?>

<style>
    .dt-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 10px;
    }
</style>

<br>
<h3>Tabela de Acervos Cadastrados</h3>

<div class="row">
    <div class="col-lg-4"> 
        <br> <br>
        <button type="button" class="btn btn-warning btn-icon-text gerar_etiquetas">
            Gerar Etiquetas
        </button> 
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="acervosTable" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Capa</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Setor</th>
                <th>Estante</th>
                <th>Prateleira</th>
                <th class='text-center no-print'>Ações</th>
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>
</div>

<div id="modalVisualizarAcervo" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Acervo</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="acervoCapa" src="" class="img-fluid" style="max-height: 300px;">
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
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Inicialização da Tabela (Modo Rápido / Server-side)
    const table = $('#acervosTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "cad_acervos/busca_acervo.php",
            "type": "POST",
            "error": function(xhr) {
                console.error("Erro na carga dos dados:", xhr.responseText);
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "checkbox", "orderable": false },
            { "data": "capa", "orderable": false },
            { "data": "titulo" },
            { "data": "categoria" },
            { "data": "setor" },
            { "data": "estante" },
            { "data": "prateleira" },
            { "data": "acoes", "orderable": false }
        ],
        "language": {
            "url": "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
        },
        "pageLength": 5,
        "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        "dom": '<"dt-buttons-container"B>lfrtip',
        "buttons": [
            { extend: "print", text: "Imprimir", className: "btn-info" },
            { extend: "excelHtml5", text: "Salvar Excel", className: "btn-success" }
        ],
        "order": [[3, 'asc']]
    });

    // --- DELEGAÇÃO DE EVENTOS (Necessário para AJAX) ---

    // Botão Editar
    $(document).on('click', '.edita-btn', function() {
        var id = $(this).data('id');
        $('#tabela').load("cad_acervos/editar_acervo.php?id=" + id);
    });

    // Botão Excluir
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você não poderá reverter isso!",
            icon: 'warning',
            showCancelButton: true,
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
                            Swal.fire('Excluído!', res.message, 'success');
                            table.draw(false); // Recarrega a página atual da tabela
                        } else {
                            Swal.fire('Erro!', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // Botão Ver (Visualizar)
    $(document).on('click', '.ver-btn', function() {
        const id = $(this).data('id');
        Swal.fire({ title: 'Carregando...', didOpen: () => { Swal.showLoading(); }});
        
        $.ajax({
            url: 'cad_acervos/get_acervo.php',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    const data = response.data;
                    $('#acervoCapa').attr('src', data.capa || '../img/book.png');
                    $('#acervoTitulo').text(data.titulo);
                    $('#acervoAutor').text(data.autor);
                    $('#acervoEditora').text(data.editora);
                    $('#acervoISBN').text(data.isbn);
                    $('#acervoCategoria').text(data.categoria);
                    $('#acervoTipo').text(data.tipo);
                    $('#acervoSinopse').text(data.sinopse);
                    $('#modalVisualizarAcervo').modal('show');
                }
            }
        });
    });

    // Selecionar Todos
    $(document).on('change', '#selectAll', function() {
        $('.row-select').prop('checked', this.checked);
    });

});
</script>