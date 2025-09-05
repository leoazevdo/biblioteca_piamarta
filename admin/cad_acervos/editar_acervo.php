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
$id = $_GET['id'] ?? '';
if (empty($id)) {
    echo "ID é obrigatório.";
    exit;
}

$query = "SELECT * FROM cad_acervo WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    echo "Registro não encontrado.";
    exit;
}

// Dados do registro para editar
$titulo = $row['titulo'];
$autor = $row['autor'];
$editora = $row['editora'];
$categoria = $row['categoria'];
$tipo = $row['tipo'];
$quantidade = $row['quantidade'];
$prateleira = $row['prateleira'];
$estante = $row['estante'];
$setor = $row['setor'];
$capa = $row['capa'];
$isbn = $row['isbn'];
$sinopse = $row['sinopse'];
$codigo = $row['codigo'];
// Continue com os outros campos...

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




?>
<style>
    label {
        color: black;
    }
    @media (max-width: 767px) {  /* Smartphone */
        #conteudo img {
            width: 100% !important;
        }
        #foto img {
            width: 100% !important;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {  /* Tablet */
        #conteudo img {
            width: 75% !important;
        }
        #foto img {
            width: 75% !important;
        }
    }
    @media (min-width: 1024px) {  /* Computador */
        #conteudo img {
            width: 50% !important;
        }
        #foto img {
            width: 50% !important;
        }
    }
</style>

<br>
<script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- Inclua o arquivo de idioma do Parsley para português -->
<script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>
<div id="categorias">
<h3>Editar Cadastro</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-4">    
            <div class="text-center">
                <div class="editor2 mb-2">
                    Capa do Acervo
                    <div id="foto">
                        <img src="<?php echo $caminhoFinal ?>" alt="Book Cover">
                    </div>  
                </div>
                <br>
                <button type="button" id="btnInsertImage2" class="btn btn-primary btn-lg  ">
                    <i class="fa fa-camera"></i> <!-- Ícone de câmera -->
                </button>

                <input type="file" name="capa"  id="imageInput" style="display: none;">
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-12">    
                    <div class="row">
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="ISBN">ISBN</label>
                                <input type="text" style="border: 1px solid #C0C0C0;" name="isbn" class="form-control" value="<?php echo $isbn ?>" id="isbn" placeholder="ISBN">
                            </div>
                        </div>
                        
                    </div>     
                </div>
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Título</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" required class="form-control" id="titulo" value="<?php echo $titulo ?>" name="titulo" placeholder="Título">
                    </div>
                </div>
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Autor</label>
                        <input type="text" value="<?php echo $autor ?>" style="border: 1px solid #C0C0C0;" class="form-control" name="autor" id="autor" placeholder="Autor">
                    </div>
                </div>
                <div class="col-lg-12">    
                    <div class="row">
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="Editora">Editora</label>
                                <input type="text" value="<?php echo $editora ?>" name="editora" style="border: 1px solid #C0C0C0;" class="form-control" id="editora" placeholder="Editora">
                            </div>
                        </div>
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="setor">Setor</label>
                                <input type="text" value="<?php echo $setor ?>" style="border: 1px solid #C0C0C0;" class="form-control" name="setor" id="setor" placeholder="Setor">
                            </div>
                        </div>
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="Nome">Área de Conhecimento</label>
                                <select class="form-control" required id="categoria" name="categoria" style="border: 1px solid #C0C0C0;">
                                    <option value="">Selecione...</option>
                                    <?php
                                        // Consulta todos os registros da tabela cad_categoria
                                        $result = $db->query("SELECT * FROM cad_categoria ORDER BY titulo");
                                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                            $selected = $row['id'] == $categoria ? 'selected' : ''; // Verifica se o valor deve ser selecionado
                                            ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($row['titulo']); ?>
                                            </option>
                                            <?php
                                        }
                                    ?>
                                </select>                        
                            </div>
                        </div>
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="Nome">Formato</label>
                                <select class="form-control" required id="tipo" name="tipo" style="border: 1px solid #C0C0C0;">
                                    <option value="">Selecione...</option>
                                    <?php
                                        // Consulta todos os registros da tabela cad_tipo
                                        $result = $db->query("SELECT * FROM cad_tipo ORDER BY descricao");
                                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                            $selected = $row['id'] == $tipo ? 'selected' : ''; // Verifica se o valor deve ser selecionado
                                            ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($row['descricao']); ?>
                                            </option>
                                            <?php
                                        }
                                    ?>
                                </select>                        
                            </div>
                        </div>

                        <div class="col-lg-2">    
                            <div class="form-group">
                                <label for="Nome">Quantidade</label>
                                <input type="number" value="<?php echo $quantidade ?>" required name="quantidade" style="border: 1px solid #C0C0C0;" class="form-control" id="quantidade" placeholder="0" value="1" min="1">
                       
                            </div>
                        </div>
                        <div class="col-lg-4">    
                            <div class="form-group">
                                <label for="Nome">Estante</label>
                                <input type="text" value="<?php echo $estante ?>" name="estante" style="border: 1px solid #C0C0C0;" class="form-control" id="estante" placeholder="">                      
                            </div>
                        </div>
                        <div class="col-lg-4">    
                            <div class="form-group">
                                <label for="Nome">Prateleira</label>
                                <input type="text" value="<?php echo $prateleira ?>" name="prateleira" style="border: 1px solid #C0C0C0;" class="form-control" id="prateleira" placeholder="">                        
                            </div>
                        </div>
                        <div class="col-lg-12">    
                            <div class="form-group">
                                <label for="Nome">Sinopse</label>                                
                                <textarea class="form-control texto" name="sinopse" style="border: 1px solid #C0C0C0;" id="sinopse" rows="6"><?php echo $sinopse ?></textarea>                       
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
                                <input type="hidden" id="codigo" name="codigo" value="<?php echo $codigo ?>">
                                <button type="submit" class="btn btn-success me-2">Salvar</button>
                                <button type="button" class="btn btn-light me-2" id="cancelButton">Cancelar</button>
                                <button type="button" class="btn btn-light me-2" id="retornarButton">Retornar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>

