<?
require_once("../inc/php/functions.php");

cbSetPostHeader('1','html');

 $sqlu = "UPDATE device d
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
                JOIN
            devicesensorbloco dsb ON dsb.iddevicesensor = ds.iddevicesensor
                AND dsb.tipo in ('p', 'd')
                JOIN
            tag t ON t.idtag = d.idtag
                LEFT JOIN
            tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai 
            SET 
                calibrar = 'Y', dsb.offset = 0,  d.iddevicecal = d.iddeviceref, d.iddeviceref = 240
            WHERE
                d.subtipo = 'DIFERENCIAL'
                    AND d.status = 'ATIVO' and calibrar <> 'Y'
                    and d.iddevice in (".$_REQUEST['iddevice'].")";
    mysql_query($sqlu) or die(mysql_error()." Erro ao realizar update nos device=".$sqlu);
    $keys = re::dis()->KEYS('_calibracao:*');
    foreach ($keys as $key=>$val){
        //echo $keys[$key];
        re::dis()->del(''.$keys[$key].'');
    }
    
  echo "OK";

?>