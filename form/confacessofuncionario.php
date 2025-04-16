<?
require_once("../inc/php/validaacesso.php");

if ($_POST) require_once("../inc/php/cbpost.php");

//Parâmetros mandatórios para o carbon
$pagvaltabela = "pessoa";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idpessoa" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from pessoa where idpessoa = '#pkid'";
//die($pagsql);
//Validacao do GET e criacao das variáveis 'variáveis' para a página
include_once("../inc/php/controlevariaveisgetpost.php");
$i = 99;

// CONTROLLERS
require_once(__DIR__."/controllers/confacessofuncionario_controller.php");

$idPessoa       = $_1_u_pessoa_idpessoa;    
$webMailEmail   = $_1_u_pessoa_webmailemail;
$usuario        = $_1_u_pessoa_usuario;

$grupoDeAssinatura       = ConfAcessoFuncionarioController::buscarGrupoDeAssinatura($idPessoa, $webMailEmail);
$assinaturaDoFuncionario = ConfAcessoFuncionarioController::buscarWebmailAssinaturaFuncionario($idPessoa, $webMailEmail);
$outrasAssinaturasDeFuncionarios = ConfAcessoFuncionarioController::buscarOutrasAssinaturasDosFuncionarios($idPessoa, $webMailEmail);
$emailVirtual            = ConfAcessoFuncionarioController::buscarEmailVirtual($idPessoa, $emailVirtual, $usuario);
$novoCertificadoDigital  = ConfAcessoFuncionarioController::buscarNovoCertificadoDigitalPorIdPessoa($idPessoa);
$imMsgConf = ConfAcessoFuncionarioController::buscarImMsgConf();
$assinaturaDeGruposDeEmail = ConfAcessoFuncionarioController::buscarAssinaturaDeGruposDeEmailPorIdPessoa($idPessoa);
$assinaturaDeFuncionariosRelacionados = ConfAcessoFuncionarioController::buscarAssinaturaDeFuncionariosRelacionados($idPessoa, $webMailEmail);

$tagsVinculadas = ConfAcessoFuncionarioController::buscarTagsVinculadasAoFuncionario($idPessoa);

$historicoConselhoAreaDepartamentoSetor = ConfAcessoFuncionarioController::buscarHistoricoPorIdPessoa($idPessoa);

function listaAlertas($idPessoa)
{
    $alertas = ConfAcessoFuncionarioController::buscarAlertasPorIdPessoa($idPessoa);

    echo "<table class='table-hover'>
            <tbody>";
    foreach($alertas as $alert)
    {
        $opacity = 'opacity';
        $cor = 'vermelho hoververmelho ';

        if ($alert["status"] == 'ATIVO')
        {
            $opacity = '';
            $cor = 'verde hoververde';
        }

        echo    "<tr id='{$alert["idimmsgconfdest"]}' class='{$opacity}'>
                    <td>{$alert["titulo"]}</td>
                    <td>
                        <i class='fa fa-check-circle-o $cor' status='{$alert["status"]}' idimmsgconfdest='{$alert["idimmsgconfdest"]}'  onclick='AlteraStatusImsg(this)'></i>
                    </td>
                    <td>
                        <a class='fa fa-bars pointer hoverazul' title='Alerta' onclick=\"janelamodal('?_modulo=immsgconf&_acao=u&idimmsgconf={$alert["idimmsgconf"]}')\"></a>
                    </td>
                </tr>";
    }
    echo "      </tbody>
            </table>";
}

function listaFluxos($idPessoa)
{
	$fluxos = ConfAcessoFuncionarioController::buscarFluxosPorIdPessoa($idPessoa);

	if(count(ConfAcessoFuncionarioController::$controllerErrors))
	{
        foreach(ConfAcessoFuncionarioController::$controllerErrors as $error)
        {
            echo "<tr>
                    <td>$error!</td>
                </tr>";
        }

		return false;
	}
	foreach($fluxos as $fluxo)
	{
		echo "	<tr>
					<td>
						".($fluxo['tipoobjeto'] ? $fluxo['tipoobjeto'] : '[VALOR NÃO DEFINIDO]')."
					</td>
					<td align='center'>
						<a href='?_modulo=fluxo&_acao=u&idfluxo={$fluxo['idfluxo']}' target='_blank'>
							<i class='fa fa-bars pointer' title='Editar fluxo'></i>
						</a>
					</td>
				</tr>
		";
    }
}

