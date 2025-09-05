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
// Consulta para obter os empréstimos com informações do usuário e do acervo

$query = "
    SELECT e.*, e.id as id_emprestimo, u.id as user_id, u.nome as nome_usuario, a.id as acervo_id, a.titulo as titulo_acervo 
    FROM emprestimos e
    LEFT JOIN cad_usuario u ON e.user_id = u.id
    LEFT JOIN cad_acervo a ON e.acervo_id = a.id
    
    ORDER BY e.data_devolucao ASC
";

$result = $db->query($query);


// Função para converter data do formato DD/MM/YYYY para YYYY-MM-DD para comparação
function formatDateToCompare($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d ? $d->format('Y-m-d') : $date;
}

// Função para converter data do formato YYYY-MM-DD para DD/MM/YYYY para exibição
function formatDateToDisplay($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}

// Data atual para comparação
$currentDate = date('Y-m-d');
$ano = date('Y');
?>
<style>
    .dt-buttons {
        display: flex;
        justify-content: center; /* Centraliza os botões horizontalmente */
        gap: 10px; /* Adiciona espaçamento entre os botões */
        margin-bottom: 10px; /* Espaçamento abaixo dos botões */
    }
    
    
    /* Remover estilo de fundo padrão do DataTable */
table.dataTable tbody tr {
    background-color: initial !important; /* Usa o estilo padrão ou inicial */
}

