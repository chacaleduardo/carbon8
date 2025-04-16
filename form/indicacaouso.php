<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 * pk: indica parámetro chave para o select inicial
 * vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "indicacaouso";
$pagvalcampos = array(
    "idindicacaouso" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM indicacaouso WHERE idindicacaouso = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 11px;">Indicação de Uso</div>
            <div class="panel-body">
                <div class="col-xs-12 alinhamento-esquerda-20" style="font-size: 11px !important;">
                    <div class="form-group col-xs-6">
                        <label>Descrição:</label>
                        <div class="text-uppercase input-group">
                            <input type="hidden" name="_1_<?=$_acao?>_indicacaouso_idindicacaouso" vnulo value="<?=$_1_u_indicacaouso_idindicacaouso?>">
                            <input class="size50" id="descricao" type="text" name="_1_<?=$_acao?>_indicacaouso_descricao" vnulo value="<?=$_1_u_indicacaouso_descricao?>">
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 alinhamento-esquerda-20" style="font-size: 11px !important;">
                    <div class="form-group col-xs-12">
                        <label>Status:</label>
                        <div class="text-uppercase razaosocial input-group">
                            <select id="_1_<?=$_acao?>_indicacaouso_status" name="_1_<?=$_acao?>_indicacaouso_status" vnulo>
                                <option></option>
                                <? fillselect(array('ATIVO' => 'Ativo', 'INATIVO' => 'Inativo'), $_1_u_indicacaouso_status); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<p>
    <?
    if (!empty($_1_u_feriado_idferiado)) { // trocar p/ cada tela a tabela e o id da tabela
        $_idModuloParaAssinatura = $_1_u_feriado_idferiado; // trocar p/ cada tela o id da tabela
        require 'viewAssinaturas.php';
    }
    $tabaud = "feriado"; //pegar a tabela do criado/alterado em antigo
    require 'viewCriadoAlterado.php';
    ?>