<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
} else {
    // Conexão com o banco de dados
    $db = new SQLite3('../../data/bibliotecario.db');
    $id_usuario = $_SESSION['user_id'];
}

// Consulta já com a verificação de disponibilidade
$query = "
    SELECT 
        a.id, 
        a.titulo,
        a.imagem_caminho,
        a.sinopse, 
        a.autor, 
        a.arquivo_caminho, 
        a.editora, 
        c.titulo AS categoria,
        CASE 
            WHEN EXISTS (
                SELECT 1 
                FROM emprestimos e 
                WHERE e.id_livro = a.id 
                  AND e.devolvido = 'Não'
            ) THEN 'Indisponível'
            ELSE 'Disponível'
        END AS status
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
            <th>Resumo</th>
            <th class="text-center">Visualizar</th>
            <th>Categoria</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $ordem = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $titulo = htmlspecialchars($row['titulo']);
            $sinopse = htmlspecialchars($row['sinopse']);
            $categoria = htmlspecialchars($row['categoria']);
            $capa = htmlspecialchars($row['imagem_caminho']);
            $arquivo = htmlspecialchars($row['arquivo_caminho']);
            $status = htmlspecialchars($row['status']);
        ?>
        <tr>
            <td><?php echo $ordem; ?></td>
            <td class="text-center"><img src='../uploads/imagens/<?php echo $capa ?>' width='100'></td>
            <td><?php echo $titulo; ?></td>
            <td><?php echo $sinopse; ?></td>
            <td class="text-center"><a href="<?php echo '../uploads/arquivos/'.$arquivo ?>" target="_blank" class="btn btn-success">Abrir</a></td>
            <td><?php echo $categoria; ?></td>
            <td>
                <?php if ($status === 'Disponível') { ?>
                    <span class="badge bg-success"><?php echo $status; ?></span>
                <?php } else { ?>
                    <span class="badge bg-danger"><?php echo $status; ?></span>
                <?php } ?>
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
        pageLength: 10,
        dom: 'lfrtip',
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            }
        ],
        order: [[2, 'asc']]
    });
});
</script>
