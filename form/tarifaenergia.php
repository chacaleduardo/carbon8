<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/tarifaenergia_controller.php");

$idtarifaenergiapadrao = $_GET["idtarifaenergiapadrao"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tarifaenergiapadrao";
$pagvalcampos = array(
    "idtarifaenergiapadrao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from tarifaenergiapadrao where idtarifaenergiapadrao = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<?$statustarifa = PrecoEnergiaController::buscarStatusTarifa($_1_u_tarifaenergiapadrao_idtarifaenergiapadrao);?>
<div class="row">
	<div class="col md 12">
		<div class="panel panel-default">
			<div class="panel-body">
				<table>
                    <!-- HORAS PADRÃO -->
					<tr>
                        <td>
                            <?
                            if (empty($_1_u_tarifaenergiapadrao_idtarifaenergiapadrao)) {
                                $_1_u_tarifaenergiapadrao_idtarifaenergiapadrao = $idtarifaenergiapadrao;
                            }
                            ?><input name="_1_<?= $_acao ?>_tarifaenergiapadrao_idtarifaenergiapadrao" id="idtarifaenergiapadrao" type="hidden" readonly value="<?= $_1_u_tarifaenergiapadrao_idtarifaenergiapadrao ?>">
                        </td>
                        </tr>
                        <tr>
                            <td align="right">Status:</td>
                            <td>
                                <label class="alert-warning"><?= $_1_u_tarifaenergiapadrao_status ?></label>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                            </td>
                            <?
                            if($_1_u_tarifaenergiapadrao_status == 'ATIVO'){
                                $disabled =  "disabled='disable'";
                                $style = 'background-color:#E0E0E0;';
                            }
                            ?>
                        </tr>
                            <tr>
                                <td align="right">Valor Padrão:</td>
                                <td>
                                    <input <?=$disabled ?> placeholder="Adicione valor de cobrança" name="_1_<?= $_acao ?>_tarifaenergiapadrao_valor" type="text" id="valorpadrao" value="<?= $_1_u_tarifaenergiapadrao_valor ?>" size="28" style="<?=$style?>">
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Valores de Pico:</td>
                                <td>
                                <i id="novoitempico" class="fa fa-plus-circle fa-2x verde btn-lg pointer" title="Inserir novo Item" onclick="abrirModalTarifaPico(<?= $_1_u_tarifaenergiapadrao_idtarifaenergiapadrao ?>)"></i>
                            </tr>
                        </table>
                        <hr>
                    <!-- HORAS DE PICO -->
                        <div class="panel-body" style="display: flex; flex-wrap: wrap;">
                            <?
                            $valordepico = PrecoEnergiaController::buscarValordePico($_1_u_tarifaenergiapadrao_idtarifaenergiapadrao);
                                if($valordepico['qtdLinhas'] > 0){
                                    foreach($valordepico['dados'] as $valor) { 
                                        
                                        if($_1_u_tarifaenergiapadrao_status == 'ABERTA')
                                        {
                                        ?>
                                        <i class="fa fa-pencil btn-lg pointer" title='Editar Unidade' onclick="abrirModalAlteraPico(<?= $valor['idtarifaenergiapico']?>, <?= $valor['valor']?>, '<?= $valor['inicio']?>', '<?= $valor['fim']?>')"></i>
                                        <?}?>
                                        <table>
                                        <input id="idtarifaenergiapico" type="hidden" value="<?= $valor['idtarifaenergiapico'] ?>">
                                            <tr>
                                                <td align="right">Valor:</td>
                                                <td>
                                                    <input readonly="readonly" value="<?= $valor['valor'] ?>" type="text" id="valorpico" value="<?= $_1_u_tarifaenergiapadrao_valor ?>" size="28" style="background-color:#E0E0E0;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right">Inicio:</td>
                                                <td>
                                                    <input readonly="readonly"type="time" id="inicio" size="20" value="<?= $valor['inicio'] ?>" style="background-color:#E0E0E0;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right">Fim:</td>
                                                <td>
                                                    <input readonly="readonly" type="time" id="fim" size="20" value="<?= $valor['fim'] ?>" style="background-color:#E0E0E0;">
                                                </td>
                                            </tr>
                                        </table>
                                    <?}
                                }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?
if (!empty($_1_u_tarifaenergiapadrao_idtarifaenergiapadrao)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_tarifaenergiapadrao_idtarifaenergiapadrao; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "tarifaenergiapadrao"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once(__DIR__."/../form/js/tarifaenergia_js.php");
?>