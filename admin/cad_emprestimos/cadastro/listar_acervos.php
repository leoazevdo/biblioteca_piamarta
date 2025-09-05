<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php");
    exit();
}

try {
    // Conexão com o banco de dados SQLite
    $db = new SQLite3('../../../data/bibliotecario.db');
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados']);
    exit();
}

// Receber o valor do filtro (se existir)
$filtro = isset($_GET['filtro']) ? trim($_GET['filtro']) : '';
$filtro = SQLite3::escapeString($filtro); // Escapa o valor para prevenir SQL Injection

// Query para buscar os acervos disponíveis
$query = "
    SELECT a.id, a.titulo, a.isbn, a.sinopse, a.capa, c.titulo AS categoria
    FROM cad_acervo AS a
    LEFT JOIN cad_categoria AS c ON a.categoria = c.id
    LEFT JOIN emprestimos AS e ON a.id = e.acervo_id AND e.devolvido = 'Não'
    WHERE e.id IS NULL
";

// Adicionar o filtro à consulta, se aplicável
if (!empty($filtro)) {
    $query .= " AND (a.id LIKE '%$filtro%' OR a.titulo LIKE '%$filtro%' OR a.isbn LIKE '%$filtro%')";
}

$query .= " ORDER BY a.titulo ASC";

$result = $db->query($query);

$acervos = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $acervos[] = [
        'id' => $row['id'],
        'titulo' => $row['titulo'],
        'isbn' => $row['isbn'],
        'sinopse' => $row['sinopse'],
        'capa' => $row['capa'] ?: 'default.jpg', // Usar imagem padrão se não houver capa
        'categoria' => $row['categoria']
    ];
}

// Retorna os acervos como JSON
echo json_encode($acervos);
