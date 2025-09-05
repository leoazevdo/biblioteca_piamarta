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
    .dt-buttons {
        display: flex;
        justify-content: center; /* Centraliza os botões horizontalmente */
        gap: 10px; /* Adiciona espaçamento entre os botões */
        margin-bottom: 10px; /* Espaçamento abaixo dos botões */
    }
</style>

<br>
<h3>Tabela de Usuários</h3>

<div class="row">
        <div class="col-lg-4"> 
           <br> <br>
            <div class="row">
                <div class="col-lg-12"> 
                                  
                    <button type="button" class="btn btn-warning btn-icon-text gerar_carteira">
                        
                        Gerar Carteira
                    </button> 
                </div>
                
            </div>
        </div>
</div>

<table class="table table-bordered table-striped" id="tabela_usuarios">
    <thead>
                <tr>
                    <th>#</th>
                    <th></th>
                    <th class="sticky-col">Nome</th>
                    <th>Turma/Setor</th>
                    <th>Nível</th>
                    <th>Fone</th>
                    <th>Login</th>
                    <th class='text-center no-print'>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Conexão com o banco de dados
                $mysqli = new SQLite3('../../data/bibliotecario.db');

                // Consulta todos os registros da tabela cad_usuario e junta com a tabela cad_turma para obter o nome da turma
                $query = "
                    SELECT u.id, u.nome, t.nome AS turma, u.fone, u.login, u.nivel 
                    FROM cad_usuario u
                    LEFT JOIN cad_turma t ON u.turma = t.id ORDER BY u.nome
                ";
                $result = $mysqli->query($query);
                $ordem = 1;
                // Verifica se há registros e os exibe
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $id = htmlspecialchars($row['id'] ?? '');
                    $nome = ($row['nome'] ?? '');
                    $turma = htmlspecialchars($row['turma'] ?? '');
                    $fone = htmlspecialchars($row['fone'] ?? '');
                    $login = htmlspecialchars($row['login'] ?? '');
                    $nivel = htmlspecialchars($row['nivel'] ?? '');

                    // Define o valor do campo Nível baseado no nível
                    switch ($nivel) {
                        case 1:
                            $nivel_texto = 'Administrador(a)';
                            break;
                        case 2:
                            $nivel_texto = 'usuário';
                            break;
                        
                        default:
                            $nivel_texto = 'Desconhecido';
                            break;
                    }
                    echo "<tr>";
                    echo "<td style='color:black'>$ordem</td>";
                    echo "<td style='color:black'><input type='checkbox'></td>";
                    echo "<td style='color:black' class='sticky-col'>$nome </td>";

                    echo "<td style='color:black'>$turma</td>";
                    echo "<td style='color:black'>$nivel_texto</td>";
                    echo "<td style='color:black'>$fone</td>";
                    echo "<td style='color:black'>$login</td>";
                    echo "<td class='action-buttons text-center no-print'>
                        <button type='button' class='btn btn-warning btn-rounded btn-icon edit-btn' data-id='$id'>
                            <i class='fa fa-edit'></i> <!-- Ícone para editar -->
                        </button>
                        <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id='$id'>
                            <i class='fa fa-trash'></i> <!-- Ícone para excluir -->
                        </button>
                        <button type='button' class='btn btn-success btn-rounded btn-icon carteira-btn' data-id='$id'>
                            <i class='fa fa-photo'></i> <!-- Ícone para carteirinha -->
                        </button>
                    </td>";

                    echo "</tr>";
                    $ordem++;
                }
                ?>
            </tbody>

</table>

<!-- Modal para Carteirinha -->
<div id="modalCarteirinha" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalCarteirinhaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarteirinhaLabel">Carteirinha do Usuário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card"  >
                    <div id="carteira" style="width: 450px; height: auto; border: 1px solid #ccc; border-radius: 10px; padding: 15px; background: linear-gradient(to right, #d4edda, #c3e6cb);">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div style="border: 1px solid #000; width: 100px; height: 120px; margin: auto;">
                                    <img id="fotoUsuario" src="" alt="Foto do Usuário" class="img-fluid" style="width: 100px; height: 120px;">
                                </div>
                                <p style="margin-top: 5px; font-size: 12px;">Foto 3x4</p>
                            </div>
                            <div class="col-md-9" style="padding-left: 30px; margin-top: -10px">
                                <h5 style="font-weight: bold; text-transform: uppercase;">SISTEMA BIBLIOTECÁRIO</h5>
                                <p style="margin: 5px 0;"><strong>Nome Completo:</strong> <span id="nomeUsuario"></span></p>
                                <p style="margin: 5px 0;"><strong>Turma/Setor:</strong> <span id="turmaUsuario"></span></p>
                                <p style="margin: 5px 0;"><strong>Nível:</strong> <span id="nivelUsuario"></span></p>
                                <p style="margin: 5px 0;"><strong>Telefone:</strong> <span id="foneUsuario"></span></p>
                                <p style="margin: 5px 0;"><strong>Validade:</strong> Dezembro/<?php echo date("Y"); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="imprimir_carteirinha">Imprimir</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Carteirinhas -->
