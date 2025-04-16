<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/ao_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lote";
$pagvalcampos = array(
    "idlote" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM lote WHERE idlote = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td align="right"><strong>Partida</strong></td>
                        <td><label class="alert-warning"><?= $_1_u_lote_partida ?>/<?= $_1_u_lote_exercicio ?></label>
                            <input id="idlote" name="_1_<?= $_acao ?>_lote_idlote" type="hidden" value="<?= $_1_u_lote_idlote ?>" readonly='readonly'>
                        </td>
                        <td>Status:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_lote_statusao" onchange="CB.post()">
                                <? fillselect("select 'ABERTO','Aberto'
                                union select 'PENDENTE','Pendente'
                                union select 'FECHADO','Fechado'", $_1_u_lote_statusao); ?>
                            </select>
                        </td>
                    </tr>

                </table>
            </div>
            <div class="panel-body">
                <table>
                    <tr>
                        <td>Descr.</td>
                        <td><textarea <?= $vdisabled ?> style="margin: 0px; width: 783px; height: 71px;" name="_1_<?= $_acao ?>_lote_infadic"><?= $_1_u_lote_infadic ?></textarea></td>
                    </tr>
                    <?
                    if (!empty($_1_u_lote_idpessoa)) {
                        $sp = "select * from prodservforn 
						where idpessoa=" . $_1_u_lote_idpessoa . " 
						and idprodserv=" . $_1_u_lote_idprodserv . " 
                        and idprodservformula=" . $_1_u_lote_idprodservformula . " 
						and status='ATIVO'";
                        $rp = d::b()->query($sp) or die("erro ao buscar prodservforn: " . mysqli_error(d::b()) . "<p>SQL: " . $sp);
                        $rowpf = mysqli_fetch_assoc($rp);
                        if (!empty($rowpf['idprodservforn'])) {
                    ?>
                            <tr>
                                <td>Gerenciamento do Produto</td>
                                <td>
                                    <input type="hidden" name="_x_<?= $_acao ?>_prodservforn_idprodservforn" value="<?= $rowpf["idprodservforn"] ?>"> </input>
                                    <textarea style="color: red;margin: 0px; width: 787px; min-height: 50px; height: 70px;" name="_x_<?= $_acao ?>_prodservforn_obs"><?= $rowpf["obs"] ?></textarea>
                                </td>

                            </tr>

                        <?
                        } else { ?>
                            <td>
                                <i class="fa fa-plus-circle verde pointer fa-lg" onclick="novogp(<?= $_1_u_lote_idpessoa ?>,<?= $_1_u_lote_idprodserv ?>,<?= $_1_u_lote_idprodservformula ?>)"></i>
                            </td>
                    <?
                        } //if(!empty($_1_u_lote_idpessoa)){
                    }; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?

$sql = "select c.idlotecons,c.qtdd,c.qtdd_exp, fr.qtd as qtddisp,fr.qtd_exp as qtddisp_exp,p.conteudo,
                                l.idlote,l.partida,l.exercicio,l.idprodserv,p.descr,l.status,l.observacao,((ifnull(c.qtdd,0)*ifnull(f.dose,0))/ifnull(p.qtdpadrao,0)) as qtdoses,fr.idlotefracao
                            from lotecons c 
                                join lote l 
                                join lotefracao fr
                                join prodserv p  
                                join unidade u 
                                left join prodservformula f on(  f.idprodservformula = l.idprodservformula)
                            where c.idobjeto =" . $_1_u_lote_idlote . " 
                            and c.tipoobjeto ='lote'
                            
                            and fr.idlotefracao =c.idlotefracao
                            and l.idlote=fr.idlote
                            and fr.idunidade = u.idunidade
                            " . getidempresa('l.idempresa', 'lotepesqdes') . "
                            and u.idtipounidade=13
                            and p.idprodserv =l.idprodserv";

$res = d::b()->query($sql) or die("Erro ao buscar lotes sql=" . $sql);
$qtd = mysqli_num_rows($res);
echo "<!--";
echo $sql;
echo "-->";
if ($qtd > 0) {
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <td>Lote(s) Utilizado(s)</td>
                            <td></td>
                            <td>Volume Cons.:</td>
                            <td>
                                <? $arrvcon = AoController::buscarCalculoVolumeConsumo($_1_u_lote_idlote); ?>
                                <label class="alert-warning" title="<?= $arrvcon['strcalc'] ?>"><?= $arrvcon['volumeconsf'] ?></label>
                            </td>
                            <td>Volume Cons. Final:</td>
                            <td>
                                <? $arrvcon1 = AoController::buscarCalculoVolumeConsumo($_1_u_lote_idlote, 'ped'); ?>
                                <label class="alert-warning" title="<?= $arrvcon1['strcalc'] ?>"><?= $arrvcon1['volumeconsf'] ?></label>
                            </td>
                            <td>
                                <i title="Impressão" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/ao.php?_acao=u&idlote=<?= $_1_u_lote_idlote ?>')"></i>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="panel-body">
                    <table class="table table-striped planilha">
                        <tr>
                            <th></th>
                            <th>Padrão</th>
                            <th>Qtd</th>
                            <th></th>
                            <th>Lote</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <? if ($_1_u_lote_statusao != 'FECHADO') { ?>
                                <th>Estoque</th>
                            <? } ?>
                            <th>Status</th>
                            <th>Observação</th>
                            <th></th>
                        </tr>
                        <?
                        $prodForm = getObjeto("prodserv", $_1_u_lote_idprodserv, "idprodserv");
                        while ($row = mysqli_fetch_assoc($res)) {
                            $sqi = "select qtdi,qtdi_exp from prodservformulains where idprodservformula = " . $_1_u_lote_idprodservformula . " and status='ATIVO' and idprodserv=" . $row['idprodserv'];
                            $rei = d::b()->query($sqi) or die("Erro ao buscar formulacao dos insumos sql=" . $sqi);
                            $qtdi = mysqli_num_rows($rei);
                            $roi = mysqli_fetch_assoc($rei);
                            $padrao = ($_1_u_lote_qtdajust * $roi['qtdi']) / $prodForm['qtdpadrao'];

                            $ak = 'u';
                            $input = "<input idobjeto='" . $_1_u_lote_idlote . "' idlote='" . $row['idlote'] . "' idlotecons='" . $row['idlotecons'] . "'	name='lotecons_qtdd' size='6' type='text' value='" . recuperaExpoente($row['qtdd'], $row['qtdd_exp']) . "' onchange=\"atualizacons(this,'u')\" >";
                            $cor = "#ffeb0061";
                            $dlote = "<i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' onclick=\"excluiritem(" . $row['idlotecons'] . ")\" alt='Excluir !'></i>";


                        ?>
                            <tr style="background-color:<?= $cor ?>" class="trInsumo">
                                <td>
                                    <?
                                    if (!empty($_1_u_lote_idprodservformula)) {
                                        $sqi = "select qtdi,qtdi_exp from prodservformulains where idprodservformula = " . $_1_u_lote_idprodservformula . " and status='ATIVO' and idprodserv=" . $row['idprodserv'];
                                        $rei = d::b()->query($sqi) or die("Erro ao buscar formulacao dos insumos sql=" . $sqi);
                                        $qtdi = mysqli_num_rows($rei);
                                        if ($qtdi > 0) {
                                            $roi = mysqli_fetch_assoc($rei);
                                            $padrao = ($_1_u_lote_qtdajust * $roi['qtdi']) / $prodForm['qtdpadrao'];
                                        } else {
                                            $padrao = 0;
                                            $sqlvol = "select ifnull(f.volumeformula,'') as volumeform, 
                                ifnull(p.qtdpadrao,'') as qtdpadrao,
                                ifnull(p.qtdpadrao_exp,'') as qtdpadrao_exp
                               from prodservformula f,prodserv p
                                where f.idprodservformula=" . $_1_u_lote_idprodservformula . "
                                and p.idprodserv= f.idprodserv";
                                            $resvol = d::b()->query($sqlvol) or die("Erro ao buscar volume da formula : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlvol);
                                            $rowvol = mysqli_fetch_assoc($resvol);
                                            $volumeform = (empty($rowvol['volumeform']) or $rowvol['volumeform'] == 0) ? 0 : $rowvol['volumeform'];

                                            if (strpos(strtolower($rowvol['qtdpadrao_exp']), "d")) {
                                                $arrExp = explode('d', strtolower($rowvol['qtdpadrao_exp']));
                                                $vqtdpadrao = $arrExp[0];
                                            } elseif (strpos(strtolower($rowvol['qtdpadrao_exp']), "e")) {
                                                $arrExp = explode('e', strtolower($rowvol['qtdpadrao_exp']));
                                                $vqtdpadrao =  $arrExp[0];
                                            } else {
                                                $vqtdpadrao = (empty($rowvol['qtdpadrao']) or $rowvol['qtdpadrao'] == 0) ? 1 : $rowvol['qtdpadrao'];
                                            }

                                            if (strpos(strtolower($_1_u_lote_qtdajust_exp), "d")) {
                                                $arrExp = explode('d', strtolower($_1_u_lote_qtdajust_exp));
                                                $volumeprod = $arrExp[0];
                                            } elseif (strpos(strtolower($_1_u_lote_qtdajust_exp), "e")) {
                                                $arrExp = explode('e', strtolower($_1_u_lote_qtdajust_exp));
                                                $volumeprod =  $arrExp[0];
                                            } else {
                                                $volumeprod = tratanumero($_1_u_lote_qtdajust);
                                            }
                                            $strcalc = "[(" . $volumeprod . "*" . $volumeform . ")/" . $vqtdpadrao . "]";
                                            $volumeprod = ($volumeprod * $volumeform) / $vqtdpadrao;
                                    ?>

                                            <span class=" hidden volumeprod" id="volumeprod<?= $row['idlote'] ?>" style="color: #1C1C1C;"><?= $volumeprod ?></span>
                                            <input idlote="<?= $row['idlote'] ?>" placeholder='Diluição' name='diluicao' size='6' type='text' value="" onkeyup="mostraPadrao(this)">
                                    <? }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class=" badgepadrao sQtdpadrao" id="iqtdpadrao<?= $row['idlote'] ?>" style="color: #1C1C1C;"><?= $padrao ?></span>
                                </td>
                                <td align="center" class='nowrap'>

                                    <input idobjeto="<?= $_1_u_lote_idlote ?>" idlote="<?= $row['idlote'] ?>" idlotecons="<?= $row['idlotecons'] ?>" name='lotecons_qtdd' size='6' type='text' value="<?= recuperaExpoente($row['qtdd'], $row['qtdd_exp']) ?>" onchange="atualizacons(this,'u')" onkeyup="mostraConsumo(this)">

                                </td>
                                <td><span class="badge sParticipacao " id="sParticipacao<?= $row['idlote'] ?>"></span>
                                <td>
                                    <a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=lotepesqdes&_acao=u&idlote=<?= $row["idlote"] ?>');">
                                        <?= $row['partida'] ?>/<?= $row['exercicio'] ?>
                                    </a>
                                </td>
                                <td>
                                    <?= $row['descr'] ?>
                                </td>
                                <td>
                                    <?= $row['qtdoses'] ?> <?= $row['conteudo'] ?>
                                </td>
                                <? if ($_1_u_lote_statusao != 'FECHADO') { ?>
                                    <td>
                                        <?= recuperaExpoente($row["qtddisp"], $row["qtddisp_exp"]) ?>
                                    </td>
                                <? } ?>
                                <td>
                                    <?= $row['status'] ?>
                                </td>
                                <td><?= nl2br($row['observacao']) ?></td>
                                <td align="center"><?= $dlote ?></td>
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
} // if($qtd>0)



if (($_1_u_lote_status == "FORMALIZACAO" or $_1_u_lote_status == "TRIAGEM") and $_1_u_lote_statusao == 'PENDENTE') {

    $sql = "select * from (
                        select 
                                '' as idlotecons,'' as qtdd,'' as qtdd_exp,f.qtd as qtddisp, f.qtd_exp as qtddisp_exp,
                                l.idlote,l.partida,l.exercicio,p.descr,l.status,l.observacao,f.idlotefracao
                            from lote l,
                                prodserv p,
                                unidade u,
                                lotefracao f
                            where f.idunidade = u.idunidade
                            and l.idlote=f.idlote
				and p.venda='N'
                            " . getidempresa('l.idempresa', 'lotepesqdes') . "
                            and u.idtipounidade=13
							and p.idtipoprodserv=19
                            and p.idprodserv = l.idprodserv
                            and l.status  in('APROVADO')
                            and f.status='DISPONIVEL'
                            and not exists(
                                            select 1 from lotecons c where c.idobjeto =" . $_1_u_lote_idlote . " 
                                                and tipoobjeto ='lote'
                                                and l.idlote =c.idlote
                                        )                          
                       
                        ) as u order by u.idlotecons desc,u.partida ";
    // echo($sql);    

    $res = d::b()->query($sql) or die("Erro ao buscar lotes sql=" . $sql);
    $qtd = mysqli_num_rows($res);
    if ($qtd > 0) {
    ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Lote Disponível
                        <?

                        $sqlm = "select m.* from carbonnovo._modulo m 
            join unidadeobjeto o on(o.tipoobjeto = 'modulo' and o.idobjeto = m.modulo)
            join unidade u on(u.idunidade=o.idunidade and u.idtipounidade = 13 and u.idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . ")
            where m.modulotipo='lote'";
                        $resm = d::b()->query($sqlm) or die("Erro ao buscar modulo de PeD sql=" . $sqlm);

                        $rowm = mysqli_fetch_assoc($resm);
                        ?>

                        <i class="fa fa-plus-circle fa-1x verde btn-lg pointer" title="Criar um novo P&D" onclick="janelamodal('?_modulo=<?= $rowm['modulo'] ?>&_acao=i');"></i>


                    </div>
                    <div class="panel-body">
                        <table class="table table-striped planilha">
                            <tr>
                                <th>Selecionar</th>
                                <th>Lote</th>
                                <th>descrição</th>
                                <th>Estoque</th>
                                <th>Status</th>
                                <th>Observação</th>
                                <th></th>
                            </tr>
                            <?
                            while ($row = mysqli_fetch_assoc($res)) {
                                if (empty($row["idlotecons"])) {
                                    $ak = 'i';
                                    if ($row['status'] != "APROVADO") {
                                        $input = "<a title='E necessário aprovar o lote para utilização' class='fa fa-exclamation-triangle laranja btn-lg pointer' onclick='alertalote()'></a>";
                                    } else {
                                        $input = "<input idobjeto='" . $_1_u_lote_idlote . "' idlote='" . $row['idlote'] . "' idlotefracao='" . $row['idlotefracao'] . "'  idlotecons='" . $row['idlotecons'] . "' title='utilizar' type='checkbox'  name='nameagrupar' onchange=\"atualizacons(this,'i')\">";
                                    }
                                    $cor = "";
                                    $dlote = "";
                                } else {
                                    $ak = 'u';
                                    $input = "<input idobjeto='" . $_1_u_lote_idlote . "' idlote='" . $row['idlote'] . "' idlotefracao='" . $row['idlotefracao'] . "' idlotecons='" . $row['idlotecons'] . "'	name='lotecons_qtdd' size='6' type='text' value='" . recuperaExpoente($row['qtdd'], $row['qtdd_exp']) . "' onchange=\"atualizacons(this,'u')\" >";
                                    $cor = "#ffeb0061";
                                    $dlote = "<i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' onclick=\"excluiritem(" . $row['idlotecons'] . ")\" alt='Excluir !'></i>";
                                }

                            ?>
                                <tr style="background-color:<?= $cor ?>">
                                    <td align="center">
                                        <?= $input ?>
                                    </td>
                                    <td>
                                        <a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=lotepesqdes&_acao=u&idlote=<?= $row["idlote"] ?>');">
                                            <?= $row['partida'] ?>/<?= $row['exercicio'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= $row['descr'] ?>
                                    </td>
                                    <td>
                                        <?= recuperaExpoente($row["qtddisp"], $row["qtddisp_exp"]) ?>
                                    </td>
                                    <td>
                                        <?= $row['status'] ?>
                                    </td>
                                    <td><?= nl2br($row['observacao']) ?></td>
                                    <td align="center"><?= $dlote ?></td>
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
    } // if($qtd>0){
}
?>

<!-- div id="novolote" style="display: none"> 
<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">    
   <div class="row">
      <div class="col-md-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <table>
                  <tr>
                     <td align="right"><strong>Produto:</strong></td>
                     <td >
                        <select  id="idprodservlote" name="" onchange="preencheformula(this)">
                           <option></option>
                           <? fillselect("select p.idprodserv,p.descr 
                                from prodserv p 
                                where p.tipo = 'PRODUTO'
                                and p.status = 'ATIVO' 
                                and exists(select 1 from prodservformula f where f.idprodserv = p.idprodserv and f.status='ATIVO')
                                order by p.descr"); ?>		
                        </select>
                     
                        <input	id="idlotelote" name="" type="hidden"	value=""	>
                        <input	id="statuslote" name="" type="hidden"	value="ABERTO">
                       
                        <input	id="idunidadegplote" name="" type="hidden"	value="14" >
                        <input  id="exerciciolote" name=""  type="hidden"	value="<?= date("Y") ?>"	>
                     </td>
                     <td>Qtd.:</td>
                     <td>
                        <input	id="qtdpedida" class="size8" name="" type="text" value="" vnulo>
                     </td>
                  </tr>
                  <tr>
                    <td>Formula:</td>
                    <td>
                        <select name="" id="idprodservformula" vnulo>
                            <option value=""></option>
                        </select>
                    </td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
</div -->

<script>
    function alertalote() {
        alert("É necessário aprovar o lote para utilização.");
    }

    function atualizacons(vthis, inacao) {
        if (inacao == 'u') {
            var str = "_1_u_lotecons_idlotecons=" + $(vthis).attr('idlotecons') + "&_1_u_lotecons_qtdd=" + $(vthis).val()
        } else {
            var str = "_1_i_lotecons_idlote=" + $(vthis).attr('idlote') + "&_1_i_lotecons_idlotefracao=" + $(vthis).attr('idlotefracao') + "&_1_i_lotecons_tipoobjeto=lote&_1_i_lotecons_idobjeto=" + $(vthis).attr('idobjeto')
        }

        CB.post({
            objetos: str,
            parcial: true
        });
    }

    function altstatus(vthis, inidlotecons) {
        CB.post({
            objetos: "_1_u_lotecons_idlotecons=" + inidlotecons + "&_1_u_lotecons_status=" + $(vthis).val(),
            parcial: true
        });

    }

    function excluiritem(inid) {
        CB.post({
            objetos: "_1_d_lotecons_idlotecons=" + inid,
            parcial: true
        });
    }

    function novogp(idpessoa, idprodserv, idprodservformula) {
        CB.post({
            objetos: "_2_i_prodservforn_idpessoa=" + idpessoa +
                "&_2_i_prodservforn_idprodserv=" + idprodserv +
                "&_2_i_prodservforn_idprodservformula=" + idprodservformula,
            parcial: true
        });
    }

    function inovo(inidresultado) {
        var strCabecalho = "</strong>NOVO AO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='criaragente();'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#novolote").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#idlotelote").attr("name", "_999_i_lote_idlote");
        objfrm.find("#idprodservlote").attr("name", "_999_i_lote_idprodserv");

        objfrm.find("#exerciciolote").attr("name", "_999_i_lote_exercicio");
        objfrm.find("#statuslote").attr("name", "_999_i_lote_status");
        objfrm.find("#qtdpedida").attr("name", "_999_i_lote_qtdpedida");
        objfrm.find("#idunidadegplote").attr("name", "_999_i_lote_idunidade");
        objfrm.find("#idprodservformula").attr("name", "_999_i_lote_idprodservformula");


        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');

    }

    function preencheformula(vthis) {

        $("#cbModalCorpo #idprodservformula").html("<option value=''>Procurando....</option>");

        $.ajax({
            type: "get",
            url: "ajax/buscaformula.php",
            data: {
                idprodserv: $(vthis).val()
            },

            success: function(data) {
                $("#cbModalCorpo #idprodservformula").html(data);
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax

    }



    function criaragente() {
        var str = "_x_i_lote_idprodserv=" + $("[name=_999_i_lote_idprodserv]").val() +
            "&_x_i_lote_status=ABERTO&_x_i_lote_exercicio=" + $("[name=_999_i_lote_exercicio]").val() +
            "&_x_i_lote_idunidade=" + $("[name=_999_i_lote_idunidade]").val() +
            "&_x_i_lote_idprodservformula=" + $("[name=_999_i_lote_idprodservformula]").val();

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

    function mostraPadrao(vthis) {

        var idlote = $(vthis).attr('idlote');
        qtddiluicao = normalizaQtd($(vthis).val());
        qtdVolprod = normalizaQtd($("#volumeprod" + idlote).html());
        valpradrao = qtdVolprod * qtddiluicao;
        $('#iqtdpadrao' + idlote).html(valpradrao.toFixed(2));
    }

    function mostraConsumo(vthis) {

        var idlote = $(vthis).attr('idlote');


        qtdPadrao = normalizaQtd($("#iqtdpadrao" + idlote).html());
        qtdUsando = normalizaQtd($(vthis).val());
        valparcipacao = ((qtdUsando * 100) / qtdPadrao);

        if (valparcipacao > 0 && valparcipacao != 'Infinity') {
            $('#sParticipacao' + idlote).html(valparcipacao.toFixed(2) + '%');
        }
    }

    function normalizaQtd(inValor) {
        var sVlr = "" + inValor;
        var $arrExp;
        var fVlr;
        if (sVlr.toLowerCase().indexOf("d") > -1) {
            $arrExp = sVlr.toLowerCase().split('d');
            fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
            fVlr = parseFloat(fVlr);
        } else if (sVlr.toLowerCase().indexOf("e") > -1) {
            $arrExp = sVlr.toLowerCase().split('e');
            fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
        } else {
            fVlr = parseFloat(sVlr).toFixed(2);
        }

        return parseFloat(fVlr);
    }

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>
<?
require_once '../inc/php/readonly.php';
?>