<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/solcomimagensitens_controller.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

$_idsolcomitem = $_GET['idsolcomitem'];
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
    		<div class="panel-heading">
				<table>
					<tr>
						<td align="right"><strong>Produto:</strong></td>
						<td nowrap>										
							<label class="alert-warning"><?=traduzid('solcomitem', 'idsolcomitem', 'descr', $_idsolcomitem)?></label>											
							<input name="_1_u_solcomitem_idsolcomitem" type="hidden" value="<?=$_idsolcomitem?>"> 
							<input name="_1_u_solcomitem_idprodserv" type="hidden" value="<?=traduzid('solcomitem', 'idsolcomitem', 'idprodserv', $_idsolcomitem)?>"> 
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="cbupload" id="fotoproduto" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
					<i class="fa fa-cloud-upload fonte18"></i>
				</div>
				<br /><br /><br /><br />
				<?
				$resImagem = SolcomImagensItensController::buscarArquivoPorTipoArquivoTipoobjetoIdobjeto('FOTOPRODUTO', 'solcomitem', $_idsolcomitem);
				?>
				<table>
					<tr>
						<? 
						$i = 0;
						foreach($resImagem as $_imagens) 
						{ 
							?>
							<td style="padding-right: 30px;">
								<table>
									<tr>
										<td colspan="2">
											<a href="<?=$_imagens['caminho']?>" target="_blank"><img id="imagempequena" src="<?=$_imagens['caminho']?>" style="width: 100px;height: 100px;" title="Clique para abrir com tamanho original em uma nova Aba"/></a>
										</td>
									</tr>
									<tr>
										<td style="text-align: center;">
											<input type="radio" id="imagempadrao" name="imagempadrao" <? if($_imagens['imagempadrao'] == 'Y'){ ?> checked <? } ?> idarquivo="<?=$_imagens['idarquivo']?>" title="Imagem Padrão" onclick="InserirImagemPadrao()" style="vertical-align: middle;">
											<i style="font-size: medium;" class="fa fa-trash fa-1x btn-lg pointer" title="Adicione uma Imagem do Produto" onclick="removerImagem('<?=$_imagens['idarquivo']?>')"></i>
										</td>
									</tr>
								</table>
							</td>
							<? 
							$i++;
							if(($i%8) == 0)
							{
								echo '</tr><tr>';
							}
						} 
						?>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<? require_once('../form/js/solcomimagensitens_js.php'); ?>