// Emails
// GVT - 19/05/2020 - Função retorna a lista de emails virtuais associados ao funcionário.
function listaEmailVirtual()
{
    GLOBAL $_1_u_pessoa_idpessoa;

	$emails = ConfAcessoFuncionarioController::buscarEmailVirtualPorIdPessoa($_1_u_pessoa_idpessoa);

    foreach(ConfAcessoFuncionarioController::$controllerErrors as $error)
    {
        echo "<tr>
                <td>$error!</td>
            </tr>";
    }

	echo "<table class='table-hover'>
            <tbody>";
	foreach($emails as $email)
    {
		echo "  <tr>
                    <td>{$email["email_original"]}</td>
                    <td>
                        <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='excluiremailvirtual({$email["idemailvirtualconf"]})'></i>
                    </td>
                </tr>";
	}
	echo "      </tbody>
            </table>";
}

function listarConselhoAreaDepartamentoSetorPorIdPessoa($idPessoa)
{
    $areasDeAtuacao = ConfAcessoFuncionarioController::buscarAreasDeAtuacaoPorIdPessoa($idPessoa);

    if(!count($areasDeAtuacao))
    {
        echo "<table>
                <tr>
                    <td><input id='sgsetorvinc' class='compacto' type='text' cbvalue placeholder='Selecione'></td>
                </tr>
            </table>";

        return false;
    }

    echo "<table class='table table-striped planilha'>
            <tbody class='vinculos'>";

    foreach($areasDeAtuacao as $area)
    {
        if($area['descricao'])
        {
            echo "<tr>
                    <td>
                        {$area['objeto']} - {$area['descricao']}
                    </td>
                    <td>
                        <a class='fa fa-bars cinzaclaro hoverazul pointer' onclick='janelamodal(`?_modulo={$area['link']}`);'></a>
                    </td>
                    <td align='center'>
                        <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativaobjeto({$area['idpessoaobjeto']}, `pessoaobjeto`)' title='Excluir!'></i>
                    </td>
                </tr>";
            }
    }

    echo  "</tbody>
        </table>";
}

