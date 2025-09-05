<?php
$db = new SQLite3('../../data/bibliotecario.db');

// Ver a estrutura da tabela
$result = $db->query("PRAGMA table_info(cad_acervo_digital)");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}
