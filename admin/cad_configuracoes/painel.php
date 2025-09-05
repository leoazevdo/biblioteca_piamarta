<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
} else {
    // Cria a conexão com o banco de dados SQLite3
    $db = new SQLite3('../../data/bibliotecario.db'); // Certifique-se de que o arquivo do banco de dados existe e está no caminho correto


    /*usar se necessário 
$nome = "Instituição " . rand(1000, 9999);
    $logo = "logo_" . rand(1, 100) . ".png";
    $endereco = "Rua Exemplo, " . rand(1, 500);
    $preferencias = "titulo,categoria,tipo";
    
    $query = "INSERT INTO cad_configuracoes (nome_instituicao, logo_instituicao, endereco_instituicao, preferencias) 
              VALUES (:nome, :logo, :endereco, :preferencias)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':logo', $logo, SQLITE3_TEXT);
    $stmt->bindValue(':endereco', $endereco, SQLITE3_TEXT);
    $stmt->bindValue(':preferencias', $preferencias, SQLITE3_TEXT);
    $stmt->execute();
    
 usar se necessário */

 
    $id_usuario = $_SESSION['user_id'];
    // Consulta combinada para obter dados do usuário e configurações do sistema
    $stmt = $db->prepare("SELECT 
        u.id AS user_id, 
        u.nivel AS user_level, 
        u.nome AS user_name, 
        u.foto AS user_photo, 
        c.nome_instituicao AS institution_name, 
        c.logo_instituicao AS institution_logo, 
        c.endereco_instituicao AS institution_address
    FROM cad_usuario u
    LEFT JOIN cad_configuracoes c ON 1=1
    WHERE u.id = :id");
    $stmt->bindValue(':id', $id_usuario, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if ($result) {
        $dados = $result->fetchArray(SQLITE3_ASSOC); // Pega os dados em formato associativo
    
        if ($dados) {
            $id_usuario = $dados['user_id'];
            $nivel_usuario = $dados['user_level'];
            $nome_usuario = $dados['user_name'];
            $foto_usuario = $dados['user_photo'];
            $nome_instituicao = $dados['institution_name'];
            $logo_instituicao = $dados['institution_logo'];
            $endereco_instituicao = $dados['institution_address'];
    
            // Configuração da foto do usuário
            if (empty($foto_usuario)) {
                $foto_usuario = '../img/default.jpg';
            } else {
                $foto_usuario = '../uploads/imagens/' . $foto_usuario;
            }
    
            // Verifica o nível do usuário
            $nivel = "";
            if ($nivel_usuario != "1") {
                header("Location: ../index.php");
                exit();
            } else {
                $nivel = 'Administrador';
            }
        } else {
            // Usuário não encontrado, redirecionar para o login
            header("Location: ../index.php");
            exit();
        }
    }

    //Verifica se a coluna 'preferencias' já existe na tabela 'cad_configuracoes'
    $query = "PRAGMA table_info(cad_configuracoes)";
    $result = $db->query($query);

    $preferenciasExiste = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === 'preferencias') {
            $preferenciasExiste = true;
            break;
        }
    }

    // Se a coluna 'foto' não existir, adiciona
    if (!$preferenciasExiste) {
      $sql = "ALTER TABLE cad_configuracoes ADD COLUMN preferencias TEXT";
      if ($db->exec($sql)) {
          echo "Coluna 'preferencias' adicionada com sucesso!";
      } else {
          echo "Erro ao adicionar a coluna 'foto'.";
      }
  }

  $resultPreferencias = $db->querySingle("SELECT preferencias FROM cad_configuracoes ", true);
  // Transforme a string de volta em um array
  $colunas_selecionadas = explode(',', $resultPreferencias['preferencias']);
}


?>

<style>
    .checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    padding: 5px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s, border-color 0.3s;
    cursor: pointer;
}

.checkbox-group label:hover {
    background: #e6f7ff;
    border-color: #b3e5fc;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    accent-color: #007bff; /* Definir cor do checkbox */
    transform: scale(1.2); /* Deixar o checkbox um pouco maior */
}

