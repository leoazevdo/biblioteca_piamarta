<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php");
    exit();
} else {
    // Cria a conexão com o banco de dados SQLite3
    $db = new SQLite3('../../../data/bibliotecario.db'); // Certifique-se de que o arquivo do banco de dados existe e está no caminho correto

    $id_usuario = $_SESSION['user_id'];

}

$id = intval($_GET['id']); // Certifique-se de que $id é um número inteiro

    // Consulta todos os registros da tabela cad_turma
    $result = $db->query("SELECT * FROM cad_tipo WHERE id='$id'");
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $descricao = htmlspecialchars($row['descricao']);


?>
<br>

<h3>Editar Formato de Acervo</h3><hr>
<form id="cadastroForm2" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-8">	
            <div class="form-group">
                <label for="Nome">Nome do Formato do Aervo </label>
                <input type="text" value='<?php echo $descricao ?>' style="border: 1px solid #C0C0C0;" required class="form-control" name="descricao" id="descricao" placeholder="Descrição">

            </div>
        </div>
        

        <div class="col-lg-4">
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
    $(function(){
        $('#cadastroForm2').on('submit', function(e) {
            e.preventDefault();

            var descricao = $('#descricao').val();
            var id = $('#id').val();
           
                $.ajax({
                    type: 'POST',
                    url: 'cad_acervos/tipo/salva_editar-tipo.php',
                    data: { 
			id: id,
                        descricao: descricao
                       
                    },
                    success: function(response) {
                        
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Formato do salvo com sucesso!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                               // $('#cadastroForm2')[0].reset();
                               $("#tabela").load("cad_acervos/tipo/novo_formato.php")
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
            
        });

        $('#cancelButton').on('click', function() {
            $("#tabela").load("cad_acervos/tipo/novo_formato.php")
        });
    })
</script>