function montarAssinaturaDeEmailHTML($idPessoa, $webMailEmail = false)
{
    $assinaturaEmailCampos = ConfAcessoFuncionarioController::buscarAssinaturaEmailCamposPorIdPessoa($idPessoa);

    echo "<ul class='nav'>
            <li class='panel conteudo-assinatura-email' style='background:#e6e6e6;border: 1px solid #ddd;'>
                <div class='titulo_email pointer'>
                    <a>Conteúdo Assinatura de E-mail</a>
                </div>";

    if(!count($assinaturaEmailCampos))
    {
        echo " <div style='padding: 5px;background:whitesmoke;'>
                    <i class='fa fa-plus-circle verde hovercinza btn-lg pointer' onclick='criarAssinaturaCampos()'></i>
                </div>
            </li>
        </ul>";

        return;
    }

    if(count($assinaturaEmailCampos) > 1)
    {
        echo "  <div style='padding: 5px;background:whitesmoke;'>
                    Mais de uma configuração de campos de assinatura para o funcionário
                </div>
            </li>
        </ul>";

        return;
    }

    echo "<div style='padding: 5px;background:whitesmoke;'>
                <table class='table'>
                    <tr>
                        <td>
                            Nome Assinatura:
                            <input type='hidden' name='_ass1_u_assinaturaemailcampos_idassinaturaemailcampos' value='{$assinaturaEmailCampos[0]['idassinaturaemailcampos']}'>
                            <input type='text' name='_ass1_u_assinaturaemailcampos_nome' value='{$assinaturaEmailCampos[0]['nome']}'>
                        </td>
                        <td colspan='2'>
                            Cargo:
                            <input type='text' name='_ass1_u_assinaturaemailcampos_cargo' value='{$assinaturaEmailCampos[0]['cargo']}'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Telefone:
                            <input type='text' name='_ass1_u_assinaturaemailcampos_telefone' value='{$assinaturaEmailCampos[0]['telefone']}'>
                        </td>
                        <td>
                            Ramal:
                            <input type='text' name='_ass1_u_assinaturaemailcampos_ramal' value='{$assinaturaEmailCampos[0]['ramal']}'>
                        </td>
                        <td>
                            Celular:
                            <input type='text' name='_ass1_u_assinaturaemailcampos_celular' value='{$assinaturaEmailCampos[0]['celular']}'>
                        </td>
                    </tr>
                </table>
            </div>
        </li>
    </ul>
    <hr>
    <ul class='nav'>
        <li class='panel' style='background:#e6e6e6;border: 1px solid #ddd;'>
            <div class='titulo_email pointer' data-toggle='collapse' href='#assinaturaprincipal'>
                <a>Assinaturas de E-mail para $webMailEmail</a>
            </div>
            <div id='assinaturaprincipal' class='collapse'>
                <div style='padding: 15px;background:whitesmoke;'>
                    <input id='outrasassinaturas' class='compacto' type='text' cbvalue placeholder='Selecione'>
                </div>";

    // Busca todos os templates do tipo colaborador vínculados a pessoa
    $emailTemplates = ConfAcessoFuncionarioController::buscarEmailTemplateDoTipoColaborador($idPessoa, $webMailEmail);

    if(!count($emailTemplates))
    {
        echo "</li>
            </ul>";

        return;
    }

    foreach($emailTemplates as $template)
    {
        echo "
                    <div class='templates_email' idtemplate='{$template['idwebmailassinaturatemplate']}' idwebmailassinaturaobjeto='{$template['id2']}'>
                        <div class='titulo_email' style='padding: 6px 9px;'>
                            {$template['descricao']}<i class='fa fa-trash cinzaclaro hoververmelho pointer fright' onclick='deletaidentidade([{$template['removeids']}])'></i>
                        </div>
                        <div style='zoom:0.65;-moz-transform: scale(0.65);padding: 20px;background:whitesmoke;'>
                            {$template["htmlassinatura"]}
                        </div>
                    </div>
            ";
    }
            "</div>
        </li>
    </ul>";
}

function montarGrupoAssinaturaDeEmailHTML()
{
    GLOBAL $assinaturaDeGruposDeEmail;

    echo "<table class='table'>";

    foreach($assinaturaDeGruposDeEmail as $assinatura)
    {
        echo "  <tr>
                    <td>
                        {$assinatura['email']} ({$assinatura['descricao']})
                    </td>
                    <td style='text-align: center;'>
                        <i class='fa fa-bars hoververmelho cinza pointer' onclick=showWebmailAssinatura1({$assinatura['idwebmailassinaturaobjeto']})></i>
                    </td>
                    <td style='text-align: center;'>
                        <i class='fa fa-trash hoververmelho cinza pointer' onclick=delWebmailAssinatura1({$assinatura['idwebmailassinaturaobjeto']})></i>
                    </td>
                </tr>";
    }
    echo "</table>";
}

