<?
require_once("../inc/php/validaacesso.php");
if ($_GET['versao'] == 0) {
	$versao = true;
}else {
	
}

if (empty($_GET['idprativ']) or is_null($_GET['versao'])) {
	die('Parametros GET não fornecidos!');
 }else {
	 
 }


function jsonAtiv(){

	$sql= "select distinct trim(ativ) as ativ
			from prativ 
			where ativ>''
				and length(ativ)>2
                                ".getidempresa('idempresa','prativ')."
			order by trim(ativ)";

	$res = d::b()->query($sql) or die("Erro ao recuperar Hist de Ativ: ".mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysql_fetch_assoc($res)) {
		$arrtmp[$r["ativ"]]=$r["ativ"];
        $i++;
    }

	$json = new Services_JSON();
	return $json->encode($arrtmp);

}


$sqlproc="select * from objetojson where idobjeto=".$_GET['idprativ']." and tipoobjeto='prativ' and versaoobjeto=".$_GET['versao'];
$res = d::b()->query($sqlproc) or die("Erro ao recuperar json: ".mysqli_error(d::b()));
$row = mysqli_fetch_assoc($res);
$rc= unserialize(base64_decode($row["jobjeto"]));
$_1_u_prativ_idprativ = $rc['prativ']['res']['idprativ'];
$_1_u_prativ_ativ = $rc['prativ']['res']['ativ'];
$_1_u_prativ_nomecurtoativ = $rc['prativ']['res']['nomecurtoativ'];
$_1_u_prativ_travasala = $rc['prativ']['res']['travasala'];
$_1_u_prativ_versao = $rc['prativ']['res']['versao'];
$_1_u_prativ_status = $rc['prativ']['res']['status'];
$_1_u_prativ_idsubtipoamostra = $rc['prativ']['res']['idsubtipoamostra'];
?>

<html>
<head>
<title>Atividade</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
<style>
.rotulo{
font-weight: bold;
font-size: 9px;
}
.texto{
font-size: 9px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}
.breakw {
	word-break: break-word;
}

@media print{
	#rodapengeraarquivo{
		position: fixed;
		bottom: 0;
	}
	.breakw {
	word-break: break-word;
	position:absolute; 
	top: 3cm;
}
}
</style>
</head>
<body >
<div style="width: 650px;">
<?
$_sqltimbrado="select * from empresaimagem where 1 ".getidempresa('idempresa','empresa')." and tipoimagem = 'HEADERPRODUTO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado=mysql_fetch_assoc($_restimbrado);
			$_timbradocabecalho = $_figtimbrado["caminho"];

if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
				<br>
			<?}?>
			<table border="1" cellspacing='0' cellpading='0'> 
				<tr>
					<td>
						<table > 
								<tr>
									<td class="rotulo">Versão:</td>
									<td ><?=$_1_u_prativ_versao?>.0</td>
								</tr>
								<tr  >
									<td class="rotulo">Atividade:</td>
									<td colspan="3"><?=$_1_u_prativ_ativ?></td>
								
								</tr>
								<tr >
									<td  class="rotulo">
										Nome Curto Atividade:
									</td>
									<td><?=$_1_u_prativ_nomecurtoativ?></td>
									
								
								</tr>
								<tr>
									<td class="rotulo">Sala:</td>
									<td class="texto">
										<?
										echo ($rc["prativsala"]["res"]['tagtipo']);
										?>
										
									</td>
								</tr>
								<tr>  
									<td  class="rotulo">Reserva:</td>
									<td class="texto">
									<?
										if($_1_u_prativ_travasala=='S'){
											echo("Simultânea");
										}elseif($_1_u_prativ_travasala=='C'){
											echo("Compartilhada");
										}elseif($_1_u_prativ_travasala=='E'){
											echo("Exclusiva");
										}
									?>
									</td>
									<td class="rotulo">Status:</td>
									<td><?=$_1_u_prativ_status?></td>	
								</tr>						
							</table>
						</td>
				</tr>
