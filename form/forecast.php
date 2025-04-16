<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/planejamentoprodserv_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$idempresa = $_GET['idempresa'];
$tipo = $_GET['tipo'];
$idcontaitem = $_GET['idcontaitem'];
$tipoitem = $_GET['tipoitem'];
$plantel = $_GET['plantel'];
$idprodserv = $_GET['idprodserv'];

?>
<script src="https://unpkg.com/htmx.org@1.7.0/dist/htmx.js?<?=date("dmYhms")?>"></script><style>
    .htmx-indicator{
        opacity:0;
        transition: opacity 500ms ease-in;
    }
    .htmx-request .htmx-indicator{
        opacity:1
    }
    .htmx-request.htmx-indicator{
        opacity:1
    }

</style>
<div class="row">

    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
            <div class="row"> 
                <div class="col-md-12">
                    <table  >
                        <tr>
                            <td  align="left">Empresa: </td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select name="idempresa" >
                                    <?
                                    $sql ='select * from (SELECT idempresa,nomefantasia from empresa where idempresa in (select idempresa from matrizconf where idmatriz='.cb::idempresa().') and status = "ATIVO" and exists
                                    (select 1 from objempresa oe where oe.empresa = empresa.idempresa and oe.objeto = "pessoa" and oe.idobjeto = '.$_SESSION["SESSAO"]["IDPESSOA"].' )
                                    UNION
                                    SELECT idempresa,nomefantasia from empresa where idempresa ='.cb::idempresa().') a order by idempresa;';
    
                                    fillselect($sql,$idempresa);
                                    ?>				
                                </select>
                            </td>
                            <td></td>
                            <td  align="left">Tipo: </td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select name="tipo" >
                                    <?
                                    fillselect(["PRODUTO"=>"Produto","SERVICO"=>"Serviço"],$tipo);
                                    ?>				
                                </select>
                            </td>
                            <td></td>
                            <td align="left">Categoria:</td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select style="width:350px" name="idcontaitem"  id="picker_grupoes"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                                <?$arcontaitem = explode(',',$idcontaitem);  
    
                                
                                    $sqlm="SELECT distinct
                                    c.idcontaitem,c.contaitem
                                    FROM contaitem c
                                    JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' AND ov.idobjeto in (".getModsUsr("LPS").") AND ov.tipoobjeto = '_lp'
                                        WHERE c.status = 'ATIVO'
                                        ".share::otipo('cb::usr')::contaitemlp("c.idcontaitem")."
                                        order by contaitem";
                                    $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                                    while ($rowm = mysqli_fetch_assoc($resm)) {
                                        if (in_array($rowm['idcontaitem'],$arcontaitem)){
                                                $selected= 'selected';
                                        }else{
                                                $selected= '';
                                        }
    
                                        echo '<option data-tokens="'.retira_acentos($rowm['contaitem']).'" value="'.$rowm['idcontaitem'].'" '.$selected.' >'.$rowm['contaitem'].'</option>'; 
                                    }?>
                                </select>  
                                <input type="hidden" name="sel_picker_idcontaitem" id="sel_picker_idcontaitem" value="<?=$_idcontaitem?>">
                                
                            </td>
                            <td></td> 
                            <td align="left"> Subcategoria:</td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select style="width:350px" name="tipoitem"  id="picker_tipoitem"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                                <?$arrtipoitem = explode(',',$tipoitem);  
    
                                
                                    $sqlm="SELECT idtipoprodserv, tipoprodserv FROM tipoprodserv t 
                                    WHERE t.status = 'ATIVO' ORDER BY tipoprodserv";
                                    $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                                    while ($rowm = mysqli_fetch_assoc($resm)) {
                                        if (in_array($rowm['idtipoprodserv'],$arrtipoitem)){
                                                $selected= 'selected';
                                        }else{
                                                $selected= '';
                                        }
    
                                        echo '<option data-tokens="'.retira_acentos($rowm['tipoprodserv']).'" value="'.$rowm['idtipoprodserv'].'" '.$selected.' >'.$rowm['tipoprodserv'].'</option>'; 
                                    }?>
                                </select>  
                                <input type="hidden" name="sel_picker_tipoitem" id="sel_picker_tipoitem" value="<?=$_tipoitem?>">
                                
                            </td>
                            <td></td>
                            <td align="left">Descrição:</td>
                            <td></td>
                            <td align="left" colspan="10">
                                <input class="size25" name="idprodserv" value="<?=$idprodserv?>">
                            </td>
                        </tr> 
                        <!-- <tr>
                            <td align="left">Unidade de Negócio:</td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select style="width:350px" name="plantel"  id="picker_plantel"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                                <?
                                // $arrplantel = explode(',',$tipoitem);  
                                //     $sqlm="SELECT 
                                //                 u.idplantel,
                                //                 u.plantel
                                //             from plantel u 
                                //                 join plantelobjeto p on( u.idplantel = p.idplantel and p.idobjeto in (".getModsUsr("LPS").") and p.tipoobjeto = 'lp')
                                //             where u.status='ATIVO'
                                //             AND u.idempresa = ".cb::idempresa()."
                                //             order by u.plantel";
                                //     $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                                //     //echo $sqlm;
                                //     if(mysqli_num_rows($resm) < 1){
                                //         $sqlm="SELECT 
                                //                     u.idplantel,
                                //                     CONCAT(e.sigla,' - ',u.plantel) as plantel
                                //                 from plantel u 
                                //                 join empresa e on (e.idempresa = u.idempresa)
                                //                 where u.status='ATIVO'
                                //                 AND e.idempresa = ".cb::idempresa()."
                                //                 order by u.plantel";
                                //         $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                                //     }
                                //     while ($rowm = mysqli_fetch_assoc($resm)) {
                                //         if (in_array($rowm['idplantel'],$arrplantel)){
                                //                 $selected= 'selected';
                                //         }else{
                                //                 $selected= '';
                                //         }
    
                                //         echo '<option data-tokens="'.retira_acentos($rowm['plantel']).'" value="'.$rowm['idplantel'].'" '.$selected.' >'.$rowm['plantel'].'</option>'; 
                                //     }?>
                                </select>
                            </td>
                            <td></td>
                        </tr>  -->
                        <tr>
                            <td></td>
                            <td></td>
                            <td colspan="10"></td>
                            <td  align="right"></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 flex justify-end">
                    <button class="btn btn-primary" onclick="pesquisar()"><i class="fa fa-search"></i> Pesquisar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?