// Webmail
function montarWebMailPrincipalHTML()
{
    GLOBAL  $_1_u_pessoa_webmailpermissao, $_1_u_pessoa_webmailusuario, $_1_u_pessoa_webmailemail, $_1_u_pessoa_usuario,
            $_1_u_pessoa_idpessoa, $_acao;

    $habilitarWebmailEmpresa = traduzid('empresa', 'idempresa', 'habilitarwebmail', $_SESSION["SESSAO"]["IDEMPRESA"]);

    if($_1_u_pessoa_idpessoa and $habilitarWebmailEmpresa == 'Y')
    {
        echo "<tr>";

        if($_1_u_pessoa_webmailpermissao == "N" and $_1_u_pessoa_usuario)
        {
            echo "<td class='lbr'>Web Email</td>
                    <td>
                        <select name='_1_{$_acao}_pessoa_webmailpermissao' onchange='confemailpermissao();'>"
                        .fillselect([
                                'N' => 'NÃO',
                                'Y' => 'SIM'
                            ], $_1_u_pessoa_webmailpermissao)."
                        </select>
                    </td>";
        } else
        {
            // GVT - 19/05/2020 - Adicionada a funcionalidade do usuário escolher um email principal para o seu WebMail
            //					- com base nos domínios cadastrados na empresa.
            if (!$_1_u_pessoa_webmailusuario and !$_1_u_pessoa_webmailemail and $_1_u_pessoa_usuario)
            {
                echo "<td class='lbr'>Email Principal</td>
                        <td>
                            <div>";

                $dominios = ConfAcessoFuncionarioController::buscarDominios();

                foreach($dominios as $dominio)
                {
                    $email = "$_1_u_pessoa_usuario@{$dominio['dominio']}";
                    $auxuser = explode(".", $dominio["dominio"]);
                    $user = "{$_1_u_pessoa_usuario}_{$auxuser[0]}";
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        echo "  <input type='radio' name='_1_{$_acao}_pessoa_webmailemail' user='$user' id='k_{$dominio["iddominio"]}' value='$email'>
                                <label for='k_{$dominio["iddominio"]}'>$email</label><br>";
                    } else
                    {
                        echo "<div>ERRO: $email</div>";
                    }

                    echo "</div>
                    </td>";
                }
            } else {
                echo "  <td class='lbr'>Email Principal</td>
                        <td class='nowrap'>
                            <font color='red'>$_1_u_pessoa_webmailusuario $_1_u_pessoa_webmailemail</font>
                            <input name='_1_{$_acao}_pessoa_webmailusuario' type='hidden' size='5' value='$_1_u_pessoa_webmailusuario'>
                            <input name='_1_{$_acao}_pessoa_webmailemail' type='hidden' size='5' value='$_1_u_pessoa_webmailemail'>
                            <a title='Gerar Assinatura de Email.' class='fa fa-print cinza pointer hoverazul' onclick='janelamodal(`form/assinaturaemail.php?_acao=u&idpessoa=$_1_u_pessoa_idpessoa`)'></a>
                        </td>";
            }
        }

        echo "</tr>";
    }
}

function montarAssinaturaDeFuncionariosRelacionados()
{
    GLOBAL $assinaturaDeFuncionariosRelacionados;

    echo "<table class='table'>";

    foreach($assinaturaDeFuncionariosRelacionados as $assinatura)
    {
        echo "  <tr>
                    <td>
                        {$assinatura['email']} ({$assinatura['descricao']})
                    </td>
                    <td style='text-align: center;'>
                        <i class='fa fa-bars hoververmelho cinza pointer' onclick=showWebmailAssinatura({$assinatura['idwebmailassinaturaobjeto']})></i>
                    </td>
                    <td style='text-align: center;'>
                        <i class='fa fa-trash hoververmelho cinza pointer' onclick=delWebmailAssinatura({$assinatura['idwebmailassinaturaobjeto']})></i>
                    </td>
                </tr>";
    }

    echo "</table>";
}

function listarLps()
{
    GLOBAL $_1_u_pessoa_idpessoa;

    if(!$_1_u_pessoa_idpessoa)
    {
        return false;
    }

    $lps = ConfAcessoFuncionarioController::buscarLpsPorIdPessoa($_1_u_pessoa_idpessoa);

    echo "<table class='table table-striped planilha'>
            <tr>
                <th colspan='4'>LISTA(S) DE PERMISSÃO</th>
            </tr>";

    if(!count($lps))
    {
        echo "  <tr>
                    <th colspan='4'>Nenhum Lp Encontrada!</th>
                </tr>
            </table>";

        return false;
    }

    foreach($lps as $lp)
    {
        echo "<tr>
                <td>{$lp['sigla']}</td>
                <td>{$lp['descricao']}</td>
                <td><a title='Remover' class='fa fa-trash cinzaclaro hoverazul pointer' onclick='CB.post({objetos: {".'_x_d_lpobjeto_idlpobjeto'.":{$lp['idlpobjeto']}}, parcial: true});'></a></td>
                <td><a title='Editar Lista de permissão' class='fa fa-bars cinzaclaro hoverazul pointer' onclick='janelamodal(`?_modulo=_lp&_acao=u&idlp={$lp['idlp']}`);'></a></td>
            </tr>";
    }

    echo '</table>';
}

