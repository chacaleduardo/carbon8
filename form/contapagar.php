<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");

require_once(__DIR__."/controllers/conciliacaofinanceira_controller.php");

$idcliente = $_GET["idpessoa"];
$idformapagamento = $_GET["idformapagamento"];

if (!empty($_GET["idcontapagarcp"]) and empty($_GET["idcontapagar"])) {
    $_GET["idcontapagar"] = $_GET["idcontapagarcp"];
    $_GET["_acao"] = 'u';
    $idcontapagarcp = 'Y';
}

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os par&acirc;metros GET que devem ser validados para compor o select principal
 *                pk: indica par&acirc;metro chave para o select inicial
 *                vnulo: indica par&acirc;metros secund&aacute;rios que devem somente ser validados se nulo ou n&atilde;o
 */
$pagvaltabela = "contapagar";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idcontapagar" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as vari&aacute;veis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contapagar where idcontapagar = '#pkid' ";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das vari&aacute;veis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$conciliacaoFinanceira = [];

if ($idcontapagarcp == 'Y') {
    $_acao = 'i';
    $_1_u_contapagar_status = 'PENDENTE';
}
if (!empty($_1_u_contapagar_idformapagamento)) {

    //se for agrupado por nota não pode alterar o valor deve alterar o valor da nota
    $sqlf = "select agrupnota,agrupado    
    from formapagamento 
    where idformapagamento=".$_1_u_contapagar_idformapagamento;
    $resf = d::b()->query($sqlf) or die("erro ao buscar forma de pagamento\n".mysqli_error(d::b())."\n".$sqlf);
    $rowf = mysqli_fetch_assoc($resf);
    if ($rowf['agrupnota'] == 'Y' and $rowf['agrupado'] == 'Y') {
        $readonlyval = "readonly='readonly'";
    }
}

if ($_1_u_contapagar_saldook == 'Y') {
    $disabled = "disabled='disabled' ";
    $readonly = "readonly='readonly'";
    $readonlyval = "";
} elseif ($_1_u_contapagar_status == "QUITADO") {
    $dtdisabled = " disabled='disabled' ";
    $mostracal = 'N';
}
if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15) {
    $disabled = "disabled='disabled' ";
    $readonly = "readonly='readonly'";
    $readonlyval = "";
    $dtdisabled = " disabled='disabled' ";
}

if ($_acao == 'u') {
    $campdisabled = "disabled='disabled' ";
    $campreadonly = "readonly='readonly'";

    $conciliacaoFinanceira = ConciliacaoFinanceiraController::buscarConciliacaoFinananceiraPorIdContaPagar($_1_u_contapagar_idcontapagar);
}