/* Garante que estilos personalizados prevaleçam */
table.dataTable tbody tr.bg-amarelo {
    background-color: #fff9c4 !important;
    color: #000 !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}

table.dataTable tbody tr.bg-vermelho {
    background-color: #ffcdd2 !important;
    color: #b71c1c !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}

table.dataTable tbody tr.bg-verde {
    background-color: #c8e6c9 !important;
    color: #1b5e20 !important;
    border: 1px solid #000 !important; /* Adiciona borda preta */
}



</style>
<br>
<h3>Tabela Geral de Empréstimos</h3>
<div class="table-responsive">
<table class="table table-bordered table-striped" class="display" id="emprestimos">
    <thead>
        <tr>
            <th>#</th>
            <th>Usuário</th> <!-- Simplificado para "Usuário" -->
            
            <th>Título do Item</th> <!-- Substitui "Acervo" por "Item" para maior clareza -->
            <th>ID Emprétimo</th> <!-- Mais específico -->
            <th>Data de Empréstimo</th> <!-- Linguagem mais formal -->
            <th>Data de Devolução</th> <!-- Consistente com o anterior -->
            <th>ID Acervo</th>
            <th>Devolvido?</th> <!-- Indica que é uma pergunta (sim/não) -->
            <th>Situação</th>
            <th class='text-center'>Opções</th> <!-- "Opções" em vez de "Ações" para indicar interatividade -->
        </tr>
    </thead>

    <tbody>
        <?php
        $ordem = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // Convertendo a data de devolução e data atual para formato de comparação
            $dataDevolucao = formatDateToCompare($row['data_devolucao']);
            $currentDate = date('Y-m-d');

            $situacao = "";
            
           
            
            // Inicializa a variável de classe CSS da linha
            $rowClass = '';

            // Definir classes de linha baseadas no status da devolução
            if (strtolower($row['devolvido']) === 'sim') {
                $rowClass = 'bg-verde'; // Acervo devolvido  
                $situacao = "Em dia";              
            } elseif ($dataDevolucao < $currentDate) {
                $rowClass = 'bg-vermelho'; // Data de devolução é anterior à data atual
                $situacao = "Pendente";
            } elseif ($dataDevolucao == date('Y-m-d', strtotime('+1 day', strtotime($currentDate))) || $dataDevolucao == $currentDate ) {
                $rowClass = 'bg-amarelo'; // Data de devolução é um dia após a data atual
                $situacao = "Em dia";
            }else{
                $situacao = "Em dia";
            }

            // Exibe as datas no formato DD/MM/YYYY
            $dataAtual = formatDateToDisplay($row['data_atual']);
            $dataDevolucao = formatDateToDisplay($row['data_devolucao']);
            $dataEntregue = !empty($row['data_entregue']) ? formatDateToDisplay($row['data_entregue']) : '';

            //style='text-decoration:none; color: black;'
            echo "<tr class='{$rowClass}'>";
            echo "<td>" . htmlspecialchars($ordem) . "</td>";
            echo "<td><b><a href='#'  class='user-info' data-id='" . htmlspecialchars($row['user_id']) . "'>" . htmlspecialchars($row['nome_usuario']) . "</a></b></td>";

            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['titulo_acervo']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['id']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($dataAtual) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($dataDevolucao) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['acervo_id']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($row['devolvido']) . "</span></td>";
            echo "<td><span class='{$rowClass}'>" . htmlspecialchars($situacao) . "</span></td>";
            
            echo "<td class='text-center' style='white-space: nowrap;'>
                <div class='action-buttons'>
                    <button type='button' class='btn btn-primary btn-sm btn-rounded btn-icon edit-btn' data-id='" . $row['id_emprestimo'] . "'>
                        <i class='glyphicon glyphicon-edit'></i>
                    </button>
                    <button type='button' class='btn btn-danger btn-sm btn-rounded btn-icon delete-btn' data-id='" . $row['id_emprestimo'] . "'>
                        <i class='glyphicon glyphicon-trash'></i>
                    </button>
                    <button type='button' class='btn btn-info btn-sm btn-rounded btn-icon devolvido-btn' data-id='" . $row['id_emprestimo'] . "'>
                        <i class='glyphicon glyphicon-ok'></i>
                    </button>
                    <button class='btn btn-success btn-sm btn-rounded btn-icon msg-btn ' data-id='" . $row['id_emprestimo'] . "'>
                        <i class='fa fa-whatsapp'></i>
                    </button>
                </div>
            </td>";

            echo "</tr>";
            $ordem++;
        }
        ?>
       

    </tbody>


</table>
</div>
<table class="table">
<tr>
            <th>Legenda</th>
            <th>
                <button type='button' class='btn btn-primary btn-rounded btn-icon '>
                    <i class='glyphicon glyphicon-edit'></i>
                </button> Editar 
            </th>
            <th>
                <button type='button' class='btn btn-danger btn-rounded btn-icon '>
                    <i class='glyphicon glyphicon-trash'></i>
                </button> Excluir 
            </th>
            <th>
                <button type='button' class='btn btn-info btn-rounded btn-icon '>
                    <i class='glyphicon glyphicon-ok'></i>
                </button> Marcar Devolvido
            </th>
            <th>
                <button class='btn btn-success'>
                        <i class='fa fa-whatsapp'></i>
                </button> Enviar Mensagem por WhatsApp
            </th>
        </tr>
</table>

<!-- Modal para edição do empréstimo -->
<div class="modal fade" id="modalEditarEmprestimo" tabindex="-1" role="dialog" aria-labelledby="modalEditarEmprestimoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarEmprestimoLabel">Editar Empréstimo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditarEmprestimo">
          <input type="hidden" id="emprestimoId" name="emprestimoId">
          <div class="row">
            <div class="col-md-4 text-center">
              <img id="acervoCapa" src="../img/book.png" alt="Capa do Acervo" style="max-width: 100%; height: auto; border: 1px solid #ddd;">
            </div>
            <div class="col-md-8">
              <h5>Usuário</h5>
              <p id="usuarioNome"></p>
              <p id="usuarioFone"></p>
              <h5>Título do Acervo</h5>
              <p id="acervoTitulo"></p>
              <div class="form-group">
                <label for="dataAtual">Data de Empréstimo</label>
                <input type="date" class="form-control" id="dataAtual" name="dataAtual">
              </div>
              <div class="form-group">
                <label for="dataDevolucao">Data de Devolução</label>
                <input type="date" class="form-control" id="dataDevolucao" name="dataDevolucao">
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary" id="salvarEdicaoEmprestimo">Salvar</button>
      </div>
    </div>
  </div>
</div>



<!-- Modal para Enviar Mensagem -->
<div class="modal fade" id="modalEnviarMensagem" tabindex="-1" role="dialog" aria-labelledby="modalEnviarMensagemLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEnviarMensagemLabel">Enviar Mensagem para Usuário</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Usuário:</strong> <span id="mensagemUsuarioNome"></span></p>
        <p><strong>Telefone:</strong> <span id="mensagemUsuarioFone"></span></p>
        <p><strong>Acervo:</strong> <span id="mensagemAcervoTitulo"></span></p>
        <textarea class="form-control" id="mensagemTexto" rows="5" placeholder="Digite sua mensagem aqui..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-success" id="enviarMensagemWhatsApp">Enviar via WhatsApp</button>
      </div>
    </div>
  </div>
</div>



<script>
$(function(){

    // Define um tipo de dados personalizado para datas no formato DD/MM/YYYY
    $.fn.dataTable.ext.type.order['date-uk-pre'] = function (date) {
        if (!date || date === '') {
            return 0; // Retorna 0 para células vazias
        }
        const parts = date.split('/'); // Divide a data no formato DD/MM/YYYY
        return new Date(parts[2], parts[1] - 1, parts[0]).getTime(); // Retorna timestamp para ordenação
    };

        $('#emprestimos').DataTable({
            columnDefs: [
                {
                    targets: [4, 5], // Índice da coluna com as datas (ajuste conforme necessário)
                    type: 'date-uk', // Usa o tipo de ordenação personalizado
                },
                {
                    targets: 0, // Primeira coluna
                    orderable: false, // Desabilita a ordenação nesta coluna
                    searchable: false, // Desabilita a pesquisa nesta coluna
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Calcula o índice da linha (começa em 0) e soma 1
                    },
                },
            ],
            stripeClasses: [], // Remove as classes que alternam as cores das linhas
            language: {
                url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
            },
            pageLength: 20, // Define o número de registros exibidos na primeira página
            dom: '<"dt-buttons-container"B>lfrtip', // Ativa o uso dos botões
            buttons: [
																		
						{
						  extend: "print",
                          text: "Imprimir",
						  className: " btn-info"
						},
					  ],
           
        order: [[5, 'asc']], // Define a sexta coluna como padrão para ordenação
        
        });

    // Evento para abrir o modal ao clicar no botão "edit-btn"
    $(document).on('click', '.edit-btn', function () {
        const emprestimoId = $(this).data('id');

        // Fazer a requisição AJAX para obter as informações do empréstimo
        $.ajax({
            url: 'cad_emprestimos/obter_emprestimo.php', // Crie este arquivo para retornar os dados do empréstimo em JSON
            method: 'GET',
            data: { id: emprestimoId },
            dataType: 'json',
            success: function (data) {
            if (data.status === 'success') {
                const capa = data.emprestimo.capa; // Obtém o valor da capa retornado pela API

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

                // Preencher os campos do modal com as informações retornadas
                $('#emprestimoId').val(data.emprestimo.id);
                $('#usuarioNome').text(data.emprestimo.nome_usuario);
                $('#usuarioFone').text(data.emprestimo.fone_usuario);
                $('#acervoTitulo').text(data.emprestimo.titulo_acervo);
                $('#acervoCapa').attr('src', capaFinal); // Usa o valor calculado em `capaFinal`
                $('#dataAtual').val(data.emprestimo.data_atual);
                $('#dataDevolucao').val(data.emprestimo.data_devolucao);

                // Abrir o modal
                $('#modalEditarEmprestimo').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao carregar as informações do empréstimo.',
                });
            }
        },

            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro na comunicação com o servidor.',
                });
            },
        });
    });


    $('#salvarEdicaoEmprestimo').on('click', function () {
        const emprestimoId = $('#emprestimoId').val();
        const dataAtual = $('#dataAtual').val();
        const dataDevolucao = $('#dataDevolucao').val();

        if (!dataAtual || !dataDevolucao) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos obrigatórios',
                text: 'Preencha todas as datas antes de salvar.',
            });
            return;
        }

        $.ajax({
            url: 'cad_emprestimos/salvar_editar_emprestimo.php',
            type: 'POST',
            data: {
                id: emprestimoId,
                data_atual: dataAtual,
                data_devolucao: dataDevolucao,
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: response.message,
                    }).then(() => {
                        $('#modalEditarEmprestimo').modal('hide');

                        // Atualizar apenas a linha afetada
                        atualizarLinhaTabela(emprestimoId, dataAtual, dataDevolucao);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao salvar o empréstimo.',
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro na comunicação com o servidor.',
                });
            },
        });
    });

    // Função para atualizar apenas uma linha específica
    function atualizarLinhaTabela(emprestimoId, dataAtual, dataDevolucao) {
        const tabela = $('#emprestimos').DataTable();

        tabela.rows().every(function () {
            const dados = this.data();
            
            // Supondo que o ID do empréstimo esteja na 4ª coluna (índice 3)
            if ($(dados[3]).text() == emprestimoId) {

                // Atualiza células específicas (colunas 5 e 6, índices 4 e 5)
                dados[4] = formatarDataExibir(dataAtual);
                dados[5] = formatarDataExibir(dataDevolucao);

                // Atualiza os dados na tabela
                this.data(dados).draw(false);
            }
        });
    }

    // Função para formatar datas de YYYY-MM-DD para DD/MM/YYYY
    function formatarDataExibir(dataISO) {
        const partes = dataISO.split("-");
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    $(document).on('click', '.msg-btn', function () {
        const emprestimoId = $(this).data('id');

        // Fazer a requisição AJAX para obter os dados do empréstimo
        $.ajax({
            url: 'cad_emprestimos/obter_emprestimo.php', // Altere para o caminho do seu arquivo PHP
            method: 'GET',
            data: { id: emprestimoId },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    const emprestimo = data.emprestimo;
                    const dataDevolucao = new Date(emprestimo.data_devolucao);
                    const dataAtual = new Date();

                    // Preencher o modal com os dados
                    $('#mensagemUsuarioNome').text(emprestimo.nome_usuario);
                    $('#mensagemUsuarioFone').text(emprestimo.fone_usuario);
                    $('#mensagemAcervoTitulo').text(emprestimo.titulo_acervo);

                    // Gerar mensagem personalizada
                    let mensagem = '';
                    const diffDias = Math.ceil((dataDevolucao - dataAtual) / (1000 * 60 * 60 * 24));

                    if (diffDias < 0) {
                        mensagem = `O prazo para devolução do acervo "${emprestimo.titulo_acervo}" expirou. Por favor, regularize sua situação na biblioteca.`;
                    } else if (diffDias === 1) {
                        mensagem = `Lembre-se que o prazo para devolução do acervo "${emprestimo.titulo_acervo}" é amanhã.`;
                    }

                    $('#mensagemTexto').val(mensagem);

                    // Abrir o modal
                    $('#modalEnviarMensagem').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message || 'Erro ao obter informações do empréstimo.',
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro na comunicação com o servidor.',
                });
            },
        });
    });


    $('#enviarMensagemWhatsApp').on('click', function () {
        const telefone = $('#mensagemUsuarioFone').text().trim(); // Número de telefone
        const mensagem = encodeURIComponent($('#mensagemTexto').val().trim()); // Mensagem

        if (!telefone || !mensagem) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Telefone ou mensagem não podem estar vazios.',
            });
            return;
        }

        // Remove caracteres não numéricos e formata o número
        let telefoneFormatado = telefone.replace(/[\s()-]/g, ''); // Remove espaços, parênteses e traços

        // Adiciona o código do Brasil (55) se não estiver presente
        if (!telefoneFormatado.startsWith('55')) {
            telefoneFormatado = '55' + telefoneFormatado;
        }

        if (telefoneFormatado.length < 12 || telefoneFormatado.length > 13) {
            Swal.fire({
                icon: 'error',
                title: 'Número Inválido',
                text: 'O número de telefone parece estar incorreto. Por favor, verifique.',
            });
            return;
        }

        // Monta o link do WhatsApp
        const whatsappUrl = `https://wa.me/${telefoneFormatado}?text=${mensagem}`;
        window.open(whatsappUrl, '_blank'); // Abre em uma nova aba
    });


     // Evento click para o botão Deletar
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        var $button = $(this); // Salva o botão clicado em uma variável
        
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você não poderá desfazer essa ação!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cad_emprestimos/delete_emprestimo.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire({
                                title: 'Excluído!',
                                text: 'Empréstimo excluído com sucesso!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $button.closest('tr').remove(); // Remove a linha associada ao botão
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Erro ao excluir o empréstimo: ' + res.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao excluir o empréstimo:', error);
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Ocorreu um erro ao excluir o empréstimo.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });


    // Evento click para o botão Marcar como Devolvido
    $('.devolvido-btn').on('click', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Marcar como devolvido?',
            text: "Você quer marcar este empréstimo como devolvido?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, marcar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cad_emprestimos/marcar_devolvido.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Empréstimo marcado como devolvido com sucesso!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $("#tabela").load("cad_emprestimos/tabela.php")
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Erro ao marcar como devolvido: ' + res.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao marcar como devolvido:', error);
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Ocorreu um erro ao marcar o empréstimo como devolvido.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });




})
</script>