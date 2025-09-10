<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Cria a conexão com o banco de dados SQLite3
$db = new SQLite3('../../data/bibliotecario.db');


$mes = isset($_GET['mes']) ? $_GET['mes'] : null;
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

// Query corrigida para ranking de alunos
if ($mes) {
    $query = "SELECT 
        e.user_id,
        a.nome as nome_aluno,
        COUNT(*) as total_emprestimos
    FROM emprestimos e
    INNER JOIN user_id a ON e.id = a.id
    WHERE strftime('%Y', e.data_entregue) = :ano 
    AND strftime('%m', e.data_entregue) = :mes
    GROUP BY e.user_id, a.nome
    ORDER BY total_emprestimos DESC
    LIMIT :limite";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':ano', $ano, SQLITE3_TEXT);
    $stmt->bindValue(':mes', sprintf('%02d', $mes), SQLITE3_TEXT);
    $stmt->bindValue(':limite', $limite, SQLITE3_INTEGER);
} else {
    $query = "SELECT 
        e.id,
        a.nome as nome_aluno,
        COUNT(*) as total_emprestimos
    FROM emprestimos e
    INNER JOIN cad_usuario a ON e.user_id = a.id
    WHERE strftime('%Y', e.data_entregue) = :ano
    GROUP BY e.user_id, a.nome
    ORDER BY total_emprestimos DESC
    LIMIT :limite";
    
    $stmt = $db->prepare($query);
   // $stmt->bindValue(':ano', $ano, SQLITE3_TEXT);
    $stmt->bindValue(':limite', $limite, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-trophy"></i> Ranking de Alunos - <?php echo $mes ? " / ".date('F', mktime(0, 0, 0, $mes, 1)) : " (Ano todo)"; ?></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr style="background-color: #337ab7; color: white;">
                                <th style="text-align: center;">Posição</th>
                                <th>Nome do Aluno</th>
                                <th style="text-align: center;">Total de Empréstimos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicao = 1;
                            while ($row = $result->fetchArray(SQLITE3_ASSOC)): 
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <span class="badge badge-<?php echo $posicao <= 3 ? 'warning' : 'primary'; ?>">
                                        <?php echo $posicao; ?>°
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['nome_aluno']); ?></td>
                                <td style="text-align: center;">
                                    <strong><?php echo $row['total_emprestimos']; ?></strong>
                                </td>
                            </tr>
                            <?php 
                            $posicao++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" onclick="window.print();">
                            <i class="fa fa-print"></i> Imprimir
                        </button>
                        <button class="btn btn-info" onclick="exportarExcel();">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportarExcel() {
    // Implementar exportação para Excel se necessário
    alert('Funcionalidade de exportação ainda em desenvolvimento');
}
</script>
