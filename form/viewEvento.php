<?
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

			$rese = d::b()->query($sqle) or die("A Consulta dos eventos falhou :".mysql_error()."<br>Sql:".$sqle);		
			?>			
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading"  data-toggle="collapse" href="#gpEventos">Evento(s)</div>
					<div class="panel-body collapse overflow-x-auto" id="gpEventos" style="padding-top: 8px !important;">
						<?
						if($qtde=mysqli_num_rows($rese) > 0){ ?>
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
							<?
						} else {
							?>
							Não possui Evento.
							<?
						}
						?>
					</div>            
				</div>   
			</div>
		</div>
	<?}?>
<?
}
?>