<div id="modalCarteirinhas" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalCarteirinhasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarteirinhasLabel">Carteirinhas dos Usuários Selecionados</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="carteirinhasContainer" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between;">
                    <!-- Carteirinhas geradas dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="imprimirTodasCarteirinhas">Imprimir</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#tabela_usuarios').DataTable({
            language: {
                url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
            },
            pageLength: 50, // Define o número de registros exibidos na primeira página
            dom: '<"dt-buttons-container"B>lfrtip', // Ativa o uso dos botões
            buttons: [
																		
						{
						  extend: "print",
                          text: "Imprimir",
						  className: " btn-info"
						},
					  ],
            columnDefs: [
            {
                targets: 0, // Primeira coluna
                orderable: false, // Desabilita a ordenação nesta coluna
                searchable: false, // Desabilita a pesquisa nesta coluna
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Calcula o índice da linha (começa em 0) e soma 1
                }
            }  
        ],
        order: [[1, 'asc']] // Define a segunda coluna como padrão para ordenação
        });

        // Evento ao clicar no botão "carteira-btn"
        $('.carteira-btn').on('click', function() {
                var id = $(this).data('id'); // Obtém o ID do usuário
                $.ajax({
                    type: 'POST',
                    url: 'cad_usuarios/get_usuario.php', // Endpoint para buscar os dados do usuário
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            //alert(response.data.turma)
                            // Preenche o modal com os dados do usuário
                            // Atualiza o caminho da foto do usuário
                            $('#fotoUsuario').attr(
                                'src', 
                                response.data.foto ? '../uploads/imagens/' + response.data.foto : '../img/default.jpg'
                            ); 
                            $('#nomeUsuario').text(response.data.nome); // Nome do usuário
                            $('#turmaUsuario').text(response.data.turma_nome || 'Não Informada'); // Turma
                            $('#nivelUsuario').text(response.data.nivel); // Nível
                            $('#foneUsuario').text(response.data.fone || 'Não Informado'); // Telefone

                            // Exibe o modal
                            $('#modalCarteirinha').modal('show');
                        } else {
                            Swal.fire('Erro', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Erro', 'Não foi possível carregar os dados do usuário.', 'error');
                    }
                });
            });

           
            // Função para editar um registro
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                $('#tabela').load("cad_usuarios/editar_usuario.php?id="+id)
            });

            // Função para excluir um registro
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                var row = $(this).closest('tr'); // captura a linha atual
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'POST',
                            url: 'cad_usuarios/delete_usuario.php',
                            data: { id: id },
                            success: function(response) {
                                
                                if (response.status === 'success') {
                                    Swal.fire(
                                        'Excluído!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        row.fadeOut(300, function() {
                                            $(this).remove(); // remove a linha atual
                                        });
                                    });
                                } else {
                                    Swal.fire(
                                        'Erro!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire(
                                    'Erro!',
                                    'Erro ao excluir o usuário.',
                                    'error'
                                );
                                console.error(xhr);
                            }
                        });
                    }
                });
            });

        $(".gerar_carteira").on("click", function () {
                const selectedUsers = [];
                $("#tabela_usuarios input[type='checkbox']:checked").each(function () {
                    const userId = $(this).closest("tr").find(".carteira-btn").data("id");
                    if (userId) selectedUsers.push(userId);
                });

                if (selectedUsers.length === 0) {
                    Swal.fire("Atenção", "Nenhum usuário selecionado.", "warning");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "cad_usuarios/get_usuarios.php", // Endpoint para buscar dados de vários usuários
                    data: { ids: selectedUsers },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            const users = response.data;
                            const container = $("#carteirinhasContainer");
                            container.empty();

                            users.forEach((user) => {
                                const carteiraHtml = `
                                    <div class="" style="width: 30%; border: 1px solid #ccc;  padding: 10px; margin-left: -5px; margin-right: -5px; background: linear-gradient(to right, #d4edda, #c3e6cb);">
                                        <table>
                                           <tr>
                                              <td>
                                                <div style="border: 1px solid #000; width: 60px; height: auto; margin-top: -5px; ">
                                                    <img src="../uploads/imagens/${user.foto || "default.jpg"}" alt="Foto do Usuário" class="img-fluid" style="width: 100%; height: auto;">
                                                </div>
                                              </td>
                                              <td>
                                                 <p style="font-weight: bold; text-transform: uppercase; text-align: left; margin-left: 5px;">SISTEMA BIBLIOTECÁRIO</p>
                                                  
                                              </td>
                                           </tr>
                                            <tr>
                                              <td colspan="2">
                                              <p style="margin: 5px; font-size: 14px;"><strong>Nome:</strong> ${user.nome}</p>
                                                <p style="margin: 5px; font-size: 12px;"><strong>Setor:</strong> ${user.turma_nome || "Não Informada"}<br>
                                                <strong>Nível:</strong> ${user.nivel}<br>
                                                <strong>Telefone:</strong> ${user.fone || "Não Informado"}<br>
                                                <strong>Validade:</strong> Dezembro/${new Date().getFullYear()}</p>
                                              </td>
                                              
                                           </tr>
                                        </table>
                                        
                                    </div>
                                `;
                                container.append(carteiraHtml);
                            });

                            // Exibe o modal com as carteirinhas
                            $("#modalCarteirinhas").modal("show");
                        } else {
                            Swal.fire("Erro", response.message, "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire("Erro", "Não foi possível carregar os dados dos usuários.", "error");
                    }
                });
    });

    // Evento para imprimir as carteirinhas
    $("#imprimirTodasCarteirinhas").on("click", function () {
        
        $('#carteirinhasContainer').print({
                iframe : false,
                mediaPrint : false,
                noPrintSelector : ".avoid-this",
                //add título 
                prepend : "",
                append : ""
            });
    });            
           
})    

        $('#imprimir_carteirinha').on('click', function () {
            $('#carteira').print({
                iframe : false,
                mediaPrint : false,
                noPrintSelector : ".avoid-this",
                //add título 
                prepend : "",
                append : ""
            });

        })
    </script>


