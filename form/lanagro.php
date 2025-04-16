<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/lanagro_controller.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
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



/*
 * colocar condição para executar select
*/
if($_GET and !empty($clausulad)){

	$res = LanagroController::inserirResultadoPositivo($clausulad);
	
	$res = LanagroController::montarConsultaParaRelatorio($clausulad);

	$qtdr=count($res);
	
	
	
	
}
?>

<style>

a.dcontexto {
	position: relative;
	font: 12px arial, verdana, helvetica, sans-serif;
	padding: 0;
	color: #039;
	text-decoration: none;
	cursor: hand;
	z-index: 24;
}
a.dcontexto:hover {
	background: transparent;
	z-index: 25;
}
a.dcontexto div {
	display: none;
}
a.dcontexto:hover div {
	display: block;
	position: absolute;
	width: 230px;
	top: 0em;
	text-align: justify;
	left: 6em;
	font: 10px Verdana, arial, helvetica, sans-serif;
	padding: 5px 10px;
	border: 1px solid #999;
	background: #E8EBF2;
	color: #000;
}
.mostratab{
	display:none;
}

</style>


<style data-cke-temp="1" type="text/css" media="<?=$cssp?>">

.escondetab{
	display: none;
}
.mostratab{
	display:block;
}

</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >

	<table>
	<?if(empty($exercicio)){$exercicio=date('Y');}?>
		<tr>
			<td class="rotulo">Exercício</td>
			<td></td>
			<td><input name="exercicio" vpar="" value="<?=$exercicio?>" autocomplete="off" type="text"  class="input10"></td>
		</tr>
		<tr>
			<td class="rotulo">Período</td>
			<td><font class="9graybold">entre</font></td>
			<td><input name="vencimento_1" vpar="" id="vencimento_1"
				value="<?=$vencimento_1?>" autocomplete="off" type="text"
				class="size10 calendario " onchange="this.focus()"> 
                        </td>
			<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
			<td><input name="vencimento_2" vpar="" id="vencimento_2"
				value="<?=$vencimento_2?>"  type="text"
				class="calendario size10" onfocus="fill_2(this)">
                        </td>
		</tr>
		<tr>
			<td class="rotulo">ID Reg.</td>
			<td><font class="9graybold">entre</font></td>
			<td><input name="idregistro_1" vpar="" value="<?=$idregistro_1?>" autocomplete="off" type="text"  class="input10"></td>
			<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
			<td><input name="idregistro_2" vpar="" value="<?=$idregistro_2?>" autocomplete="off" type="text"  class="input10"></td>
		</tr>
		<tr>
                    <td></td>
                    <td></td>
                    <td>
                        <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
                            <span class="fa fa-search"></span>
                        </button> 
                    </td>
                    <td>
                        <a title="IMPRIMIR" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimir();">  &nbsp;&nbsp;Imprimir</a>     
                    </td>
		</tr>
	</table>
        </div>
    </div>
    </div>
