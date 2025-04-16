<?
include_once("../inc/php/validaacesso.php");
$listaacao =$_GET["listaacao"];
?>
<html>
<head>
<title>Tag</title>
</head>


<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../inc/js/functions.js"></script>

<style>
.res{
	text-transform: uppercase;
}
</style>
<body>
<?
// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	//$figurarelatorio = "../inc/img/repheader.png";
	$figurarelatorio = $figrel["logosis"];
	
//$figurarelatorio = "../inc/img/repheader.png";
if (!empty($_REQUEST['idtag'])) {
$id = $_REQUEST['idtag'];
}
	

$sql=" select 
			t.idtag,
			t.status,
			t.descricao,
			t.fabricante,
			t.modelo,
			t.numnfe,
			t.nserie,
			t.exatidao,
			t.padraotempmin,
			t.padraotempmax,
			t.status,
			t.exatidao,
			t.padraotempmin,
			t.padraotempmax,
			t.obs,
			tc.tagclass,
			tt.tagtipo
		from 
			tag t 
		left join 
			tagclass tc on tc.idtagclass = t.idtagclass 
		left join
			tagtipo tt on tt.idtagtipo = t.idtagtipo
		where 
			t.idtag = '".$id."';
		";
//echo "<!-- ".$sql." -->";

$res = d::b()->query($sql) or die("Falha ao pesquisar Tag : " . mysqli_error(d::b()) . "<p>SQL: $sql");
$row = mysqli_fetch_array($res);
$idtag = $row['idtag'];
?>
<table>
<tr>
	<td>
	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
	</tr>
	</table>	
	</td>
	<td >
	<table class="tbrepheader">
	<tr>
      <td class="header" pre-line>Tag:</td>
      <td class="header" pre-line><?=$row['idtag'];?></td>
    </tr>
    <tr>
      <td class="header" pre-line>Classificação:</td>
      <td class="header" pre-line><?=strtoupper($row['tagclass']);?></td>
    </tr>
    <tr>
      <td class="header" pre-line>Descrição:</td>
      <td class="header " pre-line><?=strtoupper($row['descricao']);?></td>
    </tr>
	<tr>
      <td class="header" pre-line>Status:</td>
      <td class="header " pre-line><?=strtoupper($row['status']);?></td>
    </tr>
	</table>
	</td>
</tr>
</table>	

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Reservas da TAG</legend>
</fieldset>	
<?
if(!empty($idtag)){
  $sql = "SELECT 
				*
			FROM
				tagreserva tr where tr.idtag =".$idtag." and inicio > DATE_SUB(now(), INTERVAL 15 DAY) order by tr.fim";
	
	$res = d::b()->query($sql) or die("A Consulta das reservas falhou:".mysql_error()."<br>Sql:".$sql); 
	$qtdrow= mysqli_num_rows($res);
	
	if($qtdrow> 0){
?> 

<p>&nbsp;</p>	
<table class='normal'>

<tr class="header">

                        <td >Evento</td>
                        <td >Início</td>	
                        <td >Fim</td>							
                        <td >Trava</td>
                        <td >Status</td>
						<td >Reservado por</td>
						<td >Reservado em</td>
                    </tr>
</tr>
 <?
                    while($rowa=mysqli_fetch_assoc($res)){
						if($rowa['objeto']=='evento'){
							$sq="select evento from evento where idevento =".$rowa['idobjeto'];
							$re = d::b()->query($sq) or die("A Consulta do evento falhou:".mysql_error()."<br>Sql:".$sq); 
							$ro= mysqli_fetch_assoc($re);
							$evento=$ro['evento'];
						}elseif($rowa['objeto']=='loteativ'){
							$sq="select concat(l.partida,' - ',a.ativ) as evento from loteativ a join lote l on(l.idlote=a.idlote)
								where a.idloteativ =".$rowa['idobjeto'];
							$re = d::b()->query($sq) or die("A Consulta da loteativ falhou:".mysql_error()."<br>Sql:".$sq); 
							$ro= mysqli_fetch_assoc($re);
							$evento=$ro['evento'];
							
						}else{
							$evento=$rowa['evento'];
						}
                    ?>
                    <tr class="res">
                        <td ><?=$evento?></td>
                        <td ><?=dmahms($rowa["inicio"])?></td>
                        <td ><?=dmahms($rowa["fim"])?></td>
                        <td ><?=$rowa["trava"]?></td>		
                        <td ><?=$rowa["status"]?></td>	
						<td ><?=$rowa["alteradopor"]?></td>	
						<td ><?=dmahms($rowa["alteradoem"])?></td>	
                    </tr>
                    <?
                    }
                    ?>
</table>
<?
	}
?>

<p>&nbsp;</p>
	<? } ?>

</body>
</html>


