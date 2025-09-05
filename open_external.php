<?php
// Obtém o nome do host da máquina servidor
$hostName = gethostname();

// Obtém o endereço IP associado ao nome do host
$serverIp = gethostbyname($hostName);
$url = "http://".$serverIp.":54007/painel.php";
exec("start $url");

?>