</style>

<div class="page-title" >
    <div class="title_left" >
        <img src="<?php echo $logo_instituicao ?>" style="width: 90px; margin-bottom: 10px;"  alt="">
    </div>
    <div class="title_right" >
        <h3 class="text-right" style="margin-right: 30px; margin-top: 30px;"><?php echo $nome_instituicao ?>
    </h3>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Configurações do Sistema</h2>                    
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div>
                    <script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
                    <!-- Inclua o arquivo de idioma do Parsley para português -->
                    <script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>
                    <br><br>
                    
                    <form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="Nome">Nome da Instituição </label>
                                    <input type="text" style="border: 1px solid #C0C0C0;" required class="form-control" name="turma" id="turma" placeholder="Descrição" value="<?php echo htmlspecialchars($nome_instituicao); ?>">
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="Nome">Logo da Instituição </label>
                                    <input type="file" name="logo" accept="image/*" class="form-control ">
                                    <?php if (!empty($logo_instituicao)): ?>
                                        <p>Logo atual: <img src="<?php echo $logo_instituicao; ?>" alt="Logo" style="width: 100px;"></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="Nome">Endereço da Instituição </label>
                                    <input type="text" style="border: 1px solid #C0C0C0;" required class="form-control" name="endereco" id="endereco" placeholder="Endereço" value="<?php echo htmlspecialchars($endereco_instituicao); ?>">
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="Nome">Marque as colunas para exibir na tabela de Acervos </label>
                                    <div class="checkbox-group">
                                                                               
                                        <label><input type="checkbox" name="colunas[]" value="tipo" <?php echo in_array('tipo', $colunas_selecionadas) ? 'checked' : ''; ?>> Formato</label>
                                        <label><input type="checkbox" name="colunas[]" value="isbn" <?php echo in_array('isbn', $colunas_selecionadas) ? 'checked' : ''; ?>> ISBN</label>
                                        <label><input type="checkbox" name="colunas[]" value="autor" <?php echo in_array('autor', $colunas_selecionadas) ? 'checked' : ''; ?>> Autor</label>
                                        <label><input type="checkbox" name="colunas[]" value="editora" <?php echo in_array('editora', $colunas_selecionadas) ? 'checked' : ''; ?>> Editora</label>
                                        
                                        <label><input type="checkbox" name="colunas[]" value="quantidade" <?php echo in_array('quantidade', $colunas_selecionadas) ? 'checked' : ''; ?>> Quantidade</label>
                                        
                                        <label><input type="checkbox" name="colunas[]" value="sinopse" <?php echo in_array('sinopse', $colunas_selecionadas) ? 'checked' : ''; ?>> Sinopse</label>
                                    </div>

                                </div>

                            </div>
                        </div>
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
                
                <br>
                <div id="tabela"></div>
            </div>
        </div>
    </div>
</div>
<script>  
   
    $(function(){
        $(".cancelar").click(function(){
            $("#conteudo").load("cad_configuracoes/painel.php");
        });
         // Evento de submissão do formulário
         $('#cadastroForm').on('submit', function(e) {
            e.preventDefault(); // Evita o comportamento padrão do formulário

            // Cria um objeto FormData para enviar os dados do formulário, incluindo arquivos
            var formData = new FormData(this);

            // Envia os dados via AJAX para salva_perfil.php
            $.ajax({
                url: 'cad_configuracoes/salva_configuracao.php',
                type: 'POST',
                data: formData,
                contentType: false, // Não definir o tipo de conteúdo (necessário para envio de arquivos)
                processData: false, // Não processar os dados (também necessário para arquivos)
                success: function(response) {
                    var data = JSON.parse(response); // Converte a resposta JSON para objeto JS
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            // Recarrega a página ou o conteúdo desejado após o sucesso
                            $("#conteudo").load("cad_configuracoes/painel.php");
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
                        text: 'Ocorreu um erro ao tentar salvar as configurações. Tente novamente.',
                    });
                }
            });
        });
    })
</script>