if($_GET['pesquisa'] == "Y"){?>
    <?
    
    $claus = '';
    $and = '';

    if($idempresa){
        $claus .= $and.' vw.idempresa = '. $idempresa;
        $and = ' and ';
    }
    if($tipo){
        $claus .= $and.' vw.tipo = "'. $tipo.'"';
        $and = ' and ';
    }
    if($idcontaitem){
        $claus .= $and.' vw.idcontaitem in ('. $idcontaitem.')';
        $and = ' and ';   
    }    if($tipoitem){
        $claus .= $and.' vw.idtipoprodserv in ('. $tipoitem.')';
        $and = ' and ';
    }
    if($plantel){
        $claus .= $and.' vw.plantel in ('. $plantel.')';
        $and = ' and ';
    }
    if($idprodserv){
        $claus .= $and.' vw.descr like "%'. $idprodserv.'%"';
        $and = ' and ';
    }
    


    $sql = "SELECT vw.*,pf.idprodservformula,Ifnull(pf.rotulo,'Geral') as rotulo, group_concat(u.idunidade) as idunidade from vw8prodserv vw
                     JOIN
                unidadeobjeto ou ON (ou.idobjeto = vw.idprodserv
                    AND ou.tipoobjeto = 'prodserv')
                    JOIN
                unidade u ON (u.idunidade = ou.idunidade
                    AND u.status = 'ATIVO')
                    LEFT JOIN
                prodservformula pf ON (pf.idprodserv = vw.idprodserv
                    AND pf.status = 'ATIVO')
                where
                    $claus
                    and vw.status = 'ATIVO'
                group by vw.idprodserv,pf.idprodservformula
                order by vw.descr asc, vw.idprodserv";
    $res =  d::b()->query($sql)  or die("Erro buscar produtos sql:\n".$sql);
    ?>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">Resultados</div>
            <div class="panel-body">
                <div class="col-md-12">
                    <?
                    if(mysqli_num_rows($res) == 0){?>
                        <h4>Nenhum resultado encontrado!!</h4>
                    <?}else{?>
                        <table class="table table-striped planilha">
                            <? while($row = mysqli_fetch_assoc($res)){?>
                                <tr>
                                    <td class="panel panel-default">
                                        <div class="col-md-12 panel-heading">
                                            <?=$row['descr'].' Fórmula - '.$row['rotulo']?>
                                        </div>
                                        <div class="panel-body">
                                          <div class="panel-default">
                                            <div class="panel-heading col-md-12">
                                                <div class="col-md-11"
                                                
                                                        hx-get="/ajax/forecastprodform.php?idprodserv=<?=$row['idprodserv']?>&idprodservformula=<?=$row['idprodservformula']?>"
                                                        hx-trigger="click" hx-target="#dados<?=$row['idprodserv'].$row['idprodservformula']?>"
                                                        hx-swap="outerHTML"  id="controle<?=$row['idprodserv'].$row['idprodservformula']?>">
                                                    <a class="pointer"
                                                        >
                                                        Ver Forecast
                                                    </a>
                                                </div>
                                                <div class="col-md-1"><i class="fa fa-arrows-v pointer" idprodserv="<?=$row['idprodserv']?>" idprodservformula="<?=$row['idprodservformula']?>" data-toggle="collapse" href="#dados<?=$row['idprodserv'].$row['idprodservformula']?>"></i></div>

                                                </div>
                                                <div class="panel-body" id="dados<?=$row['idprodserv'].$row['idprodservformula']?>"></div>
                                          </div>
                                        </div>
                                    </td>
                                </tr>
                            <?}?>
                        </table>
                    <?}?>
                </div>
            </div>
        </div>
    </div>
<?}?>
<script>

