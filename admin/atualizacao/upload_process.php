<?php
session_start();
set_time_limit(0); // Permite que o script rode por mais tempo, se necessário
ini_set('memory_limit', '512M'); // Aumenta limite de memória, se necessário

// --- Configurações ---
// !!! AJUSTE ESTE CAMINHO para apontar CORRETAMENTE para o seu banco V3 !!!
$v3_db_path = __DIR__ . '/../../data/bibliotecario.db'; // Caminho para o banco V3 ATUAL
// $v3_db_path = 'D:/PHP-DESKTOP/bibliotecario-2025/www/data/bibliotecario.db'; // Exemplo de caminho absoluto se necessário

// !!! AJUSTE ESTE CAMINHO para a pasta onde deseja salvar uploads temporários !!!
$upload_dir = __DIR__ . '/uploads/'; // Pasta temporária para o upload
// $upload_dir = 'D:/PHP-DESKTOP/bibliotecario-2025/www/admin/atualizacao/uploads/'; // Exemplo de caminho absoluto se necessário

$v1_uploaded_file = '';

// --- Mapeamento de Tabelas e Colunas ---
$table_map = [
    'cad_categoria' => [],
    'cad_curso' => [],
    'cad_escola' => [],
    'cad_usuario' => [],
    'cad_acervo' => [],
    'cad_emprestimo' => [ // Nome da tabela na v1
        'target' => 'emprestimos',   // Nome da tabela na v3
        'cols' => [                 // Mapeamento específico de colunas v1 => v3
            'id' => 'id',
            'id_usuario' => 'user_id',
            'id_acervo' => 'acervo_id',
            'data_atual' => 'data_atual',
            'data_devolucao' => 'data_devolucao',
            // Colunas V1 ignoradas: hora, devolvido_em, id_escola, mes, ano
        ],
        'defaults_v3' => [ // Valores padrão para colunas V3 NOT NULL sem correspondente em V1
            'total_dias' => 0,
            'dia_semana' => '',
            'devolvido' => 'Não',
            'data_entregue' => null // Permite NULL na v3
        ]
    ]
    // sqlite_sequence é ignorada
];

// --- Funções Auxiliares ---
function update_progress($progress, $message) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['migration_progress'] = $progress;
    $_SESSION['migration_message'] = $message;
    session_write_close(); 
    session_start(); 
}

function finish_request($success, $message) {
    global $v1_uploaded_file;
    if (!empty($v1_uploaded_file) && file_exists($v1_uploaded_file)) {
        @unlink($v1_uploaded_file);
    }
    if (session_status() != PHP_SESSION_NONE) {
        unset($_SESSION['migration_progress']);
        unset($_SESSION['migration_message']);
        session_write_close();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// --- Validação Inicial ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    finish_request(false, 'Método inválido.');
}

if (!isset($_FILES['dbfile']) || $_FILES['dbfile']['error'] !== UPLOAD_ERR_OK) {
    $error_map = [
        UPLOAD_ERR_INI_SIZE   => "Arquivo excede upload_max_filesize.",
        UPLOAD_ERR_FORM_SIZE  => "Arquivo excede MAX_FILE_SIZE.",
        UPLOAD_ERR_PARTIAL    => "Upload parcial.",
        UPLOAD_ERR_NO_FILE    => "Nenhum arquivo enviado.",
        UPLOAD_ERR_NO_TMP_DIR => "Pasta temporária ausente.",
        UPLOAD_ERR_CANT_WRITE => "Falha ao escrever no disco.",
        UPLOAD_ERR_EXTENSION  => "Extensão PHP interrompeu o upload.",
    ];
    $error_code = $_FILES['dbfile']['error'] ?? UPLOAD_ERR_NO_FILE;
    finish_request(false, 'Erro no upload: ' . ($error_map[$error_code] ?? 'Erro desconhecido (' . $error_code . ').'));
}

// --- Cria diretório de upload se não existir ---
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0775, true)) {
        finish_request(false, "Não foi possível criar o diretório de uploads: $upload_dir. Verifique as permissões.");
    }
}
if (!is_writable($upload_dir)) {
    finish_request(false, "O diretório de uploads não tem permissão de escrita: $upload_dir");
}