<?if($_1_u_prativ_idprativ){?>
				<tr>
					<td>
						<table class="normal">

						<tr>
							<td class="header" >Campos:</td>
						</tr>
						<tr>
							<td class="res">
							<?
							$virg='';
							$i1 = 1;
							while($rowc= $rc['prativobjcampos']['res'][$i1]){						
							?>	
								
								<?
									echo($virg.$rowc["descr"]);
								?>
									
								
							<?
							$virg=', ';
							$i1 ++;
							}
							?>
							</td>
							</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
					<table class="normal" >
                    <tr class="header">
                        <td>Equipamentos</td>
                    </tr>
			<?
				$i2 = 1;	
                while($item=$rc['prativobjtagtipo']['res'][$i2]){
					$i++;
			?>	
					<tr class='res'>
						<td>
							<?=$item['tagtipo']?>
						</td>						
					</tr>
			<?$i2 ++;
                }//while($item1=mysqli_fetch_assoc($resitem)){
			?>				
					
					</table>
				</td>
			</tr>
			<tr>
				<td>
                    <table class="normal" >
                    <tr class="header">
                        <td>Testes</td>
                    </tr>
					<tr>
						<td><?=$rc['prativ']['res']['subtipoamostra']?></td>
					</tr>
			<?
					$i3 = 1;
					while($item=$rc['prativobjteste']['res'][$i3]){
					$i++;
			?>
					<tr>
						<td>
							<?=$item['descr']?>
						</td>						
					</tr>
			<?$i3 ++;
					}//while($item1=mysqli_fetch_assoc($resitem)){
			?>
					</table>
				</td>
			</tr>
             <tr>
				<td>
					<table class="normal" >
                    <tr class="header">
                        <td>Informações especí­ficas</td>
                    </tr>
					<?
					$i4 = 1;			
					while($item=$rc['prativobjctrl']['res'][$i4]){?>	
					<tr>
						<td>
                            <?=$item['descr']?>
                        </td>						
					</tr>
			<?
					$i4 ++;
					}//while($item1=mysqli_fetch_assoc($resitem)){
				?>
					</table>
				</td>
			 </tr>
			 <tr>
				<td>
					<table class="normal" >
                    <tr class="header">
                        <td>Materiais e Utensí­lios</td>
                    </tr>
	<?				$i5 = 1;
					while($item=$rc['prativobjmat']['res'][$i5]){
			?>	
					<tr>
						<td>
							<?=$item['descr']?>
                    </td>
					</tr>
			<?
				$i5++;
					}//while($item1=mysqli_fetch_assoc($resitem)){?>					
					
					</table>
				</td>
			 </tr>
			</table>
			<br>
<?
                                        
}//if($_1_u_prativ_idprativ){
?>				

<table cellspacing='0' cellpading='0' border="1" style="width: 650px;">
<tr  class="header">
    <td colspan="2">
		<b>Histórico</b>
    </td>
</tr>
<tr>
    <td>
        <b>Versões</b>
    </td>
    <td>
        <b>Descrição</b>	
    </td>
        <?		$sql = 'SELECT * FROM objetojson where idobjeto ='.$_1_u_prativ_idprativ.' and versaoobjeto <= '.$_GET['versao'].' and tipoobjeto="prativ" order by versaoobjeto desc';
                $res = d::b()->query($sql) or die("A Consulta de versões falhou :".mysqli_error(d::b())."<br>Sql:".$sql);
                while($row = mysqli_fetch_assoc($res)){			
        ?>	
                        <tr class="res">
						<td nowrap>Versão:  <?=$row['versaoobjeto']?>.0</td>
                            <?
                            $rc1 = unserialize(base64_decode($row["jobjeto"]));
                            ?>
                            <td style="line-height: 1.5;"><?=nl2br($rc1['prativ']['res']['descr'])?></td>
                        </tr>				
        <?							
                }
        ?>	
    </td>
</tr>
</table>
</div>
</body>
</html>