<?
require_once("../inc/php/validaacesso.php");

$idpessoa=$_GET['idpessoa'];


$situacao = $_GET['situacao'];

?>
<html>
<head>
<title>Relatório Qualidade</title>



<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />


<style>
.rotulo{
font-weight: bold;
font-size: 10px;
}
.texto{
font-size: 12px;
}
.textoitem{
font-size: 10px;
}
</style>

</head>
<body style="max-width:1000px;">


<table >
	<tr>
	<?
			// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
			$sqlfig="select figrelatorioprod from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
			$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
			$figrel=mysqli_fetch_assoc($resfig);

			//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
			//$figurarelatorio = "../inc/img/repheader.png";
			$figurarelatorio = $figrel["figrelatorioprod"];
			
		?>
		<td><img src="<?=$figurarelatorio?>" style="max-width: 800px"></td>		
	</tr>
</table>

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Dados do Fornecedor</legend>
</fieldset>
<table>
	<tr>		
		<td>
		<table>
		<tr>	
			<td align="right" class="rotulo">Nome:</td> 
			<td nowrap class="texto"><?=traduzid("pessoa","idpessoa","nome",$idpessoa)?></font></td>
		</tr>
		<tr>
			<td align="right" nowrap class="rotulo">Razão Social:</td>
			<td colspan="5" class="texto" nowrap><?=traduzid("pessoa","idpessoa","razaosocial",$idpessoa)?></td>
		</tr>
		<tr>
			<td align="right" class="rotulo">CNPJ:</td>
			<td class="texto"><?=traduzid("pessoa","idpessoa","cpfcnpj",$idpessoa)?></td>
			<td align="right" class="rotulo">I.E:</td>
			<td class="texto"><?=traduzid("pessoa","idpessoa","inscrest",$idpessoa)?></td>
		</tr>
		
		</table>
	</tr>
</table>	

<br>
<?

	if(empty($situacao)){
		$instr= " ";
	}elseif($situacao=="pendresolv"){
		$instr= " and pendente = 'Y' and resolvido = 'Y' ";
	}elseif($situacao=="pendnresolv"){
		$instr= " and pendente = 'Y' and resolvido = 'N' ";
	}
	

	$sql0 = "select * from nf 	where idpessoa  = ".$idpessoa." 
		and dtemissao between SUBDATE(sysdate(), INTERVAL 1 year) and sysdate() 
		 ".$instr." 
		and status in ('DIVERGENCIA','CONCLUIDO')
		order by dtemissao";	
	$qr0 = d::b()->query($sql0) or die("Erro ao buscar notas:".mysqli_error());
		$troca="S";
	while($row=mysqli_fetch_assoc($qr0))	{



	//mudar a cor da linha
	if($troca=="S"){
		$cortr = "#FFFFFF";
		$troca="N";
	}else{
		$cortr = "#E8E8E8";
		$troca="S";
	}

?>
<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>NF <?=$row['nnfe']?></legend>
</fieldset>	
	<table>
		<tr style="background-color: #B5B5B5">
			<td align="center" class="rotulo"><font style="vertical-align: top;">RESULTADO</font></td>			
			<td align="center" class="rotulo"><font style="vertical-align: top;">CONFERÊNCIA</font></td>	
		</tr>	 

		
	<tr> 
		<td nowrap class="textoitem"><?=$row["entregaok"]?></td>	
		<td nowrap class="textoitem">(1)-A Nota Fiscal da entrega está de acordo com a Ordem de Compras do Laudo.</td> 		
	</tr>
	<tr> 
		<td nowrap class="textoitem"><?=$row["itenok"]?></select>
		</td>
		<td nowrap class="textoitem">(2)-Os itens entregues correspondem com a Nota fiscal do fornecedor.</td> 		
	</tr>
	<tr>
		<td nowrap class="textoitem"><?=$row["embalagemok"]?></select>
		</td>		
		<td nowrap class="textoitem">(3)-A embalagem chegou integra (sem avarias) e está seguramente fechada.</td> 		
	</tr>
	<tr> 
		<td nowrap class="textoitem"><?=$row["identificacaook"]?></select>
		</td>
		<td nowrap class="textoitem">(4)-A identificação na embalagem é completa e está devidamente aderida.</td> 		
	</tr>
	<tr> 
		<td nowrap class="textoitem"><?=$row["dataentregaok"]?></select>
		</td>
		<td nowrap class="textoitem" >(5)-A entrega foi realizada na data prevista.</td> 		
	</tr>
	<tr> 
		<td nowrap class="textoitem"><?=$row["laudook"]?></select>
		</td>
		<td nowrap class="textoitem">(6)-Consta o Laudo Técnico/Certificado de Análise do insumo/material.</td> 
	</tr>
	<?	
		IF(!empty($row["obs"])){?>
	<tr  style="background-color: <?=$cortr?>">
		<td align="left" class="textoitem" nowrap>obs</td>			
 	 	<td style="width:150px;" class="textoitem"><?=nl2br($row["obs"])?></td>
	</tr> 	
<? 		
		}

		if($row["pendente"]=='Y'){
			if($row["resolvido"]=='N'){

				$corobspend="#FF6A6A";
				$strresultado="Não Resolvido";
			}else{

				$corobspend="#FF6A6A";
				$strresultado="Resolvido";
			}
?>
	<tr  style="background-color: <?=$corobspend?>">
		<td align="left" class="textoitem" nowrap>Pendência</td>			
 	 	<td style="width:150px;" class="textoitem"><?=nl2br($row["obspendente"])?></td>
 	 	<td><?=$strresultado?></td>
	</tr> 	
<?
		}
?>
	</table>
<?		
	}
?>


<p></p>
<?if(!empty($_1_u_nf_obs)){?>
<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Observação</legend>
</fieldset>	
	<table style="font-size: 12px">
		
		<tr>
			<td colspan="50" style="max-width:550px"><?=nl2br($_1_u_nf_obs)?></td>
		</tr>
	</table>	
		<?}?>	
<br>
</body>
</html>


