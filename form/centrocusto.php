<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
* $pagvaltabela: tablea principal a ser atualizada pelo formulario html
* $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
*                pk: indica parâmetro chave para o select inicial
*                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou nã
*/
$pagvaltabela = "centrocusto";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idcentrocusto" => "pk"
);
/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "select * from " . _DBAPP . ".centrocusto where idcentrocusto = '#pkid' ";
/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
require_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__."/controllers/centrocusto_controller.php");

?>
<!-- CSS -->
<link rel="stylesheet" href="/form/css/padrao.css" />
<div class="panel panel-default w-100">
    <div class="panel-heading">
        <div class="w-100 d-flex flex-wrap flex-between">
            <!-- ID -->
            <? if($_1_u_centrocusto_idcentrocusto)
            {?>
                <input name="_1_<?= $_acao ?>_centrocusto_idcentrocusto" type="hidden" value="<?= $_1_u_centrocusto_idcentrocusto ?>" readonly='readonly'>
                <div class="col-xs-6 col-sm-3 form-group">
                    <label for="" class="text-white">ID</label>
                    <div class="form-control alert-warning">
                        <label for="">
                            <?= $_1_u_centrocusto_idcentrocusto ?>
                        </label>
                    </div>
                </div>
            <?}?>
            <!-- DESCRICAO -->
            <div class="col-xs-6 col-sm-3 form-group">
                <label for="" class="text-white">Centro de custo</label>
                <input class="form-control" name="_1_<?= $_acao ?>_centrocusto_centrocusto" type="text" value="<?= $_1_u_centrocusto_centrocusto ?>" vnulo>
            </div>
            <!-- STATUS -->
            <div class="col-xs-6 col-sm-3 form-group">
                <label for="" class="text-white">Status</label>
                <select id="status" name="_1_<?= $_acao ?>_centrocusto_status" class="form-control" vnulo>
                    <? fillselect(CentroCustoController::$status, $_1_u_centrocusto_status); ?>
                </select>
            </div>
        </div>
    </div>
</div>
<?
$tabaud = "centrocusto"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>