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
?>
<style>
  .usuario-item:hover {
    background-color: #f1f1f1; /* Cor de fundo ao passar o mouse */
    cursor: pointer; /* Mostra o cursor como "mão" */
  }

  .list-group-item {
    padding: 8px !important; /* Reduz o espaçamento entre os itens */
    font-size: 14px; /* Ajusta o tamanho da fonte */
  }
  .modern-div {
    padding: 10px; /* Espaçamento interno */
    border: 1px solid #dcdcdc; /* Borda leve */
    border-radius: 8px; /* Bordas arredondadas */
    background: linear-gradient(135deg, #f9f9f9, #e8e8e8); /* Fundo com leve gradiente */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra para efeito de profundidade */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transição suave ao passar o mouse */
  }
  .simple-div {
    background-color: #ffffff; /* Fundo branco */
    padding: 10px; /* Espaçamento interno */
    border: 1px solid #dcdcdc; /* Borda leve */
    border-radius: 5px; /* Bordas levemente arredondadas */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra discreta */
    min-height: 250px;
  }

  
</style>
<br>
<script src="../arquivos/vendors/parsleyjs/dist/parsley.min.js"></script>
<!-- Inclua o arquivo de idioma do Parsley para português -->
<script src="../arquivos/vendors/parsleyjs/dist/i18n/pt-br.js"></script>
<br>
<h3>Registrar Novo Empréstimo</h3><hr>
<form id="cadastroForm" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" novalidate=""> 
    <div class="row">
        
        <div class="col-md-2 col-sm-12 col-xs-12">
            <button class="btn btn-info selecionar-user">SELECIONAR USUÁRIO</button>
        </div> 
        <div class="col-md-10 col-sm-12 col-xs-12">
            <input type="hidden" class="recebe_id" value="" name="user_id">
            <input type="text" name="" class="form-control recebe_nome" disabled>
        </div> 
        
    </div> 
    <hr>
    <div class="modern-div">
        <!-- Conteúdo da div -->
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
            <button class="btn btn-success selecionar-acervo">SELECIONAR ACERVO</button>
            <br>
            </div>
            <div class="col-md-5 col-sm-12 col-xs-12">
                
                <div class="simple-div mostra-acervo">

                </div>
                <div class="text-right " style="margin-top: 10px;">
                    <button class="btn btn-primary inserir-acervo">Inserir >></button>
                </div>
            </div> 

            <div class="col-md-7 col-sm-12 col-xs-12 " >
                <div class="simple-div">
                <p>Acervos selecionados</p>
                    <table class="table table-bordered table-striped table-sm" id="recebe-acervos">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>ID</th>
                                <th>Título</th>
                                <th class="text-center">Data</th>
                                
                                <th class="text-center">Devolução</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="text-right " style="margin-top: 10px;">
                    <button class="btn btn-primary fechar-emprestimos">Fechar Empréstimos</button>
                </div>

            </div>
        </div> 
    </div>
</form>

<!-- Modal para seleção de usuários -->
<div class="modal fade" id="modalSelecionarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalUsuarioLabel">Selecionar Usuário</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="filtroUsuario" class="form-control mb-3" placeholder="Filtrar usuários...">
        <ul id="listaUsuarios" class="list-group">
          <!-- Lista de usuários será carregada dinamicamente -->
        </ul>
      </div>
    </div>
  </div>
</div>


<!-- Modal para seleção de acervos -->
<div class="modal fade" id="modalSelecionarAcervo" tabindex="-1" role="dialog" aria-labelledby="modalAcervoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAcervoLabel">Selecionar Acervo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="filtroAcervo" class="form-control mb-3" placeholder="Filtrar por ID, Título ou ISBN...">
        <ul id="listaAcervos" class="list-group">
          <!-- Lista de acervos será carregada dinamicamente -->
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    // Abrir o modal ao clicar no botão
  $('.selecionar-acervo').on('click', function (e) {
    e.preventDefault();
    $('#modalSelecionarAcervo').modal('show');

    // Carregar a lista de acervos
    carregarAcervos();
  });

 // Função para carregar acervos com base no filtro
 function carregarAcervos(filtro = '') {
        const lista = $('#listaAcervos');

        if (filtro.trim() === '') {
            lista.empty(); // Limpa a lista
            lista.hide(); // Oculta a lista se o filtro estiver vazio
            return;
        }

        $.ajax({
            url: 'cad_emprestimos/cadastro/listar_acervos.php',
            method: 'GET',
            data: { filtro: filtro },
            dataType: 'json',
            success: function (response) {
                lista.empty(); // Limpa a lista antes de adicionar novos itens

                if (response.length > 0) {
                    lista.show(); // Exibe a lista se houver resultados
                    response.forEach(function (acervo) {
                        lista.append(
                            `<li class="list-group-item acervo-item" style="cursor: pointer;" 
                                data-id="${acervo.id}" 
                                data-titulo="${acervo.titulo}" 
                                data-sinopse="${acervo.sinopse}" 
                                data-capa="${acervo.capa}" 
                                data-categoria="${acervo.categoria}">
                                ${acervo.titulo} (ISBN: ${acervo.isbn})
                            </li>`
                        );
                    });
                } else {
                    lista.hide(); // Oculta a lista se não houver resultados
                }
            },
            error: function () {
                alert('Erro ao carregar acervos.');
            },
        });
    }

    // Capturar digitação no campo de filtro
    $('#filtroAcervo').on('input', function () {
        const filtro = $(this).val();
        carregarAcervos(filtro);
    });

   // Selecionar acervo ao clicar no item da lista
    $(document).on('click', '.acervo-item', function () {
        const id = $(this).data('id');
        const titulo = $(this).data('titulo');
        const sinopse = $(this).data('sinopse');
        const capa = $(this).data('capa');
        const categoria = $(this).data('categoria');

        let capaFinal;

        // Verifica se o valor de `capa` é uma imagem base64
        if (/^data:image\/(jpeg|png|gif|bmp|webp);base64,/.test(capa)) {
            capaFinal = capa; // Mantém o valor atual se for uma imagem base64
        } else if (capa === '../master/images/book.png' || !capa ) {
            capaFinal = '../img/book.png'; // Define uma imagem padrão se não houver capa
        } else {
            // Altera para o caminho padrão no diretório de uploads
            capaFinal = '../uploads/imagens/' + capa;
        }

        // Atualizar a div mostra-acervo com os dados selecionados
        $('.mostra-acervo').html(`
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #fff;">
                <div class="text-center">
                <img src="${capaFinal}" alt="Capa do Livro" style="max-width: 50%; height: auto; margin-bottom: 10px; object-fit: contain;">
                </div>
                <h5>${titulo}</h5>
                <p><strong>Categoria:</strong> ${categoria}</p>
                <p>${sinopse}</p>
                <input type="hidden" class="id-acervo" value="${id}">
            </div>
        `);

        $('#listaAcervos').hide(); // Oculta a lista após selecionar um acervo
        $('#modalSelecionarAcervo').modal('hide');
    });


 // Inserir acervo na tabela ao clicar no botão
$('.inserir-acervo').on('click', function (e) {
    e.preventDefault();

    const acervoId = $('.mostra-acervo .id-acervo').val(); // Captura o ID do acervo selecionado
    const titulo = $('.mostra-acervo h5').text(); // Captura o título do acervo
    const dataAtual = new Date();
    let devolucao = new Date(dataAtual);

    // Adicionar 3 dias, ignorando finais de semana
    for (let i = 0; i < 3; i++) {
        devolucao.setDate(devolucao.getDate() + 1);
        if (devolucao.getDay() === 6 || devolucao.getDay() === 0) {
            i--; // Ignorar sábados e domingos
        }
    }

    // Formatar as datas para YYYY-MM-DD (compatível com input[type="date"])
    const formatarDataParaInputDate = (data) => {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0'); // Mês começa do 0
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    };

    

    const dataAtualFormatada = formatarDataParaInputDate(dataAtual);
    const devolucaoFormatada = formatarDataParaInputDate(devolucao);

    // Verificar se o ID do acervo já está na tabela
    if ($(`#recebe-acervos tr td:contains(${acervoId})`).length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Este acervo já foi adicionado.',
        });
        return;
    }

    // Adicionar nova linha à tabela
    $('#recebe-acervos tbody').append(`
      <tr>
        <td><button class="btn btn-danger btn-sm remover-acervo">&times;</button></td>
        <td>${acervoId}</td>
        <td>${titulo}</td>
        <td class="text-center"><input type="date" value="${dataAtualFormatada}" class="form-control"</td>
        <td class="text-center"><input type="date" value="${devolucaoFormatada}" class="form-control"></td>
      </tr>
    `);

    // Limpar a div mostra-acervo
    $('.mostra-acervo').empty();
});