$v1_uploaded_file = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . 'uploaded_v1_' . uniqid() . '.db';

if (!move_uploaded_file($_FILES['dbfile']['tmp_name'], $v1_uploaded_file)) {
    finish_request(false, 'Falha ao mover o arquivo enviado para ' . $v1_uploaded_file . '. Verifique as permissões.');
}

// --- Verificação básica se é um arquivo SQLite ---
$is_sqlite = false;
if (filesize($v1_uploaded_file) > 16) {
    $file_header = file_get_contents($v1_uploaded_file, false, null, 0, 16);
    if ($file_header === "SQLite format 3\x00") {
        $is_sqlite = true;
    }
}

if (!$is_sqlite) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $v1_uploaded_file);
    finfo_close($finfo);
    if ($mime !== 'application/x-sqlite3' && $mime !== 'application/vnd.sqlite3' && $mime !== 'application/octet-stream') {
         finish_request(false, 'O arquivo enviado não parece ser um banco de dados SQLite v3 válido (header incorreto ou mime type inesperado: ' . $mime . ').');
    }
}

// --- Preparar Conexões PDO ---
$pdo_v1 = null;
$pdo_v3 = null;
try {
    $pdo_v1 = new PDO('sqlite:' . $v1_uploaded_file);
    $pdo_v1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_v1->query('PRAGMA schema_version;');

    if (!file_exists($v3_db_path)) {
        throw new Exception("Banco de dados V3 não encontrado em: $v3_db_path. Verifique o caminho no script.");
    }
    if (!is_writable(dirname($v3_db_path))) {
         throw new Exception("A pasta do banco de dados V3 não tem permissão de escrita: " . dirname($v3_db_path));
    }
    if (file_exists($v3_db_path) && !is_writable($v3_db_path)) {
         throw new Exception("O arquivo do banco de dados V3 não tem permissão de escrita: " . $v3_db_path);
    }

    $pdo_v3 = new PDO('sqlite:' . $v3_db_path);
    $pdo_v3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_v3->query('PRAGMA schema_version;');

} catch (Exception $e) {
    finish_request(false, 'Erro ao conectar/validar bancos de dados: ' . $e->getMessage());
}

// --- Iniciar Migração ---
$total_tables_to_migrate = count($table_map);
$tables_processed = 0;
$global_start_time = microtime(true);

update_progress(0, 'Iniciando migração...');

