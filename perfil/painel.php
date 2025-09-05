<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit(); // Garante que o script não continue sendo executado
} else {
    // Inclui a configuração do banco de dados SQLite3
    $db = new SQLite3('../data/bibliotecario.db'); // Certifique-se de que este arquivo contém a conexão com o SQLite3

    $id_usuario = $_SESSION['user_id'];

    // Consulta segura usando prepared statements
    $stmt = $db->prepare("SELECT id, nivel, nome, foto, email, fone, login FROM cad_usuario WHERE id = :id");
    $stmt->bindValue(':id', $id_usuario, SQLITE3_INTEGER);
    $result = $stmt->execute();

    // Verifica se o usuário foi encontrado
    $dados = $result->fetchArray(SQLITE3_ASSOC);
    if ($dados) {
        // Extrai os dados do usuário e define valores padrão para campos vazios
        $id_usuario = $dados['id'] ?? '';
        $nivel_usuario = $dados['nivel'] ?? '';
        $nome_usuario = $dados['nome'] ?? '';
        $foto_usuario = $dados['foto'] ?? '';
        $email_usuario = $dados['email'] ?? '';
        $fone_usuario = $dados['fone'] ?? '';
        $login_usuario = $dados['login'] ?? '';

        echo $id_usuario.'mmm';

        // Define a foto do usuário
        if (empty($foto_usuario)) {
            $foto_usuario = '../img/cte.jpg';
        } else {
            $foto_usuario = '../uploads/imagens/' . $foto_usuario;
        }
    } else {
        // Caso o registro não seja encontrado
        header("Location: ../index.php");
        exit(); // Interrompe a execução do script
    }

    $stmt->close(); // Fecha o statement
    $db->close(); // Fecha a conexão com o banco de dados
}
?>


<script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- Inclua o arquivo de idioma do Parsley para português -->
<script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>

<div class="page-title">
    <div class="title_left">
        <h3>Perfil de usuário</h3><hr>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Atualizar</h2>                    
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br>
                <!-- Adicione o atributo enctype para permitir upload de arquivos -->
                <form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
                    <!-- Seção de Upload de Foto -->
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Foto de Perfil</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <!-- Quadro para as fotos -->
                            <div class="row">
                                <!-- Foto Anterior -->
                                <div class="col-md-6">
                                    <p>Foto Anterior:</p>
                                    
                                    <img id="foto-anterior" src="<?php echo $foto_usuario ?>" alt="Foto Anterior" style="width: 100%; max-width: 120px;">
                                </div>
                                <!-- Foto Atual -->
                                <div class="col-md-6">
                                    <p>Foto Atual:</p>
                                    <img id="foto-atual" src="<?php echo $foto_usuario ?>" alt="Foto Atual" style=" width: 100%; max-width: 120px;">
                                </div>
                            </div>
                            <br>
                            <!-- Input para subir arquivo -->
                            <input type="file" id="foto" name="foto" accept="image/*" class="form-control ">
                        </div>
                    </div>
                    <!-- Campo Nome -->
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nome">Nome <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="nome" value="<?php echo $nome_usuario ?>" name="nome" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <!-- Campo Email -->
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="login">Login 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" value="<?php echo $login_usuario ?>" id="login" name="login"  class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    
                    <!-- Campo Fone -->
                    <div class="form-group">
                        <label for="fone" class="control-label col-md-3 col-sm-3 col-xs-12">Fone</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="fone" value="<?php echo $fone_usuario ?>" class="form-control col-md-7 col-xs-12" type="tel" name="fone">
                        </div>
                    </div>
                    <!-- Campo Sexo -->
                    
                    
                    <!-- Botões de Ação -->
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button class="btn btn-primary cancelar" type="button">Cancelar</button>
                            
                            <button type="submit" class="btn btn-success salvar">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    $(function(){
        // Máscara para o campo de telefone
        $('#fone').mask('(00) 00000-0000');

        // Função para pré-visualizar a imagem selecionada
        $('#foto').change(function(){
            var input = this;
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#foto-atual').attr('src', e.target.result);
                    $('#foto-atual').css('display', 'block');
                }

                reader.readAsDataURL(input.files[0]);
            }
        });

        $(".cancelar").click(function(){
            $("#conteudo").load("../perfil/painel.php");
        });

        // Evento de submissão do formulário
        $('#cadastroForm').on('submit', function(e) {
            e.preventDefault(); // Evita o comportamento padrão do formulário

            // Cria um objeto FormData para enviar os dados do formulário, incluindo arquivos
            var formData = new FormData(this);

            // Envia os dados via AJAX para salva_perfil.php
            $.ajax({
                url: '../perfil/salva_perfil.php',
                type: 'POST',
                data: formData,
                contentType: false, // Não definir o tipo de conteúdo (necessário para envio de arquivos)
                processData: false, // Não processar os dados (também necessário para arquivos)
                success: function(response) {
                    var data = (response); // Converte a resposta JSON para objeto JS
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            // Recarrega a página ou o conteúdo desejado após o sucesso
                            $("#conteudo").load("../perfil/painel.php");
                        });
                    } else {
                        // Se houver um erro, mostra a mensagem de erro
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Se ocorrer um erro na chamada AJAX, exibe uma mensagem genérica
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Ocorreu um erro ao tentar salvar o perfil. Tente novamente.',
                    });
                }
            });
        });
    });
</script>
