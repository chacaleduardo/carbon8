<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/folhapagamento_controller.php");
if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

$_idempresa = $_GET['_idempresa'];
$_acao = $_GET['_acao'];

$pagvaltabela = "folhapagamento";
$pagvalcampos = array(
    "idfolhapagamento" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM folhapagamento where idfolhapagamento = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if ($_1_u_folhapagamento_idfolhapagamento) {
    $arquivo = FolhaPagamentoController::buscarArquivoPorTipoObjetoEIdObjeto($_1_u_folhapagamento_idfolhapagamento, 'folhapagamento');
    $lancamentos = FolhaPagamentoController::buscarGruposConciliacao($_1_u_folhapagamento_idfolhapagamento);
}

?>
<link rel="stylesheet" href="../form/css/folhapagamento_css.css?_<?=date("dmYhms")?>" />
<link rel="stylesheet" href="../inc/css/datatables/ag-theme-balham.min.css">
<script src="../inc/js/datatables/ag-grid-community.min.js?_<?=date("dmYhms")?>"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<div id="mensagem-erro" class="col-xs-12 alert alert-danger mb-4 hidden" role="alert"></div>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-12">
                    Folha Pagamento
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group col-xs-10 col-md-10">
                <label>Empresa</label>
                <div style="font-size: 12px;">
                    <input type="hidden" id="idfolhapagamento" name="_1_<?=$_acao?>_folhapagamento_idfolhapagamento" value="<?=$_1_u_folhapagamento_idfolhapagamento?>">
                    <select name="_1_<?=$_acao?>_folhapagamento_idempresa" vnulo class="size30">
                        <option></option>
                        <?fillselect(FolhaPagamentoController::listarEmpresasAtivas(), $_idempresa);?>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-2 text-end">
                <? if (!$arquivo['idarquivo']) { ?>
                    <label for="" id="uploadtxt" class="btn btn-primary rounded text-light" <?= !$_1_u_folhapagamento_idfolhapagamento ? 'disabled' : '' ?>>
                        IMPORTAR "ARQUIVO.TXT"
                    </label>
                <? } else { ?>
                    <a href="/<?= $arquivo['caminho'] ?>" target="_blank" class="me-2" style="padding-right: 10px;">
                        <i class="fa fa-file-text-o fa-2x" title="Extrato da fatura"></i>
                    </a>     
                    <? if(count($lancamentos) == 0){ ?>               
                        <label for="" id="remover-arquivo" class="btn btn-danger rounded text-light" <?= $_1_u_folhapagamento_status == 'CONCILIADO' ? 'disabled' : '' ?>>
                            REMOVER ARQUIVO
                        </label>
                        <? 
                    }
                } 
                ?>
            </div>
        </div>
    </div>
</div>

<?
if ($_1_u_folhapagamento_idfolhapagamento) {    
    if(count($lancamentos) > 0){
        ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Relatórios de Saldos (<?=count($lancamentos)?> itens)
                    <span style="float: right;">Período: <?=$_1_u_folhapagamento_periodo; ?></span>
                </div>
                <div class="panel-body">
                    <table class="table table-striped planilha grade">
                        <tr>
                            <th class="size20 text-center">DATA DO LANÇAMENTO</th>
                            <th class="size20 text-center">DESCRIÇÃO DO LANÇAMENTO</th>
                            <th class="text-center">DESCRIÇÃO DO HISTÓRICO</th>
                            <th class="size15 text-center">CÓDIGO DO HISTÓRICO</th>
                            <th class="size20 text-center">VALOR TOTAL</th>
                            <th class="size8 text-center">GERAR NF</th>
                        </tr>
                        <?
                        foreach($lancamentos as $_lancamento){
                            ?>
                            <tr>
                                <td align="center"><?=dma($_lancamento['datalancamento'])?></td>
                                <td align="center"><a onclick="mostrarDetalhamentoFolha('<?=$_lancamento['codigoevento']?>', '<?=$_lancamento['historicoevento']?>')"><?=$_lancamento['descricaolancamento']?> (<span class="qtd-<?= $_lancamento['codigoevento'] ?>"><?=$_lancamento['contlancamento']?></span>)</a></td>
                                <td align="center"><?=$_lancamento['historicoevento']?></td>
                                <td align="center"><?=$_lancamento['codigoevento']?></td>
                                <td align="right">
                                    <a class="valor-<?= $_lancamento['codigoevento'] ?>" onclick="mostrarDetalhamentoFolha('<?=$_lancamento['codigoevento']?>', '<?=$_lancamento['historicoevento']?>')">R$ <?=number_format(tratanumero($_lancamento['valorlancamento']), 2, ',', '.')?></a>
                                    <? $detalhamentoLancamentos[$_lancamento['codigoevento']] = FolhaPagamentoController::buscarDetalhamentoLancamento($_1_u_folhapagamento_idfolhapagamento, $_lancamento['codigoevento']); ?>                                    
                                </td>
                                <td align="center">
                                    <?
                                    if(empty($_lancamento['idnf'])) {
                                        ?>
                                        <button id="gerar-btn-<?=$_lancamento['codigoevento']?>" onclick="gerarNF('<?=$_1_u_folhapagamento_idfolhapagamento?>', '<?=$_lancamento['codigoevento']?>', '<?=$_1_u_folhapagamento_idempresa?>')" class="btn btn-primary rounded text-light botao-gerar-nf">
                                            <span id="btn-text">GERAR</span>
                                        </button>
                                        <?
                                    } else {
                                        ?>
                                        <a href="/?_modulo=comprasrh&_acao=u&idnf=<?=$_lancamento['idnf']?>&_idempresa=<?=$_lancamento['idempresa']?>" target="_blank"><?=$_lancamento['idnf']?></a>
                                    <? } ?>
                                </td>
                            </tr>                    
                            <?
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
        <?
    }
}

$tabaud = "folhapagamento";
require_once('../form/viewEvento.php');
require_once('../form/viewCriadoAlteradoNew.php');
require_once('../form/js/folhapagamento_js.php');
?>