<?php
require_once "inc/php/functions.php";

$idempresa = $_GET['idempresa']; // Exemplo: ?empresa=empresa1

$sql = "SELECT nomefantasia, corsistema, iconelateral, idempresa, sigla FROM empresa WHERE status = 'ATIVO'";
$empresas = d::b()->query($sql);
while($_empresa = mysqli_fetch_assoc($empresas)){
    $configs['empresa' . $_empresa['idempresa']] = [
        'name'  => $_empresa['nomefantasia'],
        'theme' => $_empresa['corsistema'],
        'logo'  => $_empresa['iconelateral'],
        'sigla'  => $_empresa['sigla']
    ];
}

$dados = $configs[$empresa] ?? $configs['empresa'.$idempresa];

header('Content-Type: application/json');
echo json_encode([
    "name" => $dados['name'],
    "short_name" => $dados['name'],
    "start_url" => "./?empresa=$idempresa",
    "display" => "standalone",
    "theme_color" => $dados['theme'],
    "background_color" => "#ffffff",
    "icons" => [
        [
            "src" => "./inc/img/" . $dados['sigla']."-192.png",
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => "./inc/img/" . $dados['sigla']."-512.png",
            "sizes" => "512x512",
            "type" => "image/png"
        ]
    ],
]);
?>
