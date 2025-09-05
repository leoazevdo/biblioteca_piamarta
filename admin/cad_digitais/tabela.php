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

// Consulta com Limite, Offset e Filtros
$query = "
    SELECT a.id, a.titulo,a.imagem_caminho,a.sinopse, a.autor, a.arquivo_caminho, a.editora, c.titulo AS categoria
    FROM cad_acervo_digital a
    LEFT JOIN cad_categoria c ON a.categoria = c.id
    
";
$result = $db->query($query);
?>
<br>
<h3>Tabela de Acervos Digitais</h3>
<table class="table table-bordered table-striped" id="tabelaDigitais">
    <thead>
        <tr>
            <th>#</th>
            <th class="text-center">Capa</th>
            <th>Título</th>
            <th>Sinopse</th>
            <th>Visualizar</th>
                
                
            <th>Categoria</th>
            <th class='text-center no-print'>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $ordem = 1;
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $id = htmlspecialchars($row['id']);
                $titulo = htmlspecialchars($row['titulo']);
                $autor = htmlspecialchars($row['autor']);
                $sinopse = htmlspecialchars($row['sinopse']);
                $editora = htmlspecialchars($row['editora']);
                $categoria = htmlspecialchars($row['categoria']);
                $capa = htmlspecialchars($row['imagem_caminho']);
                $arquivo = htmlspecialchars($row['arquivo_caminho']);
             
        ?>
        <tr>
            <td><?php echo $ordem ?></td>
            <td class="text-center"><img src='../uploads/imagens/<?php echo $capa ?>' width='100'></td>
            <td><?php echo $titulo ?></td>
            <td><?php echo $sinopse ?></td>
            <td><a href="<?php echo '../uploads/arquivos/'.$arquivo ?>" target="blanck">Abrir</a></td>
            <td><?php echo $categoria ?></td>

            <td class="text-center">
                <button type='button' class='btn btn-warning btn-rounded btn-icon edit-btn' data-id=' <?php echo  $id ?> '>Editar</button>
                <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id=' <?php echo  $id ?> '>Deletar</button>
            </td>
        </tr>

        <?php
        $ordem++;
            }

        ?>
    </tbody>
</table>

<script>
        $(document).ready(function() {
            $('#tabelaDigitais').DataTable({
                language: {
                    url: "../arquivos/vendors/datatables-pt-BR/pt-BR.json"
                },
                pageLength: 10, // Define o número de registros exibidos na primeira página
                dom: 'lfrtip', // Ativa o uso dos botões
            
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
            order: [[3, 'asc']] // Define a terceira coluna como padrão para ordenação
            });
            // Função para editar um registro
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                
                // Lógica para editar o registro
                $('#tabela').load('cad_digitais/editar_digital.php?id=' + encodeURIComponent(id));

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
                            url: 'cad_digitais/delete_digital.php',
                            data: { id: id },
                            success: function(response) {
                                var res = response;
                                if (res.status === 'success') {
                                    Swal.fire(
                                        'Excluído!',
                                        res.message,
                                        'success'
                                    ).then(() => {
                                        //location.reload();
                                        $('#tabela').load("cad_digitais/tabela.php")
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
                                    'Erro ao excluir o acervo.',
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

