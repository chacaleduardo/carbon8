<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
$idcontapagar=$_GET["idcontapagar"];


    

if(!empty($idcontapagar)){
    $insql=" and r.idcontapagar = ".$idcontapagar;
}

if($_GET and !empty($insql)){   
    
    $sql="select * from vwcontapagaragrupada r
	    where 1 ".$insql;
    $res=d::b()->query($sql) or die("Erro ao buscar conta do representante sql=".$sql);
    $qtdrows=mysqli_num_rows($res);
}

?>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Informaçàµes da Agrupadora</div>
        <div  class="panel-body">
	<table class="table table-striped planilha">
	<thead>
	<tr>				
		<th>Nome</th>
		<th>T. Faturamento</th>
		<th>Agência</th>
		<th>Tipo</th>
		<th>Forma Pgto.</th>
		<th>Parcela</th>
		<th>Data</th>
		<th>Valor</th>
		<th>Status</th>	 
		<th></th>
	</tr>
	</thead>
	<tbody>
	    <?	   
	    while($row=mysqli_fetch_assoc($res)){	
	    ?>
	    <tr>
	
		<td><?=$row['nome']?></td>
		<td><?=$row['contaitem']?></td>
		<td><?=$row['agencia']?> </td>
		<td><?=$row['tipo']?></td>
		<td><?=$row['formapagto']?></td>
		<td><?=$row['parcela']?>/<?=$row['parcelas']?></td>
		<td><?=dma($row['datareceb'])?></td>
		<td><?=$row['valor']?></td>
		<td><?=$row['status']?></td>
		<td  style="cursor: pointer;">
		    <a title="Editar Conta" class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$row['idcontapagar']?>');"></a>			
		</td>			
	    </tr>
	    <?}?>
	   

	</tbody>
	</table>
        </div>
    </div>
    </div>
</div>
 <?
/*	    if(!empty($idpessoarep)){
		if(!empty($idcontapagaritem)){
		    $insql1=" and r.idcontapagaritem != ".$idcontapagaritem;
		}
		if(!empty($idcontapagar)){
		    $insql1=" and (idcontapagar !=".$idcontapagar." or idcontapagar is null) ";
		}
		 $sql="select * from vwextratorepresentante r
			where r.statusrep='PENDENTE' and r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." ".$insql1." and r.idpessoarep= ".$idpessoarep;
		$res=d::b()->query($sql) or die("Erro ao buscar conta do representante sql=".$sql);
		$qtdrows=mysqli_num_rows($res);
		if($qtdrows>0){
	?>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading"> Pendentes</div>
        <div  class="panel-body">
	    <table class="table table-striped planilha">
		<tr>
		     <th>NFE</th>
		    <th>Cliente</th>
		    <th>NF Valor</th>
		    <th>Parcela Cliente</th>
		    <th>Data</th>	
		    <th>Representante</th>
		    <th>Participação</th>
		    <th>Comissão</th>
		    <th>Status</th>
		    <th>Data</th>		   
		  	  
		</tr>
	    <?
	    $valorrep=0;
	    $valor=0;
	    while($row=mysqli_fetch_assoc($res)){
		$valorrep=$valorrep+$row['valorrep'];
		$valor=$valor+$row['valor'];
   ?>
	    <tr>
		<td onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$row['idnf']?>');" style="cursor: pointer;">
			<font color="blue"><?=$row['nnfe']?></font>			
		</td>
		<td><?=$row['nome']?></td>
		<td><?=$row['valor']?></td>
		<td><?=$row['status']?></td>
		<td><?=dma($row['datareceb'])?></td>	
		<td><?=$row['nomerep']?></td>
		<td><?=$row['participacao']?> % </td>
		<td><?=$row['valorrep']?></td>
		<td><?=$row['statusrep']?></td>
		<td><?=dma($row['dmadatapgrep'])?></td>			
	
	    </tr>
	    <?}?>
	    <tr>
		<td colspan="2"></td>
		<td><?=number_format($valor,2,'.','');?></td>
		<td colspan="4"></td>
		<td><?=number_format($valorrep,2,'.','');?></td>
	    </tr>
	  
	    </table>
	</div>
    </div>
    </div>
</div>
  <?
		}
	    }
 
 */
?>
<script>

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>

