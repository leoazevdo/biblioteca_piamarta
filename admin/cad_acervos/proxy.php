<?php
$isbn = $_GET['isbn']; // Recebe o ISBN da requisição
$url = "https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn";

// Faz a requisição ao Google Books
$response = file_get_contents($url);

// Retorna a resposta para o frontend
header('Content-Type: application/json');
echo $response;
?>