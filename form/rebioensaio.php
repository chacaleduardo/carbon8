<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/rebioensaio_controller.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
?>

<style>
i{
    padding-left: 0px !important; 
    padding-right: 5px !important;    
    padding-top: 3px !important;
    padding-bottom: 3px !important;

}
.linha{  
    padding:0;  
    clear:both; 

}        
.coluna{      
     height:100%;  
     border-bottom: 1px dashed #000000;  
     width:100%;        
     float:left; 
     margin:0;  
     text-align: center;
     background-color :white;
 } 
.linhacab{  
    padding:0;  
    clear:both;            
    display: inline-block;
    vertical-align: top;
    margin: 0px;
    background-color :white;
    margin-right: 1px;
    border: 1px dashed #A9A9A9;
    height: 100%;
    width: 100%;
} 
.colunainv{      
    height:100%;  
    border: 1px solid #A9A9A9;  
    width:95%; 
    float:left;  
    position:relative;
    text-align: center;
    background-color :#E0E0E0;           
}


</style>



<script>
function ShowHideDIV(Valor){
    var controlador = document.getElementById("controlador");
    var filhos = $(controlador.children).children();
    var i = 0;
    if(Valor == 'inicio'){
        $(filhos).css("display", "block");
    }else{
        while(filhos[i] != undefined){
            if($(filhos[i]).attr('id') != Valor){
                $(filhos[i]).css("display","none");
            }else{
                $(filhos[i]).css("display","block"); 
            }
            i = i+1;
        }
    }
    
}
(async function(){
    $("[idtagtipo]").each((index,element) => {
        $.ajax({
                url: "ajax/carregamapadebioensaio.php",
                type: "POST",	
                data: { 
                    idtagpai : $(element).attr('idtagpai'),
                    idtagtipo : $(element).attr('idtagtipo'),
                    idunidadepadrao : $("#unidadepadrao").val(),
                },	
                success: function(ret){
                    $(element).find(".panel-body.carregando").html(ret).removeClass("carregando")
                },						
                error: function(objxmlreq){
                    console.error(objxmlreq.status); 
                }
            });
    });
})()
</script>

<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div style="padding-top: 0px !important;justify-content:center" class="panel-body">
            <div class="col-md-2" >
                <div style="align-items:center;"  class="panel panel-default" >
                    <div class="panel-heading">
                        <a  onclick="ShowHideDIV('inicio');"><i class="fa fa-bar-chart pointer hoverazul">&nbsp;&nbsp;&nbsp;GERAL</i></a>
                    </div>
                </div>
<?
$res=RebioensaioController::buscarTipoDeSalasDeBioensaioPorUnidade($idunidadepadrao);
$i = 0;
foreach($res as $k => $row1){?>
        <?if($i == 1){?>
        </div>
        <?}?>
        <?if($i == 1){?>
            <div class="col-md-2" >
        <?}?>
            <div style="align-items:center;" class="panel panel-default" >
                <div class="panel-heading ">
                    <a  onclick="ShowHideDIV(<?=$row1['idtagtipo']?>);"><i class="fa fa-home pointer hoverazul">&nbsp;&nbsp;&nbsp;<?=$row1['tagtipo']?></i></a>
                </div>
            </div>
            <?$i++;
            if($i > 1){
                $i = 0;
            }?>
<?}?>
            <?if($i == 1 or $i == 0){
                ?></div><?
            }
            ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div id="servicos" style=" display: block; font-size:12px;" >
                <div class="col-md-3">
                    <div class="panel panel-default" >
                        <div class="panel-heading">
                            SERVIÇOS BIOTÉRIO
                            <input type="hidden" id="unidadepadrao" value="<?=$idunidadepadrao?>">
                        </div>
                            <div class="panel-body">
                                <?
                                //buscar os serviços
                                servicospendentes();
                                ?>
                            </div>
                    </div> 
                </div> 
            </div>
            <div id="controlador" class="col-md-9" style="max-width: 86vw;">
                <?
                $resa= RebioensaioController::buscarSalasDeBioensaioPorUnidade($idunidadepadrao);
                $qtda = count($resa);
                if ($qtda > 0) {
                    $c = 1;
                    foreach ($resa as $k => $r) {
                        if($c == 1){?>
                            <div class="row">
                        <?}?>
                        <div idtagtipo='<?=$r['idtagtipo']?>' idtagpai='<?=$r['idtagpai']?>' id="<?=$r['idtagtipo']?>" style=" display: block; font-size:12px;" >
                            <div align="Center" class="col-md-2">
                                <div align="Center" class="panel panel-default col-md-12">
                                    <div class="panel-heading"><?=$r['descrpai']?></div>
                                    <div class="panel-body carregando">
                                        &nbsp;&nbsp;
                                    </div>
                                </div>                    
                            </div>  
                        </div>
                        <?
                        if($c%6 == 0){?>
                            </div>
                             <div class="row">
                        <?}
                        $c++;?>
                    <?}//while ($r = mysqli_fetch_assoc($res))
                    ?>
                    </div>
                <?}//if(qtda > 0)?>
            </div>           
        </div> 
    </div>        
