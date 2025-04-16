<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

// CONTROLLERS
require_once(__DIR__."/controllers/tag_controller.php");
require_once(__DIR__."/controllers/tagclass_controller.php");
require_once(__DIR__."/controllers/tagtipo_controller.php");

/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tagclass";
$pagvalcampos = array(
	"idtagclass" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from tagclass where idtagclass = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


//Lista as Lp's inseridas no Departamento (Lidiane - 13-03-2020)
function listaTipos($idtagclass)
{
	$tagTipo = TagTipoController::buscarTagTipoPorIdTagClass($idtagclass);

	foreach($tagTipo as $tipo)
	{
		echo "<tr><td><a target='_blank' href='?_modulo=tagtipo&_acao=u&idtagtipo=" . $tipo["idtagtipo"] . "'>" . $tipo["tagtipo"] . "</a></td>
                 <td align='center'>	
                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='removerTagTipo(" . $tipo['idtagtipo'] . ")' title='Excluir'></i>
                </td>
                </tr>";
	}
}

$tagTipo = TagTipoController::buscarTagTipoSemVinculo(true);
?>
<link href="<?= "/form/css/padrao.css?_".date('dmYhms') ?>" rel="stylesheet" />

<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading d-flex flex-wrap flex-between">
				<? if (!empty($_1_u_tagclass_idtagclass)) { ?>
					<div class="form-group col-xs-4 col-md-2">
						<input name="_1_<?= $_acao ?>_tagclass_idtagclass" id="idtagclass" type="hidden" value="<?= $_1_u_tagclass_idtagclass ?>" readonly='readonly'>
						<label class="text-white">ID:</label>
						<div class="form-control alert-warning">
							<label><?= $_1_u_tagclass_idtagclass ?></label>
						</div>
					</div>
				<? } ?>
				<div class="form-group col-xs-8 col-md-6">
					<label class="text-white">Descr</label>
					<input name="_1_<?=$_acao?>_tagclass_tagclass" type="text" value="<?=$_1_u_tagclass_tagclass?>" class="form-control" />
				</div>
				<div class="form-group col-xs-12 col-md-4">
					<label class="text-white">Status</label>
					<select name="_1_<?=$_acao?>_tagclass_status" class="form-control">
						<?fillselect(TagClassController::$status, $_1_u_tagclass_status);?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row mt-4">
	<? if (!empty($_1_u_tagclass_idtagclass)) { ?>
		<div class="col-xs-12 d-flex align-items-center justify-content-end">
			<label for="calendario" class="mr-2">Exibir no calendario</label>
			<input id="calendario" type="checkbox" name="_1_<?= $_acao ?>_tagclass_calendario" class="m-0" <?= ($_1_u_tagclass_calendario == 'Y' ? 'checked' : '')?> />
		</div>
	<? } ?>
    <div class="col-xs-12 px-0">
        <? if (!empty($_1_u_tagclass_idtagclass)) { ?>
			<div class="col-xs-12 col-md-4">
				<? //Lista, Insere e Exclui as Lp's do Departamento (13-03-2020) ?>
				<div class="panel panel-default">
					<div class="panel-heading">Tipo(s) Associados</div>
					<div class="panel-body">
						<table>
							<tr>
								<td><input id="tipotag" class="compacto" type="text" autocomplete="new-password" cbvalue placeholder="Selecione"></td>
							</tr>
						</table>
						<table class="table-hover w-100 overflow-auto">
							<tbody>
								<?= listaTipos($_1_u_tagclass_idtagclass) ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
		<? } ?>
    </div>
</div>
<?if ($_acao == 'u') {
	$_idModuloParaAssinatura = $_1_u_tagclass_idtagclass;
	require '../form/viewAssinaturas.php';
}
?>
<?$tabaud = "tagclass";
require_once(__DIR__."/js/tagclass_js.php");
require 'viewCriadoAlterado.php';?>
