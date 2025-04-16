<?
require_once("../inc/php/functions.php");

$sqlfluxostatus = "select fsh.idfluxostatushist, f.colprazod, fs.prazod, fsh.idmodulo, f.modulo, fsh.alteradoem, cbm.tab, mto.col
from fluxostatushist fsh
inner join fluxostatus fs on fsh.idfluxostatus = fs.idfluxostatus
inner join fluxo f on f.idfluxo = fs.idfluxo
inner join carbonnovo._modulo cbm on cbm.modulo = f.modulo
inner join carbonnovo._mtotabcol mto on mto.tab = cbm.tab and mto.primkey = 'Y'
where f.colprazod is not null
    and fs.prazod is not null
    and fsh.idmodulo is not null
    and fsh.alteradoem between '2024-01-01' and '2024-12-31'
    and atrasodias IS NULL;";

$resfluxostatus = d::b()->query($sqlfluxostatus);

while ($rfluxostatus=mysql_fetch_assoc($resfluxostatus)) {
    $sqlbuscaregistro = "SELECT ".$rfluxostatus["colprazod"]." FROM ".$rfluxostatus["tab"]." WHERE ".$rfluxostatus["col"]." = ".$rfluxostatus["idmodulo"];
    $resregistro = d::b()->query($sqlbuscaregistro);
    $rregistro = mysql_fetch_assoc($resregistro);
    $prazo = $rregistro[$rfluxostatus["colprazod"]];
    $datasaida = $rfluxostatus["alteradoem"];
    $prazodiasetapa = $rfluxostatus["prazod"];
    $datasaidaEsperada = date_add(date_create($prazo),date_interval_create_from_date_string($prazodiasetapa . " days"));
    $datasaidaEsperada->settime(0,0);
    $datasaida = date_create($datasaida);
    $datasaida->settime(0,0);
    if($datasaida > $datasaidaEsperada) {
        $atraso = date_diff($datasaidaEsperada, $datasaida);
        $atraso = $atraso->days;
    }else {
        $atraso = 0;
    }
    $sqlupdate = "UPDATE fluxostatushist SET atrasodias = ".$atraso." WHERE idfluxostatushist = ".$rfluxostatus["idfluxostatushist"].";";
    d::b()->query($sqlupdate);
}
echo "Finalizado!";