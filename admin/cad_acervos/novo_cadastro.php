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
<h3>Inserir Novo Cadastro</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-4">    
            <div class="text-center">
                <div class="editor2 mb-2">
                    Capa do Acervo
                    <div id="foto">
                        <img src="../img/book.png" alt="Book Cover">
                    </div>  
                </div>
                <br>
                <button type="button" id="btnInsertImage2" class="btn btn-primary btn-lg  ">
                    <i class="fa fa-camera"></i> <!-- Ícone de câmera -->
                </button>

                <input type="file"  id="imageInput" style="display: none;">
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-12">    
                    <div class="row">
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="ISBN">ISBN</label>
                                <input type="text" style="border: 1px solid #C0C0C0;" name="isbn" class="form-control" id="isbn" placeholder="ISBN">
                            </div>
                        </div>
                        <div class="col-lg-6">    
                        <label for="pesquisar em">Pesquisar em:</label>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary mt-4 pesq-google">GoogleBooks</button>
                                <button type="button" class="btn btn-primary mt-4 pesq-open">OpenLibrary</button>
                               
                            </div>
                        </div>
                        <div class="col-lg-12">    
                            <div class="form-group resposta">
                                Resultado da pesquisa: 
                            </div>
                        </div>
                    </div>     
                </div>
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Título</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" required class="form-control" id="titulo" name="titulo" placeholder="Título">
                    </div>
                </div>
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Autor</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" name="autor" id="autor" placeholder="Autor">
                    </div>
                </div>
                <div class="col-lg-12">    
                    <div class="row">
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="Editora">Editora</label>
                                <input type="text" style="border: 1px solid #C0C0C0;" name="editora" class="form-control" id="editora" placeholder="Editora">
                            </div>
                        </div>
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="setor">Setor</label>
                                <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" name="setor" id="setor" placeholder="Setor">
                            </div>
                        </div>
                        <div class="col-lg-6">    
                            <div class="form-group">
                                <label for="Nome">Área de Conhecimento</label>
                                <select class="form-control"  required id="categoria" name="categoria" style="border: 1px solid #C0C0C0;">
                                    <option value="">Selecione...</option>
                                    <?php
                                        // Consulta todos os registros da tabela cad_categoria
                                        $result = $db->query("SELECT * FROM cad_categoria ORDER BY titulo");
                                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                            ?>
                                            <option value="<?php echo htmlspecialchars($row['id']) ?>"><?php echo htmlspecialchars($row['titulo']) ?></option>
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
                                            ?>
                                            <option value="<?php echo htmlspecialchars($row['id']) ?>"><?php echo htmlspecialchars($row['descricao']) ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>                        
                            </div>
                        </div>
                        <div class="col-lg-2">    
                            <div class="form-group">
                                <label for="Nome">Quantidade</label>
                                <input type="number" required name="quantidade" style="border: 1px solid #C0C0C0;" class="form-control" id="quantidade" placeholder="0" value="1" min="1">
                       
                            </div>
                        </div>
                        <div class="col-lg-4">    
                            <div class="form-group">
                                <label for="Nome">Estante</label>
                                <input type="text" name="estante" style="border: 1px solid #C0C0C0;" class="form-control" id="estante" placeholder="">                      
                            </div>
                        </div>
                        <div class="col-lg-4">    
                            <div class="form-group">
                                <label for="Nome">Prateleira</label>
                                <input type="text" name="prateleira" style="border: 1px solid #C0C0C0;" class="form-control" id="prateleira" placeholder="">                        
                            </div>
                        </div>
                        <div class="col-lg-12">    
                            <div class="form-group">
                                <label for="Nome">Sinopse</label>                                
                                <textarea class="form-control texto" name="sinopse" style="border: 1px solid #C0C0C0;" id="sinopse" rows="6"></textarea>                       
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
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
    $(document).ready(function() {
        $('#cancelButton').on('click', function() {
            $("#tabela").load("cad_acervos/novo_cadastro.php")
        });
        $('#retornarButton').on('click', function() {
            $("#tabela").load("cad_acervos/tabela.php")
        });
        // Evento de clique no botão para abrir o seletor de arquivos
        $('#btnInsertImage2').on('click', function() {
            $('#imageInput').click(); // Simula um clique no input de arquivo
        });

        // Evento para capturar a imagem selecionada
        $('#imageInput').on('change', function(event) {
            const file = event.target.files[0]; // Obtém o arquivo selecionado

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Atualiza o src da imagem no elemento #foto
                    $('#foto img').attr('src', e.target.result);
                };

                reader.readAsDataURL(file); // Lê o arquivo como uma URL de dados
            }
        });

        $('#cadastroForm').on('submit', function(e) {
    e.preventDefault(); // Evita o envio padrão do formulário

    // Pega a imagem do elemento #foto
    const imgElement = $('#foto img');
    const imgSrc = imgElement.attr('src');

    // Se não houver imagem, apenas avisa mas continua
    if (!imgSrc || imgSrc === '../img/book.png') {
        Swal.fire({
            icon: 'info',
            title: 'Imagem ausente',
            text: 'Nenhuma imagem foi selecionada, o cadastro será salvo sem capa.'
        });
        enviarFormulario(''); // Envia string vazia como capa
        return;
    }

    // Converte a imagem em Base64 (se não estiver já em Base64)
    if (!imgSrc.startsWith('data:image/')) {
        const proxyUrl = `cad_acervos/image_proxy.php?url=${encodeURIComponent(imgSrc)}`;
        
        $.ajax({
            url: proxyUrl,
            method: 'GET',
            success: function(response) {
                const base64Image = response.image_src || '';
                enviarFormulario(base64Image);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao carregar a imagem.'
                });
            }
        });
    } else {
        enviarFormulario(imgSrc);
    }
});

