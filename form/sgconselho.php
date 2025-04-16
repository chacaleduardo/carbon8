<?

require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parámetros GET que devem ser validados para compor o select principal
 *                pk: indica parámetro chave para o select inicial
 *                vnulo: indica parámetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "sgconselho";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idsgconselho" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".sgconselho where idsgconselho = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__."/controllers/sgconselho_controller.php");
require_once(__DIR__."/controllers/pessoa_controller.php");
require_once(__DIR__ ."/controllers/imgrupo_controller.php");
require_once(__DIR__ ."/controllers/unidade_controller.php");
require_once(__DIR__ ."/controllers/sgdocumento_controller.php");

if($_1_u_sgconselho_idsgconselho)
{
	$pessoasDisponiveisParaVinculo = PessoaController::buscarPessoasDisponiveisParaVinculoPorIdEmpresa(cb::idempresa());
	$lpsDisponiveisParaVinculo = SgconselhoController::buscarLpsDisponiveisParaVinculoPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$unidadesDisponiveisParaVinculo = SgconselhoController::buscarUnidadesDisponiveisParaVinculoPorIdSconselho($_1_u_sgconselho_idsgconselho, cb::idempresa());
	$historico = SgconselhoController::buscarHistoricoPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$areasDisponiveisParaVinculo = SgconselhoController::buscarAreasDisponiveisParaVinculoPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$unidadesConselho = SgconselhoController::buscarUnidadesPorIdSgconselhoEIdEmpresa($_1_u_sgconselho_idsgconselho, cb::idempresa());
	$unidades = SgconselhoController::buscarUnidadesPorIdsgconselhoEIdempresaPadrao($_1_u_sgconselho_idsgconselho, cb::idempresa());
	$possuiVinculoComUnidade = UnidadeController::verificarSeExisteVinculoComUnidadePorIdObjetoETipoObjeto($_1_u_sgconselho_idsgconselho, 'sgconselho');
	$gruposDisponiveisParaVinculo = ImGrupoController::buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa($_1_u_sgconselho_idsgconselho, 'sgconselho');
	$documentosVinculados = SgdocumentoController::buscarDocumentosVinculadosPorIdSgSetor($_1_u_sgconselho_idsgconselho, 'sgconselho');
	$lps = SgconselhoController::buscarLpsPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$coordenadores = SgconselhoController::buscarCoordenadoresPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$areas = SgconselhoController::buscarAreasPorIdSgconselho($_1_u_sgconselho_idsgconselho);
	$grupos = ImGrupoController::buscarGruposPorIdObjetoVincETipoObjetoVinc($_1_u_sgconselho_idsgconselho, 'sgconselho');

	$unidadesDisponiveisParaVinculoEnvio=SgconselhoController::buscarUnidadesDisponiveisParaVinculoEnvio(cb::idempresa());

	if($_1_u_sgconselho_grupo == 'Y')
	{
		$idImGrupo = ImGrupoController::buscarGrupoPorIdObjetoExtETipoObjetoExt($_1_u_sgconselho_idsgconselho, 'sgconselho')['idimgrupo'];
	}
}

//Lista as Lp's inseridas no Conselho (Lidiane - 13-03-2020)
function listarLps()
{
    global $lps;


	
	foreach ($lps as $lp)
	{
        echo "	<tr>
					<td><a title='Editar LP' target='_blank' href='?_modulo=_lp&_acao=u&idlpgrupo={$lp["idlpgrupopai"]}'>{$lp["descricao"]}</a></td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularLp({$lp['idlpobjeto']})' title='Excluir!'></i>
					</td>
                </tr>";
    }	
}

function listarFucionario()
{
    global $coordenadores;

	

	foreach ($coordenadores as $coordenador)
	{
		$modulo="pessoa";
		if($coordenador["idtipopessoa"]==1)
		{
			$modulo="funcionario";
		}

        echo "	<tr>
					<td><a title='Editar Funcionario' target='_blank' href='?_modulo=".$modulo."&_acao=u&idpessoa=".$coordenador["idpessoa"]."'>".$coordenador["nome"]."</a></td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularPessoaArea(".$coordenador['idpessoaobjeto'].")' title='Excluir!'></i>
					</td>
				</tr>";
    }
	
}

