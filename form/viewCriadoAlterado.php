<?
//Caso o $_pkid esteja vazio acrecentar o controlevariaveisgetpost no módulo
if($_acao == "u"){	
	if($_pkid != null or $_pkid == ""){
		
		?>
		<div class="row w-100">

		<?
		if($_GET['_modulo'] == "evento"){
			$and = "and idevento != '".$_pkid."'";
		} else {
			$and = "";
		}

		$orderBy = 'prazo DESC';

		if($_GET['_modulo'] == "tag"){
			$concatsql="
					union all
					SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo  
						FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
						JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
						JOIN carbonnovo._status s ON s.idstatus = f.idstatus
					WHERE e.idequipamento = '".$_pkid."' ";
			$orderBy = 'eventotipo ASC, prazo DESC';
		}

			$sqle="select * From (SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
						FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
						JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
						JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
					WHERE e.modulo = '".$_GET['_modulo']."' and e.idmodulo ='".$_pkid."'".$and." 
					union all
					select e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
						from eventoobj o join evento e on (e.idevento = o.idevento)
						join eventotipo t on t.ideventotipo = e.ideventotipo 
						JOIN fluxostatus f ON (f.idfluxostatus = e.idfluxostatus)
						JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
						where o.objeto in ('".$_GET['_modulo']."')
						and o.idobjeto= '".$_pkid."' ".$concatsql.") e order by $orderBy";

	/*	
			$sqle="SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status 
				FROM evento e JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
				JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
			WHERE e.modulo = '".$_GET['_modulo']."' and e.idmodulo ='".$_pkid."' $and";
		*/

		$rese = d::b()->query($sqle) or die("A Consulta dos eventos falhou :".mysql_error()."<br>Sql:".$sqle);
	
     
		if($qtde=mysqli_num_rows($rese) > 0){
			$colmd = 6;
			?>
		
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading"  data-toggle="collapse" href="#gpEventos">Evento(s)</div>
					<div class="panel-body collapse overflow-x-auto" id="gpEventos" style="padding-top: 8px !important;">
						<table  class="table table-striped planilha"> 
							<tr>
								<td>ID</td>
								<td>Evento</td>
								<td>Tipo</td>
								<td>Prazo</td>
								<td>Status</td>
							</tr>
							<?while($rowe=mysqli_fetch_assoc($rese)){?>
								<tr>
									<td>
										<a class="background-color: #FFEFD1; pointer hoverazul" title="Evento" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$rowe["idevento"]?>')"><?=$rowe["idevento"]?></a>
									</td>
									<td><?=$rowe["evento"]?></td>
									<td><?=$rowe["eventotipo"]?></td>
									<td><?=dma($rowe["prazo"])?></td>
									<td><?=$rowe["status"]?></td>
								</tr>
							<?}?>
						</table>
					</div>            
				</div>   
			</div>

		<?}else{
			$colmd = 12;
		}
		
		if(empty($idRefDefaultDropzone)){
			$idRefDefaultDropzone = "";
		}

		if($_disableDefaultDropzone != true){
		?>
			<div class="col-xs-<?=$colmd?> px-0">
				<div class="panel panel-default">   
					<div class="panel-heading"  data-toggle="collapse" href="#gpArqAnexos">Arquivos Anexos</div>
					<div class="panel-body collapse" id="gpArqAnexos" style="padding-top: 8px !important;">
						<div class="cbupload" id="<?=$idRefDefaultDropzone?>" title="Clique ou arraste arquivos para cá" style="width:100%;">
							<i class="fa fa-cloud-upload fonte18"></i>
						</div>
					</div>
				</div>
			</div>
		<?}?>

		</div>
	<?}?>
<?}?>

<div class="row w-100">
	<div class="col-xs-12 px-0">
		<div class="panel panel-default">		
			<div class="panel-body" style="padding-top: 8px !important;background:#e6e6e6">
				<div class="w-100 d-flex flex-wrap">
					<div class="col-xs-12 col-md-6 flex justify-content-center">
						<div class="flex">
							<span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
								Criação:
							</span>
							<span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
								<?=${"_1_u_".$tabaud."_criadopor"}?>
							</span> 
							<span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
								<?=${"_1_u_".$tabaud."_criadoem"}?>
							</span>
						</div>
					</div>     
					<div class="col-xs-12 col-md-6 flex justify-content-center">
						<div class="flex">
							<span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
								Alteração:
							</span>
							<span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
								<?=${"_1_u_".$tabaud."_alteradopor"}?>
							</span> 
							<span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
								<?=${"_1_u_".$tabaud."_alteradoem"}?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>