// Função para enviar o formulário
function enviarFormulario(base64Image) {
    const formData = new FormData($('#cadastroForm')[0]);
    formData.append('capa', base64Image); // Pode ser vazio

    Swal.fire({
        title: 'Salvando dados...',
        text: 'Por favor, aguarde enquanto processamos as informações.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'cad_acervos/salva_acervo.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            Swal.close();
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: response.message
                }).then(() => {
                    $('#cadastroForm')[0].reset();
                    $('#foto img').attr('src', '../img/book.png');
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Ocorreu um erro ao salvar os dados. Tente novamente.'
            });
        }
    });
}

    // Evento de clique no botão GoogleBooks
    $('.pesq-google').on('click', function() {
        const isbn = $('#isbn').val().trim();
        if (!isbn) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Por favor, insira o ISBN antes de pesquisar.'
            });
            return;
        }

        // Exibe o alerta de progresso
        Swal.fire({
            title: 'Pesquisando no GoogleBooks...',
            text: 'Aguarde enquanto buscamos as informações.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `cad_acervos/proxy.php?isbn=${isbn}`, // Usa o proxy PHP
            method: 'GET',
            success: function(response) {
                // Processa a resposta do Google Books
                if (response.totalItems > 0) {
                    const book = response.items[0].volumeInfo;
                    // Preenche os campos do formulário
                    $('#titulo').val(book.title || '');
                    $('#autor').val(book.authors ? book.authors.join(', ') : '');
                    $('#editora').val(book.publisher || '');
                    $('#sinopse').val(book.description || '');

                    // Atualiza a imagem, se disponível
                    if (book.imageLinks && book.imageLinks.thumbnail) {
                        $('#foto img').attr('src', book.imageLinks.thumbnail);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'Informações carregadas com sucesso do GoogleBooks.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Nenhum Resultado',
                        text: 'Nenhum livro encontrado no GoogleBooks para o ISBN informado.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao buscar dados no GoogleBooks.'
                });
            }
        });
    });

    // Evento de clique no botão OpenLibrary
        $('.pesq-open').on('click', function() {
            const isbn = $('#isbn').val().trim();
            if (!isbn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Por favor, insira o ISBN antes de pesquisar.'
                });
                return;
            }

            // Exibe o alerta de progresso
            Swal.fire({
                title: 'Pesquisando no OpenLibrary...',
                text: 'Aguarde enquanto buscamos as informações.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `https://openlibrary.org/api/books?bibkeys=ISBN:${isbn}&format=json&jscmd=data`,
                method: 'GET',
                success: function(response) {
                    const bookKey = `ISBN:${isbn}`;
                    if (response[bookKey]) {
                        const book = response[bookKey];

                        // Preenche os campos do formulário
                        $('#titulo').val(book.title || '');
                        $('#autor').val(book.authors ? book.authors.map(author => author.name).join(', ') : '');
                        $('#editora').val(book.publishers ? book.publishers.map(pub => pub.name).join(', ') : '');
                        $('#sinopse').val(book.notes || '');

                        // Atualiza a imagem, se disponível
                        if (book.cover && book.cover.medium) {
                            $('#foto img').attr('src', book.cover.medium);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: 'Informações carregadas com sucesso do OpenLibrary.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nenhum Resultado',
                            text: 'Nenhum livro encontrado no OpenLibrary para o ISBN informado.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao buscar dados no OpenLibrary.'
                    });
                }
            });
        });
    
    });
</script>
