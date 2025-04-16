<?
require_once("../inc/php/validaacesso.php");
require_once("./controllers/tagtipo_controller.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tagtipo";
$pagvalcampos = array(
	"idtagtipo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from tagtipo where idtagtipo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if ($_1_u_tagtipo_idtagtipo) {
	
	$jTagTipo = json_encode(TagTipoController::listarTagsTipoNaoVinculadasAoTagTipo($_1_u_tagtipo_idtagtipo));

	$jProdserv = json_encode(TagTipoController::listarProdservsNaoVinculadasAoTagTipo());

	$jTagTipoLocalizacao = json_encode(TagTipoController::listarTagsTipoLocalizacaoNaoVinculadasAoTagTipo($_1_u_tagtipo_idtagtipo));
	
	function montarTagCampos( $campos = [] ){
		global $_1_u_tagtipo_idtagclass, $_1_u_tagtipo_idtagtipo;

		$aux = 0;
		$content = "<tr>";

		foreach ($campos as $campo => $valor){
			$aux++;
			$idtipotagcampos = TagTipoController::buscarTipoTagCamposPorCampoIdTagTipo( $campo, $_1_u_tagtipo_idtagtipo );
			$content .= '
				<td>
					<button id="'.$campo.'" class="btn btn-default btn-sm pointer" 
						onclick="mostraCampo(this, '.$_1_u_tagtipo_idtagclass.', '.$_1_u_tagtipo_idtagtipo.')" 
						value="'.$idtipotagcampos.'">
							<i class="fa fa-eye-slash"></i> '.$valor.'
					</button>
				</td>
			';

			if($aux == 5){
				$content .= "</tr><tr>";
				$aux = 0;
			}
		}

		$content .= "</tr>";
		return $content;
	}
}else{
	$jTagTipo = [];
	$jProdserv = [];
	$jTagTipoLocalizacao = [];
}
?>
<style>
	#campos{
		width:100%;
	}
	#campos button{
		width: 100%;
	}

	.color-palette
	{
		list-style: none;
		width: 100%;
		display: flex;
		flex-wrap: wrap;
		padding: 0;
		align-items: center;
		margin-bottom: 0;
	}

	.color-palette li
	{
		position: relative;
		width: 20px;
    	height: 20px;
		cursor: pointer;
		transition: .3s ease 0s;
	}

	.color-palette li.active
	{
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.color-palette li.active:before
	{
		content: '';
		position: absolute;
		width: 5px;
		height: 5px;
		border-radius: 100%;
		background-color: #fff;
	}
</style>
<div class="row ">
    <div class="col-md-12" >
		<div class="panel panel-default" >
			<div class="panel-heading">
				<table>
					<tr>
						<td>Descr:</td>
						<td>
							<input name="_1_<?=$_acao?>_tagtipo_idtagtipo"  type="hidden" value="<?=$_1_u_tagtipo_idtagtipo?>">
							<input name="_1_<?=$_acao?>_tagtipo_tagtipo"  type="text" value="<?=$_1_u_tagtipo_tagtipo?>">
						</td>
					
						<? if($_1_u_tagtipo_idtagtipo) 
						{ ?>
						<td>Bioensaio:</td>
							<?
							if($_1_u_tagtipo_bioensaio == 'Y'){
								$bioensaio='N';
								$checkedob="checked";
							}else{
								$bioensaio='Y';
								$checkedob="";
							}
							?>
						<td>
							<input name="_1_<?=$_acao?>_tagtipo_bioensaio" type="checkbox" atval="<?=$bioensaio?>" value="<?=$_1_u_tagtipo_bioensaio?>" <?=$checkedob?> idtag="<?=$_1_u_tagtipo_idtagtipo?>" onclick="flgrevisado(this)">
						</td>
						<? } ?>
						<td>Status</td>
						<td>
							<select name="_1_<?=$_acao?>_tagtipo_status" >					
								<?
								fillselect(TagTipoController::$statusFillSelect, $_1_u_tagtipo_status);
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="row col-sm-12 d-flex flex-wrap">
					<!-- Classe -->
					<div class="col-sm-12 col-md-3 d-flex">
						<div class="col-md-4 labels px-0 text-right">
							<label for="_obs" class="col col-form-label labels">Classe:</label>
						</div>
						<div class="col-md-8 inputs">
							<select name="_1_<?=$_acao?>_tagtipo_idtagclass" vnulo="">
								<option></option>
								<?
									fillselect(TagTipoController::buscarTodasTagClassAtivasPorEmpresaFillSelect(getidempresa('idempresa','tagclass')), $_1_u_tagtipo_idtagclass);
								?>
							</select>
						</div>
					</div>
					<!-- Calendario -->
					<div class="col-sm-12 col-md-3">
						<div class="col-md-4 labels px-0 text-right">
							<label for="_obs" class="col col-form-label labels">Calendário:</label>
						</div>
						<div class="col-md-8 inputs">
							<select name="_1_<?=$_acao?>_tagtipo_calendario" vnulo="">
								<option></option>
								<?
									fillselect(TagTipoController::$calendarioFillSelect, $_1_u_tagtipo_calendario);
								?>
							</select>
						</div>
					</div>
					<!-- Divisao -->
					<?if($_1_u_tagtipo_bioensaio == "Y"){?>
						<div class="col-sm-12 col-md-3">
							<div class="col-md-4 labels px-0 text-right">
								<label for="_obs" class="col col-form-label labels">Divisão:</label>
							</div>
							<div class="col-md-8 inputs">
								<select name="_1_<?=$_acao?>_tagtipo_idplantel" vnulo="">
									<?
										fillselect(TagTipoController::buscarPlantelPorEmpresaFillSeletec(getidempresa('idempresa','tagclass')), $_1_u_tagtipo_idplantel);
									?>
								</select>
							</div>
						</div>
					<? } ?>
					<!-- Cor -->
					<div class="col-sm-12 col-md-3 d-flex align-items-center flex-wrap">
						<div class="col-md-4 labels px-0 text-right">
							<label for="_obs" class="col col-form-label labels">Cor:</label>
						</div>
						<div class="col-md-8">
							<ul class="color-palette"></ul>
						</div>
						<input name="_1_<?=$_acao?>_tagtipo_cor" id="_cortipo" value="<?= $_1_u_tagtipo_cor ?>" class="hidden">
					</div>
					<!-- Icone -->
					<div class="col-sm-12 col-md-3">
						<div class="col-md-4 labels px-0 text-right">
							<label for="_obs" class="col col-form-label labels">Ícone:</label>
						</div>
						<div class="col-md-8 inputs">
							<input id="cssicone" type="text" class="hidden" name="_1_<?=$_acao?>_tagtipo_cssicone" value="<?=$_1_u_tagtipo_cssicone?>" hidden>
							<i id="seletoricones" class="<?=($_1_u_tagtipo_cssicone) ? $_1_u_tagtipo_cssicone : "fa fa-smile-o"?> fa-2x fade"></i>
						</div>
					</div>
					<!-- Obs -->
					<div class="col-sm-12 px-0">
						<div class="col-md-1 labels text-right">
							<label for="_obs" class="col col-form-label labels">Obs:</label>
						</div>
						<div class="col-md-11 inputs">
							<textarea name="_1_<?=$_acao?>_tagtipo_obs" id="obs" rows="6" cols="84"><?=$_1_u_tagtipo_obs?></textarea>
						</div>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>	
<? // CAMPOS DO TIPO DE TAG
// ************************************************ 04/12/2019 POR GABRIEL TIBURCIO ************************************************ //
if(!empty($_1_u_tagtipo_idtagtipo)){?>
<div class="row">
	<!-- Tags relacionadas -->
	<div class="col-md-4">
		<div class="panel panel-default" >
			<div class="panel-heading" data-toggle="collapse" href="#tagsvinculadas">Tag Relacionadas</div>
			<div class="panel-body collapse" id="tagsvinculadas">
				<?
				$tags = TagTipoController::buscarTagsPorTagTipo($_1_u_tagtipo_idtagtipo);
				if(count($tags) > 0){?>
					<table class="table table-stripped">
						<tr>
							<td><b>Tag</b></td>
							<td><b>Descrição</b></td>
							<td></td>
						</tr>
						<?
						foreach($tags as $k => $rw){?>
							<tr>
								<td><?=$rw["tag"]?></td>
								<td><?=$rw["descricao"]?></td>
								<td>
									<a href="?_modulo=tag&_acao=u&idtag=<?=$rw["idtag"]?>" class="fa fa-bars pointer hoverpreto" target="_blank"></a>
								</td>
							</tr>
						<?}?>
					</table>
				<?}else{?>
					<span>Não existem Tags vinculadas a esse Tipo Tag.</span>
				<?}?>
			</div>
		</div>
	</div>
	<!-- Atividades relaciondas -->
	<div class="col-md-8">
		<div class="panel panel-default" >
			<div class="panel-heading" data-toggle="collapse" href="#ativvinculadas">Atividades Relacionadas</div>
			<div class="panel-body collapse" id="ativvinculadas">
				<?
				$atividades = TagTipoController::buscarAtividadesVinculadasPorIdTagTipo($_1_u_tagtipo_idtagtipo);
				if( count($atividades) > 0 ){?>
					<table class="table table-stripped">
						<tr>
							<td><b>Ativ. Op</b></td>
							<td><b>Descrição</b></td>
							<td></td>
						</tr>
						<?
						foreach( $atividades as $k => $rwAtiv ){?>
							<tr>
								<td><?= $rwAtiv["ativ"] ?></td>
								<td><?= $rwAtiv["descr"] ?></td>
								<td>
									<a href="?_modulo=prativ&_acao=u&idprativ=<?=$rwAtiv["idprativ"]?>" class="fa fa-bars pointer hoverpreto" target="_blank"></a>
								</td>
							</tr>
						<?}?>
					</table>
				<?}else{?>
					<span>Não existem Atividades vinculadas a esse Tipo Tag.</span>
				<?}?>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<!-- Eventos Relacionados -->
	<div class="col-md-4">
		<div class="panel panel-default" >
			<div class="panel-heading" data-toggle="collapse" href="#eventostipovinculados">Tipos Evento Relacionados</div>
			<div class="panel-body collapse" id="eventostipovinculados">
				<?
				$eventoTipos = TagTipoController::buscarEventoTipoPorIdTagTipo($_1_u_tagtipo_idtagtipo);
				if( count($eventoTipos) > 0 ){?>
					<table class="table table-stripped">
						<tr>
							<td><b>Descrição</b></td>
							<td></td>
						</tr>
						<?
						foreach( $eventoTipos as $k => $rw ){?>
							<tr>
								<td><?=$rw["eventotipo"]?></td>
								<td>
									<a href="?_modulo=eventotipo&_acao=u&ideventotipo=<?=$rw["ideventotipo"]?>" class="fa fa-bars pointer hoverpreto" target="_blank"></a>
								</td>
							</tr>
						<?}?>
					</table>
				<?}else{?>
					<span>Não existem Tipos evento vinculados a esse Tipo Tag.</span>
				<?}?>
			</div>
		</div>
	</div>
	<div class="col-md-8" >
		<div class="panel panel-default" >
			<div class="panel-heading ">Tag Campos</div>
			<div class="panel-body">
				<table id="campos">
					<?
					$tagclass = TagTipoController::buscarTagClassPorIdTagClass(getidempresa('idempresa','tagtipo'), $_1_u_tagtipo_idtagclass);
					switch( $tagclass["tagclass"] ){
						case 'EQUIPAMENTO':
							echo montarTagCampos(TagTipoController::$equipamentos);
							break;
						case 'SALA':
							echo montarTagCampos(TagTipoController::$salas);
							break;
						case 'VEÍCULO':
							echo montarTagCampos(TagTipoController::$veiculos);
							break;
						case 'PRATELEIRA':
							echo montarTagCampos(TagTipoController::$prateleiras);
							break;
						case 'MOBILIÁRIO':
							echo montarTagCampos(TagTipoController::$mobiliarios);
							break;
						case 'QUARTO TÉRMICO':
							echo montarTagCampos(TagTipoController::$quartosTermicos);
							break;
						default:
							break;
					}?>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_produtos" data-toggle="collapse">
				 Produtos
			</div>
			<div class="panel-body" id="body_produtos">
				<div class="col-md-12">
					<div class="row">
						Vincular:
						<input type="text" id="busca_prodserv">
					</div>
					<?
					$prodservs = TagTipoController::buscarProdservVinculadasAoTagTipo($_1_u_tagtipo_idtagtipo);
					if ( count($prodservs) > 0 ) {?>
						<hr>
						<div class="row">
							<table style="width: 100%;">
								<tr>
									<td>Tipo</td>
									<td></td>
									<td></td>
								</tr>
								<?
								foreach( $prodservs as $k => $rowe ){?>
									<tr>
										<td>
											<?=$rowe['descr']?>
										</td>
										<td style="width:20%">
										</td>
										<td style="text-align:center;cursor:pointer;">
											<i onclick="desvinculaProdserv(<?=$rowe['idprodserv']?>)" class="fa fa-times vermelho fade"></i>
										</td>
									</tr>
								<?}?>
							</table>
						</div>
					<?}?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_localizado" data-toggle="collapse">
				 Tipo Tag (Localizado Em)
			</div>
			<div class="panel-body" id="body_localizado">
				<div class="col-md-12">
					<div class="row">
						Vincular:
						<input type="text" id="busca_tagtipovinc">
					</div>
					<?
					$tagsTipoLocalizacao = TagTipoController::buscarTagsTipoLocalizacaoVinculadasAoTagTipo($_1_u_tagtipo_idtagtipo);
					if ( count($tagsTipoLocalizacao) > 0 ) {?>
						<hr>
						<div class="row">
							<table style="width: 100%;">
								<tr>
									<td>Tipo</td>
									<td></td>
									<td></td>
								</tr>
								<?
								foreach( $tagsTipoLocalizacao as $k => $rowe ){?>
									<tr>
										<td>
											<?=$rowe['nome']?>
										</td>
										<td style="width:20%">
										</td>
										<td style="text-align:center;cursor:pointer;">
											<i onclick="desvincula(<?=$rowe['idobjetovinculo']?>)" class="fa fa-times vermelho fade"></i>
										</td>
									</tr>
								<?}?>
							</table>
						</div>
					<?}?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_possui" data-toggle="collapse">
				Tipo Tag (Possui)
			</div>
			<div class="panel-body" id="body_possui">
				<div class="col-md-12">
					<div class="row">
						Vincular:
						<input type="text" id="busca_tagtipo">
					</div>
					<?
					$tagsTipo = TagTipoController::buscarTagsTipoVinculadasAoTagTipo($_1_u_tagtipo_idtagtipo);
					if ( count($tagsTipo) > 0 ) {?>
						<hr>
						<div class="row">
							<table style="width: 100%;">
								<tr>
									<td>Tipo</td>
									<td></td>
									<td></td>
								</tr>
								<?
								foreach( $tagsTipo as $k => $rowe ){?>
									<tr>
										<td>
											<?=$rowe['nome']?>
										</td>
										<td style="width:20%">
										</td>
										<td style="text-align:center;cursor:pointer;">
											<i onclick="desvincula(<?=$rowe['idobjetovinculo']?>)" class="fa fa-times vermelho fade"></i>
										</td>
									</tr>
								<?}?>
							</table>
						</div>
					<?}?>
				</div>
			</div>
		</div>
	</div>
</div>
<?}

if(!empty($_1_u_tagtipo_idtagtipo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_tagtipo_idtagtipo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "tagtipo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require_once('./js/tagtipo_js.php');
?>