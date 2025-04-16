<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


$idpessoa = $_GET["idpessoa"];


if(empty($idpessoa)){
	die("E necessario informar o id do cliente.");
}


$sqlc = " select p.nome,p.razaosocial,p.obs1,p.obs2,dma(now()) as dataviagem from pessoa p where p.idpessoa= " . $idpessoa ;



$resc = d::b()->query($sqlc) or die("Falha ao buscar cliente para viagem : " . mysqli_error() . "<p>SQL: $sqlc");

$rowc=mysqli_fetch_assoc($resc); 


$sql = " select n.alojamento,datediff(now(),n.alojamento),round(datediff(now(),n.alojamento)/7,0) as idade,
upper(nucleo) as nucleo,
upper(lote) as lote
 from nucleo n where n.idpessoa = ".$idpessoa." and n.situacao = 'ATIVO' order by nucleo ";



$res = d::b()->query($sql) or die("Falha ao gerar o Relatório de viagem : " . mysqli_error() . "<p>SQL: $sqlc");

?>

<html>
<head>
<title>Sislaudo - Relatório de Viagem</title>
      <link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
</head>

<body style="margin:0px;padding:0px;">
<div class="col-md-8">
<div class="panel panel-default">
    <div class="panel-heading"> </div>
    <div class="panel-body">
        <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
        <thead>
        <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
            <td colspan="6" style="font-size:11px;"  align="center"><?=$rowc['nome']?> - <?=$rowc['dataviagem']?></td>
        </tr>
        <tr style="border:0px;">
	<td style="border:0px;">
		<table class="normal">            
		<tr class="header">
			<td class="tdtit grrot">NÚCLEO</td>
			<td class="tdtit grrot">LOTE</td>
			<td class="tdtit grrot">IDADE</td>
			<td class="tdtit grrot">SORO</td>
			<td class="tdtit grrot">PROPÉ/ARRASTO</td>
			<td class="tdtit grrot">ÁGUA</td>
			<td class="tdtit grrot">OVOS</td>
			<td class="tdtit grrot">MECÔNIO</td>
			<td class="tdtit grrot">AVES</td>
			<td class="tdtit grrot">FORRO</td>
			<td class="tdtit grrot">OUTROS</td>
			<td class="tdtit grrot">OUTROS</td>
			<td class="tdtit grrot">OUTROS</td>
		
		</tr>
		<?
		while ($row = mysqli_fetch_assoc($res)){
		?>
		  <tr class="res " style="height:20px;">
		    <td   ><?=$row["nucleo"]  ?></td>
		    <td style="text-align: center"   nowrap><?=$row["lote"] ?></td>
		    <td style="text-align: center"   nowrap><?=$row["idade"] ?></td>
		    <td ></td>
		    <td ></td>
		    <td ></td>
		    <td ></td>
		    <td></td>
		    <td ></td>
		    <td ></td>
		    <td ></td>
		    <td ></td>
		    <td ></td>		   
		  </tr>

		<?
		} //do while
		?>
		<tr class="res"style="height:50px;">
		  	<td  colspan="13" style="vertical-align: top;"><b>OBSERVAÇÃO:</b><br>
		  	<?$varobs = nl2br($rowc["obs1"]);
		  		echo($varobs);
		  	?></td>
		  </tr>
		  <tr class="res" style="height:50px;">
		  	<td colspan="13" style="vertical-align: top;"><b>MATERIAL A SER ENTREGUE:</b><br>
		  <?$varobs2 = nl2br($rowc["obs2"]);
		  		echo($varobs2);
		  	?></td>
		  </tr>
		</table>
		
	</td>
</tr>
<?	 	
	 	$sql = "select 
			c.idpessoacontato,
			c.emailresultado,
			c.idcontato,
			c.viagem
			,nome
			,p.usuario
			,concat(dddfixo,'-',telfixo) as tel1
			,concat(dddcel,'-',telcel) as tel2
			, email
			from pessoa p
			,pessoacontato c
			where p.status='ATIVO'
			and p.idtipopessoa != 12
			and p.idpessoa = c.idcontato
			and c.viagem='Y'
			and p.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
			and c.idpessoa = ".$idpessoa." order by nome";
	$res = d::b()->query($sql) or die("A Consulta falhou :".mysql_error()."<br>Sql:".$sql);

	$rownum1= mysqli_num_rows($res);
	if($rownum1>0){
?>	
<tr style="border:0px;">
	<td style="border:0px;">
	<table class="normal">            
		<tr class="header">
			<td >CONTATO</td>
			<td >TELEFONE 1</td>
			<td >TELEFONE 2</td>
		</tr>
	<?while($row = mysqli_fetch_assoc($res)){?>
		<tr class="res" style="height:20px;">
		    <td ><?=$row["nome"]  ?></td>
		    <td ><?=$row["tel1"] ?></td>
		    <td ><?=$row["tel2"] ?></td>	
		</tr>
	<?}?>
	</table>
	</td>
</tr>
<?}?>	
</table>
</body>
</html>
