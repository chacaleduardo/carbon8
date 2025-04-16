<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "vw8usuariosbloqueados";
$pagvalcampos = array(
    "idbloqueiousuario" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from vw8usuariosbloqueados where idbloqueiousuario = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table style="width: 100%;">
                    <tbody>
                        <tr>
                            <td align="right">
                                <strong>Usuário:</strong>
                            </td>
                            <td>
                                <label class="alert-warning">
                                    <?= $_1_u_vw8usuariosbloqueados_usuario == "" ? "Usuário Não Reconhecido" :  $_1_u_vw8usuariosbloqueados_usuario ?>
                                </label>
                            </td>

                            <td style="width: 100%;" align="right">
                                <strong>Status:</strong>
                                <label class="alert-warning">
                                    <? if ($_1_u_vw8usuariosbloqueados_status == 'Alerta') {
                                        echo 'ALERTA';
                                    } else if ($_1_u_vw8usuariosbloqueados_status == 'Bloqueado') {
                                        echo 'BLOQUEADO';
                                    } else if ($_1_u_vw8usuariosbloqueados_status == 'Liberado') {
                                        echo 'LIBERADO';
                                    } ?>
                                </label>
                                <? if ($_1_u_vw8usuariosbloqueados_status == "Bloqueado") { ?>
                                    <button id="_desbloquear" type="button" class="btn btn-success btn-xs" onclick="desbloquearIP(<?= $_1_u_vw8usuariosbloqueados_idbloqueiousuario ?>)" title="Salvar">
                                        <i class="fa fa-circle"></i>Desbloquear
                                    </button>
                                <? } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="panel-boody">
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <strong>IP:</strong>
                            </td>
                            <td align="left">
                                <label class="alert-warning">
                                    <?= $_1_u_vw8usuariosbloqueados_ip ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td title="Número de Tentativas de Login sem sucesso">
                                <strong>Falhas:</strong>
                            </td>
                            <td align="left">
                                <?= $_1_u_vw8usuariosbloqueados_Tentativas ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>ID Pessoa:</strong>
                            </td>
                            <td align="left">
                                <a href="?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_vw8usuariosbloqueados_idpessoa ?>" target="_blank"> <?= $_1_u_vw8usuariosbloqueados_idpessoa ?> </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Nome:</strong>
                            </td>
                            <td align="left">
                                <?= $_1_u_vw8usuariosbloqueados_nome ?>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="padding-top: 8px !important;background:#e6e6e6">
                <div class="row col-md-6">
                    <div class="col-md-6" style="text-align:right">
                        <span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
                            Criação:
                        </span>
                    </div>
                    <div class="col-md-6" style="text-align:left">
                        <span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                            <?= $_1_u_vw8usuariosbloqueados_criadopor ?>
                        </span>
                        <span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            <?= $_1_u_vw8usuariosbloqueados_criadoem ?>
                        </span>
                    </div>
                </div>
                <div class="row col-md-6">
                    <div class="col-md-6" style="text-align:right">
                        <span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
                            Alteração:
                        </span>
                    </div>
                    <div class="col-md-6" style="text-align:left">
                        <span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                            <?= $_1_u_vw8usuariosbloqueados_alteradopor ?>
                        </span>
                        <span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            <?= $_1_u_vw8usuariosbloqueados_alteradoem ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function desbloquearIP(ip) {
        CB.post({
            objetos: '_ajax_u__bloqueiousuario_idbloqueiousuario=' + ip + '&_ajax_u__bloqueiousuario_status=L',
            parcial: true
        })
    }
</script>