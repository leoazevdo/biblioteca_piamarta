<?php
// Conexão com o banco
$db = new SQLite3('../../data/bibliotecario.db');

// Parâmetros vindos do DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// 1. Contagem total de registros (sem filtro)
$totalRecords = $db->querySingle("SELECT COUNT(*) FROM cad_acervo");

// 2. Filtro de busca (Título ou Autor)
$filterQuery = "";
if(!empty($searchValue)){
    $filterQuery = " AND (a.titulo LIKE '%$searchValue%' OR a.autor LIKE '%$searchValue%') ";
}

// Contagem total com filtro
$totalRecordwithFilter = $db->querySingle("SELECT COUNT(*) FROM cad_acervo a WHERE 1 $filterQuery");

// 3. Consulta principal (Pegando exatamente os campos necessários)
$query = "SELECT a.id, a.capa, a.titulo, a.autor, a.categoria, a.setor, a.estante, a.prateleira, 
                 c.titulo AS titulo_categoria, t.descricao AS tipo
          FROM cad_acervo a
          LEFT JOIN cad_categoria c ON a.categoria = c.id
          LEFT JOIN cad_tipo t ON a.tipo = t.id
          WHERE 1 $filterQuery
          ORDER BY a.titulo ASC
          LIMIT $start, $rowperpage";

$results = $db->query($query);
$data = array();

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $id = $row['id'];
    $capa = $row['capa'];
    
    // --- Lógica da Capa (Igual ao seu original) ---
    $caminhoFinal = '';
    if (preg_match('/^data:image\/(jpeg|png|gif|bmp|webp);base64,/', $capa)) {
        $caminhoFinal = $capa;
    } elseif ($capa === '../master/images/book.png' || $capa === '') {
        $caminhoFinal = '../img/book.png';
    } else {
        $caminhoFinal = '../uploads/imagens/' . $capa;
    }

    // Montando as colunas para o DataTables
    $data[] = array(
        "id"        => $id,
        "checkbox"  => "<input type='checkbox' class='row-select' data-id='$id'>",
        "capa"      => "<img src='$caminhoFinal' width='50'>",
        "titulo"    => htmlspecialchars($row['titulo']),
        "categoria" => htmlspecialchars($row['titulo_categoria']),
        "setor"     => htmlspecialchars($row['setor']),
        "estante"   => htmlspecialchars($row['estante']),
        "prateleira"=> htmlspecialchars($row['prateleira']),
        // --- BOTÕES ORIGINAIS RESTAURADOS ---
        "acoes"     => "
            <td class='action-buttons text-center no-print' style='white-space: nowrap;'>
                <button type='button' class='btn btn-warning btn-rounded btn-icon edita-btn' data-id='$id'>Editar</button>
                <button type='button' class='btn btn-danger btn-rounded btn-icon delete-btn' data-id='$id'>Excluir</button>
                <button type='button' class='btn btn-primary btn-rounded btn-icon ver-btn' data-id='$id'><i class='fa fa-eye'></i></button>
            </td>"
    );
}

// Resposta final
$response = array(
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordwithFilter,
    "data" => $data
);

echo json_encode($response);