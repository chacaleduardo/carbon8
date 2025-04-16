<?php
require_once("../inc/php/functions.php");

if (empty($_GET["referencia"])) {
    echo json_encode([
        'error' => 'Nenhuma referência foi informada. Por favor, insira a referência no formato Mês/Ano'
    ]);

    exit;
}
if (empty($_GET["idtipoprodserv"])) {
    echo json_encode([
        'error' => 'Não informado nenhuma subcategoria'
    ]);

    exit;
}

if (empty($_GET["tipo"])) {
    echo json_encode([
        'error' => 'Não informado nenhuma referência'
    ]);

    exit;
}
$referencia = explode("/", $_GET["referencia"]);

$mes = (int)$referencia['0'];
$ano = $referencia['1'];
$idtipoprodserv = $_GET["idtipoprodserv"];
const MESES = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];


$mesAno = ["01/$ano", "02/$ano", "03/$ano", "04/$ano", "05/$ano", "06/$ano", "07/$ano", "08/$ano", "09/$ano", "10/$ano", "11/$ano", "12/$ano"];
$mesAteOAtual = "";
$i  = 0;

for ($i = 0; $i < count($mesAno); $i++) {
    if ($i != 0) $mesAteOAtual .= ",";
    $mesAteOAtual .= "'$mesAno[$i]'";
    if ($mesAno[$i] == $_GET["referencia"]) break;
}

$mesesAnteriores = $mes - 4;
$mesesForeacastCompras = array_slice(MESES, ($mesesAnteriores < 0  ? 0 : $mes - 4), $mesesAnteriores < 0 ? $mesesAnteriores + 3 : 3);

//valor cadastrado
$sqlprevisao = "SELECT 
                        p.idtipoprodserv,
                        t.tipoprodserv,
                        abs(p." . MESES[$mes - 1] . ") as valorcompra,
                        p." . MESES[$mes - 1] . " as valor_mes_atual,
                        c.idcontaitem,
                        c.contaitem,
                        p.jan,
                        p.fev,
                        p.mar,
                        p.abr,
                        p.mai,
                        p.jun,
                        p.jul,
                        p.ago,
                        p.set,
                        p.out,
                        p.nov,
                        p.dez
                FROM
                    planejamentocompra p
                    JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
                    JOIN contaitemtipoprodserv ci on ci.idtipoprodserv = t.idtipoprodserv
                    JOIN contaitem c on c.idcontaitem = ci.idcontaitem
                WHERE
                p.idtipoprodserv in (" . $idtipoprodserv . ") and p.idempresa = " . cb::idempresa() . " and p.exercicio = " . $ano . "
                ORDER BY c.contaitem, t.tipoprodserv;";

$resvalorcadastrado = d::b()->query($sqlprevisao) or die("Erro ao rodar: $sqlprevisao " . mysqli_error(d::b()));


$i = 0;
$divRetorno = "";
$divSubcategorias = "";
$dadosRetorno = [];
$idTipoProdservAtual = null;

