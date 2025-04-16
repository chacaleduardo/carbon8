<?
require_once("../inc/php/functions.php");

$dataini = $_GET['dataini']; 
$datafim = $_GET['datafim']; 
$idprodserv = $_GET['idprodserv']; 
$idtipoprodserv =  $_GET['idtipoprodserv'];
$idtipoprodserv =  $_GET['idtipoprodserv'];
$status = $_GET['status'];
$fabricado = $_GET['fabricado'];
$idplantel = $_GET['idplantel'];
$tiporateio = $_GET['tipo'];
$especificacaoprod = $_GET['especificacaoprod'];




if(empty($dataini) or empty($datafim) ){
	die("Não foi possível identificar o periodo de intervalo");
}
if(!empty($idprodserv)){
    $strprod=" and p.idprodserv in (".$idprodserv.")";
}

if(!empty($idtipoprodserv)){
    $strprod=$strprod." and p.idtipoprodserv in (".$idtipoprodserv.")";
}

if(!empty($especificacaoprod)){


    $arrfilprod = explode(",", $especificacaoprod);
    foreach($arrfilprod as $filprod) {
        $strprod=$strprod." and p.".$filprod." = 'Y' ";
    }

}


if(!empty($fabricado)){
    $strprod=$strprod." and p.fabricado in ('" . implode("','", explode(',', $fabricado)) . "')";
}
if(!empty($status)){
    $statuslote = " and l.status in ('" . implode("','", explode(',', $status)) . "')";
}

if(!empty($idplantel)){
    $plantel = " JOIN plantelobjeto pl ON ( pl.idplantel in (".$idplantel.") AND pl.idobjeto = p.idprodserv  AND pl.tipoobjeto = 'prodserv' )";
}


if($tiporateio=='PRODUTO'){

    $sql=" select l.idlote,l.partida,l.exercicio,ifnull(l.vlrlote,0) as vlrlote ,ifnull(l.vlrlotetotal,0) as vlrlotetotal ,l.qtdprod,l.qtdprod_exp,p.descr,l.unlote,l.status
        from lote l 
            join prodserv p on(p.idprodserv=l.idprodserv ".$strprod."  )
            
           ".$plantel."
        where l.idempresa=".cb::idempresa()."
        and l.idprodservformula is not null
        ". $statuslote."
        and l.fabricacao between '".$dataini."' and '".$datafim."' order by l.vlrlotetotal,partida desc";

    $res=  d::b()->query($sql) or die("Falha ao buscar lotes: <p>SQL: $sql");  
    $qtd=mysqli_num_rows($res);
?>
    <div class="table table-striped planilha panel panel-default " style="width:100%;font-size:9px;" >
        <div class="col-md-12 row panel-heading" style="margin:0px; font-size:9px;">
            <div class="col-md-12 text-al-r"> <b><?=$qtd?></b> resultasdos encontrados</div>									
        </div>
        <div class="col-md-12 row panel-heading" style="margin:0px; font-size:9px;">
            <div class="col-md-1">
                <input type="checkbox" name="marcardesmarcar"  checked class="pointer" title="Marcar/Desmarcar todos" onclick="selecionar(this,'inputcheckboxlote')">
            </div>
            <div class="col-md-1 text-al-r">ID</div>
            <div class="col-md-2 text-al-r">PARTIDA</div>
            <div class="col-md-2 text-al-r">STATUS</div> 
            <div class="col-md-3 text-al-r">PRODUTO</div>                                
            <div class="col-md-1 text-al-r">QTD PRODUZIDA</div>	
          
            <div class="col-md-1 text-al-r">VALOR UNITÁRIO</div>				
            <div class="col-md-1 text-al-r">VALOR TOTAL</div>									
        </div>
<?
    $i=9999999;
    while($row=mysql_fetch_assoc($res)){
        $i++;
?>
        <div class="col-md-12 row panel-heading" style="margin:0px; font-size:9px;">
            <div class="col-md-1 inputcheckboxlote">
                <input type="checkbox" checked class="changeacao" acao="i" atname="checked[<?=$i?>]" value="<?=$row['idlote'] ?>" style="border:0px">
            </div>
            <div class="col-md-1 text-al-r"><?=$row['idlote'] ?></div>
            <div class="col-md-2 text-al-r"><?=$row['partida'] ?>/<?=$row['exercicio'] ?></div>
            <div class="col-md-2 text-al-r"><?=$row['status'] ?></div>
            <div class="col-md-3 text-al-r"><?=$row['descr'] ?></div>
            
            <div class="col-md-1 text-al-r nowrap"><?=$row['qtdprod'] ?> - <?=$row['unlote'] ?></div>	
 
            <div class="col-md-1 text-al-r"><?=number_format(tratanumero($row['vlrlote']), 2, ',', '.');?></div>				
            <div class="col-md-1 text-al-r"><?=number_format(tratanumero($row['vlrlotetotal']), 2, ',', '.');?></div>
                
        </div>
<?
    }

?>
    </div>
<?
}else{//if($tiporateio=='PRODUTO'){
?>
 <div class="table table-striped planilha panel panel-default " style="width:100%;font-size:9px;" >
        <div class="col-md-12 row panel-heading" style="margin:0px; font-size:9px;">
            <div class="col-md-12 text-al-r">Verificar onde será lançado o valor neim todos os itens da nota tem idresultado</div>									
        </div>
</div>
<?

}
?>