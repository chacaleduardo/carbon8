<?
require_once("../inc/php/functions.php");

$idsolfab= $_GET['idsolfab']; 
$idprodservformula= $_GET['idprodservformula']; 
$idpessoa= $_GET['idpessoa']; 

if($idsolfab!='novo'){
    $title="Sementes que não estão na Solicitação de Vacinas Autógenas";
    $sqlsf="select  distinct(l.idlote) as idlote,l.partida,l.exercicio,l.status
		from prodservformulains i,prodservformula f,prodservformulains fi,lote l,resultado r,amostra a
		where i.idprodservformula=".$idprodservformula."
		and f.idprodserv = i.idprodserv
		and f.idprodservformula = fi.idprodservformula
		and fi.idprodserv = l.idprodserv
		and l.tipoobjetosolipor='resultado'
		and r.idresultado = l.idobjetosolipor
		and a.idpessoa = ".$idpessoa."
        and fi.status='ATIVO'
		and a.idamostra = r.idamostra                        
		and l.status in ('AUTORIZADA','APROVADO')
                and not exists (select 1 from solfabitem sf where sf.idsolfab = ".$idsolfab." and sf.tipoobjeto ='lote' and sf.idobjeto = l.idlote)
	   order by l.status";
    //die($sqlsf);
}else{
     $title="Sementes que não Possuem Solicitação de Vacinas Autógenas";
     $sqlsf="select  distinct(l.idlote) as idlote,l.partida,l.exercicio,l.status
		from prodservformulains i,prodservformula f,prodservformulains fi,lote l,resultado r,amostra a
		where i.idprodservformula=".$idprodservformula."
		and f.idprodserv = i.idprodserv
		and f.idprodservformula = fi.idprodservformula
		and fi.idprodserv = l.idprodserv
		and l.tipoobjetosolipor='resultado'
		and r.idresultado = l.idobjetosolipor
		and a.idpessoa = ".$idpessoa."
		and a.idamostra = r.idamostra                        
        and fi.status='ATIVO'              
		and l.status ='AUTORIZADA'
                and not exists (select 1 from solfabitem sf where sf.tipoobjeto ='lote' and sf.idobjeto = l.idlote)
        order by l.status";
}
    //die($sqlsf);
    $resf = d::b()->query($sqlsf) or die("A consulta das sementes do cliente falhou : " . mysql_error() . "<p>SQL: $sqlsf");
    $qtdsf= mysqli_num_rows($resf);
    if($qtdsf>0){
?>

<div class="col-md-12">
<div class="panel panel-default " >
    <div class="panel-heading"><?=$title?></div>
    <div class="panel-body alert-info">
        <?while($rowf=mysqli_fetch_assoc($resf)){
         if($rowf['AUTORIZADA']){
            $style="";
         }else{
             $style="color: green !important;";
         }  
            
        ?>
        <span>
            <a style="<?=$style?>" href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=<?=$rowf["idlote"]?>')" target="_blank" title="<?=$rowf['status']?>">
            <?=$rowf["partida"]?>/<?=$rowf["exercicio"]?>&nbsp;&nbsp;
            </a>
        </span>
        <?}?>
    </div>
</div>
</div>
<?
    }else{
?>

<div class="col-md-12">
<div class="panel panel-default " >
    <div class="panel-heading">Sementes da Solicitação de Vacinas Autógenas</div>
    <div class="panel-body alert-info">
        Não foram encontradas sementes nestas condições.
    </div>
</div>
</div>
<?        
    }
         
?>