function listarAreas()
{
    global $areas;
	
	
	
	foreach($areas as $key => $item)
    {		
        $modulo = 'sgarea';

        echo "  <tr>
                    <td><a title='Editar Área' target='_blank' href='?_modulo=$modulo&_acao=u&id$modulo=".$item["idsgarea"]."'>".$item["area"]."</a></td>
                    <td align='right'>	
                        <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularArea(".$item['idobjetovinculo'].")' title='Excluir!'></i>
                    </td>   
                </tr>";
    }	
}

function listarUnidades($unidadesDpt= null, $idobjeto = null, $tipoobjeto = null)
{
	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	if(count($unidadesDpt['centrocusto']))
	{
		foreach($unidadesDpt['centrocusto'] as $unidade)
		{
			if(isset($unidade['error']))
			{
				echo "  <tr>
							<td class='py-3'>{$unidade['error']}</td>
						</tr>";

				break;
			}

			$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";

			if(($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor'))
			{
				$trContent .= " <td align='right'>
									<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidade(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
								</td>";
				
				$trContent = str_replace("colspan='2'", '', $trContent);
			}

			$tr = " $trStart
						$trContent
					$trEnd";

			echo $tr;
		}
	}	


}

function listarGruposVinculados()
{
	global $grupos;

	

	foreach ($grupos as $grupo) {
		echo "	<tr>
					<td>
						<a title='Editar Grupo' target='_blank' href='?_modulo=imgrupo&_acao=u&idimgrupo={$grupo["idimgrupo"]}'>{$grupo["grupo"]}</a>
					</td>
					<td align='right' width='70px'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativavinculo({$grupo['idobjetovinculo']})' title='Excluir!'></i>
					</td>
				</tr>";
	}
}


function mostrarUnidades($unidades= null, $idobjeto = null, $tipoobjeto = null,$input='Y')
{
	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	if(count($unidades)> 0)
	{
		$i=99;
		foreach($unidades as $unidade)
		{ 
			$i=$i+1;
			if(isset($unidade['error']))
			{
				echo "  <tr>
							<td class='py-3'>{$unidade['error']}</td>
						</tr>";

				break;
			}
			if($input=='Y'){
				$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>
				<td title='Percentual'>
					<input type='hidden' name='_".$i."_idunidaderateio' value='".$unidade['idunidaderateio']."' >
					<input title='Percentual' type='text' name='_".$i."_rateio' class='size5 valorrateio' value='".$unidade['rateio']."' onchange='atualizarateio(this,".$unidade['idunidaderateio'].")' > %
				</td>";
	
			}else{
				$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>
				<td title='Percentual'>
				".number_format(tratanumero($unidade['rateio']), 2, ',', '.')." % </td>";
			}

		
			if($input=='Y'){
				$trContent .= " <td align='right'>
									<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidadeRateio(" . $unidade['idunidaderateio'] . ")' title='Excluir!'></i>
								</td>";
				
				$trContent = str_replace("colspan='2'", '', $trContent);
			}

			$tr = " $trStart
						$trContent
					$trEnd";

			echo $tr;
		}
	}	


}

?>
<!-- CSS -->
<link href="/form/css/sgconselho_css.css?_<?=date("dmYhms")?>" rel="stylesheet" />
<style>
.cabitem {
	background-color: #e6e6e6 !important;
}	
.cabitemAcao {
	background-color: #e6e6e6 !important;
	text-align-last: right;
}
.panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.left-content {
    display: flex;
    align-items: center;
}

.left-content a {
    margin-right: 10px; /* Espaçamento entre o ícone e o texto */
}
</style>
<div class="col-xs-12 px-0">
    <div class="panel panel-default">
        <div class="panel-heading">
			<div class="w-100 d-flex flex-wrap flex-between">
				<? if($_1_u_sgconselho_idsgconselho) { ?>
					<!-- ID -->
					<input 
						name="_1_<?=$_acao?>_sgconselho_idsgconselho" 
						type="hidden" 
						value="<?=$_1_u_sgconselho_idsgconselho?>" 
						readonly='readonly'>
					<div class="col-xs-6 col-sm-1 form-group">
						<label class="text-white">ID</label>
						<div class="form-control alert-warning">
							<label>
								<?= $_1_u_sgconselho_idsgconselho ?>
							</label>
						</div>
					</div>
				<?}?>
				<!-- CONSELHO -->
				<div class="col-xs-6 col-sm-3 form-group">
					<label class="text-white">Conselho</label>
					<input class="form-control"
						name="_1_<?=$_acao?>_sgconselho_conselho" 
						type="text" 
						value="<?=$_1_u_sgconselho_conselho?>" 
						vnulo>
				</div>
				<!-- GRUPO -->
				<div class="col-xs-6 col-sm-1 form-group">
					<label class="text-white">Grupo</label>
					<div class='nowrap'>
					<select id="status" name="_1_<?= $_acao ?>_sgconselho_grupo" class="form-control">
						<? fillselect(SgconselhoController::$grupo, $_1_u_sgconselho_grupo); ?>
					</select>
					<? if($_1_u_sgconselho_grupo=='Y' && $_1_u_sgconselho_status == 'ATIVO') { ?>
							<? if($idImGrupo) { ?>
								<a href="?_modulo=imgrupo&_acao=u&idimgrupo=<?= $idImGrupo ?>&_idempresa=1" class="fa fa-bars pointer hoverazul" title="Grupo" target="_blank"></a>
							<? } else {?>
								<i class="fa fa-warning text-warning" title="Grupo não gerado! (Atualizar bim)"></i>
							<? } ?>
						<? } ?>
					</div>
				</div>

				<!-- STATUS -->
				<div class="col-xs-6 col-sm-1 form-group">
					<label class="text-white">Status</label>
					<select id="status" name="_1_<?=$_acao?>_sgconselho_status" class="form-control">
						<?fillselect(SgconselhoController::$status, $_1_u_sgconselho_status);?>
					</select>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="d-flex flex-wrap w-100">
				
				<!-- Descricao -->
				<div class="col-xs-12 col-sm-12 form-group">
					<label>Descrição:</label>
					<textarea name="_1_<?=$_acao?>_sgconselho_desc"><?=$_1_u_sgconselho_desc?></textarea>
				</div>
			</div>
		</div>
    </div>
</div>
<?if(!empty($_1_u_sgconselho_idsgconselho)){?>
	<div class="d-flex flex-wrap col-xs-12 px-0">

		<div class="col-xs-12 col-sm-6 col-md-4 px-0">			

			<!-- Inserir unidades -->
			<div class="col-xs-12">
				
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="unidadepadrao()"></a>	
							Unidade Padrão[<?=count($unidades['centrocusto'])?>] 
						</div>
						<span data-toggle="collapse" href="#listarUn" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>					
					</div>
					<div class="panel-body overflow-x-auto" id="listarUn">
						<table class="table-hover w-100 mt-3">
							<tbody id="unidades-vinculadas">
								<?= listarUnidades($unidades, $_1_u_sgconselho_idsgconselho, 'sgconselho') ?>
							</tbody>
						</table>
						<hr>
					</div>
			
					<div id="unidadepadrao" style="display: none">								
						<div class="row">
							<div class="col-md-12">
							<?if(!$possuiVinculoComUnidade) { ?>
								<table class="w-100">
									<tr>
										<td>Selecione as unidades autorizadas a solicitar custos deste conselho.</td>
									</tr>
									<tr>										
										<td>
											<input class="compacto unidade-conselho" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
										</td>
									</tr>
								</table>
							<?}
							if(count($unidades['centrocusto'])){
							?>
								<div class="panel panel-default">
									<div class="panel-body">
										<table class="table-hover w-100 mt-3 table table-striped">
										<tbody id="unidades-vinculadas">
										<tr>
											<th class="py-3 cabitem">Unidade</th> 
											<th class="cabitemAcao" >
												Ações
											</th>
										</tr>
										<?= listarUnidades($unidades, $_1_u_sgconselho_idsgconselho, 'sgconselho') ?>
										</tbody>
										</table>
									</div>
								</div>
							<?}?>
							</div>
						</div>								
					</div>				
				
					<div class="panel panel-default">				
						<div class="panel-heading d-flex justify-content-between align-items-center">
							<div class="left-content d-flex align-items-center">
								<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novaunidade()"></a>
								Unidades solicitadoras de custo [<?=count($unidadesConselho['centrocusto'])?>]
							</div>
							<span data-toggle="collapse" href="#listarUnidadeV" aria-expanded="false" >
								<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
							</span>
						</div>	
						<div class="panel-body" id="listarUnidadeV">
							<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
							<?=listarUnidades($unidadesConselho, $_1_u_sgconselho_idsgconselho, 'sgconselho') ?>
							</tbody>
							</table>
						</div>
					</div>
	
					<div id="novaunidade" style="display: none">						
						<div class="row">
							<div class="col-md-12">
								<table class="w-100">
									<tr>
										<td>Selecione as unidades autorizadas a solicitar custos deste conselho.</td>
									</tr>
									<tr>										
										<td>
											<input class="compacto inidunidade" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
										</td>
									</tr>
								</table>
								<?
								if(count($unidadesConselho['centrocusto'])){
								?>
								<div class="panel panel-default">
										<div class="panel-body">
										<table class="table-hover w-100 mt-3">
										<tbody id="unidades-vinculadas">
										<tr>
											<th class="py-3 cabitem">Unidade</th> 
											<th class="cabitemAcao" >
												Ações
											</th>
										</tr>
											<?=listarUnidades($unidadesConselho, $_1_u_sgconselho_idsgconselho, 'sgconselho') ?>
										</tbody>
										</table>
									</div>
								</div>
								<?}?>
							</div>
						</div>						
					</div>
					</div>
			</div>
		<!-- Areas conselho-->
		<div class="col-xs-12">
				<div class="panel panel-default" >
				<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novaarea()"></a>
							Áreas do conselho [<?=count($areas)?>]
						</div>	
						<span data-toggle="collapse" href="#listarArea" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
						
					</div>
					<div class="panel-body overflow-x-auto" id="listarArea">
						<table class="table-hover w-100">
							<tbody>
								<?=listarAreas()?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
			<div id="novaarea" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Selecione os departamentos abaixo para adicionar a área.</td>
							</tr>
							<tr>
								
								<td>
									<input id="conselho-area" class="compacto conselho-area" type="text" autocomplete="new-password" cbvalue placeholder="Selecione uma área">
								</td>
							</tr>
						</table>				
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Area</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
									<?=listarAreas()?>
								</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>			
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-4 px-0">
		<!-- Inserir Conselheiro -->
		<div class="col-xs-12">
				<div class="panel panel-default" >
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novapessoa()"></a>
							Conselheiro (s) [<?=count($coordenadores)?>]
						</div>	
						<span data-toggle="collapse" href="#listarnovapessoa" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>						
					</div>
					<div class="panel-body overflow-x-auto" id="listarnovapessoa">
						<table class="table-hover w-100">
							<tbody>
							<?=listarFucionario()?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
			<div id="novapessoa" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Selecione os conselheiros abaixo para adicionar ao conselho.</td>
							</tr>
							<tr>
								
								<td>
								<input id="pessoaobjeto" class="compacto pessoaobjeto" <?if($_1_u_sgconselho_status == 'INATIVO'){?> disabled <?}else{?>type="text" autocomplete="new-password" cbvalue placeholder="Selecione um conselheiro"<?}?>>
								</td>
							</tr>
						</table>				
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Nome</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
									<?=listarFucionario()?>
								</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>			
			</div>



			<?
			if(count($unidades['centrocusto']))
			{
				reset($unidades);
				foreach($unidades['centrocusto'] as $unidade)
				{
					$_idunidade=$unidade["idunidade"];
				}
				reset($unidades);
			}	
			if(!empty($_idunidade)){

				$unidadesEv = SgconselhoController:: buscarUnidadeEnviocusto($_idunidade);
				$unidadesRecebe = SgconselhoController:: buscarUnidadeRecebecusto($_idunidade);

			?>

		<!-- Envio de Custo  -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="enviocusto()"></a>	
							Envio de Custo [<?=count($unidadesEv)?>]
							<input type="hidden" value="<?=$_idunidade?>" id="valorunidadepadrao">
						</div>
						<span data-toggle="collapse" href="#listarEnvioCusto" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>
					<div class="panel-body" id="listarEnvioCusto">
						<table class="table-hover w-100 mt-3">
							<tbody id="trunidadesenviocusto">
								<?= mostrarUnidades($unidadesEv, $_idunidade, 'unidade','Y') ?>
							</tbody>
						</table>
						<div id="enviocusto" style="display: none">								
									<div class="row">
										<div class="col-md-12">
										
											<table class="w-100">
												<tr>
													<td>Selecione as unidades autorizadas a enviar custos.</td>
												</tr>
												<tr>
													
													<td>
														<input class="compacto unidadesenviocusto" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
													</td>
												</tr>
											</table>
										<?
										if(count($unidadesEv)>0){
										?>
											<div class="panel panel-default">
												<div class="panel-body">
													<table class="table-hover w-100 mt-3 table table-striped">
													<tbody >
													<tr>
														<th class="py-3 cabitem">Unidade</th> 
														<th class="py-3 cabitem">Percentual</th> 
														<th class="cabitemAcao" >
															Ações
														</th>
													</tr>
														<?=mostrarUnidades($unidadesEv, $_idunidade, 'unidade','N') ?>
													</tbody>
													</table>
												</div>
											</div>
										<?}?>
										</div>
									</div>								
							</div>	
					</div>
				</div>
			</div>
	
			<!-- Recebimento de Custo  -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							Recebimento de Custo [<?=count($unidadesRecebe)?>]
						</div>
						<span data-toggle="collapse" href="#listarRecebCusto" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>					
					<div class="panel-body overflow-x-auto" id="listarRecebCusto">
						<table class="table-hover w-100 mt-3">
							<tbody id="unidades-vinculadas">
								<?= mostrarUnidades($unidadesRecebe, $_idunidade, 'unidade','N') ?>
							</tbody>
						</table>	
					</div>
				</div>
			</div>
		
			<?}// if(!empty($_idunidade)){?>



			<!-- Grupos Vinculados -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novogrupo()"></a>
							Grupos Vinculados [<?=count($grupos)?>]
						</div>
						<span data-toggle="collapse" href="#listargrupo" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>

					</div>
					<div class="panel-body overflow-x-auto" id="listargrupo">
						<table class='table-hover w-100'>
							<tbody>
								<?= listarGruposVinculados() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
				<div id="novogrupo" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Por favor, escolha os grupos disponíveis para a area abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto vincular-grupo" type="text" autocomplete="new-password" cbvalue placeholder="Selecione o grupo">
								</td>
							</tr>
						</table>
						<?
						if(count($grupos)> 0){
						?>
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Grupo</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listarGruposVinculados() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>
			</div>
		</div>
		<? //Lista, Insere e Exclui as Lp's da Área (13-03-2020) ?>
		<div class="col-xs-12 col-sm-6 col-md-4 px-0">
		<? // Lp's ?>
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novalp()"></a>
							LP's do Conselho [<?=count($lps)?>]
						</div>						
						<span data-toggle="collapse" href="#listarLps" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>
					</div>
					<div class="panel-body overflow-x-auto" id="listarLps">
						<table class="table-hover w-100">
							<tbody>
								<?=listarLps()?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
			<div id="novalp" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Por favor, escolha as LPs disponíveis para a área abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto lparea" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a LP">
								</td>
							</tr>
						</table>
						<?
						if(count($lps)> 0){
						?>
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">LP</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listarLps() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>
			<!-- Documentos vinculados -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
							<div class="left-content d-flex align-items-center">
								Documentos vinculados[<?=count($documentosVinculados)?>]
							</div>	
							<span data-toggle="collapse" href="#listarDocs" aria-expanded="false" >
								<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
							</span>	
					</div>
					<div class="panel-body overflow-x-auto" id="listarDocs">
						<table class="table-hover w-100">
							<? foreach ($documentosVinculados as $documento) { ?>
								<tr>
									<td><?= $documento["titulo"] ?></td>
									<td>
										<a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?= $documento['idsgdoc'] ?>')"></a>
									</td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
			</div>


		</div>
	</div>
<?}
if($_1_u_sgconselho_idsgconselho)
{?>
	<div id="historico" style="display: none">
		<table class="table table-hover"> 
			<thead>          
				<tr> 
					<th scope="col">Pessoa</th>
					<th scope="col">Conselho</th>		   
					<th scope="col">Alteração</th>
					<th scope="col">Por</th>
					<th scope="col">Em</th>
				</tr> 
			</thead>
			<tbody>
			<? foreach($historico as $item)
			{
				if($item['acao']=="i"){
					$acao = "Inserção";
				}
				if($item['acao']=="d"){
					$acao = "Remoção";
				}
				if($item['acao']=="u"){
					$acao = "Atualização";
				} ?>
				<tr> 
					<td ><?=$item['nomecurto']?></td> 
					<td ><?=$item['conselho']?></td> 
					<td ><?=$acao?></td> 
					<td ><?=$item['alteradopor']?></td> 
					<td ><?=dmahms($item['alteradoem'])?></td> 
				</tr>
			<?}?>
		</table>
	</div>
<?}?>

<?
if(!empty($_1_u_sgconselho_idsgconselho)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_sgconselho_idsgconselho; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "sgconselho"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';

	require_once(__DIR__."/js/sgconselho_js.php");
?>