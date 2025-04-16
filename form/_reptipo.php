<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatários para o carbon
$pagvaltabela = "_reptipo";
$pagvalcampos = array(
	"idreptipo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . getDbTabela("_reptipo") . "._reptipo where idreptipo = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="height: 40px;"></div>
            <div class="panel-body" style="display: flex;justify-content: flex-start;">
                <input type="hidden" name="_1_<?= $_acao ?>__reptipo_idreptipo" value="<?= $_1_u__reptipo_idreptipo ?>">
                <div style="margin-right: 10px;">
                    <span>Tipo:</span>
                    <input type="text" name="_1_<?= $_acao ?>__reptipo_reptipo" value="<?= $_1_u__reptipo_reptipo ?>">
                </div>
                <div>
                    <span>Status:</span>
                    <select name="_1_<?= $_acao ?>__reptipo_status">
                        <? fillselect(array('ATIVO' => 'ATIVO', 'INATIVO' => 'INATIVO'), $_1_u__reptipo_status); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
