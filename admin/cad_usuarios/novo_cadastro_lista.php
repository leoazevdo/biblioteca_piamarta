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
<br>
<h3>Novo Cadastro de Usuário</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">

<div class="row">
            <div class="col-lg-12">    
                <div class="form-group">
                    <label for="Nome">Colar Aqui a Lista de Usuários </label>
                    <textarea name="lista" id="lista" class="form-control" rows="10"></textarea>
                </div>
            </div>
            
           
            
            <div class="col-lg-6">    
                <div class="form-group">
                    <label for="Turma">Turma/Setor</label>
                    <select class="form-control" id="turma" name="turma" style="border: 1px solid #C0C0C0;">
                        <option value="">Selecione...</option>
                        <?php
                        // Conexão com o banco de dados
                        $mysqli = new SQLite3('../../data/bibliotecario.db');

                        // Consulta todos os registros da tabela cad_turma
                        $result = $mysqli->query("SELECT * FROM cad_turma");
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            $id_turma = htmlspecialchars($row['id']);
                            $nome = htmlspecialchars($row['nome']);
                            echo "<option value='$id_turma'>$nome</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
        </div>

        <div class="">
            <div class="form-group">
                <br><br>
                <button type="submit" class="btn btn-primary me-2">Submit</button>
                <button type="button" class="btn btn-light" id="cancelButton">Cancelar</button>
            </div>
        </div>


    </div>


</form>
<script>
    $(document).ready(function() {
         // Máscara para o campo de telefone
         $('#fone').mask('(00) 00000-0000');

            // Função para validar email
            function isValidEmail(email) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

        // Evento de submissão do formulário
        $('#cadastroForm').on('submit', function(e) {
            e.preventDefault(); // Evita o comportamento padrão de envio do formulário

            // Cria um objeto FormData para enviar os dados do formulário
            var formData = new FormData(this);

            // Envia os dados via AJAX para salva_turma.php
            $.ajax({
                url: 'cad_usuarios/salva_usuario_lista.php',
                type: 'POST',
                data: formData,
                contentType: false, // Não definir o tipo de conteúdo (necessário para envio de arquivos)
                processData: false, // Não processar os dados (também necessário para arquivos)
                dataType: 'json', // Especifica que a resposta será JSON
                success: function(response) {
                    // Verifica o status e exibe a mensagem
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            // Recarrega o conteúdo após o sucesso
                            $("#conteudo").load("cad_usuarios/painel.php");
                        });
                    } else {
                        // Exibe mensagem de erro personalizada retornada pelo servidor
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Exibe mensagem de erro genérica se houver falha na requisição AJAX
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao salvar os dados!',
                        text: 'Ocorreu um erro ao tentar salvar os dados. Tente novamente.',
                    });
                }
            });
        });

        // Limpa o formulário e recarrega a tabela ao cancelar
        $('#cancelButton').on('click', function() {
            $('#cadastroForm')[0].reset();
            $('#tabela').load("cad_usuarios/tabela.php");
        });
    });
</script>