try {
    // $pdo_v3->exec('PRAGMA foreign_keys = OFF;');

    foreach ($table_map as $v1_table => $map_info) {
        $v3_table = $map_info['target'] ?? $v1_table;
        $cols_map = $map_info['cols'] ?? [];
        $defaults_v3 = $map_info['defaults_v3'] ?? [];

        $current_table_message_prefix = "Tabela $v1_table -> $v3_table: ";
        update_progress(
            ($tables_processed / $total_tables_to_migrate) * 100,
            $current_table_message_prefix . "Iniciando..."
        );

        // Verificar se a tabela V1 existe
        try {
            $stmt_check_v1 = $pdo_v1->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='$v1_table' LIMIT 1");
            if ($stmt_check_v1->fetchColumn() === false) {
                update_progress(
                    (($tables_processed + 1) / $total_tables_to_migrate) * 100,
                    $current_table_message_prefix . "Não encontrada na V1. Pulando."
                );
                $tables_processed++;
                continue;
            }
        } catch (PDOException $e) {
             throw new Exception("Erro ao verificar tabela '$v1_table' na V1: " . $e->getMessage());
        }

        // Verificar se a tabela V3 existe
        try {
            $stmt_check_v3 = $pdo_v3->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='$v3_table' LIMIT 1");
            if ($stmt_check_v3->fetchColumn() === false) {
                update_progress(
                    (($tables_processed + 1) / $total_tables_to_migrate) * 100,
                    $current_table_message_prefix . "Tabela alvo '$v3_table' não encontrada na V3. Pulando."
                );
                $tables_processed++;
                continue;
            }
        } catch (PDOException $e) {
             throw new Exception("Erro ao verificar tabela '$v3_table' na V3: " . $e->getMessage());
        }

        // Contar registros na V1 para o progresso da tabela atual
        $total_rows = 0;
        try {
            $total_rows = (int) $pdo_v1->query("SELECT COUNT(*) FROM `$v1_table`")->fetchColumn();
        } catch (PDOException $e) {
             throw new Exception("Erro ao contar registros na tabela '$v1_table' (V1): " . $e->getMessage());
        }

        if ($total_rows === 0) {
            update_progress(
                (($tables_processed + 1) / $total_tables_to_migrate) * 100,
                $current_table_message_prefix . "Vazia na V1. Pulando."
            );
            $tables_processed++;
            continue;
        }

        // Limpar tabela V3 (CUIDADO: isto apaga os dados existentes na tabela V3)
        try {
            $pdo_v3->exec("DELETE FROM `$v3_table`");
        } catch (PDOException $e) {
             throw new Exception("Erro ao limpar tabela '$v3_table' (V3): " . $e->getMessage());
        }

        // Obter colunas da V1
        try {
            $stmt_cols_v1 = $pdo_v1->query("PRAGMA table_info(`$v1_table`)");
            $v1_columns = $stmt_cols_v1->fetchAll(PDO::FETCH_COLUMN, 1);
            if (empty($v1_columns)) {
                throw new Exception("Não foi possível obter a lista de colunas da tabela '$v1_table' (V1).");
            }
        } catch (PDOException $e) {
             throw new Exception("Erro ao obter colunas da tabela '$v1_table' (V1): " . $e->getMessage());
        }
        
        // Obter colunas da tabela V3 (para ignorar campos não existentes)
        try {
            $stmt_cols_v3 = $pdo_v3->query("PRAGMA table_info(`$v3_table`)");
            $v3_columns = $stmt_cols_v3->fetchAll(PDO::FETCH_COLUMN, 1);
            if (empty($v3_columns)) {
                throw new Exception("Não foi possível obter a lista de colunas da tabela '$v3_table' (V3).");
            }
        } catch (PDOException $e) {
             throw new Exception("Erro ao obter colunas da tabela '$v3_table' (V3): " . $e->getMessage());
        }

        // Preparar mapeamento para INSERT na V3: considerar somente as colunas que existem na tabela V3
        $insert_cols_v3 = [];
        $v1_to_v3_col_mapping = [];

        if (empty($cols_map)) { // Sem mapa específico, assume nomes iguais (apenas as colunas em comum serão usadas)
            $common_columns = array_intersect($v1_columns, $v3_columns);
            $insert_cols_v3 = array_values($common_columns);
            foreach ($common_columns as $col) {
                $v1_to_v3_col_mapping[$col] = $col;
            }
        } else { // Usa o mapa de colunas fornecido, mas inclui apenas se a coluna de destino existe na V3
            foreach ($cols_map as $v1_col => $v3_col) {
                if (in_array($v1_col, $v1_columns) && in_array($v3_col, $v3_columns)) {
                    $insert_cols_v3[] = $v3_col;
                    $v1_to_v3_col_mapping[$v1_col] = $v3_col;
                }
            }
        }
        // Adicionar colunas com valores padrão (apenas se existirem na V3)
        foreach ($defaults_v3 as $v3_col => $default_val) {
            if (in_array($v3_col, $v3_columns) && !in_array($v3_col, $insert_cols_v3)) {
                $insert_cols_v3[] = $v3_col;
            }
        }

        if (empty($insert_cols_v3)) {
             throw new Exception("Nenhuma coluna foi definida para inserção na tabela '$v3_table' (V3). Verifique o mapeamento e as colunas V1.");
        }

        // Construir INSERT para a tabela V3
        $placeholders = implode(', ', array_fill(0, count($insert_cols_v3), '?'));
        $sql_insert_v3 = "INSERT INTO `$v3_table` (" . implode(', ', array_map(function($c) { return "`$c`"; }, $insert_cols_v3)) . ") VALUES ($placeholders)";

        // Inicia a transação na V3 para esta tabela
        $pdo_v3->beginTransaction();
        $rows_processed = 0;

        try {
            $stmt_select = $pdo_v1->query("SELECT " . implode(', ', array_map(function($c) { return "`$c`"; }, $v1_columns)) . " FROM `$v1_table`");
            $stmt_insert = $pdo_v3->prepare($sql_insert_v3);

            while ($row_v1 = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
                $values_to_insert = [];
                // Mapeia os valores na ordem das colunas definidas para INSERT na V3
                foreach ($insert_cols_v3 as $v3_col_name) {
                    $found_value = false;
                    foreach ($v1_to_v3_col_mapping as $v1_key => $v3_mapped_key) {
                        if ($v3_mapped_key === $v3_col_name) {
                            if (array_key_exists($v1_key, $row_v1)) {
                                $values_to_insert[] = $row_v1[$v1_key];
                                $found_value = true;
                            } else {
                                $values_to_insert[] = null;
                                $found_value = true;
                                error_log("Aviso: Chave V1 '$v1_key' (mapeada para V3 '$v3_col_name') não encontrada na linha de dados para $v1_table.");
                            }
                            break;
                        }
                    }
                    if (!$found_value) {
                         if (array_key_exists($v3_col_name, $defaults_v3)) {
                            $values_to_insert[] = $defaults_v3[$v3_col_name];
                            $found_value = true;
                         }
                    }
                    if (!$found_value) {
                         $values_to_insert[] = null;
                         error_log("Aviso: Coluna V3 '$v3_col_name' em '$v3_table' não mapeada e sem valor padrão definido. Usando NULL.");
                    }
                }

                if (count($values_to_insert) !== count($insert_cols_v3)) {
                    $pdo_v3->rollBack();
                    throw new Exception($current_table_message_prefix . "Erro interno: Número de valores (" . count($values_to_insert) . ") não corresponde ao número de colunas (" . count($insert_cols_v3) . ") para inserir.");
                }

                $stmt_insert->execute($values_to_insert);
                $rows_processed++;

                if ($rows_processed % 50 == 0 || $rows_processed == $total_rows) {
                    $table_progress = ($rows_processed / $total_rows) * 100;
                    $global_progress = (($tables_processed + ($table_progress / 100)) / $total_tables_to_migrate) * 100;
                    update_progress(
                        $global_progress,
                        sprintf(
                            "%s %d/%d registros (%.1f%%)",
                            $current_table_message_prefix,
                            $rows_processed,
                            $total_rows,
                            $table_progress
                        )
                    );
                }
            }

            $pdo_v3->commit();

        } catch (Exception $e) {
            if ($pdo_v3->inTransaction()) {
                $pdo_v3->rollBack();
            }
            throw new Exception($current_table_message_prefix . "Erro durante a cópia de dados (linha $rows_processed / $total_rows): " . $e->getMessage(), 0, $e);
        } finally {
            if (isset($stmt_select)) {
                $stmt_select->closeCursor();
            }
        }

        $tables_processed++;
    } // Fim do loop de tabelas

    // $pdo_v3->exec('PRAGMA foreign_keys = ON;');
    // $pdo_v3->exec('VACUUM;');

    try {
        // Utilizamos INSERT OR IGNORE para evitar erro se já existir um registro com login 'admin'
        $sql_insert_admin = "INSERT OR IGNORE INTO cad_usuario (nome, login, nivel) VALUES (?, ?, ?)";
        $stmt_admin = $pdo_v3->prepare($sql_insert_admin);
        $stmt_admin->execute(['admin', 'admin', 1]);
    } catch (Exception $e) {
        error_log("Erro ao inserir usuário admin: " . $e->getMessage());
    }

    $total_time = microtime(true) - $global_start_time;
    $final_message = sprintf("Migração concluída com sucesso em %.2f segundos.", $total_time);
    update_progress(100, $final_message);
    finish_request(true, $final_message);

} catch (Exception $e) {
    $error_message = "Erro fatal na migração: " . $e->getMessage();
    update_progress($_SESSION['migration_progress'] ?? 0, "Erro: " . $e->getMessage());
    finish_request(false, $error_message);
} finally {
    $pdo_v1 = null;
    $pdo_v3 = null;
    if (!empty($v1_uploaded_file) && file_exists($v1_uploaded_file)) {
        @unlink($v1_uploaded_file);
    }
}


?>
