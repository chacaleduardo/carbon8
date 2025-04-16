<?
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");

if ($_GET['idprodservprproc'] and isset($_GET['versao'])) {
    $sql = 'SELECT * from objetojson where idobjeto = '.$_GET['idprodservprproc'].' and tipoobjeto="prodservprproc" and versaoobjeto='.$_GET['versao'];
    $res = d::b()->query($sql) or die("Falha ao buscar Json: ". mysqli_error(d::b()).'SQl =>'.$sql);
    $row = mysqli_fetch_assoc($res);
    $rc= unserialize(base64_decode($row["jobjeto"]));
    ?>
<html>
    <head>
        <title>Processo</title>
        
    </head>
    <link href="..\inc\css\carbon.css" rel="stylesheet">
    <link href="..\inc\css\bootstrap\css\bootstrap.css" rel="stylesheet">
    <link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">
    <body>
    <div  class="col-md-12">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <div  class="panel panel-default">
                <div style="background-color: #ded8d8;" class="panel panel-heading">
                    <div align="left">
                        <?foreach ($rc['prodservprproc']['res'] as $key => $value) {
                           if ($value['idprodservprproc'] == $_GET['idprodservprproc']){
                            ?><b style="font-size: 15px;color: #666;text-align: right;">Processo - <?=$value['proc']?></b> <b style="font-size: 15px;color: #666;float: right;">Versão <?=$value['versao']?>.0</b><?
                           }
                        }?>
                    </div>
                </div>
                <div class="panel panel-body">
                    <ul>
                        <?
                        foreach ($rc['prodservprproc']['res']['idprodservprproc'][$_GET['idprodservprproc']]['res'] as $key => $value) {?>
                            <?if (!empty($value['ativ'])) {?>
                                <li>
                                    <?=$value['ativ']?>
                                    <?
                                    if(!empty($rc['prodservprproc']['res']['idprodservprproc'][$_GET['idprodservprproc']]['res']['prativ'][$value['idprativ']])) {?>
                                        <ul>
                                            <?foreach ($rc['prodservprproc']['res']['idprodservprproc'][$_GET['idprodservprproc']]['res']['prativ'][$value['idprativ']]['res'] as $k => $val) {
                                                if ($val['idprodservprproc'] == $_GET['idprodservprproc']) {?>
                                                    <li>
                                                        <small><i class='fa fa-1x fa-circle cinzaclaro' style='color:<?=$val['cor']?>;'></i></small> <?echo $descr = (empty($val['descrcurta']))?$val['descr']:$val['descrcurta']?>&nbsp;<small><span class="label label-default"><?=recuperaExpoente($val["qtdi"],$val["qtdi_exp"])?></span></small>
                                                    </li>
                                                <?}?>
                                            <?}?>
                                            <??>
                                        </ul>
                                    <?}?>
                                </li>
                            <?}?>
                        <?}?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>
    </body>
</html>
<?}?>