while ($r = mysqli_fetch_assoc($resvalorcadastrado)) {
    if (!$dadosRetorno['categorias'][$r['idcontaitem']]) {
        $acumulado = 0;
        $dadosRetorno['categorias'][$r['idcontaitem']] = $r;
    }

    $sqlconsumido = "SELECT 
                        nf.criadoem, 
                        c.idcotacao, 
                        c.referencia, 
                        nf.idobjetosolipor, 
                        p.idtipoprodserv, 
                        SUM(nfi.vlritem) AS total_valor_item,
                        t.tipoprodserv
                    FROM 
                        nf
                        JOIN cotacao c ON c.idcotacao = nf.idobjetosolipor
                        JOIN nfitem nfi ON nfi.idnf = nf.idnf
                        JOIN prodserv p ON p.idprodserv = nfi.idprodserv
                        JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
                    WHERE
                        nf.`status` IN ('APROVADO', 'CONCLUIDO')
                        AND nfi.tiponf <> 'V'
                        AND c.referencia in ($mesAteOAtual)
                        AND p.idtipoprodserv = {$r['idtipoprodserv']}
                    GROUP BY 
                        p.idtipoprodserv, c.referencia
                    ORDER BY 
                        t.tipoprodserv;";

    $resvalorconsumido = d::b()->query($sqlconsumido) or die("Erro ao rodar: $sqlconsumido " . mysqli_error(d::b()));

    $itensForecastCompra = "";
    $valorDisponivelMes = 0;
    $valorGastoMes = 0;
    $valorUtilizado = 0;

    if ($idTipoProdservAtual != $r['idtipoprodserv']) {
        $novosDados = [
            'contaitem' => $r['contaitem'],
            'idtipoprodserv' => $r['idtipoprodserv'],
            'tipoprodserv' => $r['tipoprodserv'],
            'forecastMesAtual' => abs(floatval($r['valor_mes_atual'])),
            'valorUtilizado' => 0,
            'saldoAtual' => 0,
            'acumuladoTresMeses' => array_sum($novosDados['meses'])
        ];

        if(isset($mesesForeacastCompras[0]))
            $novosDados['tresMeses'][$mesesForeacastCompras[0]] = floatval($r[$mesesForeacastCompras[0]]);
        if(isset($mesesForeacastCompras[1]))
            $novosDados['tresMeses'][$mesesForeacastCompras[1]] = floatval($r[$mesesForeacastCompras[1]]);
        if(isset($mesesForeacastCompras[2]))
            $novosDados['tresMeses'][$mesesForeacastCompras[2]] = floatval($r[$mesesForeacastCompras[2]]);
    }

    if (!$resvalorconsumido->num_rows) $dadosRetorno['categorias'][$r['idcontaitem']]['subcategorias'][$r['idtipoprodserv']] = $novosDados;

    // Dados agrupados por referencia (mes)
    while ($dadosMeses = mysqli_fetch_assoc($resvalorconsumido)) {
        $valorUtilizado = 0;
        // Buscar planejamento da subcategoria atual
        $sqlprevisaoMes = "SELECT 
                            p.idtipoprodserv,
                            t.tipoprodserv,
                            c.idcontaitem,
                            c.contaitem,
                            " . implode(',', $mesesForeacastCompras) . "
                        FROM
                        planejamentocompra p
                        JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
                        JOIN contaitemtipoprodserv ci on ci.idtipoprodserv = t.idtipoprodserv
                        JOIN contaitem c on c.idcontaitem = ci.idcontaitem
                        WHERE
                        p.idtipoprodserv = {$dadosMeses['idtipoprodserv']} and p.idempresa = " . cb::idempresa() . " and p.exercicio = " . $ano . "
                        ORDER BY c.contaitem, t.tipoprodserv;";

        $resValorMes = d::b()->query($sqlprevisaoMes) or die("Erro ao rodar: $sqlprevisao " . mysqli_error(d::b()));
        $forecastMesRes = mysqli_fetch_assoc($resValorMes);

        $mesAtual = MESES[intval(explode('/', $dadosMeses['referencia'])[0]) - 1];

        $valorDisponivelMes = abs(floatval($r[MESES[floatval(intval(explode('/', $dadosMeses['referencia'])[0])) - 1]]));
        $valorGastoMes = abs(floatval($dadosMeses['total_valor_item']));
        $valorMesDescontado = $valorDisponivelMes - $valorGastoMes;

        $acumulado += $valorMesDescontado;
        $dadosRetorno['valorMeses'][$mesAtual] = $valorMesDescontado;
        $valorUtilizado += floatval($dadosMeses['total_valor_item']);

        // Dados dos 3 meses anteriores
        foreach ($mesesForeacastCompras as $mes) {
            $foreCastTresMesesMesAtual = $novosDados['tresMeses'][$mes];

            if ($mes == MESES[intval(explode('/', $dadosMeses['referencia'])[0]) - 1]) {
                $foreCastTresMesesMesAtual -= $valorUtilizado;
            }

            $novosDados['tresMeses'][$mes] = $foreCastTresMesesMesAtual;
        }

        $foreCastMesAtual = abs($novosDados['forecastMesAtual']);
        $saldoAtual = floatval($foreCastMesAtual) - floatval($valorUtilizado);
        // Dados do mes atual
        if ($dadosMeses['referencia'] == $_GET["referencia"]) {
            $novosDados['saldoAtual'] = $saldoAtual;
            $novosDados['forecastMesAtual'] = $foreCastMesAtual;
            $novosDados['valorUtilizado'] = $valorUtilizado;
        }

        $novosDados['acumulado'] = $acumulado;
        $dadosRetorno['categorias'][$r['idcontaitem']]['subcategorias'][$dadosMeses['idtipoprodserv']] = $novosDados;
    }

    $dadosRetorno['categorias'][$r['idcontaitem']]['subcategorias'][$r['idtipoprodserv']]['acumuladoTresMeses'] = array_sum($dadosRetorno['categorias'][$r['idcontaitem']]['subcategorias'][$r['idtipoprodserv']]['tresMeses']);

    $idTipoProdservAtual = $r['idtipoprodserv'];
    $subCategoriaAtual = $r['contaitem'];
}

