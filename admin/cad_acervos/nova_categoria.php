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
?>

<br>
<script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- Inclua o arquivo de idioma do Parsley para português -->
<script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>
<div id="categorias">
<h3>Inserir Nova Categoria</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-8">	
            <div class="form-group">
                <label for="Nome">Nome da Categoria </label>
                <input type="text" style="border: 1px solid #C0C0C0;" required class="form-control" name="titulo" id="titulo" placeholder="Descrição">

            </div>
        </div>
        <div class="col-lg-1">
            <div class="form-group">
                <label for="cor">Cor</label>
                <input type="color" required style="border: 1px solid #C0C0C0;" class="form-control" id="cor">
            </div>
        </div>

        <div class="col-lg-3">
        <label for="cor">Ação</label>
            <div class="form-group">
                 
                <button type="submit" class="btn btn-primary me-2">Inserir</button>
                <button type="button" class="btn btn-light" id="cancelButton">Cancelar</button>
            </div>
        </div>


    </div>


</form>
</div>
<br>
<div class="container tabela-areas">
        <h2 class="my-4">Lista de Áreas Cadastradas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cor</th>
                    <th>Título</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta todos os registros da tabela cad_turma
                $result = $db->query("SELECT * FROM cad_categoria ORDER BY titulo");
                $ordem = 1;
                // Verifica se há registros e os exibe
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $id = $row['id'] ?? '';
                    $titulo = $row['titulo'] ?? '';
                    $cor = $row['cor'] ?? '';

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ordem) . "</td>";
                    echo "<td><div style='width:30px; height: 30px; background:" . htmlspecialchars($cor) . "'></div></td>";
                    echo "<td>" . htmlspecialchars($titulo) . "</td>";
                    echo "<td class='text-center'>
                            <button type='button' class='btn btn-warning btn-rounded btn-icon edit-btn' data-id='" . htmlspecialchars($id) . "'>Editar</button>
                            <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id='" . htmlspecialchars($id) . "'>Excluir</button>
                        </td>";
                    echo "</tr>";
                    $ordem++;
                }
                ?>
            </tbody>
        </table>
</div>


<script>
    $(document).ready(function() {
       

        $('#cadastroForm').on('submit', function(e) {
            e.preventDefault();

            var titulo = $('#titulo').val();
            var cor = $('#cor').val();
            if (titulo == '') {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Preencha o campo com o Título da Área de Conhecimento.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                $.ajax({
                    type: 'POST',
                    url: 'cad_acervos/salva_categoria.php',
                    data: { 
                        titulo: titulo,
                        cor: cor
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Área de Conhecimento salva com sucesso!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                               // $('#cadastroForm')[0].reset();
                               $("#tabela").load("cad_acervos/nova_categoria.php")
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Erro ao salvar Área de Conhecimento.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        console.error(xhr);
                    }
                });
            }
        });

        $('#cancelButton').on('click', function() {
            $("#tabela").load("cad_acervos/nova_categoria.php")
        });

        // Função para editar um registro
        $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                // Lógica para editar o registro (você pode redirecionar para outra página ou abrir um modal)
                $('#categorias').load("cad_acervos/editar_categoria.php?id="+id)

                $('.tabela-areas').hide()
            });

            // Função para excluir um registro
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
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
                            url: 'cad_acervos/delete_categoria.php',
                            data: { id: id },
                            success: function(response) {
                                var res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire(
                                        'Excluído!',
                                        res.message,
                                        'success'
                                    ).then(() => {
                                        //location.reload();
                                        $("#tabela").load("cad_acervos/nova_categoria.php")
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
                                    'Erro ao excluir cadastro.',
                                    'error'
                                );
                                console.error(xhr);
                            }
                        });
                    }
                });
            });

    });
</script>