function getClientesnf($modulo)
{
    global $_acao, $_1_u_contapagar_idpessoa;
    if ($_acao == 'i') {
        $ststatus = " and p.status='ATIVO' ";
    } else {
        $ststatus = " ";
    }
    if (!empty($_1_u_contapagar_idpessoa)) {
        $orand = " or idpessoa =".$_1_u_contapagar_idpessoa." ";
    } else {
        $orand = "";
    }

    if ($modulo == 'contapagarlogistica') {
        $sql = "SELECT
                p.idpessoa,
                p.nome,
                CASE p.idtipopessoa
                WHEN 1 THEN 'FUNCIONÁRIO'
                WHEN 2 THEN 'EMPRESA'
                WHEN 5 THEN 'FORNECEDOR'
                WHEN 6 THEN 'FABRICANTE'
                WHEN 7 THEN 'TERCEIRO'
                WHEN 9 THEN 'PRESTADOR'
                WHEN 11 THEN 'TRANSPORTADOR'
                WHEN 12 THEN 'REPRESENTANTE'
                WHEN 116 THEN 'DISTRIBUIDOR'
                        END as tipo
                FROM pessoa p			
                WHERE (p.idtipopessoa  in (11,5) ".$orand." )
			".$ststatus."
			".share::pessoasPorSessionIdempresa("p.idpessoa")."
          ORDER BY p.nome";
    } else {
        $sql = "SELECT
                p.idpessoa,
                p.nome,
                CASE p.idtipopessoa
                WHEN 1 THEN 'FUNCIONÁRIO'
                WHEN 2 THEN 'EMPRESA'
                WHEN 5 THEN 'FORNECEDOR'
                WHEN 6 THEN 'FABRICANTE'
                WHEN 7 THEN 'TERCEIRO'
                WHEN 9 THEN 'PRESTADOR'
                WHEN 11 THEN 'TRANSPORTADOR'
                WHEN 12 THEN 'REPRESENTANTE'
                WHEN 116 THEN 'DISTRIBUIDOR'
                        END as tipo
                FROM pessoa p			
                WHERE 
                 (p.idtipopessoa  in (1,2,5,6,7,9,11,12,116) ".$orand." )
                 ".$ststatus."
				 ".share::pessoasPorSessionIdempresa("p.idpessoa")."
          ORDER BY p.nome";
    }


    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret = array();
    while ($r = mysqli_fetch_assoc($res)) {
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        $arrret[$r["idpessoa"]]["tipo"] = $r["tipo"];
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formaliza&ccedil;&atilde;o
$arrCli = getClientesnf($_GET["_modulo"]);
//print_r($arrCli); die;
$jCli = $JSON->encode($arrCli);

if (!empty($_1_u_contapagar_idformapagamento)) {
    $sqla = "select agrupado,agruppessoa,agrupfpagamento, vencimento,formapagamento from formapagamento where idformapagamento=".$_1_u_contapagar_idformapagamento;
    $resap = d::b()->query($sqla) or die("Erro ao buscar informações da forma de pagamento sql=".$sqla);
    $rowag = mysqli_fetch_assoc($resap);
}

?>

<style>
    .respreto {
        font-size: 11px;
    }

    .planilha td {
        font-size: 10px;
    }
</style>
<script>
    <? if ($_1_u_contapagar_status == 'QUITADO' or $_1_u_contapagar_progpagamento == 'S') { ?>
        $("#cbModuloForm").find('input').not('[name*="contapagar_obs"],[name*="contapagar_idcontapagar"],[name*="contapagar_status"],[name*="contapagar_idpessoa"],[name*="contapagar_status_ant"]').prop("disabled", true);
        $("#cbModuloForm").find("select").prop("disabled", true);
        $("#cbModuloForm").find("textarea").prop("disabled", true);
    <? } ?>
</script>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">Contas a Pagar / Receber</div>
            <div class="panel-body">
                <div class="row ">
                    <div class="col-md-2">
                        <? if ($idcontapagarcp == "Y") { ?>
                            <input name="_1_<?=$_acao?>_contapagar_idcontapagar" type="hidden" value="" readonly='readonly'>

                        <? } else { ?>
                            <input name="contapagar_idtipopessoa" type="hidden" value="<?=$_SESSION["SESSAO"]["IDTIPOPESSOA"]?>">
                            <input name="_1_<?=$_acao?>_contapagar_idcontapagar" type="hidden" value="<?=$_1_u_contapagar_idcontapagar?>">
                            <input name="_1_<?=$_acao?>_contapagar_idobjeto" type="hidden" value="<?=$_1_u_contapagar_idobjeto?>">
                            <input name="_1_<?=$_acao?>_contapagar_tipoobjeto" type="hidden" value="<?=$_1_u_contapagar_tipoobjeto?>">
                        <? } ?>
                    </div>
                </div>
                <? if ($_acao == 'i' or !empty($_1_u_contapagar_tipoespecifico)) { ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Tipo:
                        </div>
                        <div class="col-md-6">

                            <? if ($_acao == 'i') { ?>
                                <select name="_1_<?=$_acao?>_contapagar_tipoespecifico" <?=$disabled ?> vnulo>
                                    <? fillselect(array('AGRUPAMENTO' => 'Agrupamento', 'REPRESENTACAO' => 'Comissão', 'IMPOSTO' => 'Imposto'), $_1_u_contapagar_tipoespecifico); ?>
                                </select>
                            <? } else { ?>
                                <label class="idbox" style="padding: 4px 4px !important;"><?=$_1_u_contapagar_tipoespecifico ?>
                                    <input name="_1_<?=$_acao?>_contapagar_tipoespecifico" type="hidden" value="<?=$_1_u_contapagar_tipoespecifico?>">
                                </label>
                            <? } ?>
                        </div>
                    </div>
                <? } ?>

                <?
                if ($_acao == 'u') {
                    $sqlre = "SELECT idretornoremessa,valor FROM retornoremessaitem where idcontapagar = ".$_1_u_contapagar_idcontapagar." limit 1";
                    $rere = d::b()->query($sqlre) or die("Erro ao pegar Retorno Remessa: ".mysqli_error(d::b())."\n".$sqlre);
                    $rowre = mysqli_fetch_assoc($rere);
                }

                if (!empty($_1_u_contapagar_idobjeto) and ($_1_u_contapagar_tipoobjeto == "nf"
                    or $_1_u_contapagar_tipoobjeto == "gnre" or $_1_u_contapagar_tipoobjeto == 'nf_darf'
                    or $_1_u_contapagar_tipoobjeto == 'nf_ir'  or $_1_u_contapagar_tipoobjeto == 'nf_inss'
                    or $_1_u_contapagar_tipoobjeto == 'nf_issret')) {

                    $sqlex = "SELECT * FROM nf WHERE idnf = ".$_1_u_contapagar_idobjeto;

                    $qrex = d::b()->query($sqlex) or die("Erro ao buscar dados da nota:".mysql_error());
                    $rowr = mysqli_fetch_assoc($qrex);
                    if($rowr["tiponf"]=='V'){ $vtiponf = "Venda";  $link="pedido";}
                    if($rowr["tiponf"]=='C'){ $vtiponf = "Compra"; $link="nfentrada";}	
                    if($rowr["tiponf"]=='O'){ $vtiponf = "Compra"; $link="nfentrada";}		
                    if($rowr["tiponf"]=='S'){ $vtiponf = "Servi&ccedil;o";  $link="nfentrada";}
                    if($rowr["tiponf"]=='T'){ $vtiponf = "Cte";  $link="nfcte";}
                    if($rowr["tiponf"]=='E'){ $vtiponf = "Consession&aacute;ria"; $link="nfentrada";}
                    if($rowr["tiponf"]=='M'){ $vtiponf = "Manual/Cupom"; $link="nfentrada";}
                    if($rowr["tiponf"]=='B'){ $vtiponf = "Recibo"; $link="nfentrada";}
                    if($rowr["tiponf"]=='R'){ $vtiponf = "PJ"; $link="comprasrh";}
			        if($rowr["tiponf"]=='F'){ $vtiponf = "Captação"; $link="nfentrada"; $tipo='F';}
                    if($rowr["tiponf"]=='D'){ $vtiponf = "Sócios"; $link="comprassocios"; $tipo='D';}

                    ?>
                    <div class="row ">
                        <div class="col-md-2">
                            NF:
                        </div>
                        <div class="col-md-6"><?=$vtiponf?></div>
                    </div>
                    <div class="row ">
                        <div class="col-md-2">
                            ID:
                        </div>
                        <div class="col-md-6">
                            <a class="pointer hoverazul" title="Nota Fiscal" onclick="janelamodal('?_modulo=<?=$link?>&_acao=u&idnf=<?=$_1_u_contapagar_idobjeto?>')"><?=$_1_u_contapagar_idobjeto?></a>
                        </div>
                    </div>
                <?
                } elseif (!empty($_1_u_contapagar_idobjeto) and $_1_u_contapagar_tipoobjeto == "notafiscal") {
                ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Tipo:
                        </div>
                        <div class="col-md-6">Venda</div>
                    </div>
                    <div class="row ">
                        <div class="col-md-2">
                            ID:
                        </div>
                        <div class="col-md-6">
                            <a class="pointer hoverazul" title="NFs" onclick="janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=<?=$_1_u_contapagar_idobjeto?>')"><?=$_1_u_contapagar_idobjeto?></a>
                        </div>
                    </div>

                <?
                } else {
                ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Nº Documento
                        </div>
                        <div class="col-md-4">
                            <input SIZE="10" <?=$readonly ?> name="_1_<?=$_acao?>_contapagar_ndocumento" type="text" <?=$readonly ?> value="<?=$_1_u_contapagar_ndocumento?>">
                        </div>

                    </div>

                <?
                }
                $arrconfCP = getDadosConfContapagar('COMISSAO');

                if (empty($_1_u_contapagar_idobjeto) and ($_1_u_contapagar_tipoespecifico == "REPRESENTACAO" or $_1_u_contapagar_tipoespecifico == 'AGRUPAMENTO') and !empty($_1_u_contapagar_idpessoa) and $_1_u_contapagar_idformapagamento == $arrconfCP['idformapagamento']) {
                ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Gerar NF:
                        </div>
                        <div class="col-md-6">
                            <button id="" type="button" class="btn btn-success btn-xs" onclick="gerarnota(<?=$_1_u_contapagar_idcontapagar?>, <?=$_1_u_contapagar_idpessoa?>);">
                                <i class="fa fa-plus"></i>Novo
                            </button>
                        </div>
                    </div>

                <?
                }

                //if(!empty($_1_u_contapagar_idpessoa) or $_acao=='i'){
                ?>
                <div class="row ">
                    <div class="col-md-2">
                        Pessoa:
                    </div>
                    <div class="col-md-6">

                        <? if ($_1_u_contapagar_status == 'QUITADO' or $_1_u_contapagar_status == 'INATIVO') { ?>
                            <input id="idpessoa" name="_1_<?=$_acao?>_contapagar_idpessoa" type="hidden" cbvalue="<?=$_1_u_contapagar_idpessoa?>" value="<?=$_1_u_contapagar_idpessoa?>">
                        <?
                            echo traduzid("pessoa", "idpessoa", "nome", $_1_u_contapagar_idpessoa);
                        } else {
                            ?>
                            <input id="idpessoa" <?=$disabled ?> type="text" name="_1_<?=$_acao?>_contapagar_idpessoa" cbvalue="<?=$_1_u_contapagar_idpessoa?>" value="<?=$arrCli[$_1_u_contapagar_idpessoa]["nome"]?>" style="width: 30em;">
                            <?
                        }
                        if (!empty($_1_u_contapagar_idpessoa)) {
                        ?>
                            <a class="fa fa-bars pointer hoverazul" title="Cadastro de Pessoas" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_contapagar_idpessoa?>')"></a>
                        <? } ?>
                    </div>
                </div>
                <?
                if ($_1_u_contapagar_idcontadesc) {
                    ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Conta Desc.:
                        </div>
                        <div class="col-md-6">
                            <? echo (traduzid('contadesc', 'idcontadesc', 'contadesc', $_1_u_contapagar_idcontadesc)); ?>
                        </div>
                    </div>
                    <?
                }
                ?>
                <div class="row ">
                    <div class="col-md-2">
                        Observa&ccedil;&atilde;o:
                    </div>
                    <div class="col-md-10">
                        <input SIZE="60" <?=$readonly?> name="_1_<?=$_acao?>_contapagar_obs" type="text" <?=$readonly?> autocomplete="off" value="<?=$_1_u_contapagar_obs?>">
                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-2">
                        Fatura Automática:
                    </div>
                    <div class="col-md-6">
                        <?
                        if (!empty($_1_u_contapagar_idcontapagar)) {
                            $sqlf = "select agrupnota from formapagamento where agrupnota='N' and idformapagamento=".$_1_u_contapagar_idformapagamento;
                            $rtf = d::b()->query($sqlf) or die("erro ao buscar contapagaritem do conta: ".mysqli_error(d::b()));
                            $qtdf = mysqli_num_rows($rtf);
                            if ($qtdf > 0) {
                                $desabledformapagamento = "disabled='disabled' ";
                            }
                        }
                        ?>
                        <? if (empty($_1_u_contapagar_idformapagamento)) { ?>
                            <select <?=$disabled?> <?=$desabledformapagamento?> style="max-width: 410px;" name="_1_<?=$_acao?>_contapagar_idformapagamento" vnulo>
                                <option></option>
                                <? fillselect("SELECT idformapagamento,descricao 
                                       from formapagamento f
                                       where status='ATIVO' ".getidempresa('idempresa', 'formapagamento')."
                                       AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (".getModsUsr('LPS').") AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'formapagamento' and ov.idobjetovinc = f.idformapagamento )
                                       order by ord,descricao desc"); ?>
                            </select>
                            <input type='hidden' name="_1_<?=$_acao?>_contapagar_idagencia" id="idagencia" value="<?=$_1_u_contapagar_idagencia?>">
                        <? } else { ?>
                            <label class="alert-warning"><?=traduzid('formapagamento', 'idformapagamento', 'descricao', $_1_u_contapagar_idformapagamento)?></label>
                            <i <?=$disabled?> <?=$desabledformapagamento?> onclick="mostraInputFormapagamento(this)" class="fa fa-pencil azul"></i>
                            <select style="display: none;" <?=$disabled?> <?=$desabledformapagamento?> style="max-width: 410px;" name="_1_<?=$_acao?>_contapagar_idformapagamento" vnulo>
                                <option></option>
                                <? fillselect("SELECT idformapagamento,descricao 
                                       from formapagamento f
                                       where status='ATIVO' ".getidempresa('idempresa', 'formapagamento')."
                                        AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (".getModsUsr('LPS').") AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'formapagamento' and ov.idobjetovinc = f.idformapagamento )
                                        order by ord,descricao desc", $_1_u_contapagar_idformapagamento); ?>
                            </select>
                            <input type='hidden' name="_1_<?=$_acao?>_contapagar_idagencia" id="idagencia" value="<?=$_1_u_contapagar_idagencia?>">
                        <? } ?>
                    </div>
                </div>

                <div class="row ">
                    <div class="col-md-2">
                        Tipo:
                    </div>
                    <div class="col-md-4">
                        <select <?=$disabled ?> name="_1_<?=$_acao?>_contapagar_tipo" id="tipo" style="max-width: 100px;" <?=$disabled ?>>
                            <?
                            fillselect(array('D' => 'D&eacute;bito', 'C' => 'Cr&eacute;dito'), $_1_u_contapagar_tipo);
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-2">
                        Valor <? if ($_1_u_contapagar_status == "ABERTO" or $_1_u_contapagar_status == "INICIO") { ?> Provisionado<? } ?>:
                    </div>
                    <div class="col-md-2" style="max-width: 120px;">
                        <input SIZE="10" <?=$readonlyval?> <?=$readonly?> id="basicCalculator" style="max-width: 100px;" name="_1_<?=$_acao?>_contapagar_valor" type="text" <?=$readonly?> autocomplete="off" value="<?=$_1_u_contapagar_valor?>" vnulo>
                    </div>
                    <? if ($_1_u_contapagar_tipoespecifico != 'NORMAL' and !empty($_1_u_contapagar_idcontapagar)) {
                        if ($_1_u_contapagar_tipoespecifico == 'AGRUPAMENTO') {
                            $sqlci = "select sum(valor) as valori from contapagaritem where  status!='INATIVO' and idcontapagar=".$_1_u_contapagar_idcontapagar;
                        } else {
                            $sqlci = "select sum(valori) as valori from (
                                        select sum(i.valor) as valori from contapagaritem i join contapagar c on(c.idcontapagar = i.idobjetoorigem )
                                        where  i.status!='INATIVO' 
                                        and i.tipoobjetoorigem='contapagar'
                                        and i.idcontapagar=".$_1_u_contapagar_idcontapagar."
                                        union                     
                                        select sum(valor) as valori from contapagaritem where status!='INATIVO' and tipoobjetoorigem ='nf' 
                                        and idcontapagar=".$_1_u_contapagar_idcontapagar."
                                    ) as u";
                        }

                        $resci = d::b()->query($sqlci) or die("Erro ao buscar valor da contapagaritem sql=".$sqlci);
                        $rowci = mysqli_fetch_assoc($resci);
                        if (tratanumero($_1_u_contapagar_valor) != $rowci['valori']) {
                            $diferenca = 'Y';
                            $dif = $rowci['valori'] - tratanumero($_1_u_contapagar_valor);
                            ?>
                            <div class="col-md-1">
                                <label class="idbox"><?=$dif ?>
                                </label><i class="fa fa-exclamation-triangle laranja pointer" title="Diferença do valor dos itens com o valor previsionado!!!"></i>
                            </div>
                            <? if ($_1_u_contapagar_progpagamento == 'S') { ?>
                                <label class="idbox">Para realizar a alteração do campo Valor, é necessário desprogramar a parcela no extrato.</label>
                            <? } ?>
                        <?
                        }
                        ?>
                    <? }             
                        ?>
                    
                </div>
                <? if (mysqli_num_rows($rere) > 0) { ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Juros/Multa:
                        </div>
                        <div class="col-md-4">
                            <input SIZE="10" type="text" autocomplete="off" style="max-width: 100px;" disabled value="<?=tratanumero($rowre['valor']) - tratanumero($valors)?>">
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-md-2">
                            Valor Pago:
                        </div>
                        <div class="col-md-4">
                            <input SIZE="10" type="text" autocomplete="off" style="max-width: 100px;" disabled value="<?=tratanumero($rowre['valor'])?>">
                        </div>
                    </div>

                <? }
                if ( $rowag['formapagamento']=='BOLETO' and $_1_u_contapagar_tipo=='C') {
                    if($rowag['vencimento'] == 'N'){
                        $dtdisabledbol = " disabled='disabled' ";
                    }
                ?>
                    <div class="row ">
                        <div class="col-md-2">
                            <font color="red">Venc. Boleto:</font>
                        </div>
                        <div class="col-md-4 nowrap">
                            <input <?=$dtdisabled?> name="_1_<?=$_acao?>_contapagar_datapagto" size="8" class="calendario" style="max-width: 100px;" id="vencimento_2" <?=$readonly?> <?=$dtdisabled?> <?=$dtdisabledbol?> value="<?=$_1_u_contapagar_datapagto?>" vnulo>
                        </div>
                    </div>
                <?
                }elseif($_1_u_contapagar_tipo=='D'){
                    if($rowag['vencimento'] == 'N'){
                        $dtdisabledbol = " disabled='disabled' ";
                    }
                    ?>
                    <div class="row ">
                        <div class="col-md-2">
                            <font color="red">Vencimento:</font>
                        </div>
                        <div class="col-md-4 nowrap">
                            <input <?=$dtdisabled?> name="_1_<?=$_acao?>_contapagar_datapagto" size="8" class="calendario" style="max-width: 100px;" id="vencimento_2" <?=$readonly?> <?=$dtdisabled?> <?=$dtdisabledbol?> value="<?=$_1_u_contapagar_datapagto?>" vnulo>
                        </div>
                    </div>
                <?
                }               
                ?>
                <div class="row ">
                    <div class="col-md-2">
                    Recebimento:
                    </div>
                    <div class="col-md-2"  style="max-width: 120px;">
                        <input <?=$dtdisabled ?> name="_1_<?=$_acao?>_contapagar_datareceb" size="8" class="calendario" style="max-width: 100px;" id="vencimento_2" <?=$readonly ?> <?=$dtdisabled ?> value="<?=$_1_u_contapagar_datareceb?>" vnulo>
                    </div>
                
                </div>
              
                <?
                   if(!empty($_1_u_contapagar_datareceb)){

                    $sqln="select * from inadimplencia where idcontapagar = ".$_1_u_contapagar_idcontapagar;
                    $resn = d::b()->query($sqln) or die("Erro ao buscar inadimplencia sql=".$sqln);
                    $qtdn = mysqli_num_rows($resn);

                    $date1 = new DateTime();
                    $date2 = DateTime::createFromFormat('d/m/Y', $_1_u_contapagar_datareceb);

                    if(($_1_u_contapagar_tipo=='C' and ($_1_u_contapagar_status =='ABERTO' OR  $_1_u_contapagar_status =='FECHADO' OR  $_1_u_contapagar_status =='PENDENTE') and $date1 > $date2 )  or ($qtdn>0)){
                        $rowind=mysqli_fetch_assoc($resn);
?>
                    <div class="row">
                        <div class="col-md-2"  style="margin-top: max-width: 102px;  color:red;">
                            Inadimpência:                      
                        </div>
                        <div class="col-md-2 nowrap">
                            <?if(empty($rowind['status'])){?>
                                <div class="inadimplencia pointer"  style="color:red;">
                                GERAR <i class="fa fa-thumbs-o-down vermelho pointer  btn-lg inadimplencia" title="Parcela Vencida- Gerar Inadimplência"></i>
                                </div>
                            <?}else{
                                if($rowind['status']=='INATIVO'){$corr='green'; $varst="CONCLUIDA";}else{$corr='red';$varst=$rowind['status'];}
                                if($rowind['status']== 'PENDENTE'){
                                    $varst= 'PENDENTE';
                                }elseif($rowind['status']== 'PENDENTE NEGATIVADO'){
                                    $varst='PENDENTE NEGATIVADO';
                                }elseif($rowind['status']== 'PENDENTE RECUPERACAO JUDICIAL'){
                                    $varst='PENDENTE RECUPERAÇÃO JUDICIAL';
                                }elseif($rowind['status']== 'INATIVO'){
                                    $varst='CONCLUIDA';
                                } 

                                ?>
                                <div class="inadimplencia pointer"  style="color:<?=$corr?>;">
                                    <?=$varst?><i class="fa fa-bars azul pointer  btn-lg inadimplencia" title="Parcela Vencida inadimplencia"></i>
                                </div>
                                
                                <?}?>
                        </div>
                    </div>
<?
                    }
                }

?>  
               
                <?
                if (empty($_1_u_contapagar_idformapagamento) and !empty($idformapagamento)) {
                    $_1_u_contapagar_idformapagamento = $idformapagamento;
                }

                if (empty($_1_u_contapagar_parcela)) {
                    $_1_u_contapagar_parcela = 1;
                } ?>
                <div class="row ">
                    <div class="col-md-2">
                        Parcelas:
                    </div>
                    <div class="col-md-4">
                        <?=$_1_u_contapagar_parcela ?> de <input name="_1_<?=$_acao?>_contapagar_parcela" <?=$readonly ?> <?=$campreadonly ?> type="hidden" value="<?=$_1_u_contapagar_parcela?>">
                        <select name="_1_<?=$_acao?>_contapagar_parcelas" id="status" <?=$disabled ?> style="max-width: 70px;" <?=$campdisabled ?>>
                            <?
                            for($isel = 1; $isel <= 60; $isel++)
                            {
                                if($isel == 1){
                                    $arrayParcelas[$isel] = $isel."x";
                                } else {
                                    $arrayParcelas[$isel] = $isel."x";
                                }
                            }
                            fillselect($arrayParcelas, $_1_u_contapagar_parcelas);
                            ?>
                        </select>
                    </div>
                </div>

                <?
                if (empty($_1_u_contapagar_intervalo)) {
                    $_1_u_contapagar_intervalo = 30;
                }
                ?>
                <div class="row ">
                    <div class="col-md-2">
                        Intervalo:
                    </div>
                    <div class="col-md-2" style="max-width: 120px;">
                        <input SIZE="2" <?=$readonly?> name="_1_<?=$_acao?>_contapagar_intervalo" type="text" style="max-width: 100px;" value="<?=$_1_u_contapagar_intervalo?>" <?=$readonly?> <?=$campreadonly?>>
                    </div>
                    <div class="col-md-2" style="margin-top: 5px; max-width: 102px;">
                        Tipo Intervalo:
                    </div>
                    <div class="col-md-2">
                        <select <?=$disabled ?> name="_1_<?=$_acao?>_contapagar_tipointervalo" style="max-width: 100px;" <?=$disabled ?>>
                            <?
                            fillselect(array('D' => 'Dias', 'M' => 'M&ecirc;s', 'Y' => 'Ano'), $_1_u_contapagar_tipointervalo);
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-2">
                        Programado:
                    </div>
                    <div class="col-md-4 nowrap">
                        <select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_progpagamento" style="max-width: 100px;" <?=$disabled ?>>
                            <?
                            fillselect(array('N' => 'N&atilde;o', 'S' => 'Sim'), $_1_u_contapagar_progpagamento);
                            ?>
                        </select>

                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-2">
                        Visualizar:
                    </div>
                    <div class="col-md-4">
                        <select <?=$disabled ?> name="_1_<?=$_acao?>_contapagar_visivel" style="max-width: 100px;" <?=$disabled ?>>
                            <?
                            fillselect(array('N' => 'N&atilde;o', 'S' => 'Sim'), $_1_u_contapagar_visivel);
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-2">
                        Status:
                        <input name="contapagar_status_ant" type="hidden" value="<?=$_1_u_contapagar_status?>">
                        <input name="_1_<?=$_acao?>_contapagar_status" type="hidden" value="<?=$_1_u_contapagar_status?>">
                    </div>
                    <div class="col-md-4">
                        <?
                        echo $_1_u_contapagar_status;
                        ?>
                    </div>
                </div>
                <? if($conciliacaoFinanceira) { ?>
                    <div class="row mt-3">
                        <div class="col-md-2">
                            Status conciliacao financeira
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <label for="" class="mr-2"><?= $conciliacaoFinanceira['status'] ?></label>
                            <a target="_blank" href="?_modulo=conciliacaofinanceira&_acao=u&idconciliacaofinanceira=<?= $conciliacaoFinanceira['idconciliacaofinanceira'] ?>&_idempresa=<?= $conciliacaoFinanceira['idempresa']?>">
                                <i class="fa fa-navicon"></i>
                            </a>
                        </div>
                    </div>
                <?}?>
                <? if (mysqli_num_rows($rere) > 0) { ?>
                    <div class="row ">
                        <div class="col-md-2">
                            Retorno Remessa:
                        </div>
                        <div class="col-md-3">
                            <a class="pointer" onclick="janelamodal('?_modulo=retornoremessa&_acao=u&idretornoremessa=<?=$rowre['idretornoremessa']?>')"> <?=$rowre['idretornoremessa']?></a>
                        </div>
                    </div>
                <? } ?>

            </div>
        </div>
    </div>
    <div class="col-md-4">
        <? if (!empty($_1_u_contapagar_idformapagamento)) {


            $sqlfc = "select  p.nome,f.*
                    from formapagamento f left join pessoa p on(p.idpessoa=f.idpessoa)
                    where f.idformapagamento=".$_1_u_contapagar_idformapagamento;
            $resfc = d::b()->query($sqlfc) or die("erro ao buscar informações da forma de pagamento sql=".$sqlfc);
            $rowfc = mysqli_fetch_assoc($resfc);

            if ($rowfc['formapagamento'] == 'C.CREDITO' or $_1_u_contapagar_tipoespecifico == 'IMPOSTO') {
        ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td align="right" class="nowrap">Cartão de Crédito:</td>
                                <td style="width:400px">
                                    <a class="pointer" onclick="janelamodal('?_modulo=formapagamento&_acao=u&idformapagamento=<?=$_1_u_contapagar_idformapagamento?>')">
                                        <?=$rowfc['descricao']?>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?
                                if ($rowfc['formapagamento'] == 'C.CREDITO') {
                                ?>
                                    <table>
                                        <tr>
                                            <td>Responsável:</td>
                                            <td>
                                                <?
                                                if (empty($rowfc['nome'])) {
                                                ?>
                                                    <font color='red'>Não configurado </font>
                                                <?
                                                } else {
                                                    echo ($rowfc['nome']);
                                                } ?>
                                            </td>
                                        </tr>
                                    </table>
                                <?
                                }
                                $sqlp = "SELECT c.contaitem,t.tipoprodserv,f.* FROM formapagamentopessoa f 
                            join contaitem c on(c.idcontaitem=f.idcontaitem)
                            join tipoprodserv t on(t.idtipoprodserv=f.idtipoprodserv)
                            WHERE idformapagamento =".$_1_u_contapagar_idformapagamento."";
                                $resp = d::b()->query($sqlp) or die("getformapagamentos: Erro: ".mysqli_error(d::b())."\n".$sqlp);
                                $nrowfp = mysqli_num_rows($resp);
                                ?>

                                <div class="panel panel-default">
                                    <div class="panel-heading" data-toggle="collapse" href="#novaprevisao">
                                        Previsão
                                    </div>
                                    <div style="padding: 10px;">
                                        <? if ($nrowfp >= 1) { ?>
                                            <div>
                                                <table class="table table-striped">
                                                    <tr>
                                                        <th>Categoria</th>
                                                        <th>Tipo</th>
                                                        <th>Previsão</th>
                                                    </tr>
                                                    <tbody>
                                                        <?
                                                        while ($rp = mysqli_fetch_assoc($resp)) {
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?=$rp['contaitem']?>
                                                                </td>
                                                                <td>
                                                                    <?=$rp['tipoprodserv']?>
                                                                </td>
                                                                <td>
                                                                    <?=$rp["previsao"] ?>
                                                                </td>
                                                            </tr>
                                                        <? } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <? } else { ?>
                                            <table>
                                                <tr>
                                                    <th>
                                                        Não existem Previsões Cadastradas para esta Fatura Automática.
                                                    </th>
                                                </tr>
                                            </table>
                                        <? } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?
            } elseif ($rowfc['formapagamento'] == 'BOLETO' and $rowfc['agruppessoa'] == 'Y') {
                $sqlp = "select  c.contaitem,t.tipoprodserv,p.nome,f.formapagamento,f.agruppessoa,fp.*
                from formapagamento f join formapagamentopessoa fp on(fp.idformapagamento=f.idformapagamento and fp.idpessoa=".$_1_u_contapagar_idpessoa.")
                 join pessoa p on(p.idpessoa=fp.idpessoa )
                 join contaitem c on(c.idcontaitem=fp.idcontaitem)
                 join tipoprodserv t on(t.idtipoprodserv=fp.idtipoprodserv)
                where f.idformapagamento=".$_1_u_contapagar_idformapagamento;
                $resp = d::b()->query($sqlp) or die("getformapagamentos: Erro: ".mysqli_error(d::b())."\n".$sqlp);
                $nrowfp = mysqli_num_rows($resp);
                $rp = mysqli_fetch_assoc($resp);
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td align="right" class="nowrap">Boleto Agrupado:</td>
                                <td style="width:400px">
                                    <a class="pointer" onclick="janelamodal('?_modulo=formapagamento&_acao=u&idformapagamento=<?=$_1_u_contapagar_idformapagamento?>')">
                                        <?=$rowfc['descricao']?>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table>
                                    <tr>
                                        <td>Fornecedor:</td>
                                        <td>
                                            <?
                                            if (empty($rp['nome'])) {
                                            ?>
                                                <font color='red'>Não configurado </font>
                                            <?
                                            } else {
                                                echo ($rp['nome']);
                                            } ?>
                                        </td>
                                    </tr>
                                </table>
                                <div class="panel panel-default">
                                    <div class="panel-heading" data-toggle="collapse" href="#novaprevisao">
                                        Previsão
                                    </div>
                                    <div style="padding: 10px;">
                                        <? if ($nrowfp >= 1) { ?>
                                            <div>
                                                <table class="table table-striped">
                                                    <tr>
                                                        <th>Categoria</th>
                                                        <th>Tipo</th>
                                                        <th>Previsão</th>
                                                    </tr>
                                                    <tbody>

                                                        <tr>
                                                            <td>
                                                                <?=$rp['contaitem']?>
                                                            </td>
                                                            <td>
                                                                <?=$rp['tipoprodserv']?>
                                                            </td>
                                                            <td>
                                                                <?=$rp["previsao"] ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <? } else { ?>
                                            <table>
                                                <tr>
                                                    <th>
                                                        Não existem Previsões Cadastradas para esta Fatura Automática.
                                                    </th>
                                                </tr>
                                            </table>
                                        <? } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?

            }
        }        ?>
    </div>

</div>
<div class="row">
    <div class="col-md-12">
        <?
        if (!empty($_1_u_contapagar_idformapagamento)) {


            if ($rowag['agrupado'] == "Y" or $_1_u_contapagar_tipoespecifico == 'AGRUPAMENTO' or $_1_u_contapagar_tipoespecifico == 'REPRESENTACAO') {

                //if($_1_u_contapagar_tipoespecifico=='REPRESENTACAO'){
                $sqlCountAjuste = "SELECT 1 FROM contapagaritem WHERE idcontapagar = $_1_u_contapagar_idcontapagar AND ajuste = 'Y' AND status <> 'INATIVO';";
                $resCountAjuste = d::b()->query($sqlCountAjuste) or die("Não foi possível buscar Quantidade Ajuste \n".mysqli_error(d::b())."\n".$sqlf);
                $rowCountAjuste = mysql_num_rows($resCountAjuste);

                $sqlp = "SELECT *  FROM (SELECT 'sv' AS tiponf,
                                                c.ajuste,
                                                f.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                cli.nome,
                                                p.nome AS pessoa,
                                                n.emissao AS dtemissao,
                                                f.descricao AS formapgto,
                                                n.nnfe,
                                                n.idnotafiscal AS idnf,
                                                n.idempresa,
                                                'idnotafiscal' AS par,
                                                'nfs' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                cp.datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                pl.idplantel,
                                                pl.plantel,
                                                IF(c.idobjetoorigem IS NULL, cp.idobjeto, c.idobjetoorigem) AS idobjeto,
                                                IF(c.tipoobjetoorigem IS NULL, cp.tipoobjeto, c.tipoobjetoorigem) AS tipoobjeto
                                           FROM contapagaritem c JOIN pessoa p ON c.idpessoa = p.idpessoa AND c.tipoobjetoorigem = 'contapagar'
                                           JOIN contapagar cp ON cp.idcontapagar = c.idobjetoorigem AND cp.tipoobjeto = 'notafiscal'
                                           JOIN notafiscal n ON  n.idnotafiscal = cp.idobjeto
                                           JOIN pessoa cli On cli.idpessoa = n.idpessoa
                                      LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                      LEFT JOIN plantelobjeto po ON (po.idobjeto = cli.idpessoa AND po.tipoobjeto = 'pessoa')
                                      LEFT JOIN plantel pl ON (pl.idplantel = po.idplantel)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar'
                                            AND c.status != 'INATIVO'                                            
                                    UNION 
                                         SELECT n.tiponf,
                                                c.ajuste,
                                                f.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                cli.nome,
                                                p.nome AS pessoa,
                                                n.dtemissao,
                                                f.descricao AS formapgto,
                                                n.nnfe,
                                                n.idnf,
                                                n.idempresa,
                                                'idnf' AS par,
                                                'pedido' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                cp.datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                pl.idplantel,
                                                pl.plantel,
                                                IF(c.idobjetoorigem IS NULL, cp.idobjeto, c.idobjetoorigem) AS idobjeto,
                                                IF(c.tipoobjetoorigem IS NULL, cp.tipoobjeto, c.tipoobjetoorigem) AS tipoobjeto
                                           FROM contapagaritem c JOIN pessoa p ON c.idpessoa = p.idpessoa AND c.tipoobjetoorigem = 'contapagar'
                                           JOIN contapagar cp ON cp.idcontapagar = c.idobjetoorigem AND cp.tipoobjeto = 'nf'
                                           JOIN nf n ON n.idnf = cp.idobjeto 
                                           JOIN pessoa cli ON cli.idpessoa = n.idpessoa
                                      LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                      LEFT JOIN plantelobjeto po ON (po.idobjeto = cli.idpessoa AND po.tipoobjeto = 'pessoa')
                                      LEFT JOIN plantel pl ON (pl.idplantel = po.idplantel)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar'
                                            AND c.status != 'INATIVO'
                                    UNION 
                                         SELECT n.tiponf,
                                                c.ajuste,
                                                f.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                p.nome,
                                                ps.nome AS pessoa,
                                                n.dtemissao,
                                                f.descricao AS formapgto,
                                                n.nnfe,
                                                n.idnf,
                                                n.idempresa,
                                                'idnf' AS par,
                                                'nfentrada' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                cp.datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                pl.idplantel,
                                                pl.plantel,
                                                IF(c.idobjetoorigem IS NULL, cp.idobjeto, c.idobjetoorigem) AS idobjeto,
                                                IF(c.tipoobjetoorigem IS NULL, cp.tipoobjeto, c.tipoobjetoorigem) AS tipoobjeto
                                           FROM pessoa p JOIN contapagaritem c ON c.tipoobjetoorigem = 'nf'
                                           JOIN nf n ON n.idnf = c.idobjetoorigem AND n.idpessoa = p.idpessoa  
                                           JOIN pessoa ps ON (c.idpessoa = ps.idpessoa)
                                      LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                      LEFT JOIN contapagar cp ON (cp.idcontapagar = c.idcontapagar)
                                      LEFT JOIN plantelobjeto po ON (po.idobjeto = n.idpessoa AND po.tipoobjeto = 'pessoa')
                                      LEFT JOIN plantel pl ON (pl.idplantel = po.idplantel)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar'
                                            AND c.status != 'INATIVO'
                                    UNION 
                                         SELECT 'sv' AS tiponf,
                                                c.ajuste,
                                                f.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                p.nome,
                                                ps.nome AS pessoa,
                                                n.emissao AS dtemissao,
                                                f.descricao AS formapgto,
                                                n.nnfe,
                                                n.idnotafiscal AS idnf,
                                                n.idempresa,
                                                'idnotafiscal' AS par,
                                                'nfs' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                cp.datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                pl.idplantel,
                                                pl.plantel,
                                                IF(c.idobjetoorigem IS NULL, cp.idobjeto, c.idobjetoorigem) AS idobjeto,
                                                IF(c.tipoobjetoorigem IS NULL, cp.tipoobjeto, c.tipoobjetoorigem) AS tipoobjeto
                                           FROM pessoa p JOIN contapagaritem c ON c.tipoobjetoorigem = 'notafiscal'
                                           JOIN notafiscal n ON n.idpessoa = p.idpessoa AND n.idnotafiscal = c.idobjetoorigem
                                           JOIN pessoa ps ON (c.idpessoa = ps.idpessoa)
                                      LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                      LEFT JOIN contapagar cp ON (cp.idcontapagar = c.idcontapagar)
                                      LEFT JOIN plantelobjeto po ON (po.idobjeto = n.idpessoa AND po.tipoobjeto = 'pessoa')
                                      LEFT JOIN plantel pl ON (pl.idplantel = po.idplantel)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar'
                                            AND c.status != 'INATIVO'
                                     UNION 
                                         SELECT 'ajuste' AS tiponf, 
                                                c.ajuste,
                                                c.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                '' AS nome,
                                                '' AS pessoa,
                                                '' AS dtemissao,
                                                f.descricao AS formapgto,
                                                '' AS nnfe,
                                                '' AS idnf,
                                                c.idempresa,
                                                '' AS par,
                                                '' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                c.datapagto AS datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                '' AS idplantel,
                                                '' AS plantel,
                                                c.idobjetoorigem AS idobjeto,
                                                c.tipoobjetoorigem AS tipoobjeto
                                           FROM contapagaritem c LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar' 
                                            AND ajuste = 'Y'
                                            AND idobjetoorigem IS NULL 
                                            AND c.status != 'INATIVO'
                                    UNION 
                                         SELECT '' AS tiponf,
                                                c.ajuste,
                                                f.idformapagamento,
                                                c.idcontapagaritem,
                                                c.obs,
                                                c.idcontapagar,
                                                c.status,
                                                c.datapagto,
                                                c.valor,
                                                p.nome,
                                                p.nome AS pessoa,
                                                '' AS dtemissao,
                                                f.descricao AS formapgto,
                                                '' AS nnfe,
                                                '' AS idnf,
                                                '' AS idempresa,
                                                'idnf' AS par,
                                                'pedido' AS modulo,
                                                c.parcela,
                                                c.parcelas,
                                                cp.datareceb,
                                                c.criadopor,
                                                c.criadoem,
                                                c.alteradopor,
                                                c.alteradoem,
                                                '' AS idplantel,
                                                '' AS plantel,
                                                IF(c.idobjetoorigem IS NULL, cp.idobjeto, c.idobjetoorigem) AS idobjeto,
                                                IF(c.tipoobjetoorigem IS NULL, cp.tipoobjeto, c.tipoobjetoorigem) AS tipoobjeto
                                           FROM contapagaritem c JOIN pessoa p ON c.idpessoa = p.idpessoa
                                      LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                                      LEFT JOIN contapagar cp ON (cp.idcontapagar = c.idcontapagar)
                                          WHERE c.idcontapagar = '$_1_u_contapagar_idcontapagar'
                                            AND c.tipoobjetoorigem IS NULL
                                            AND c.idobjetoorigem IS NULL
                                            AND c.status != 'INATIVO') AS u
                            GROUP BY idcontapagaritem
                            ORDER BY datareceb , dtemissao";

                $resp = d::b()->query($sqlp) or die("Erro ao buscar outras parcelas de comissao sql=".$sqlp);
                $qtdp = mysqli_num_rows($resp);
                $i = 1;
                if ($qtdp > 0) {
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <table>
                                <tr>
                                    <td>
                                        <input placeholder="Parcelas Agrupadas" type="text" class="size20" onkeyup="findcontaitem(this)"> <i class="fa fa-search azul"></i>
                                    </td>
                                    <td>
                                        <a title="Imprimir Contas" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/impcontapagaritem.php?_acao=u&idcontapagar=<?=$_1_u_contapagar_idcontapagar?>')"></a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped planilha">
                                <tr class="header">
                                    <th>Danfe - NNFe</th>
                                    <th>Emiss&atilde;o</th>
                                    <th>Data Receb.</th>
                                    <th>Parcela</th>
                                    <th>Fatura Automática</th>
                                    <th>Nome</th>
                                    <th>Pessoa</th>
                                    <th>Divisão</th>
                                    <th>Obs</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <? if ($_1_u_contapagar_status == 'ABERTO' or $_1_u_contapagar_status == 'INICIO' or  $_1_u_contapagar_status == 'FECHADO') { ?>
                                        <th>Conta Vinc.</th>
                                    <? } ?>
                                    <th></th>

                                </tr>
                                <?
                                $valor = 0;
                                while ($rowp = mysqli_fetch_assoc($resp)) {
                                    $_idempresa = '';

                                    if($rowp["tiponf"]=='V'){ $vtiponf = "Venda";  $link="pedido";}
                                    if($rowp["tiponf"]=='C'){ $vtiponf = "Compra"; $link="nfentrada";}	
                                    if($rowp["tiponf"]=='O'){ $vtiponf = "Compra"; $link="nfentrada";}		
                                    if($rowp["tiponf"]=='S'){ $vtiponf = "Servi&ccedil;o";  $link="nfentrada";}
                                    if($rowp["tiponf"]=='T'){ $vtiponf = "Cte";  $link="nfcte";}
                                    if($rowp["tiponf"]=='E'){ $vtiponf = "Consession&aacute;ria"; $link="nfentrada";}
                                    if($rowp["tiponf"]=='M'){ $vtiponf = "Manual/Cupom"; $link="nfentrada";}
                                    if($rowp["tiponf"]=='B'){ $vtiponf = "Recibo"; $link="nfentrada";}
                                    if($rowp["tiponf"]=='R'){ $vtiponf = "PJ"; $link="comprasrh";}
                                    if($rowp["tiponf"]=='F'){ $vtiponf = "Fatura"; $link="nfentrada"; }
                                    if($rowp["tiponf"]=='D'){ $vtiponf = "Sócios"; $link="comprassocios"; }
                                    if($rowp["tiponf"]=='sv'){ $vtiponf = "Serviço"; $link="nfs"; }
                                    if($rowp["tiponf"]==''){ $vtiponf = ""; $link=""; }


                                    //Marcelo retornou o modulo para o nome de nfentrada por causa dos links que tem no sistema - Lidiane (08/06/2020)
                                    //if ($_SESSION["SESSAO"]["IDEMPRESA"] != 1 and $rowp['modulo'] == 'nfentrada'){
                                    //	$rowp['modulo'] = 'comprasunificadas';
                                    //}
                                    $i = $i + 1;
                                    $valor = $valor + $rowp['valor'];
                                    if (empty($rowp['nnfe'])) {
                                        $nnfe = $rowp['idnf'];
                                    } else {
                                        $nnfe = $rowp['nnfe'];
                                    }
                                    $sqlf = "SELECT agrupnota, agrupado, formapagamento, tipoespecifico    
                                               FROM formapagamento 
                                              WHERE idformapagamento = $_1_u_contapagar_idformapagamento";
                                    $resf = d::b()->query($sqlf) or die("erro 2 ao buscar forma de pagamento \n".mysqli_error(d::b())."\n".$sqlf);
                                    $rowf = mysqli_fetch_assoc($resf);
                                    ?>
                                    <tr class="respreto">
                                        <td class="col-md-1 nowrap">
                                            <?                                            
                                            if ($rowf['agrupado'] == 'Y' && $rowf['agrupnota'] == 'N' && ($rowf['tipoespecifico'] == "AGRUPAMENTO" || $rowf['tipoespecifico'] == "IMPOSTO" )&& $_1_u_contapagar_status != "QUITADO" && $rowf['formapagamento'] != "C.CREDITO" && !empty($rowp['idobjeto']) && $rowCountAjuste == 0) {
                                                ?>
                                                <a class="fa fa-plus-circle verde pointer hoverazul" idobjeto="<?=$rowp['idobjeto']?>" tipoobjeto="<?=$rowp['tipoobjeto']?>" title="Ajuste do valor da fatura" onclick="showModal(null, this)"></a>
                                                <?
                                            }
                                            
                                            if ($rowp['modulo'] != 'nfs') { ?>
                                                <a class="fa fa-print btn-lg fa-1x pointer hoverazul" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz3/func/printDANFE.php?idnotafiscal=<?=$rowp['idnf']?>')"></a>
                                                -
                                            <? } 
                                            
                                            if (!empty($rowp['idempresa'])) {
                                                $_idempresa = '&_idempresa='.$rowp['idempresa'];
                                            } ?>
                                            <a class="pointer" onclick="janelamodal('?_modulo=<?=$link?>&_acao=u&<?=$rowp['par']?>=<?=$rowp['idnf']?><?=$_idempresa?>');">
                                                <?=$nnfe ?>
                                            </a>
                                            &nbsp;
                                            <a class="fa fa-info-circle tip" title="Informações de Criação" data-toggle="popover" href="#<?=$rowp['idcontapagaritem']?>" data-trigger="hover"></a>
                                            <div id="modalpopover_<?=$rowp['idcontapagaritem']?>" class="modal-popover hidden">
                                                <table>
                                                    <tr>
                                                        <td nowrap><b>Criado por:</b></td>
                                                        <td><?=dmahms($rowp['criadopor'])?></td>

                                                    </tr>
                                                    <tr style="margin-top: 10px;">
                                                        <td nowrap><b>Criado em:</b> </td>
                                                        <td><?=dmahms($rowp['criadoem'])?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                        <td class="col-md-1"><?=dma($rowp['dtemissao'])?></td>
                                        <td class="col-md-1"><?=dma($rowp['datareceb'])?></td>
                                        <td class="col-md-1"><?=$rowp['parcela']?> de <?=$rowp['parcelas']?></td>
                                        <td class="col-md-1 nowrap">
                                            <? if ($_1_u_contapagar_status == 'ABERTO' or $_1_u_contapagar_status == 'INICIO') { ?>
                                                <select class="size15" name="contapagaritem_idformapagamento" onchange="atualizafpagto(this,<?=$rowp['idcontapagaritem']?>)">
                                                    <option value=""></option>
                                                    <? fillselect("select c.idformapagamento,c.descricao
                                                                    from formapagamento c
                                                                    where  c.status='ATIVO'
                                                                    ".getidempresa('c.idempresa', 'formapagamento')."
                                                                    and c.agrupado='Y'
                                                                    order by c.ord,c.descricao", $rowp['idformapagamento']); ?>
                                                </select>
                                            <? } else {
                                                echo $rowp['formapgto'];
                                            } ?>
                                        </td>

                                        <td class="col-md-2"><?=$rowp['nome']?></td>
                                        <td class="col-md-2"><?=$rowp['pessoa']?></td>
                                        <td class="col-md-2"><?=$rowp['plantel']?></td>
                                        <td class="col-md-2"><?=$rowp['obs']?></td>
                                        <td class="col-md-1"><?=$rowp['status']?></td>
                                        <td align="right" class="col-md-1"><?=number_format(tratanumero($rowp['valor']), 2, ',', '.');?></td>
                                        <? if ($_1_u_contapagar_status == 'ABERTO' or $_1_u_contapagar_status == 'INICIO' or  $_1_u_contapagar_status == 'FECHADO') { ?>
                                            <td>
                                                <?
                                                if ($rowag['agruppessoa'] == "Y") {
                                                    $sqls = "select idcontapagar, dma(datareceb) as dtreceb
                                                            from contapagar 
                                                            where tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO','IMPOSTO')
                                                            and status in ('ABERTO','INICIO','FECHADO')
                                                            and idformapagamento=".$_1_u_contapagar_idformapagamento."
                                                            and idempresa=".$_1_u_contapagar_idempresa."
                                                            and idpessoa=".$_1_u_contapagar_idpessoa." order by datareceb";
                                                } elseif ($rowag['agrupfpagamento'] == "Y") {
                                                    $sqls = "select idcontapagar, dma(datareceb) as dtreceb
                                                            from contapagar 
                                                            where tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO','IMPOSTO')
                                                            and status in ('ABERTO','INICIO','FECHADO')
                                                            and idempresa=".$_1_u_contapagar_idempresa."
                                                            and idformapagamento=".$_1_u_contapagar_idformapagamento."
                                                            order by datareceb";
                                                } else {
                                                    $sqls = "select idcontapagar, dma(datareceb) as dtreceb
                                                            from contapagar 
                                                            where idcontapagar=".$_1_u_contapagar_idcontapagar."
                                                            order by datareceb";
                                                }
                                                ?>
                                                <select class="size10" name="contapagaritem_idcontapagar" onchange="atualizacontaitem(this,<?=$rowp['idcontapagaritem']?>)">
                                                    <?
                                                    fillselect($sqls, $rowp['idcontapagar']);
                                                    ?>
                                                </select>
                                            </td>
                                        <? } ?>
                                        <td>
                                            <? if (($_1_u_contapagar_status != "QUITADO" and empty($rowp['obs'])) or ($_1_u_contapagar_status != "QUITADO" and  $rowp['ajuste'] == 'Y')) { ?>
                                                <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="deletaitem(<?=$rowp['idcontapagaritem']?>)" alt="Excluir!"></i>
                                            <? } ?>
                                        </td>
                                    </tr>
                                <?
                                } //while($rowp=mysqli_fetch_assoc($resp)){				
                                ?>
                                <tr>
                                    <td colspan="10"></td>
                                    <th id="vlrfim" value="<?=number_format(tratanumero($valor), 2, ',', '.');?>" align="right"><?=number_format(tratanumero($valor), 2, ',', '.');?></th>
                                    <td colspan="2"></td>
                                </tr>
                                <?                                
                                // não pode ter ajuste me cartão de credito
                                if ($rowf['agrupado'] == 'Y' and ($rowf['agrupnota'] == 'Y' or $rowf['tipoespecifico'] == "REPRESENTACAO" or $rowf['tipoespecifico'] == "IMPOSTO") and $_1_u_contapagar_status != "QUITADO" and $rowf['formapagamento'] != "C.CREDITO" and !empty($_1_u_contapagar_idobjeto)) {
                                    ?>
                                    <tr>
                                        <td>
                                            <a class="fa fa-plus-circle verde pointer hoverazul" title="Ajuste do valor da fatura" onclick="showModal()"></a>
                                        </td>
                                    </tr>
                                <? } ?>
                            </table>

                            <? if (!empty($_GET['idcontapagar'])) {
                                $agruppessoa = traduzid("formapagamento", "idformapagamento", "agruppessoa", $_1_u_contapagar_idformapagamento);
                                $agrupfpagamento = traduzid("formapagamento", "idformapagamento", "agrupfpagamento", $_1_u_contapagar_idformapagamento);

                                if ($agruppessoa == "Y") {
                                    $sqls = "select idcontapagar,valor, dma(datareceb) as dtreceb
                                            from contapagar 
                                            where tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO')
                                            and status in ('ABERTO','INICIO','FECHADO')
                                            and idformapagamento=".$_1_u_contapagar_idformapagamento."
                                            and idpessoa=".$_1_u_contapagar_idpessoa." order by datareceb";
                                } elseif ($agrupfpagamento == "Y") {
                                    $sqls = "select idcontapagar,valor, dma(datareceb) as dtreceb
                                            from contapagar 
                                            where tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO')
                                            and status in ('ABERTO','INICIO','FECHADO')
                                            and idformapagamento=".$_1_u_contapagar_idformapagamento."
                                            order by datareceb";
                                } else {
                                    $sqls = "select idcontapagar,valor, dma(datareceb) as dtreceb
                                            from contapagar 
                                            where idcontapagar=".$_1_u_contapagar_idcontapagar."
                                            order by datareceb";
                                }


                                $relink = d::b()->query($sqls) or die("erro ao buscar idcontapagar sql=".$sqlp);
                                $qtdp = mysqli_num_rows($relink);
                                $t = '';

                                if ($qtdp >= 1) {
                                    echo "Faturas:";
                                } 
                                while ($rl = mysqli_fetch_assoc($relink)) {
                                    if ($rl['idcontapagar'] == $_GET['idcontapagar']) {
                                        continue;
                                    } else { ?>
                                        <span>
                                            <?=$t ?> <a class="pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rl['idcontapagar']?>');"><?=$rl['dtreceb']?> (R$ <?=$rl['valor']?>) </a>
                                        </span>
                                        <? $t = ' - ';
                                    }
                                }
                            } ?>

                        </div>
                    </div>
                    <?
                } //if($qtdp>0){
            }
        } //if(!empty($_1_u_contapagar_idcontapagar)){
        ?>

    </div>
</div>

<div id="novaparcela" style="display: none;">
    <div class="row">
        <div class="col-md-12">
            <table style="margin-left: 26%;margin-bottom: 10px;">

                <tr>
                    <td style="width: 100px !important; "><input onchange="calculanv()" type="radio" id="checkcredito" name="_modalnovaparcelacontapagar_tipo_" value="C" <?if($dif<0){echo $ck = 'checked="yes"';}?> style="margin-right: 5px;"> Creditar </td>  
                    <td style="width: 100px !important; " ><input onchange="calculanv()"  type="radio" id="checkdebito" name="_modalnovaparcelacontapagar_tipo_" value="D" <?if($dif>0){echo $ck = 'checked="yes"';}?>  style="margin-right: 5px;"> Debitar</td> 
                </tr>
            </table>
            <table>
                <tr>
                    <td align="right">Justificativa:</td>
                    <td>
                        <select id="contapagarobs" name="contapagarobs">
                            <option></option>
                            <? fillselect(array('Arredondamento' => 'Arredondamento', 'Desconto' => 'Desconto', 'Devolução' => 'Devolução', 'Estorno' => 'Estorno', 'Multa/Juros' => 'Multa/Juros')); ?>
                        </select>
                    </td>
                    <td align="right">Valor:</td>
                    <td><input class="size6" type="text" id="valornovaparc" name="valornovaparc" value="" onkeyup="calculanv()"></td>
                    <td align="right">Valor Fatura:</td>
                    <td>
                        <label class="idbox" id="valoratual">
                            <?=number_format(tratanumero($valor), 2, ',', '.'); ?>
                        </label>
                        <span id="info_contapagar"></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?
if (!empty($_1_u_contapagar_idobjeto) and !empty($_1_u_contapagar_tipoobjeto)) {
    $sqlp = "select dma(datareceb) as dmadatareceb,datareceb,valor,idcontapagar,tipo,status,parcela,parcelas from contapagar where idcontapagar!=".$_1_u_contapagar_idcontapagar." and idobjeto = ".$_1_u_contapagar_idobjeto." and tipoobjeto ='".$_1_u_contapagar_tipoobjeto."'";
    $resp = d::b()->query($sqlp) or die("Erro ao buscar outras parcelas sql=".$sqlp);
    $qtdp = mysqli_num_rows($resp);

    if ($qtdp > 0) {
?>
        <div class="row ">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Parcelas</div>
                    <div class="panel-body">
                        <table class="table table-striped planilha">
                            <tr class="header">
                                <th>Id</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th colspan="2">Parcela</th>

                                <th>Tipo</th>
                                <th>Status</th>
                            </tr>
                            <?

                            while ($rowp = mysqli_fetch_assoc($resp)) {
                                $i = $i + 1;
                            ?>
                                <tr class="respreto">
                                    <td>
                                        <input name="_<?=$i?>_u_contapagar_idcontapagar" type="hidden" value="<?=$rowp['idcontapagar']?>">

                                        <a class="fa pointer hoverazul" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp["idcontapagar"]?>')"><?=$rowp['idcontapagar']?></a>
                                    </td>
                                    <td>
                                        <? if ($rowp['status'] == 'PENDENTE') { ?>
                                            <input name="_<?=$i?>_u_contapagar_valor" type="text" size="5" value="<?=$rowp['valor']?>">
                                        <? } else { ?>
                                            <?=$rowp['valor']?>
                                        <? } ?>
                                    </td>
                                    <td>
                                        <? if ($rowp['status'] == 'PENDENTE') { ?>
                                            <input name="_<?=$i?>_u_contapagar_datareceb" type="text" class="datad" size="8" value="<?=$rowp['dmadatareceb']?>">
                                        <? } else { ?>
                                            <?=$rowp['dmadatareceb']?>
                                        <? } ?>
                                    </td>
                                    <td><?=$rowp['parcela']?></td>
                                    <td><?=$rowp['parcelas']?></td>
                                    <td><?=$rowp['tipo']?></td>
                                    <td><?=$rowp['status']?></td>
                                </tr>
                            <?
                            }
                            $parcela = $_1_u_contapagar_parcelas + 1;
                            $parcelas = $_1_u_contapagar_parcelas + 1;
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?
    }
}
?>
<?
if (!empty($_1_u_contapagar_idcontapagar)) {
    $sql = "select i.idremessaitem,
		    i.idremessa,
		    i.idcontapagar,
		    i.status as remessa,
		    r.dataenvio,
		    r.status,
            a.boleto
		from remessaitem i,remessa r,agencia a
		where i.idremessa = r.idremessa 
        and a.idagencia=r.idagencia
		and i.idcontapagar =".$_1_u_contapagar_idcontapagar;
    $res = d::b()->query($sql) or die("Erro ao buscar remessa sql=".$sql);
    $qtd = mysqli_num_rows($res);
    if ($qtd > 0) {
?>
        <div class="row ">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Remessa/Boleto</div>
                    <div class="panel-body">
                        <table class="table table-striped planilha">
                            <tr>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Remessa Item</th>
                                <th>Boleto</th>
                            </tr>
                            <?
                            while ($row = mysqli_fetch_assoc($res)) {
                                $i = $i + 1;
                                ?>
                                <tr>
                                    <td>
                                        <a class="pointer hoverazul" title="Parcela" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')">
                                            <?=$row['idremessa']?>
                                        </a>
                                    </td>
                                    <td><?=$row['status']?></td>
                                    <td>
                                        <input name="_<?=$i?>_u_remessaitem_idremessaitem" type="hidden" value="<?=$row['idremessaitem']?>">
                                        <select name="_<?=$i?>_u_remessaitem_status">
                                            <?
                                            fillselect(array('P' => 'Pendente', 'E' => 'Erro', 'C' =>'Concluido', 'A' => 'Alterado'), $row['remessa']);
                                            ?>
                                        </select>
                                    </td>
                                    <TD>
                                        <a class="fa fa-wpforms pointer hoverazul btn-lg pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$row['boleto']?>.php?idcontapagar=<?=$row['idcontapagar']?>')"></a>
                                    </td>
                                </tr>
                            <?
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?
    }

}
?>

<?
if (!empty($_1_u_contapagar_idcontapagar)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_contapagar_idcontapagar; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "contapagar"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>

<script>
    jCli = <?=$jCli ?>; // autocomplete cliente
    contapagar_status = '<?=$_1_u_contapagar_status?>';

    //mapear autocomplete de clientes
    jCli = jQuery.map(jCli, function(o, id) {
        return {
            "label": o.nome,
            value: id + "",
            "tipo": o.tipo
        }
    });

    if ($("[name=contapagar_idtipopessoa]").val() == 12) {
        document.getElementById("cbSalvar").style.display = "none";
    }
    //autocomplete de clientes
    $("[name*=_contapagar_idpessoa]").autocomplete({
        source: jCli,
        delay: 0,
        select: function(event, ui) {
            preenchecontaitem(ui.item.value);
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });
    // FIM autocomplete cliente

    function geraparcela(datapagto, datareceb, intervalo, parcela, parcelas) {

        var str2 = "";
        var str3 = "";
        if ($("[name=_1_u_contapagar_idcontadesc]").val() != null && $("[name=_1_u_contapagar_idcontadesc]").val() !== 'undefined') {
            str2 = "_x_i_contapagar_idcontadesc=" + $("[name=_1_u_contapagar_idcontadesc]").val();
        }
        if ($("[name=_1_u_contapagar_idcontaitem]").val() != null && $("[name=_1_u_contapagar_idcontaitem]").val() !== 'undefined') {
            str3 = "_x_i_contapagar_idcontaitem=" + $("[name=_1_u_contapagar_idcontaitem]").val();
        }
        if ($("#idpessoa").attr('cbvalue') != null && $("#idpessoa").attr('cbvalue') !== 'undefined') {
            str4 = "&_x_i_contapagar_idpessoa=" + $("#idpessoa").attr('cbvalue');
        }

        var str1 = "&_x_i_contapagar_idformapagamento=" + $("[name=_1_u_contapagar_idformapagamento]").val() +
            "&_x_i_contapagar_tipoobjeto=" + $("[name=_1_u_contapagar_tipoobjeto]").val() +
            "&_x_i_contapagar_idobjeto=" + $("[name=_1_u_contapagar_idobjeto]").val() +
            "" + str4 + "&_x_i_contapagar_parcela=" + parcela +
            "&_x_i_contapagar_parcelas=" + parcelas +
            "&_x_i_contapagar_valor=" + $("[name=contapagarvalor]").val() +
            "&_x_i_contapagar_intervalo=" + intervalo +
            "&_x_i_contapagar_datapagto=" + datapagto +
            "&_x_i_contapagar_datareceb=" + datareceb +
            "&_x_i_contapagar_status=PENDENTE&_x_i_contapagar_formapagto=" + $("[name=_1_u_contapagar_formapagto]").val() +
            "&_x_i_contapagar_tipo=" + $("[name=_1_u_contapagar_tipo]").val();


        var str = str1.concat(str2).concat(str3);

        CB.post({
            objetos: str
        });
    }
    $().ready(function() {
        $("#formapagto").change(function() {
            if ($("#formapagto").val() == "C.CREDITO") {
                $("#lbcartao").show();
                $("#cartao").show();
            } else {
                $("#lbcartao").hide();
                $("#cartao").hide();
            }
        });
        $("#status_conta").change(function() {
            if ($("#statusant").val() != "") {
                if (!confirm("Deseja realmente alterar o status da Parcela?")) {
                    $('#status_conta').val($("#statusant").val());
                } else {
                    CB.post();
                }
            }
        });



    });
    
    function preenchecontaitem(inidpessoa) {
        vIdPessoa = $(":input[name=_1_" + CB.acao + "_contapagar_idpessoa]").cbval();

        if (vIdPessoa) {
            $("#idcontaitem").html("<option value=''>Procurando....</option>");
            //alert($("#idpessoa").val());	
            $.ajax({
                type: "get",
                url: "ajax/dropdesc.php?idpessoa=" + vIdPessoa,
                success: function(data) {
                    $("#idcontaitem").html(data);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax

        } else {
            console.warn("js: preencheendereco: Erro: idIdpessoa n&atilde;o informado;")
        }
    } //function preencheendereco(){

    function altcheckR(vthis, vop, inidcontapagar) {

        vthis.disabled = true;
        if (vop == 'D') {
            var str = "_x_u_contapagaritem_idcontapagaritem=" + $(vthis).attr('idcontapagaritem') + "&_x_u_contapagaritem_idcontapagar=0&_x_u_contapagaritem_status=ABERTO";
        } else {
            var str = "_x_u_contapagaritem_idcontapagaritem=" + $(vthis).attr('idcontapagaritem') + "&_x_u_contapagaritem_idcontapagar=" + inidcontapagar + "&_x_u_contapagaritem_status=PENDENTE";
        }

        CB.post({
            objetos: str,
            refresh: false
        });
    }

    function altcheck(vthis, vop, inidcontapagar) {


        if (vop == 'D') {
            var str = "_x_u_contapagaritem_idcontapagaritem=" + $(vthis).attr('idcontapagaritem') + "&_x_u_contapagaritem_idcontapagar=0&_x_u_contapagaritem_status=ABERTO";
        } else {
            var str = "_x_u_contapagaritem_idcontapagaritem=" + $(vthis).attr('idcontapagaritem') + "&_x_u_contapagaritem_idcontapagar=" + inidcontapagar + "&_x_u_contapagaritem_status=PENDENTE";
        }

        CB.post({
            objetos: str
        });
    }

    function findcontaitem(vthis) {


        var insstr = $(vthis).val();
        if (insstr != '' && insstr.length > 0) {
            vtr = $(vthis).parent().parent().children().children().children().children();
            var instrucase = insstr.toUpperCase(); //transform parameter to upper case
            for (var i = 0; i < vtr.length; i++) {
                var contentucase = vtr[i].textContent.toUpperCase(); //transform UL textcontent to upper case
                //console.log(contentucase.indexOf(instrucase));
                if (contentucase.indexOf(instrucase) >= 0) {
                    vtr[i].style.display = "table";
                    vtr[i].style.width = "100%";
                } else {
                    vtr[i].style.display = "none";
                }
            }
        } else {
            vtr = $(vthis).parent().parent().children().children().children().children();
            for (var i = 0; i < vtr.length; i++) {

                vtr[i].style.display = "table";
                vtr[i].style.width = "100%";
            }

        }
    }

    if ($("[name=_1_u_contapagar_idcontapagar]").attr('value')) {
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_contapagar_idcontapagar]").attr('value'),
            tipoObjeto: 'contapagar',
            idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>'
        });

        ST.on("posVerificarInicio", function(data, texto, jqXHR) {
            if (jqXHR.getResponseHeader("X-CB-FORMATO") == '999_i' && jqXHR.getResponseHeader("X-CB-RESPOSTA") && $("[name=contapagar_status_ant]").val() == "") {
                $("[name=contapagar_status_ant]").val(jqXHR.getResponseHeader("X-CB-RESPOSTA"))
            }
        })
    }
    <?
    if (!empty($_1_u_contapagar_idcontapagar)) {
        $sqla = "select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_contapagar_idcontapagar." 
	    and tipoobjeto in ('contapagar','contapagaritem')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
        $resa = d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
        $qtda = mysqli_num_rows($resa);
        if ($qtda > 0) {
            $rowa = mysqli_fetch_assoc($resa);

    ?>
            botaoAssinar(<?=$rowa['idcarrimbo']?>);
    <?  } // if($qtda>0){
    } //if(!empty($_1_u_sgdoc_idsgdoc)){
    ?>

    function botaoAssinar(inidcarrimbo) {
        $bteditar = $("#btAssina");
        if ($bteditar.length == 0) {
            CB.novoBotaoUsuario({
                id: "btAssina",
                rotulo: "Assinar",
                class: "verde",
                icone: "fa fa-pencil",
                onclick: function() {
                    CB.post({
                        objetos: "_x_u_carrimbo_idcarrimbo=" + inidcarrimbo + "&_x_u_carrimbo_status=ASSINADO",
                        parcial: true,
                        posPost: function(data, textStatus, jqXHR) {
                            $('#btAssina').hide();
                            $('#btRejeita').hide();
                        }
                    });
                }
            });
            CB.novoBotaoUsuario({
                id: "btRejeita",
                rotulo: "Rejeitar",
                class: "vermelho",
                icone: "fa fa-ban",
                onclick: function() {
                    CB.post({
                        objetos: "_x_u_carrimbo_idcarrimbo=" + inidcarrimbo + "&_x_u_carrimbo_status=REJEITADO",
                        parcial: true,
                        posPost: function(data, textStatus, jqXHR) {
                            $('#btAssina').hide();
                            $('#btRejeita').hide();
                        }
                    });
                }
            });
        }
    }

    function atualizafpagto(vthis, inidcontapagaritem) {
        if (confirm('Deseja alterar item de conta?')) {
            CB.post({
                objetos: `_x_u_contapagaritem_idcontapagaritem=` + inidcontapagaritem + `&_x_u_contapagaritem_idformapagamento=` + $(vthis).val(),
                parcial: true
            });
        }
    }

    function atualizacontaitem(vthis, inidcontapagaritem) {
        const idContaPagar = $(vthis).val();

        if (confirm('Deseja alterar item de conta?')) {
            CB.posPost = () => {
                $.ajax({
                    url: '/../ajax/conciliacaofinanceira.php',
                    type: 'POST',
                    data: {
                        action: 'adicionarLancamentoPorIdContaPagarItem',
                        params: [inidcontapagaritem, idContaPagar]
                    },
                    dataType: 'json',
                    success: res => {
                        if(res.error) console.log(res.error);
                    }
                })
            }

            CB.post({
                objetos: `_x_u_contapagaritem_idcontapagaritem=` + inidcontapagaritem + `&_x_u_contapagaritem_idcontapagar=` + idContaPagar,
                parcial: true
            },);
        }
    }

    function gerarnota(inidcontapagar, inidpessoa) {
        CB.post({
            objetos: `_x_i_nf_idobjetosolipor=` + inidcontapagar + `&_x_i_nf_idformapagamento=` + $("[name=_1_u_contapagar_idformapagamento]").val() + `&_x_i_nf_tiponf=S&_x_i_nf_tipoobjetosolipor=contapagar&_x_i_nf_idpessoa=` + inidpessoa + `&_x_i_nf_total=` + $('#basicCalculator').val() + `&_x_i_nf_subtotal=` + $('#basicCalculator').val(),
            parcial: true
        });
    }

    function mostraInputFormapagamento(vthis) {
        $(vthis).hide();
        $(vthis).siblings("label").hide()
        $(vthis).siblings("select").css("display", "block");
    }

    function shcontapagar(inidcontapagar) {
        janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=' + inidcontapagar + '');
    }


    function calculanv(dif) {
        valCD = $("[name='_modalnovaparcelacontapagar_tipo_']:checked").val() || "D";
        valor1 = $("#vlrfim").attr('value');
        valor2 = $("[name=_modalnovaparcelacontapagar_valor_]").val();
        if (dif) {
            var numero2 = dif;
        } else if (valor2) {
            var numero2 = parseFloat(valor2.replace(',', '.'));
        } else {
            var numero2 = '';
        }
        var valor1 = valor1.replace('.', '');
        var valor1 = valor1.replace(',', '.');
        var numero1 = parseFloat(valor1);

        if (numero2) {


            if (valCD == 'D') {
                valor = numero1 - (numero2);

            } else {
                valor = numero1 + (numero2);
            }
            $('#novo_valoratual').html(valor.toFixed(2));
        } else {

            $('#novo_valoratual').html(numero1.toFixed(2))
        }

    }

    function showModal(dif, contapapar) {

        var strCabecalho = "<strong>Ajuste do valor da fatura<button id='cbSalvar' type='button'  style='margin-left:250px' class='btn btn-danger btn-xs' onclick='geracontapagar();'><i class='fa fa-circle'></i>Salvar</button></strong> ";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#novaparcela").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#contapagarobs").attr("name", "_modalnovaparcelacontapagar_obs_");
        objfrm.find("#valornovaparc").attr("name", "_modalnovaparcelacontapagar_valor_");
        objfrm.find("#valoratual").attr("id", "novo_valoratual");

        if (dif) {
            objfrm.find('[name="_modalnovaparcelacontapagar_valor_"]').val(dif);
        }

        if(contapapar){
            $("#info_contapagar").html((contapapar));
        }

        CB.modal({
            corpo: [objfrm],
            titulo: strCabecalho
        })
        calculanv(dif);
    }


    function geracontapagar() {
        if($('#info_contapagar a').attr('tipoobjeto')){
            var idobjetoorigem = $('#info_contapagar a').attr('idobjeto');
            var tipoobjetoorigem = $('#info_contapagar a').attr('tipoobjeto');
        } else {
            var idobjetoorigem = $("[name=_1_u_contapagar_idobjeto]").val();
            var tipoobjetoorigem = $("[name=_1_u_contapagar_tipoobjeto]").val();
        }

        let valTipo = $("[name=_1_u_contapagar_tipo]").val();
        let idpessoa = $("[name=_1_u_contapagar_idpessoa]").attr('cbvalue');

        let valCD = $("[name='_modalnovaparcelacontapagar_tipo_']:checked").val() || "D";
        valor = $("[name=_modalnovaparcelacontapagar_valor_]").val();
        obs = $("[name=_modalnovaparcelacontapagar_obs_]").val();
        if (valor <= 0 || obs <= 0) {
            alert("É necessário preencher o valor e a justificativa.");
        } else {

            if (valCD == 'D') {
                valor = $("[name=_modalnovaparcelacontapagar_valor_]").val();
                valor = "-" + valor;
            }

            var str = "_x9_i_contapagaritem_idformapagamento=" + $("[name=_1_u_contapagar_idformapagamento]").val() +
                "&_x9_i_contapagaritem_idagencia=" + $("[name=_1_u_contapagar_idagencia]").val() +
                "&_x9_i_contapagaritem_idcontapagar=" + $("[name=_1_u_contapagar_idcontapagar]").val() +
                "&_x9_i_contapagaritem_idobjetoorigem=" + idobjetoorigem +
                "&_x9_i_contapagaritem_tipoobjetoorigem=" + tipoobjetoorigem +
                "&_x9_i_contapagaritem_ajuste=Y" +
                "&_x9_i_contapagaritem_obs=" + $("[name=_modalnovaparcelacontapagar_obs_]").val() +
                "&_x9_i_contapagaritem_status=" + $("[name=_1_u_contapagar_status]").val() +
                "&_x9_i_contapagaritem_parcela=1" +
                "&_x9_i_contapagaritem_parcelas=1" + 
                "&_x9_i_contapagaritem_valor=" + valor +
                "&_x9_i_contapagaritem_datapagto=" + $("[name=_1_u_contapagar_datareceb]").val() +
                "&_x9_i_contapagaritem_tipo=" + valTipo + 
                "&_x9_i_contapagaritem_visivel=S&_x9_i_contapagaritem_idpessoa=" + idpessoa;

            CB.post({
                objetos: str,
                parcial: true,
                posPost: function(resp, status, ajax) {
                    if (status = "success") {
                        $("#cbModalCorpo").html("");
                        $('#cbModal').modal('hide');
                    } else {
                        alert(resp);
                    }
                }
            });
        }
    }

    function deletaitem(ididcontapagaritem) {
        CB.post({
            objetos: "_x_u_contapagaritem_idcontapagaritem=" + ididcontapagaritem + "&_x_u_contapagaritem_status=INATIVO",
            parcial: true
        });
    }

    $(function() {
        $('[data-toggle="popover"]').popover({
            html: true,
            content: function() {
                let ModalPopoverId = $(this).attr("href").replaceAll("#", "")
                return $("#modalpopover_" + ModalPopoverId).html();
            }
        });
    });

    CB.on('prePost', function(){
        dataCadastro = $('#vencimento_2').val().split('/');
        datareceb = new Date(dataCadastro[2], dataCadastro[1] - 1, dataCadastro[0], 23,59,59);
        hoje = new Date();
        const status = ['ABERTO', 'FECHADO', 'PENDENTE'];
        if((datareceb < hoje ? true : false) && status.indexOf(contapagar_status) >= 0){
            if(!confirm('A data de Recebimento está anterior a data atual\n\nDeseja continuar?')){
                
                return {objetos:{},msgSalvo:false,refresh:false}
            }
        }
    });

    $(".inadimplencia").click(function() {
		var idcontapagar = $("[name=_1_u_contapagar_idcontapagar]").val();
		CB.modal({
			url: "?_modulo=inadimplencia&_acao=u&idcontapagar=" + idcontapagar,
			header: "Inadimplência",
            classe: 'cinquenta',
            aoFechar: function() {
                location.reload();
            }
		});
	});

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>

<?
require_once '../inc/php/readonly.php';
?>