</div>

<?

 //BUSCAR SERVIàOS PENDENTES
function servicospendentes(){
 global $idunidadepadrao;
    $rese= RebioensaioController::buscarServicosPendentesPorUnidade($idunidadepadrao);
    $qtde= count($rese);
?>	
    <table style=" font-size:11px;" >
<?		
    $data = '';
    foreach($rese as $k => $rowe){

        if($rowe['dmadata']!=$data){
            if(!empty($data)){
?>		
        <tr>
            <td  colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
            }//if(!empty($data))
?>
        <tr>
            <td align="center" colspan="2" style="vertical-align: top;  padding: 0; border: none;">
                <font color="<?=$rowe["cor"]?>">
                <?=$rowe['dmadata']?>
                </font>
            </td>			
        </tr>
<?
            $data=$rowe['dmadata'];
        }//if($rowe['dmadata']!=$data)
?>		        
        <tr>
            <td  colspan="3" style="vertical-align: top;  padding: 0; border: none;">
                <div style=" border-bottom: 1px double darkgray; width: 100%;"></div>
            </td>
        </tr>
        <tr title="<?=$rowe['bioensaio']?>-<?=$rowe['partida']?> | <?=$rowe['local']?> - <?=$rowe['gaiola']?>">
            <td>
                <input title="Concluir serviço" type="checkbox" name="nameconcluir" onclick="concluiservico(<?=$rowe['idservicoensaio']?>);">
            </td>
            <td  align="center" style="vertical-align: inherit; padding: 0; border: none;">			
               <a href="javascript:janelamodal('?_modulo=<?=RebioensaioController::buscarModuloPadrao('bioensaio',$idunidadepadrao)?>&_acao=u&idbioensaio=<?=$rowe['idbioensaio']?>')">
                    <?=$rowe['servico']?> - B<?=$rowe['idregistro']?> <?=$rowe['local']?>-<?=$rowe['gaiola']?>
                </a>
            </td>
        </tr>		
<?
    }//foreach($rese as $k => $rowe)
    if($qtde>0){
?>		
        <tr>
            <td nowrap colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
    }?>
    </table>
<?
}//function servicospendentes()?>


<script type="text/javascript">
        
function concluiservico(vidservicoensaio){
	
    CB.post({
        objetos: "_x_u_servicoensaio_idservicoensaio="+vidservicoensaio+"&_x_u_servicoensaio_status=CONCLUIDO"        
    });
}

function concluificha(vidficharep){
	
    CB.post({
        objetos: "_x_u_ficharep_idficharep="+vidficharep+"&_x_u_ficharep_status=CONCLUIDO"        
    });
}

        //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>