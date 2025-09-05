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
$id = intval($_GET['id']); // Certifique-se de que $id é um número inteiro

$result = $db->query("SELECT * FROM cad_categoria WHERE id='$id'");
$row = $result->fetchArray(SQLITE3_ASSOC);

$titulo = htmlspecialchars($row['titulo'] ?? '');
$cor = htmlspecialchars($row['cor'] ?? '');

?>

<h3>Inserir Nova Categoria</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-8">	
            <div class="form-group">
                <label for="Nome">Nome da Categoria </label>
                <input type="text" value='<?php echo $titulo ?>' style="border: 1px solid #C0C0C0;" required class="form-control" name="titulo" id="titulo" placeholder="Descrição">

            </div>
        </div>
        <div class="col-lg-1">
            <div class="form-group">
                <label for="cor">Cor</label>
                <input type="color" value='<?php echo $cor ?>' required style="border: 1px solid #C0C0C0;" class="form-control" id="cor">
            </div>
        </div>

        <div class="col-lg-3">
        <label for="cor">Ação</label>
            <div class="form-group">
                <input type="hidden" id="id" value="<?php echo $id ?>">

                <button type="submit" class="btn btn-success me-2">Salvar</button>
                <button type="button" class="btn btn-light" id="cancelButton">Cancelar</button>
            </div>
        </div>


    </div>


</form>

<script>
    $(document).ready(function() {
        $('#cadastroForm').on('submit', function(e) {
            e.preventDefault();

            var id = $('#id').val();
            var titulo = $('#titulo').val();
            var cor = $('#cor').val();

            $.ajax({
                type: 'POST',
                url: 'cad_acervos/salvar_editar_categoria.php',
                data: { id: id, titulo: titulo, cor: cor },
                success: function(response) {
                    var res = JSON.parse(response);
                    Swal.fire({
                        title: res.status === 'success' ? 'Sucesso!' : 'Erro!',
                        text: res.message,
                        icon: res.status,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed && res.status === 'success') {
                            $("#tabela").load("cad_acervos/nova_categoria.php")
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao salvar cadastro.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    console.error(xhr);
                }
            });
        });

        $('#cancelButton').on('click', function() {
            $("#tabela").load("cad_acervos/nova_categoria.php")
        });
    });
</script>
