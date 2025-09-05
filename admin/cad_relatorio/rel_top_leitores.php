<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$db = new SQLite3('../../data/bibliotecario.db');

$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? '';

// Monta o filtro dinâmico
$filtro = " WHERE strftime('%Y', e.data_atual) = :ano ";
if (!empty($mes)) {
    // garante 2 dígitos para o mês
    $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
    $filtro .= " AND strftime('%m', e.data_atual) = :mes ";
}

$query = "
    SELECT u.nome, COUNT(e.id) AS total_emprestimos
    FROM emprestimos e
    JOIN cad_usuario u ON u.id = e.user_id
    $filtro
    GROUP BY e.user_id
    ORDER BY total_emprestimos DESC
    LIMIT 10
";

$stmt = $db->prepare($query);
$stmt->bindValue(':ano', $ano, SQLITE3_TEXT);

if (!empty($mes)) {
    $stmt->bindValue(':mes', $mes, SQLITE3_TEXT);
}

$result = $stmt->execute();
?>

<h3>📖 Top Leitores <?php echo $mes ? "$mes/$ano" : "de $ano"; ?></h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Nome</th>
            <th>Total de Empréstimos</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $rank = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>
                    <td>{$rank}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['total_emprestimos']}</td>
                  </tr>";
            $rank++;
        }

        if ($rank === 1) {
            echo "<tr><td colspan='3' class='text-center'>Nenhum empréstimo encontrado neste período.</td></tr>";
        }
        ?>
    </tbody>
</table>
