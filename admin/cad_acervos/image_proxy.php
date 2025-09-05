<?php
header("Content-Type: application/json"); // Define o cabeçalho correto para JSON

if (!isset($_GET['url']) || empty($_GET['url'])) {
    echo json_encode(['error' => 'URL da imagem não fornecida']);
    exit();
}

$imageUrl = filter_var($_GET['url'], FILTER_VALIDATE_URL); // Valida a URL

if (!$imageUrl) {
    echo json_encode(['error' => 'URL inválida']);
    exit();
}

// Verifica se `allow_url_fopen` está ativado
if (!ini_get('allow_url_fopen')) {
    echo json_encode(['error' => 'allow_url_fopen está desativado no servidor']);
    exit();
}

// Baixa a imagem com cURL (melhor alternativa ao file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $imageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Seguir redirecionamentos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignorar verificação SSL (caso necessário)

$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// Verifica se o download foi bem-sucedido
if ($imageData === false || $httpCode !== 200) {
    echo json_encode(['error' => 'Falha ao baixar a imagem']);
    exit();
}

// Converte a imagem para Base64
$base64Image = base64_encode($imageData);

// Ajusta o tipo de imagem dinamicamente
$validMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($contentType, $validMimeTypes)) {
    echo json_encode(['error' => 'Tipo de imagem não suportado']);
    exit();
}

$imageSrc = 'data:' . $contentType . ';base64,' . $base64Image;

// Retorna a imagem como JSON
echo json_encode(['image_src' => $imageSrc]);
?>