</div>
	
	<?if($_GET and !empty($clausulad)){?>
 <div class="row">
    <div class="col-md-12" >
    	<div class="panel panel-default" >
        <div class="panel-heading"><?=$qtdr?> Resultados</div>
        	<div class="panel-body">
				<table class="table table-striped planilha">
					<tr class="header">
						<th align="center">Reg.</th>
						<!-- th align="center">Propriedade</th -->
						<th align="center">Tipo <br> de <br> Ave</th>
						<th align="center">Tipo <br> de <br> Amostra</th>
						<th align="center">Esp.Finalidade</th>
						<th align="center">Idade</th>
						<th align="center">Tipo <br> de <br> Exploração</th>
						<th align="center">Tipo <br> de <br> Vigilancia</th>
						<th align="center">Motilidade</th>
						<th align="center">Salina 2%</th>
						<th align="center">O</th>
						<th align="center">B</th>   
						<th align="center">D</th> 
						<th align="center">HG</th>
						<th align="center">HM</th>
						<th align="center">HP</th>
						<th align="center">Hi</th>
						<th align="center">H2</th>
						<th align="center">Hr</th>
						<th align="center">Diagnóstico</th> 
						<th>Obs</th>
						<th align="center">Imprimir</th> 
					</tr>
					<?$i=0;
					foreach($res as $k => $row){
						$i=$i+1;
						
						if($row['status']=='INATIVO'){
							$cor="white";
							}else{
								$cor="#00FF7F";
							}?>
						<tr class="respreto " style="background-color: <?=$cor?>"> 
							<td align="center"><input 	name="_<?=$i?>_u_plpositivo_idplpositivo"	type="hidden"	value="<?=$row['idplpositivo']?>"	readonly='readonly'	> 
								<input 	name="_<?=$i?>_u_plpositivo_idresultado" 	type="hidden" 	value="<?=$row['idresultado']?>" 	>
								
								<a onclick="javascript:janelamodal('inclusaoresultado.php?acao=u&idamostra=<?=$row['idamostra']?>&idresultado=<?=$row['idresultado']?>');"
								class="dcontexto"><b><?=$row['idregistro']?></b><div><? echo strip_tags($row["descritivo"], '<span>');
								?></div></a>
							
							</td> 
							<!-- td>
								<input 
									name="_<?=$i?>_u_plpositivo_propriedade" 
									type="text" 
									value="<?=$row['propriedade']?>" 
														>
							</td --> 
							
							<td>
							<?
							$alertatipoave = empty($row['tipoavenovo'])?"background-color:red;":"";
							?>
							<select name="_<?=$i?>_u_plpositivo_tipoave" style="font-size: 10px;<?=$alertatipoave?>">
								<option value=""></option>
								<?fillselect("select 'GALINHA','GALINHA' union select 'PERU','PERU' union select 'AVESTRUZ','AVESTRUZ' union select 'CODORNA','CODORNA'
										union select 'MARRECO','MARRECO' union select 'PATO','PATO' union select 'EMA','EMA' union select 'PERDIZ','PERDIZ' 
										union select 'OUTRAS (espec. OBS)','OUTRAS'",$row['tipoavenovo']);?>
							</select>
							</td> 
							<td>
								<input type="hidden" name="_<?=$i?>_u_plpositivo_tipoamostra" style="font-size: 10px" value="<?=$row['tiposubtipoamostra']?>">
								<?=$row['tiposubtipoamostra']?>
						<!--		<select name="_<?=$i?>_u_plpositivo_tipoamostra" style="font-size: 10px">
									<option value=""></option>
									<?fillselect("	select 'Fezes','Fezes' union select
													'Papel de caixa transporte','Papel cx transp.'  union select
													'Mecà´nio','Mecà´nio' union select
													'Ninho','Ninho' union select
													'Ovos Férteis','Ovos Férteis' union select
													'Ovos Bicados','Ovos Bicados' union select
													'àrgãos','àrgãos' union select
													'Suabe de Arrasto','SWB de Arrasto'  union select
													'Suabe de Cloaca','SWB de Cloaca' union select
													'Suabe de fundo de caixa','SWB fundo de cx.' union select
													'Suabe de Traquéia','SWB de Traquéia' union select 
													'Soro Sanguà­neo','Soro Sang.' union select
													'Propé','Propé' union select
													'Pinto morto','Pinto morto'",$row['tipoamostra']);?>		
								</select>-->
							</td> 
							<td>
						<?
							echo $row['espfin'];
						?>
							</td>
							<td>
						<?
							echo $row['idadetipo'];
						?>
							</td>
							
							<td>
								<select name="_<?=$i?>_u_plpositivo_tipoexploracao" style="font-size: 10px">
									<option value=""></option>
									<?fillselect("select 'MATRIZ','MATRIZ' union select 
												'AVà','AVà' union select
												'POSTURA','POSTURA' union select
												'BISAVà','BISAVà' union select
												'LINHA PURA','LINHA PURA' union select
												'SPF','SPF' union select
												'PROD. OVOS CONTR.','PROD. OVOS CTR.' union select
												'CORTE','CORTE' union select
												'OUTRAS (espec.OBS)','OUTRAS'",$row['tipoexploracao']);?>		
								</select>
							</td> 
							
							<td>
								<select name="_<?=$i?>_u_plpositivo_tipovigilancia" style="font-size: 10px">
									<option value=""></option>
									<?fillselect("SELECT 'CERTIFICAààO','CERTIFICAààO' union select 
										'MONITORAM. COMERCIAL IN 10/20013','M. COM. IN 10/20013' union select
										'OF. CIRCULAR 01/2009  e complementares','OF. CIRC. 01/2009'",$row['tipovigilancia']);?>		
										</select>
							</td> 
							
							<td>
								<select name="_<?=$i?>_u_plpositivo_flmotilidade">
									<option value=""></option>
									<?fillselect(array('R'=>'R','N'=>'N','NA'=>'NA'),$row['flmotilidade']);?>		
										</select>
							</td> 
							
							<td>
								<select name="_<?=$i?>_u_plpositivo_flsalina">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flsalina']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_flo">
								<option value=""></option>
									<?fillselect(array('R'=>'R','N'=>'N','NA'=>'NA'),$row['flo']);?>		
										</select>
							</td> 

							
							<td>
								<select name="_<?=$i?>_u_plpositivo_flb">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flb']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_fld">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['fld']);?>		
										</select>
							</td> 
						
							<td>
								<select name="_<?=$i?>_u_plpositivo_flhg">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flhg']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_flhm">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flhm']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_flhp">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flhp']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_flh1">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flh1']);?>		
										</select>
							</td> 

							<td>
								<select name="_<?=$i?>_u_plpositivo_flh2">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flh2']);?>		
										</select>
							</td> 
								<td>
								<select name="_<?=$i?>_u_plpositivo_flhr">
								<option value=""></option>
									<?fillselect("select 'N','N' union select 'R','R' union select 'NA','NA'",$row['flhr']);?>		
										</select>
							</td> 

							<td>
									<select name="_<?=$i?>_u_plpositivo_diagnostico" style="font-size: 10px">
									<option value=""></option>
									<?fillselect("select 'S.GALLINARUM','S.GALLIN' union
									select 'S.ENTERITIDIS','S.ENTERIT' union
									select 'S.TYPHIMURIUM','S.TYPHIM' union
									select 'S.PULLORUM','S.PULL' union
									select 'S.PP','S.PP' union
									select 'CEPA RUGOSA','C. RUG.'  union
									select '1,4[5],12:-:1,2','1,4[5],12:-:1,2'  union
									select '1,4[5],12:i:-','1,4[5],12:i:-'",$row['diagnostico']);?>		
												</select>
							</td> 

							<td>
								<input 
									name="_<?=$i?>_u_plpositivo_obs" 
									type="text" 
									value="<?=$row['obs']?>" 
														>
							</td> 
							<td align="center">
								<select name="_<?=$i?>_u_plpositivo_status">
							
									<?fillselect("select 'INATIVO','N' union select 'ATIVO','S'",$row['status']);?>		
										</select>
							</td> 
							
						</tr>



				<?}?>
				</table>
			<?}?>
            </div>
    	</div>
    </div>
</div>
<?require_once(__DIR__."/js/lanagro_js.php");?>