?>
<style type="text/Css">
    .mg0 {
        padding-top: 8px !important;
    }

    .titulo_email {
        position: relative;
        display: block;
        padding: 6px 9px;
        font-size: 11px;
        text-transform: uppercase;
    }

    .clickcollapse {
        cursor: pointer;
    }

    .clickcollapse:hover {
        color: #337ab7;
        text-decoration: underline;
    }

    .panel
    {
        margin-top: 1rem !important;
    }

    #avatarFoto {
		margin: 5px;
		border-radius: 5%;
		cursor: pointer;
		height: 180px;
		width: auto;
	}
</style>
<div class='row'>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading ">
                <table>
                    <tr>
                        <td>Id.</td>
                        <td><label class="idbox"><?= $_1_u_pessoa_idpessoa ?></label></td>
                        <td>Nome</td>
                        <td><input class="size25" name="_1_<?= $_acao ?>_pessoa_nome" type="text" class="" value="<?= $_1_u_pessoa_nome ?>" vnulo readonly></td>
                        <td>Nome Curto</td>
                        <td>
                            <input id="idpessoa" name="_1_<?= $_acao ?>_pessoa_idpessoa" type="hidden" value="<?= $_1_u_pessoa_idpessoa ?>" readonly>
                            <input type="hidden" name="_1_<?= $_acao ?>_pessoa_idtipopessoa" value="1">
                            <input class="size15" name="_1_<?= $_acao ?>_pessoa_nomecurto" type="text" class="" value="<?= $_1_u_pessoa_nomecurto ?>" vnulo readonly>
                            <? //LTM 01-09-20: Seta para os novos cadastros o jsonpreferencias para que pois quando faz alteração (update) em alguns módulos, não estava salvando a preferência do usuário pois o campo está NULL 
                            ?>
                            <? if ($_acao == 'i') { ?>
                                <input name="_1_<?= $_acao ?>_pessoa_jsonpreferencias" type="hidden" value="{}" readonly='readonly'>
                            <? } ?>
                        </td>
                        <td title="Número Informado Pela Contabilidade Obrigatório para Transferência de Arquivos">Contrato Emprego</td>
                        <td><input class="size7" name="_1_<?= $_acao ?>_pessoa_contratoemp" type="text" class="" value="<?= $_1_u_pessoa_contratoemp ?>" readonly></td>
                        <td class="lbr"><a class="fa fa-print pointer hoverazul" title="IMPRIMIR" onclick="janelamodal('./report/relfuncionario.php?acao=u&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')">
                                <? if (!empty($_1_u_pessoa_idpessoa)) { ?>
                        <td>
                            <button type="button" class="btn btn-primary btn-xs" style="margin-left: 100%;" onclick="sincronizarbiometria(<?= $_1_u_pessoa_idpessoa ?>)" title="SINCRONIZAR BIOMETRIA">
                                <i class="fa fa-circle"></i>Sincronizar Biometria
                            </button>
                        </td>
                    <? } ?>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Historico Conselho / Area / Dep / Setor -->