foreach ($dadosRetorno['categorias'] as $categoria) {
    $divSubcategorias = "";

    if (!$categoria['subcategorias'] || !count($categoria['subcategorias'])) {
        $itensForecastCompra = "<div class='p-1 pl-4' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>-</span></div>
                                <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ -</span></div>
                                <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ - </span></div>
                                <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ - </span></div>";

        $divSubcategorias .= "<div class='w-100 d-flex text-lg'>
                                $itensForecastCompra
                                <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ - </span></div>
                            </div>";
    }

    foreach ($categoria['subcategorias'] as $subcategoria) {
        $itensForecastCompra = "";
        $acumuladoTotal = $subcategoria['acumulado'];
        $classAtual = "class='" . ($subcategoria['saldoAtual'] <= 0 ? 'text-danger' : 'text-success') . "'";

        if (($subcategoria['referencia'] == $_GET["referencia"]) || !$subcategoria['referencia']) {
            if (empty($subcategoria['total_valor_item'])) {
                $subcategoria['total_valor_item'] = 0;
            }

            if (empty($categoria['valorcompra'])) {
                $categoria['valorcompra'] = 0;
            }

            $itensForecastCompra = "<div class='p-1 pl-4' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>{$subcategoria['tipoprodserv']}</span></div>
                                    <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ " . number_format(abs($subcategoria['forecastMesAtual']), 2, ',', '.') . "</span></div>
                                    <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ " . number_format($subcategoria['valorUtilizado'], 2, ',', '.') . "</span></div>
                                    <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span $classAtual>R$ " . number_format(abs($subcategoria['saldoAtual']), 2, ',', '.') . "</span></div>";

            $classAcumulado = "class='" . ($subcategoria['acumulado'] <= 0 ? 'text-danger' : 'text-success') . "'";

            if (!$itensForecastCompra) $itensForecastCompra = "<div class='p-1 pl-4' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>{$subcategoria['tipoprodserv']}</span></div>
                                                                                            <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ -</span></div>
                                                                                            <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ - </span></div>
                                                                                            <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span>R$ - </span></div>";

            $divSubcategorias .= "<div class='w-100 d-flex text-lg'>
                                                            $itensForecastCompra
                                                            <div class='text-center p-1' style='width: 20%;border: 1px solid rgba(152, 152, 152, 1)'><span $classAcumulado>R$ " . number_format(abs($subcategoria['acumulado']), 2, ',', '.') . "</span></div>
                                                        </div>";
        }
    }

    if ($itensForecastCompra)
        $divRetorno .= "<div class='w-100 mb-3'>
                        <div class='w-100 categoria-item collapsed' style='border: 1px solid rgba(152, 152, 152, 1);' data-toggle='collapse' href='#forecast-compra-{$categoria['idcontaitem']}'>
                                <div class='w-100 d-flex flex-between px-4 py-3 text-white' style='background-color: rgba(152, 152, 152, 1)'>
                                    <h5 class='text-uppercase m-0'>{$categoria['contaitem']}</h5>
                                    <i class='fa fa-chevron-down'></i>
                                </div>
                            </div>
                            <div id='forecast-compra-{$categoria['idcontaitem']}' class='w-100 collapse mb-3'>
                                $divSubcategorias
                            </div>
                        </div>
                    ";
}

$dadosRetorno['html'] = $divRetorno;
$dadosRetorno['resumoMeses'] = $mesesForeacastCompras;

// Buscar forecast
$forecastSQL = "SELECT idforecastcompra, idempresa
                FROM forecastcompra
                WHERE exercicio= $ano
                AND idempresa = " .  cb::idempresa();

$forecast = d::b()->query($forecastSQL);
$forecastRes = mysqli_fetch_assoc($forecast);

$linkForecast = "#";

if ($forecastRes) $linkForecast = "/?_modulo=forecastcompra&_acao=u&idforecastcompra={$forecastRes['idforecastcompra']}&_idempresa={$forecastRes['idempresa']}";

$dadosRetorno['linkForecast'] = $linkForecast;

echo json_encode($dadosRetorno);
