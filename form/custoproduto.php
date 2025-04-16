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
                            <td align="left"> Subcategoria:</td>
                            <td></td>
                            <td align="left" colspan="10">
                                <select style="width:350px" name="tipoitem"  id="picker_tipoitem"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                                <?$arrtipoitem = explode(',',$tipoitem);  
    
                                
                                    $sqlm="SELECT t.idtipoprodserv, CONCAT(e.sigla,' - ',t.tipoprodserv) as tipoprodserv
                                            FROM tipoprodserv t
                                            JOIN empresa e on (e.idempresa = t.idempresa)
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
    
    


    $sql = "SELECT *
            FROM vw8prodserv vw
            WHERE
            $claus
                AND status = 'ATIVO'
                AND tipo = 'PRODUTO'
                AND venda = 'Y'
            ";
    // die($sql);
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
                                            <?=$row['descr']?>
                                        </div>
                                        <div class="panel-body">
                                          <div class="panel-default">
                                            <div class="panel-heading col-md-12">
                                                <div class="col-md-11"
                                                        hx-get="/ajax/custosproduto.php?idprodserv=<?=$row['idprodserv']?>"
                                                        hx-trigger="revealed" hx-target="#dados<?=$row['idprodserv']?>"
                                                        hx-swap="innerHTML"  id="controle<?=$row['idprodserv']?>">
                                                        Custos
                                                </div>
                                                <div class="col-md-1"><i class="fa fa-arrows-v pointer" idprodserv="<?=$row['idprodserv']?>" data-toggle="collapse" href="#dados<?=$row['idprodserv']?>"></i></div>

                                                </div>
                                                <div class="panel-body" id="dados<?=$row['idprodserv']?>"></div>
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
                $(`#controle${$(e1.target).attr('idprodserv')}`).click()
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
function criaCustoProdserv(idprodserv,vthis){
    $(vthis).html(`<i class="fa fa-spinner fa-pulse"></i>`)
    CB.post({
        objetos: {
            "_1_i_prodservcusto_idprodserv":idprodserv
        },
        parcial:true,
        refresh:false
    })
}

function salvarBloco(idprodservcusto){
        obj = {}
        erro = false
        $(`[idprodservcusto=${idprodservcusto}]`).each((i,e)=>{
            if($(e).attr("type") == "number" && ($(e).val() > 100 || $(e).val() < 0)){
                erro = true;
            }
            name = $(e).attr("name");
            value = $(e).val();
            obj[name] = value
        })
        if(erro){
            alertAtencao("Valores não podem ultrapassar 100%")
        }else{
            CB.post({
                objetos: obj,
                parcial:true,
                refresh:false
            })
        }
    }
</script>