<div id="historico" style="display: none">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">Pessoa</th>
                <th scope="col">Tipo</th>
                <th scope="col">Descrição</th>
                <th scope="col">Alteração</th>
                <th scope="col">Por</th>
                <th scope="col">Em</th>
            </tr>
        </thead>
        <tbody>
            <?foreach($historicoConselhoAreaDepartamentoSetor as $item) 
            {
                if ($item['acao'] == "i") {
                    $acao = "Inserção";
                }
                if ($item['acao'] == "d") {
                    $acao = "Remoção";
                }
                if ($item['acao'] == "u") {
                    $acao = "Atualização";
                }
            ?>
                <tr>
                    <td><?= $item['nomecurto'] ?></td>
                    <td><?= $item['tipoobjeto'] ?></td>
                    <td><?= $item['descricao'] ?></td>
                    <td><?= $acao ?></td>
                    <td><?= $item['alteradopor'] ?></td>
                    <td><?= dmahms($item['alteradoem']) ?></td>
                </tr>
            <?}?>
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-12 col-md-6 px-0">
        <!-- Sistemas -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#sistema">Sistema</div>
                <div class="panel-body">
                    <div class="collapse" id="sistema">
                        <div class="alert alert-info">
                            <? if (!empty($_1_u_pessoa_idpessoa)) { ?>
                                <table>
                                    <tr>
                                        <td rowspan=99><i class="fa fa-shield fa-2x"></i></td>
                                        <td class="lbr">Usuário:</td>
                                        <td class="fonte14 preto bold">
                                            <?
                                            if (empty($_1_u_pessoa_usuario)) { //deixar informar somente se nao tiver sido salvo e a variavel da pagina nao existir
                                            ?>
                                                <input name="_1_<?= $_acao ?>_pessoa_usuario" id="username" type="text" size="15" value="<?= strtolower(str_replace(' ', '', $_1_u_pessoa_nomecurto)) ?>" vnulo maxlength="30" vregex="^[a-zA-Z0-9_.]+$" autocomplete="off" onchange="gerauser(this)">
                                            <?
                                            } else {
                                            ?>
                                                <?= $_1_u_pessoa_usuario ?>
                                            <?
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <? if (!empty($_1_u_pessoa_usuario) and empty($_1_u_pessoa_email)) { ?>
                                    </tr>
                                </table>
                                <table>
                                    <tr>
                                        <td colspan=99 class="cinza">Atenção: Para geração ou alteração de senha, o usuário deve ter salvo o seu <span class="link" onclick="$('[name=_1_u_pessoa_email]').addClass('highlight').focus();">email pessoal</span></b>
                                        </td>
                                    <? } else { ?>
                                        <td>Senha:</td>
                                        <td><span class="link bold" onclick="javascript:CB.mostraRecuperaSenha('<?= $_1_u_pessoa_usuario ?>','<?= $_1_u_pessoa_email ?>')">Gerar Senha</span></td>
                                    <? } ?>
                                    </tr>
                                </table>
                            <? } ?>
                        </div>
                        <table>
                            <tr>
                                <td class="lbr">Assina Teste</td>
                                <td class="fw-bold">
                                    <?= $_1_u_pessoa_assinateste == 'Y' ? 'SIM' : 'Não' ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbr">Visualiza Resultado</td>
                                <td class="fw-bold">
                                    <?= $_1_u_pessoa_visualizares == 'Y' ? 'SIM' : 'Não' ?>
                                </td>
                            </tr>

                            <tr>
                                <!--JLAL - 14-08-20 **Acrescimo do campo (Acesso ext. ao sistema) para verificar se o usuário terá acesso ao sistema mesmo que não enteja presente na empresa**-->
                                <td>Acesso ext. ao sistema</td>
                                <td class="fw-bold">
                                    <?= $_1_u_pessoa_acesso == 'Y' ? 'SIM' : 'Não' ?>
                                </td>
                            </tr>
                            <!-- One Office -->
                            <tr>
                                <td>Acesso Only Office</td>
                                <td>
                                    <select name="_1_<?= $_acao ?>_pessoa_workspace_onlyoffice" class="size25">

                                        <? fillselect([
                                                'Y' => 'SIM',
                                                'N' => 'NÃO'
                                            ], $_1_u_pessoa_workspace_onlyoffice); ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>
                            <?= montarWebMailPrincipalHTML(); ?>
                            <?
                            // GVT - 19/05/2020 - Um funcionário pode ter mais de um email associado.
                            //					- Ao associar um novo email, é criado um registro na tabela
                            //					- emailvirtualconf, setando o campo email original com o valor selecionado
                            //					- no input abaixo e o campo email destino com o valor do input do Email Principal ("_1_u_pessoa_webmailemail)
                            if (!empty($_1_u_pessoa_usuario)) { ?>
                                <tr>
                                    <td class="lbr">Email Corporativo</td>
                                    <td><input id="emailvirtual" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?= listaEmailVirtual($idPessoa); ?>
                                    </td>
                                </tr>
                            <? } ?>
                        </table>
                        <?= listarLps() ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Conselhos, Áreas, Departamentos e Setores de Atuação -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#areasetor">
                    Conselhos, Áreas, Departamentos e Setores de Atuação
                </div>
                <div class="panel-body collapse" id="areasetor">
                    <a class="fa fa-search azul pointer hoverazul mb-3" title="Histórico" onClick="historico();"></a>
                    <?= listarConselhoAreaDepartamentoSetorPorIdPessoa($idPessoa) ?>
                </div>
            </div>
        </div>
        <!-- Assinaturas -->
        <? if ($_1_u_pessoa_webmailemail) { ?>
            <div class="col-sm-12">
                <!-- Assinatura de E-mail pessoal -->
                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas1">
                        Assinatura de E-mail pessoal
                    </div>
                    <div class="panel-body mg0 collapse" id="assinaturas1">
                        <?=  montarAssinaturaDeEmailHTML($idPessoa, $webMailEmail) ?>
                    </div>
                    <!-- Assinaturas de Grupos de E-mail -->
                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas3">
                        Assinaturas de Grupos de E-mail
                    </div>
                    <div class="panel-body mg0" id="assinaturas3">
                        <input id="assinaturasgrupo" class="compacto" type="text" cbvalue placeholder="Selecione">
                        <?= montarGrupoAssinaturaDeEmailHTML() ?>
                    </div>
                    <!-- Assinaturas de Funcionários Relacionados -->
                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas2">Assinaturas de Funcionários Relacionados</div>
                    <div class="panel-body mg0" id="assinaturas2">
                        <input id="assinaturasfunc" class="compacto" type="text" cbvalue placeholder="Selecione">
                        <?= montarAssinaturaDeFuncionariosRelacionados() ?>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>
    <div class="col-sm-12 col-md-6 px-0">
        <!-- Foto -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#avatar">Foto do Colaborador</div>
                <div class="panel-body">
                   <div class="col-sm-12 collapse text-center"  id="avatar">
                        <img src="inc/img/avatarprofile.png" id="avatarFoto" title="Clique para alterar a foto do funcionário" class="dz-clickable">
                   </div>
                </div>
            </div>
        </div>
        <!-- Tags Vinculadas -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#tags">Tags Vinculadas</div>
                <div class="panel-body">
                    <div class="collapse" id="tags">
                        <table class="table table-striped">
                            <thead>
                                <th>Tag</th>
                                <th>Descrição</th>
                                <th></th>
                            </thead>
                            <tbody>
                                <? foreach($tagsVinculadas as $tag) { ?>
                                    <tr>
                                        <td><?= $tag['sigla'] ?>-<?= $tag['tag'] ?></td>
                                        <td><?= $tag['descricao'] ?></td>
                                        <td>
                                            <a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $tag['idtag'] ?>');"></a>
                                        </td>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Certificados -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#certificado">
                    Certificado
                </div>
                <div class="panel-body" id="certificado">
                    <table>
                        <tr>
                            <td>
                                Anexo Certificado:
                            </td>
                            <td>
                                <i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: inline-flex;" id="certanexo" title="Clique para adicionar um certificado"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Imagem Assinatura:
                            </td>
                            <td>
                                <i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: inline-flex;" id="imagemassinatura"></i>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- Alertas -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#alertas">Alertas</div>
                <div class="panel-body collapse" id="alertas">
                    <table class="table table-striped planilha ">
                        <tr>
                            <td><input id="immsgconf" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                        </tr>
                    </table>
                    <?= listaAlertas($idPessoa) ?>
                </div>
            </div>
        </div>
        <!-- Fluxos vinculados -->
		<div class="col-sm-12" >
			<div class="panel panel-default ">
				<div class="panel-heading" data-toggle="collapse" href="#fluxos">Fluxos vinculados</div>
				<div class="panel-body" id="fluxos">
					<table class='table table-striped w-100'>
						<tbody>
							<?=listaFluxos($idPessoa)?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>
    </div>
</div>

<script>
	function excluiremailvirtual(idemailvirtual) {
		if (confirm("Deseja retirar o email virtual?")) {
			CB.post({
				objetos: "_x_d_emailvirtualconf_idemailvirtualconf=" + idemailvirtual,
				parcial: true
			});
		}
	}
</script>
<?
    $tabaud = "pessoa"; //pegar a tabela do criado/alterado em antigo
    require 'viewCriadoAlterado.php';
    require_once(__DIR__."/../form/js/confacessofuncionario_js.php");
?>