<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/webmailassinaturatemplate_controller.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "webmailassinaturatemplate";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idwebmailassinaturatemplate" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from webmailassinaturatemplate where idwebmailassinaturatemplate = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getImagensRodape(){
    return WebMailAssinaturaTemplateController::buscarImagensDeRodape();
}

function getPessoas(){
    return WebMailAssinaturaTemplateController::buscarPessoasSetorDepartamentosAreas();
}

function getEmailvirtual(){
    return WebMailAssinaturaTemplateController::buscarGruposDeEmail();
}

if($_acao == 'u' AND empty($_1_u_webmailassinaturatemplate_htmltemplate)){
    $_1_u_webmailassinaturatemplate_htmltemplate = '<p>Atenciosamente,</p><table style="font-family: Arial, sans-serif, Helvetica; width: 706px; height: 181px; vertical-align: top; background-repeat: no-repeat;">
    <tbody>
    <tr style="height: 103px;">
    <td id="_temp" style="position: relative;">
    <div id="_template" style="height: 120px; width: 700px;">Insira a Imagem aqui
    </div>
    </td>
    </tr>
    <tr style="height: 56px;">
    <td style="font-size: 10px; text-align: justify; height: 56px; width: 688px;" colspan="2">_razaosocial_ <br />CNPJ: _cnpj_ - I.E: _inscestadual_ - _enderecocompleto_ - _cep_ - _mun_/_uf_<br />As informa&ccedil;&otilde;es contidas neste e-mail e documentos anexos s&atilde;o particulares, sigilosos e de propriedade da empresa _empresa_.<br />Se voc&ecirc; n&atilde;o for o destinat&aacute;rio ou se recebeu esta mensagem irregularmente ou por erro, apague o e-mail e avise o remetente. <br />Este e-mail n&atilde;o pode ser divulgado, armazenado, utilizado, publicado ou copiado por qualquer um que n&atilde;o o(s) seu(s) destinat&aacute;rio(s).</td>
    </tr>
    <tr style="height: 14px;">
    <td style="font-size: 11px; cursor: pointer; height: 14px; width: 688px;" colspan="2"><a href="_site_" target="_blank" rel="noopener">_site_</a></td>
    </tr>
    </tbody>
    </table>';
}
?>
<style>
    table{
        width: 100%;
    }

    .panel-body{
        padding-top: 10px !important;
    }

    #editor{
		height: 300px;
		width: 100%;
		overflow-y: scroll;
		background-color: white;
	}

</style>
<div class="row">
    <div class="col-sm-3">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <td>
                                <div class="col-sm-12">
                                    <div class="col-sm-3">
                                        ID.:
                                        <label class="alert-warning"><?=$_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate?></label>
                                        <input type="hidden" name="_1_<?=$_acao?>_webmailassinaturatemplate_idwebmailassinaturatemplate" value="<?=$_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate?>">
                                    </div>
                                    <div class="col-sm-5">
                                        Status: 
                                        <select name="_1_<?=$_acao?>_webmailassinaturatemplate_status" vnulo>
                                            <option value=""></option>
                                            <?fillselect("select 'ATIVO','Ativo' 
                                            union select 'INATIVO','Inativo'",$_1_u_webmailassinaturatemplate_status);?>
                                        </select>
                                    </div>
                                    <?if(!empty($_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate)){?>
                                    <div class="col-sm-4">
                                        Email principal?
                                        <?if($_1_u_webmailassinaturatemplate_principalempresa == 'N'){?>
                                            <input type="checkbox" onclick="altprincipalempresa('Y')">
                                        <?}else{?>
                                            <input type="checkbox" onclick="altprincipalempresa('N')" checked>
                                        <?}?>
                                    </div>
                                    <?}?>
                                </div>
                            </td>      
                        </tr>
                    </table>
                </div>
                <div class="panel-body">
                    <table>
                        <tr>
                            <td>
                                <div class="col-sm-12">
                                    <div class="col-sm-12">
                                        Descrição:
                                        <input type="text" name="_1_<?=$_acao?>_webmailassinaturatemplate_descricao" value="<?=$_1_u_webmailassinaturatemplate_descricao?>" vnulo>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-sm-12">
                                    <div class="col-sm-12">
                                        Tipo: 
                                        <select name="_1_<?=$_acao?>_webmailassinaturatemplate_tipo" vnulo>
                                            <option value=""></option>
                                            <?fillselect("select 'COLABORADOR','Colaborador' 
                                            union select 'EMAILVIRTUAL','Email Virtual'",$_1_u_webmailassinaturatemplate_tipo);?>
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Gerar template para:</div>
                <div class="panel-body">
                    <input type="text" id="gerartemplateinput">
                    <hr>
                    <div id="gerartemplatelistaselecionados">

                    </div>
                    <div class="col-sm-12" style="text-align: center;">
                        <button id="gerartemplates" class="btn btn-primary">Gerar Templates</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?if(!empty($_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate)){?>
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-heading">Template:</div>
            <div class="panel-body" style="height:400px;">
                <table>
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <div class="col-sm-12" >
                                    <div id="editor"></div>
                                    <textarea class="hidden" name="_1_<?=$_acao?>_webmailassinaturatemplate_htmltemplate"><?=$_1_u_webmailassinaturatemplate_htmltemplate?></textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?}?>
</div>
<?
if(!empty($_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "_1_u_webmailassinaturatemplate"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
include_once(__DIR__."/js/webmailassinaturatemplate_js.php");
?>