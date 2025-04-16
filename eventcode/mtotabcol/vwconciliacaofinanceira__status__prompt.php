<?
require_once(__DIR__ . "/../../form/controllers/fluxo_controller.php");
$status = cb::idempresa() == 2 ? FluxoController::buscarStatusPorModulo('conciliacaofinanceiracartoes') : [
    [
        'statustipo' => 'EM CONCILIAÇÃO',
        'rotulo' => 'Em conciliação'
    ],
    [
        'statustipo' => 'CONCILIADO',
        'rotulo' => 'Conciliado'
    ]
];

$arrRetorno = [];

foreach ($status as $item) {
    $arrRetorno[] = [
        $item['statustipo'] => $item['rotulo']
    ];
}

echo json_encode($arrRetorno);
