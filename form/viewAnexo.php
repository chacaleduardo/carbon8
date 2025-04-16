<?
if($_acao == "u"){	
	if($_pkid != null or $_pkid == ""){		
		?>
		<div class="row w-100">
			<?		
			if(empty($idRefDefaultDropzone)){
				$idRefDefaultDropzone = "";
			}

			if($_disableDefaultDropzone != true){
				?>
				<div class="col-xs-12 px-0">
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
<?
}
?>