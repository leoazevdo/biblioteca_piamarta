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
<h3>Tabela de Turmas/Setores</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Nome da Turma/Setor</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
                <?php
               
                // Consulta todos os registros da tabela cad_turma
                $result = $db->query("SELECT * FROM cad_turma");
                $ordem = 1;
                // Verifica se há registros e os exibe
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ordem) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                    echo "<td class='text-center'>
                            <button type='button' class='btn btn-warning btn-rounded btn-icon edit-btn' data-id='" . $row['id'] . "'>Editar</button>
                            <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id='" . $row['id'] . "'>Deletar</button>
                          </td>";
                    echo "</tr>";
                    $ordem++;
                }
                ?>
    </tbody>


</table>

<script>
        $(document).ready(function() {
            // Função para editar um registro
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                // Lógica para editar o registro (você pode redirecionar para outra página ou abrir um modal)
                $('#tabela').load("cad_turma/editar_turma.php?id="+id)
            });

            // Função para excluir um registro
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
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
                            url: 'cad_turma/delete_turma.php',
                            data: { id: id },
                            success: function(response) {
                                var res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire(
                                        'Excluído!',
                                        res.message,
                                        'success'
                                    ).then(() => {
                                        //location.reload();
                                        $('#tabela').load("cad_turma/tabela.php")
                                    });
                                } else {
                                    Swal.fire(
                                        'Erro!',
                                        res.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire(
                                    'Erro!',
                                    'Erro ao excluir a turma.',
                                    'error'
                                );
                                console.error(xhr);
                            }
                        });
                    }
                });
            });
        });
    </script>

