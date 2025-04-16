<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$exercicio 	= $_GET["exercicio"];
$idregistro_1 	= $_GET["idregistro_1"];
$idregistro_2	= $_GET["idregistro_2"];
$cssp=$_GET["cssp"];
if(empty($cssp)){
	$cssp="print";
}

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulad .= " and (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

if(!empty($exercicio)){
	$clausulad .=" and a.exercicio=".$exercicio." ";
}
if(!empty($idregistro_1) and !empty($idregistro_2)){
	$clausulad .= " and (a.idregistro  BETWEEN '" . $idregistro_1 ."' and '" .$idregistro_2 ."')";
}

    if($_GET and !empty($clausulad)){
    $sql1="select a.idamostra,a.idpessoa,r.idresultado,r.descritivo,r.idsecretaria, concat(a.nucleoamostra,' ',a.lote) as nucleo,a.granja,
		a.exercicio,a.idregistro,a.tc,dma(r.alteradoem) as alteradoem, MONTHNAME(a.dataamostra) as mes,
		pos.*,
		ef.tipoespecie,
		ef.finalidade
		from prodserv p
		join resultado r
		join amostra a
		join plpositivo pos
		left join especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
		where pos.status='ATIVO'
			and pos.idresultado = r.idresultado
			and r.idtipoteste =p.idprodserv
		and p.relatoriopositivo = 'Y'
		and r.alerta ='Y'
		and r.status = 'ASSINADO'
		and r.idsecretaria is not null 
		and r.idamostra = a.idamostra
		and exists (Select 1 from amostra a1 join resultado r1 on a1.idamostra = r1.idamostra where a.idamostra = a1.idamostra and r1.idsecretaria is not null and not r1.idsecretaria = 0)
		and exists (Select 1 from amostra a1 join resultado r1 on a1.idamostra = r1.idamostra where a.idamostra = a1.idamostra and r1.idtipoteste = 678)
		".$clausulad."  
		GROUP BY  a.idamostra
		order by a.idregistro";
	echo "<!--";
	echo $sql1;
	echo "-->";
	$res1 = d::b()->query($sql1) or die("Falha 3: " . mysqli_error() . "<p>SQL: $sql");
?>
<html>
<head>

<title>Relatório Lanagro</title>

<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
 <table class="mostratab" style="">
  <tr>
  
 	<td colspan="22" align="center" style="font-size: 25px;">
 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 	
 		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 	Envio de Culturas de Salmonela ao Lanagro/SP</td>

 </tr>
 </table>
 <p>
<table class="mostratab" border=1 cellspacing=0 cellpadding=2 bordercolor="black">


<tr style="font-weight: bold;">
	<td align="center" rowspan="2">Laboratório</td>
	<td align="center" rowspan="2">Ano</td>
	<td align="center" rowspan="2">Mês</td>
	<td align="center" rowspan="2">UF da Amostra</td>
	<td align="center" rowspan="2">Tipo <br>de<br>Ave</td>
	<td align="center" rowspan="2">Tipo <br>de<br>Exploração</td>
	<td align="center" rowspan="2">Tipo <br>de<br>Vigilância</td>
	<td align="center" rowspan="2">Municipio</td>
	<td align="center" rowspan="2">Propriedade</td>
	<td align="center" rowspan="2">Núcleo</td>
	<td align="center" rowspan="2">Tipo <br>de<br>Amostra</td>
	<td align="center" rowspan="2">TC</td>
	<td align="center" rowspan="2">Nº Registro<br>no <br>Laborátorio</td>
	<td align="center" rowspan="2">Data<br>Resultado<br>Final <br> dia/mês/ano</td>	
	<td colspan="11" align="center">CLASSIFICAÇÃO  DA CULTURA (Portaria 126)<br>N= Não Reagente<br>R= Reagente<br>NA=Não aplicavel</td>
	<td align="center" rowspan="2">Diagnóstico Final<br> Laboratório<br> Credênciadao</td> 
	<td align="center" rowspan="2">Data<br> Envio <br> ao Lab.<br> Referencia  <br>dia/mês/ano</td> 
	<td rowspan="2">Observações</td>
</tr>
<tr style="font-weight: bold;">

		<td align="center">Motilidade</td>
		<td align="center">Salina 2%</td>
		<td align="center">O</td>
		<td align="center">B</td>   
		<td align="center">D</td> 
		<td align="center">HG</td>
		<td align="center">HM</td>
		<td align="center">HP</td>
		<td align="center">Hi</td>
		<td align="center">H2</td>
		<td align="center">Hr</td>

</tr>
<?
	while($row=mysqli_fetch_assoc($res1)){

		//$sqlc="select * from endereco where status = 'ATIVO'  and idpessoa=".$row['idsecretaria']." limit 1";
		$sqlc="select c.cidade,e.uf
				from endereco e,nfscidadesiaf c
				where c.codcidade = e.codcidade
				and e.idtipoendereco = 2
				and  e.idpessoa=".$row['idpessoa'];
		$rec=d::b()->query($sqlc) or die("Erro ao buscar endereço sql=".$sqlc);
		$rowc=mysqli_fetch_assoc($rec);
		$uf=$rowc['uf'];
		
		
		$sqldt="select dma(max(alteradoem)) as malteradoem from resultado where idamostra =".$row['idamostra']." and idtipoteste in (678,640,2000) ";
		$resdt=d::b()->query($sqldt) or die("Erro ao buscar a maior data entre os testes do registro sql=".$sqldt);
		$rowdt=mysqli_fetch_assoc($resdt);
		
?>
<tr class="respreto " style="background-color: white"> 


	<td align="center" >LAUDO</td>
	<td align="center"><?=$row['exercicio']?> </td>
	<td align="center" ><?=$row['mes']?> </td>
	<td align="center"><?=$uf?> </td>
	<td align="center"><?=$row['tipoespecie']?> </td>
	<td align="center"><?=$row['finalidade']?> </td>
	<td align="center"><?=$row['tipovigilancia']?> </td>
	<td align="center" ><?=$rowc['cidade']?></td>
	<td align="center" ><?=$row['granja']?></td>
	<td align="center" ><?=$row['nucleo']?></td>
	<td align="center"><?=$row['tipoamostra']?> </td>
	<td align="center"><?=$row['tc']?> </td>
	<td align="center"><?=$row['idregistro']?> </td>
	<td align="center"><?=$rowdt['malteradoem']?> </td>

	
	
	<td align="center"><?=$row['flmotilidade']?></td> 
	
	<td align="center"><?=$row['flsalina']?></td> 

	<td align="center"><?=$row['flo']?></td> 

	
	<td align="center"><?=$row['flb']?></td> 

	<td align="center"><?=$row['fld']?></td> 
 
	<td align="center"><?=$row['flhg']?></td> 

	<td align="center"><?=$row['flhm']?></td> 

	<td align="center"><?=$row['flhp']?></td> 

	<td align="center"><?=$row['flh1']?></td> 

	<td align="center"><?=$row['flh2']?></td> 
	<td align="center"><?=$row['flhr']?></td> 

	<td align="center"><?=$row['diagnostico']?></td> 

	<td align="center"><?=date("d/m/Y");//=$row['envio']?></td> 

	<td><?=$row['obs']?></td> 
</tr>



 <? 
        }
 ?>
 </table>
</head>
</html>
 <? 
    }
 ?>