$('.selectpicker').selectpicker('render');

$(`.fa.fa-arrows-v.pointer`).each((i,e)=>{
    $(e).on("click",async (e1)=>{
        seta =$(e1.target).attr('href');
        if($(seta).html().length == 0){
            $(seta).removeClass('collapse')
            setTimeout(()=>{
                // $(`#`).click()
                $(`#controle${$(e1.target).attr('idprodserv')}${($(e1.target).attr('idprodservformula') ?? "")}`).click()
            },500)
        }
    })
});

function pesquisar(){
    var idempresa,tipo,idcontaitem,tipoitem,plantel,idprodserv = "";

    idempresa = $("[name=idempresa]").val() ?? "";
    tipo = $("[name=tipo]").val() ?? "";
    idcontaitem = $("[name=idcontaitem]").val() ?? "";
    tipoitem = $("[name=tipoitem]").val() ?? "";
    plantel = $("[name=plantel]").val() ?? "";
    idprodserv = $("[name=idprodserv]").val() ?? "";

    var str="idempresa="+idempresa+"&tipo="+tipo+"&idcontaitem="+idcontaitem+"&tipoitem="+tipoitem+"&plantel="+plantel+"&idprodserv="+idprodserv+"&pesquisa=Y";
    CB.go(str);
    }

    function novoPlanejamento(inidunidade){
    $('#mais_'+inidunidade).addClass('hide');
    $('#select_'+inidunidade).removeClass('hide');
    }

    function salvaBloco(vthis){debugger
        post = "";
        and = "";
        $(vthis).closest('table').find(':input').each((i,e)=>{
            val = $(e).val() ?? ""
            name = $(e).attr('name') ?? ""
            if(name && val)
                post += and+name+"="+val
            and = "&"
        });
        CB.post({
            objetos: post,
            refresh:false,
            parcial:true,
            posPost: function(){
                $(`#controle${$(vthis).attr('idprodserv')}${($(vthis).attr('idprodservformula') ?? "")}`).click()
            }
        })
    }

    function inserirPlanejamento(vthis,idprodserv,idunidade){
        
        var exercicio = $(vthis).val();
        form = '';
        var idprodservformula = $(vthis).attr('idprodservformula');
        
        let str='';

        for (let i = 1; i < 13; i++) {
        if(idprodservformula)
            form = '&_'+i+'_i_planejamentoprodserv_idprodservformula='+idprodservformula;

        str +='&_'+i+'_i_planejamentoprodserv_idprodserv='+idprodserv+'&_'+i+'_i_planejamentoprodserv_idunidade='+idunidade+'&_'+i+'_i_planejamentoprodserv_exercicio='+exercicio+'&_'+i+'_i_planejamentoprodserv_mes='+i+form;
        }

        CB.post({
                    objetos: str,
                    parcial: true,
                    refresh:false,
                    posPost: function(){
                        $(`#controle${idprodserv}${(idprodservformula ?? "")}`).click()
                    }
                });
                
    }

    function atualizaad(vthis,vclass){
        debugger;
        $("."+vclass).val($(vthis).val());
    }

    function alteravalor(campo, valor, tabela, inid, texto){
        htmlTrModelo = "";
        htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="planejamentoprodserv" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10 " type="text">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?=fillselect(PlanejamentoProdServController::$_justificativa)?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>`;

        if (campo == 'previsaoentrega') 
        {
            var objfrm = $(htmlTrModelo);
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        } else {
            var objfrm = $(htmlTrModelo);
            objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        }

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";
    
        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                

                 $(`[name="_h1_i_${tabela}_valor"]`).val(valor);
            }
        });
    }


    function alteraoutros(vthis, tabela){
		valor = $(vthis).val();
		if (valor == 'OUTROS') {
           
			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
            $('#justificativa').remove();
        } else {
			$('#justificaticaText').remove();
		}
	}

    $(".historicoEnvio").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 500,
        delay: {
            show: 300,
            hide: 0
        }
    });
</script>