</form>

<script>
$(document).ready(function () {
    // Botões de cancelar e retornar
    $('#cancelButton').on('click', function () {
        $("#tabela").load("cad_acervos/tabela.php");
    });
    $('#retornarButton').on('click', function () {
        $("#tabela").load("cad_acervos/tabela.php");
    });

    // Envio do formulário
    $('#cadastroForm').on('submit', function (e) {
        e.preventDefault(); // Evita o envio padrão do formulário

        const formData = new FormData($('#cadastroForm')[0]); // Captura todos os dados do formulário

        // Exibe o alerta de progresso
        Swal.fire({
            title: 'Salvando dados...',
            text: 'Por favor, aguarde enquanto processamos as informações.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'cad_acervos/salvar_editar_acervo.php', // URL do arquivo PHP
            method: 'POST',
            data: formData,
            contentType: false, // Necessário para o envio correto do arquivo
            processData: false, // Não processa os dados como string
            success: function (response) {
               
                Swal.close(); // Fecha o alerta de progresso

                try {
                    const jsonResponse = JSON.parse(response);

                    if (jsonResponse.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: jsonResponse.message
                        }).then(() => {
                            $("#tabela").load("cad_acervos/tabela.php"); // Recarrega a tabela após o sucesso
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: jsonResponse.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro no servidor',
                        text: 'A resposta do servidor não é válida.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao salvar os dados. Tente novamente.'
                });
            }
        });
    });

    // Evento de clique no botão para abrir o seletor de arquivos
    $('#btnInsertImage2').on('click', function () {
        $('#imageInput').click(); // Simula um clique no input de arquivo
    });

    // Evento para capturar e exibir a imagem selecionada
    $('#imageInput').on('change', function (event) {
        const file = event.target.files[0]; // Obtém o arquivo selecionado

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $('#foto img').attr('src', e.target.result); // Atualiza o src da imagem
            };

            reader.readAsDataURL(file); // Lê o arquivo como URL de dados
        }
    });
});

</script>

