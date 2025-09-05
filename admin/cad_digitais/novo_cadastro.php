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

<script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- Inclua o arquivo de idioma do Parsley para português -->
<script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>
<script src="../arquivos/scripts/pdf-js/pdf.js"></script>
<br><br>
<h3>Novo Cadastro</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
<div class="row">
        <div class="col-lg-4">    
            <div class="text-center">
                <div class="editor2 mb-2">
                    Capa do Acervo Digital
                    <div id="foto" >
                        <img src="../img/book.png"  alt="Book Cover">
                    </div>  
                </div>
                
            </div>
        </div>

    
        <div class="col-lg-8">
            <div class="row">
                <div class=" col-lg-12"> 
                    <div class="form-group"> <label for="Nome">Título</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" id="titulo" name="titulo" placeholder="Título">
                        <label for="">Selecione um arquivo do tipo PDF</label>
                        <input type="file" class="form-control"  required id="arquivo" name="arquivo"  accept="application/pdf">
                         
                    </div>              

                </div>
                <div class=" col-lg-12"> 
                    <div class="form-group">
                       
                    </div>

                </div>
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Autor</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" name="autor" id="autor" placeholder="Autor">
                    </div>
                </div>
                <div class="col-lg-6">    
                    <div class="form-group">
                        <label for="Editora">Editora</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" name="editora" id="editora" placeholder="Editora">
                    </div>
                </div>
                <div class="col-lg-2">    
                    <div class="form-group">
                        <label for="Editora">Ano</label>
                        <input type="text" style="border: 1px solid #C0C0C0;" class="form-control" name="ano" id="ano" placeholder="Ano">
                    </div>
                </div>
                <div class="col-lg-4">    
                    <div class="form-group">
                        <label for="Nome">Área de Conhecimento</label>
                        <select class="form-control" id="categoria" name="categoria" required name="categoria" style="border: 1px solid #C0C0C0;">
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
                <div class="col-lg-12">    
                    <div class="form-group">
                        <label for="Nome">Sinopse</label>                                
                        <textarea class="form-control texto" style="border: 1px solid #C0C0C0;" name="sinopse" id="sinopse" rows="6"></textarea>                       
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                    <br><br>
                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                    <button type="button" class="btn btn-light" id="cancelButton">Cancelar</button>
                    </div>
                </div>
            </div>            
        </div>
    </div>

</form>

<script>
    $(function(){
        $('#cadastroForm').on('submit', function(e) {
            e.preventDefault(); // Evita o envio padrão do formulário

            // Verifica se há uma imagem no elemento #foto
            const imgElement = $('#foto img');
            const imgSrc = imgElement.attr('src');
            
            if (!imgSrc || imgSrc === '../img/book.png') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Imagem ausente',
                    text: 'Por favor, selecione uma imagem antes de salvar.'
                });
                return;
            }

            // Converte a imagem em Base64 (se não estiver já em Base64)
            let base64Image = '';
            if (!imgSrc.startsWith('data:image/')) {
                const canvas = document.createElement('canvas');
                const img = new Image();

                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    base64Image = canvas.toDataURL('image/jpeg');

                    // Enviar os dados via AJAX após a conversão
                    enviarFormulario(base64Image);
                };

                img.crossOrigin = 'Anonymous';
                img.src = imgSrc;
            } else {
                base64Image = imgSrc; // Já está em Base64
                enviarFormulario(base64Image);
            }
        });

        // Função para enviar o formulário
        function enviarFormulario(base64Image) {
            const formData = new FormData($('#cadastroForm')[0]);
            formData.append('capa', base64Image); // Adiciona a imagem como Base64

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
                url: 'cad_digitais/salva_digital.php', // URL do arquivo PHP que processará os dados
                method: 'POST',
                data: formData,
                contentType: false, // Não define o cabeçalho Content-Type
                processData: false, // Não processa os dados como uma string
                success: function(response) {
                    
                    Swal.close(); // Fecha o alerta de progresso
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message
                        }).then(() => {
                            // Redefine o formulário após o sucesso
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

           

        // Limpa o formulário e recarrega a tabela ao cancelar
        $('#cancelButton').on('click', function() {
            $('#cadastroForm')[0].reset();
            $('#tabela').load("cad_digitais/tabela.php");
        });

        // Selecionar elementos
        const fileInput = document.getElementById('arquivo');
        const fotoDiv = document.getElementById('foto');
        let imageBlob = null; // Blob da imagem

        // Configurar PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
       
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file.type === "application/pdf") {
                const fileReader = new FileReader();

                fileReader.onload = function() {
                    const typedArray = new Uint8Array(this.result);

                    // Carregar o PDF
                    pdfjsLib.getDocument(typedArray).promise.then(function(pdf) {
                        // Pegar a primeira página
                        pdf.getPage(1).then(function(page) {
                            const scale = 1.5;
                            const viewport = page.getViewport({ scale: scale });

                            // Criar um canvas onde a página será renderizada
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            // Renderizar a página no canvas
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext).promise.then(function() {
                                // Converter o canvas para JPEG Blob e mostrar na div "foto"
                                canvas.toBlob(function(blob) {
                                    imageBlob = blob; // Salvar o Blob da imagem JPEG
                                    const img = document.createElement('img');
                                    img.src = URL.createObjectURL(blob);

                                    // Ajustar tamanho para caber na div "foto"
                                    img.style.maxWidth = '100%';
                                    img.style.maxHeight = '100%';
                                    img.style.objectFit = 'contain';

                                    fotoDiv.innerHTML = '';
                                    fotoDiv.appendChild(img);
                                }, 'image/jpeg');
                            });
                        });
                    });
                };

                fileReader.readAsArrayBuffer(file);
            } else {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Por favor, selecione um arquivo PDF.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });


    })
</script>