// Remover acervo da tabela ao clicar no botão de remoção
$(document).on('click', '.remover-acervo', function () {
    $(this).closest('tr').remove();
});




  
  // Abrir o modal ao clicar no botão
  $('.selecionar-user').on('click', function (e) {
    e.preventDefault();
    $('#modalSelecionarUsuario').modal('show');

    // Carregar a lista de usuários
    carregarUsuarios();
  });

  // Função para carregar os usuários
  function carregarUsuarios() {
    $.ajax({
      url: 'cad_emprestimos/cadastro/listar_usuarios.php', // Crie um arquivo PHP para retornar a lista de usuários
      method: 'GET',
      dataType: 'json',
      success: function (response) {
        const lista = $('#listaUsuarios');
        lista.empty(); // Limpa a lista antes de adicionar novos itens

        response.forEach(function (usuario) {
          lista.append(
            `<li class="list-group-item usuario-item" 
                data-id="${usuario.id}" 
                data-nome="${usuario.nome}" 
                style="padding: 8px; cursor: pointer;">
              ${usuario.nome}
            </li>`
          );
        });
      },
      error: function () {
        alert('Erro ao carregar usuários.');
      },
    });
  }

  // Filtrar usuários conforme digitação
  $('#filtroUsuario').on('input', function () {
    const filtro = $(this).val().toLowerCase();
    $('#listaUsuarios .usuario-item').each(function () {
      const nome = $(this).data('nome').toLowerCase();
      $(this).toggle(nome.includes(filtro));
    });
  });

  // Selecionar o usuário ao clicar no item da lista
  $(document).on('click', '.usuario-item', function () {
    const userId = $(this).data('id');
    const userName = $(this).data('nome');

    // Atualizar os inputs com os valores selecionados
    $('.recebe_id').val(userId);
    $('.recebe_nome').val(userName);

    // Fechar o modal
    $('#modalSelecionarUsuario').modal('hide');
  });


  $('.fechar-emprestimos').on('click', function (e) {
        e.preventDefault();

        const userId = $('.recebe_id').val(); // Captura o ID do usuário selecionado

        // Verificar se o usuário foi selecionado
        if (!userId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Nenhum usuário selecionado. Por favor, selecione um usuário antes de fechar o empréstimo.',
            });
            return;
        }

        const emprestimos = [];

        // Percorrer as linhas da tabela de acervos
        $('#recebe-acervos tbody tr').each(function () {
            const acervoId = $(this).find('td:nth-child(2)').text(); // ID do acervo

            const dataAtual = $(this).find('td:nth-child(4) input').val(); // Data de empréstimo
            const dataDevolucao = $(this).find('td:nth-child(5) input').val(); // Data de devolução

            // Adicionar os dados ao array de empréstimos
            emprestimos.push({
                user_id: userId,
                acervo_id: acervoId,
                data_atual: dataAtual,
                data_devolucao: dataDevolucao,
                devolvido: "Não",
                total_dias: 0,
                dia_semana: 0
            });
        });

        // Verificar se há acervos selecionados
        if (emprestimos.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Nenhum acervo selecionado. Por favor, adicione acervos antes de fechar o empréstimo.',
            });
            return;
        }

        // Enviar os dados via AJAX para salvar no banco
        $.ajax({
            url: 'cad_emprestimos/cadastro/salvar_emprestimos.php', // Arquivo PHP para processar os dados
            method: 'POST',
            data: { emprestimos: JSON.stringify(emprestimos) }, // Enviar os dados como JSON
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'Empréstimos cadastrados com sucesso!',
                    }).then(() => {
                        //location.reload(); // Recarregar a página após sucesso
                        $("#tabela").load("cad_emprestimos/tabela.php")
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Ocorreu um erro ao salvar os empréstimos.',
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: `Erro na comunicação com o servidor: ${xhr.responseText || error}`,
                });
                console.error('Status:', status);
                console.error('Erro:', error);
                console.error('Resposta do servidor:', xhr.responseText);
            }
        });
    });



});

</script>

