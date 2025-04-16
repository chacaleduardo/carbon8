<?
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");

if ($_GET['idprodservformula'] and isset($_GET['versao'])) {
    $sql = 'SELECT * from objetojson where idobjeto = '.$_GET['idprodservformula'].' and tipoobjeto="prodservformula" and versaoobjeto='.$_GET['versao'];
    $res = d::b()->query($sql) or die("Falha ao buscar Json: ". mysqli_error(d::b()).'SQl =>'.$sql);
    $row = mysqli_fetch_assoc($res);
    $rc= unserialize(base64_decode($row["jobjeto"]));
    ?>
<html>
    <head>
        <title>Fórmula</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="..\inc\css\carbon.css" rel="stylesheet">
        <link href="..\inc\css\bootstrap\css\bootstrap.css" rel="stylesheet">
        <link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">
    </head>
    <body >
        <div class="col-md-12">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="agrupamento" style="border-color: <?=$rc['prodservformula']['res']['cor']?>;">
                <div class="panel-default">
                    <div class="panel panel-heading">
                        <b style="font-size: 15px;color: #666;text-align: right;">Fórmula - <?if(!empty($rc['prodserv']['res']['descrcurta'])){echo $rc['prodserv']['res']['descrcurta'];}else {echo $rc['prodserv']['res']['descr'];}?></b> <b style="text-align: right;color: #666;float: right;"> Versão <?=$rc['prodservformula']['res']['versao']?>.0</b>
                    </div>
                    <div class="panel panel-body">
                        <div>
                            <table style="width: 100%;" CELLSPACING="0" CELLPADDING="0" border="1">
                                <?if ($rc['prodserv']['res']['tipo'] == 'SERVICO') {?>
                                    <tr style="width: 100%;align-content: center;background-color: #c0c0c0;" border='0'>        
                                        <th style="text-align: center;"  colspan="4">
                                            Fase - <?=$rc['prodservformula']['res']['ordem']?>
                                        </th>
                                    </tr>
                                <tr>
                                    <th style="text-align: center;">Quant.</th>
                                    <th style="text-align: center;">Un.</th>
                                    <th style="text-align: center;">Insumo</th>
                                    <th style="text-align: center;">Visível no Resultado</th>
                                </tr>
                                <?foreach ($rc['prodservformula']['res']['prodservformulains'] as $key => $value) {
                                    if ($value['idprodservformula'] == $_GET['idprodservformula']) {
                                    // var_dump($value['un'])?>
                                    <tr>
                                        <td align="Center"><?=recuperaExpoente($value["qtdi"],$value["qtdi_exp"])?></td>
                                        <td align="Center"><?=$value['un']?></td>
                                        <td><?=$descr = (empty($value['descrcurta']))?$value['descr']:$value['descrcurta']?></td>
                                        <td align="Center"><?=$value['listares']?></td>
                                    </tr>
                                    <?}
                                }?>
                                <?}?>
                                <?if ($rc['prodserv']['res']['tipo'] == 'PRODUTO') {?>
                                <?
                                if (!empty($rc['prodservformula']['res']['idplantel'])) {?>
                                    <tr >
                                        <th style="text-align: center;" colspan="5">
                                            Fórmula para - <?=$rc['prodservformula']['res']['plantel']?>
                                        </th>
                                    </tr>
                                <?}?>
                                <tr style="width: 100%;align-content: center;background-color: #c0c0c0;">
                                        <th nowrap style="text-align: center;">
                                            Quant.
                                        </th>
                                        <th nowrap style="text-align: center;">
                                            Descrição
                                        </th>
                                        <th nowrap style="text-align: center;">
                                            <?=$rc['prodserv']['res']['conteudo']?>
                                        </th>
                                        <th nowrap style="text-align: center;">
                                            Volume
                                        </th>
                                        <th nowrap style="text-align: center;">
                                            Un Vol
                                        </th>
                                    </tr>
                                <tr>
                                    <th style="text-align: center;"><?=recuperaExpoente($rc['prodservformula']['res']["qtdpadraof"],$rc['prodservformula']['res']["qtdpadraof_exp"])?></th>
                                    <th style="text-align: center;" nowrap><?=$rc['prodservformula']['res']['rotulo']?></th>
                                    <th style="text-align: center;"><?=$rc['prodservformula']['res']['dose']?></th>
                                    <th style="text-align: center;"><?=$rc['prodservformula']['res']['volumeformula']?></th>
                                    <th style="text-align: center;"><?=$rc['prodservformula']['res']['un']?></th>
                                </tr>
                                <tr style="width: 100%;align-content: center;background-color: #c0c0c0;">
                                    <th style="text-align: center;">Quant.</th>
                                    <th style="text-align: center;">Un.</th>
                                    <th style="text-align: center;">Insumo</th>
                                    <th style="text-align: center;" colspan="2">F.P</th>
                                </tr>
                                <?foreach ($rc['prodservformula']['res']['prodservformulains'] as $key => $value) {
                                    if ($value['idprodservformula'] == $_GET['idprodservformula']) {?>
                                    <tr>
                                        <td align="Center"><?=recuperaExpoente($value["qtdi"],$value["qtdi_exp"])?></td>
                                        <td align="Center"><?=$value['un']?></td>
                                        <td><?=$descr = (empty($value['descrcurta']))?$value['descr']:$value['descrcurta']?></td>
                                        <td align="Center" colspan="2"><?=recuperaExpoente($value["qtdpd"],$value["qtdpd_exp"])?></td>
                                    </tr>
                                    <?}
                                }?>
                                <?}?>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </body>
</html>
    <?
}else {
    echo "IDPRODSERVFORMULA / VERSÃO NÃO INFORMADO";
}

?>