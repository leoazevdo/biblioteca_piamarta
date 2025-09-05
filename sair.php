<?php
session_start(); // Inicia a sessão

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a página inicial
header("Location: painel.php");
exit();
?>
