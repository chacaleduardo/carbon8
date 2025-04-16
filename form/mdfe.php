<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "mdfe";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idmdfe" => "pk"
);

$pagsql = "select * from mdfe where idmdfe = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

function getEstados($flag){
    global $_1_u_mdfe_ufinicio,$_1_u_mdfe_uffim;

    if(!$flag){
        $aux = "";
    }else{
        $aux = "";
      //  $aux = "and not exists (SELECT 1 FROM mdfetrajeto m WHERE m.uf = n.cuf and (m.uf = ".$_1_u_mdfe_ufinicio." or m.uf = ".$_1_u_mdfe_uffim."))";
    }

    $sql = "SELECT DISTINCT(n.uf) as uf, n.cuf FROM nfscidadesiaf n WHERE cpais = 1058 ".$aux;
    $res = d::b()->query($sql) or die("Erro ao buscar cidades. SQL: ".$sql);

    $arrtmp = array();
    if(!$flag){
        while($row = mysqli_fetch_assoc($res)){
            $arrtmp[$row["cuf"]] = $row["uf"];
        }
    }else{
        $i = 0;
        while($row = mysqli_fetch_assoc($res)){
            $arrtmp[$i]["uf"] = $row["uf"];
            $arrtmp[$i]["cuf"] = $row["cuf"];
            $i++;
        }
    }

    return $arrtmp;
}

function getCidades(){
    $sql = "SELECT * FROM nfscidadesiaf n WHERE n.cpais = 1058  order by uf,cidade";
    $res = d::b()->query($sql) or die("Erro ao buscar cidades. SQL: ".$sql);

    $arrtmp = array();
    $i = 0;
    while($row = mysqli_fetch_assoc($res)){
        $arrtmp[$i]["uf"] = $row["uf"];
        $arrtmp[$i]["cuf"] = $row["cuf"];
        $arrtmp[$i]["cidade"] = $row["cidade"];
        $arrtmp[$i]["codcidade"] = $row["codcidade"];
        $arrtmp[$i]["cmunfg"] = $row["cmunfg"];
        $i++;
    }

    return $arrtmp;
}
/*
function listaMunicipios($vtipo){
    global $_1_u_mdfe_idmdfe;
    $sql = "SELECT m.idmdfetrajeto,n.cidade,n.uf FROM mdfetrajeto m join nfscidadesiaf n ON (m.cidade = n.cmunfg) WHERE 1 ".getidempresa("idempresa","mdfe")." and idmdfe = ".$_1_u_mdfe_idmdfe." and tipo = '".$vtipo."'";
    $res = d::b()->query($sql) or die("Erro ao buscar cidades listadas. SQL: ".$sql);

    $tabela = "<table class='table'>";
    while($row = mysqli_fetch_assoc($res)){
        $tabela .= "<tr><td>".$row["cidade"]." - ".$row["uf"]."</td><td><i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' style='float:right' title='Desvincular' onclick='desvincular(".$row["idmdfetrajeto"].")'></i></td></tr>";
    }
    
    return $tabela .= "</table>";
}
*/
function listaNotas(){
    global $_1_u_mdfe_idmdfe;
    $sql = "select n.nnfe,n.idnf,m.idmdfenf,p.nome,e.uf,nc.* 
                from mdfenf m join nf n on(n.idnf=m.idnf)
                join endereco e on(e.idendereco = n.idendrotulo)
                join nfscidadesiaf nc ON (e.codcidade = nc.codcidade)
                join pessoa p on(p.idpessoa=n.idpessoa)
                where m.idmdfe=".$_1_u_mdfe_idmdfe." order by e.uf,nc.cidade";
    $res = d::b()->query($sql) or die("Erro ao buscar as notas listadas. SQL: ".$sql);
    ?>
        <table class='table'>
    <?
    while($row = mysqli_fetch_assoc($res)){
        ?>
        <tr>
            <td title='".$row["nome"]."'> 
                <a class='pointer' title='<?=$row["nome"]?>'  onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$row['idnf']?>')">
                <?echo($row["nnfe"]." - ".$row["nome"]." - ".$row["cidade"]." ".$row["uf"]);?> 
                </a>
            </td>
            <td><i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' style='float:right' title='Desvincular' onclick="desvincularnf(<?=$row['idmdfenf']?>)"></i></td>
        </tr>
  <?
    }
    $sqlp="select round(ifnull(sum(n.pesob),0),0) as peso,sum(qvol) as volume from mdfenf a join nf n on(n.idnf = a.idnf)
    where a.idmdfe =".$_1_u_mdfe_idmdfe;
    $resp = d::b()->query($sqlp) or die("Erro ao buscar peso das notas listadas. SQL: ".$sqlp);
    $rowp = mysqli_fetch_assoc($resp);

  ?>
  <tr>
        <td >
            
        Peso dos itens: <font style="color: red;font-size: 20px;"><b><?=$rowp['peso']?></b></font>
          
        </td>
  </tr>
  <tr>
        <td >
            
        Quantidade: <font style="color: red;font-size: 20px;"><b><?=$rowp['volume']?></b></font>
          
        </td>
  </tr>
  </table>
  <?
}

if($_acao == "u" ){
    $jcidades = $JSON->encode(getCidades());
    $jestados = getEstados(0);
    $jestados2 = $JSON->encode(getEstados(1));
   
}else{
    $jestados = getEstados(0);
   
}

?>
<style>
    table{
        width: 100%;
    }

    .panel-body{
        padding-top: 0 !important;
    }

    .opcoes{
    background: #eee;
    padding: 4px;
    border: 1px solid #ccc;
    margin-top: 2px;
    border-radius: 4px;
    float: left;
    margin-right: 4px;
}


.opcoes i{
 margin-left: 12px;   
}

</style>
<div class="row">
    <div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
                <table>
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <div class="col-sm-2">
                                    ID.:
                                    <label class="alert-warning"><?=$_1_u_mdfe_idmdfe?></label>
                                    <input name="_1_<?=$_acao?>_mdfe_idmdfe" type="hidden" value="<?=$_1_u_mdfe_idmdfe?>" vnulo>
                                </div>
                                <div class="col-sm-6"></div>
                                <div class="col-sm-2">
                                    Modalidade: 
                                    <select name="_1_<?=$_acao?>_mdfe_modalidade" >
                                        <?fillselect("select '1','Rodoviário' 
                                        union select '2','Aéreo'
                                        union select '3','Aquaviário'
                                        union select '4','Ferroviário'",$_1_u_mdfe_modalidade);?>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    Status: 
                                    <select name="_1_<?=$_acao?>_mdfe_status" >
                                        <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_mdfe_status);?>
                                    </select>
                                </div>
                            </div>
                        </td>      
                    </tr>
                </table>
            </div>
            <?if(!empty($_1_u_mdfe_idmdfe)){?>
            <div class="panel-body" style="padding-top:0 !important;">
            <div class='row'>
                <div class="col-md-6">
                <table>
                    <tr>
                        <td>UF Carregamento:</td>
                        <td>
                            <select class="size5" name="_1_<?=$_acao?>_mdfe_ufinicio" vnulo <?=$disable?>>
                                <option value=""></option>
                                <?fillselect($jestados,$_1_u_mdfe_ufinicio);?>
                            </select>
                        </td>
                        <td>Cidade:</td>
                        <td>
                            <?if(!empty($_1_u_mdfe_ufinicio) AND !empty($_1_u_mdfe_uffim)){
                                if(!empty($_1_u_mdfe_cmuncar)){
                                    $sql = "SELECT n.cidade,n.uf FROM nfscidadesiaf n  where n.cmunfg ='".$_1_u_mdfe_cmuncar."'";                           
                                    $res = d::b()->query($sql) or die("Erro ao buscar cidades listadas. SQL: ".$sql);
                                        $row = mysqli_fetch_assoc($res);
                                        echo($row["cidade"]);
                                ?>       
                                        <i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' style='float:right' title='Desvincular' onclick='dcmuncar()'></i>
                                    
                                <?  
                                }else{
                                    ?>
                            
                                    <input id="muncarregamento" class="" type="text" cbvalue placeholder="Selecione">
                                <?
                                }
                            }?>
                        </td>
                        </tr>
                        <tr>
                            <td>UF Percurso:</td>
                            <td>
                                <select class="size5" name="mdfe_ufper" onchange="inserirper(this)"  >
                                    <option value=""></option>
                                    <?fillselect('select uf,uf from uf order by uf');?>
                                </select>
                            </td>
                            <td colspan="2" style=>
                                <div class="row alert-info">
                            <?
                                $sql="select * from mdfeufper where idmdfe=".$_1_u_mdfe_idmdfe;
                                $res = d::b()->query($sql) or die("Erro ao buscar Uf de percurso. SQL: ".$sql);
                                while($row = mysqli_fetch_assoc($res)){
                            ?>
                                    <div class="col-sm-3">
                                    <a href="javascript:void(0)">
                                        <div class="opcoes">
                                            <?=$row['uf']?>
                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir" onclick=" desvincularper(<?=$row['idmdfeufper']?>)"></i>
                                        </div>
                                    </a>
                                    </div>

                            <?      
                                }

                            ?>
                                </div>

                            </td>
                        </tr> 
                        <tr>
                            <td>UF Descarregamento:</td>
                            <td>
                                <select class="size5" name="_1_<?=$_acao?>_mdfe_uffim" vnulo <?=$disable?>>
                                    <option value=""></option>
                                    <?fillselect($jestados,$_1_u_mdfe_uffim);?>
                                </select>
                            </td>
                        </tr>
                         
                                      
                    </table>
                </div>    
                <div class="col-md-6">
                <table>
                    <tr>
                        <td>Veiculo:</td>
                        <td>
                            <select class="size20" name="_1_<?=$_acao?>_mdfe_idtag" vnulo>
                                    <option value=""></option>
                                    <?fillselect("select t.idtag,t.descricao from tag t where t.idtagclass = 3  ".share::otipo('cb::usr')::mdfeVeiculos('t.idtag')."  and t.status='ATIVO' order by t.descricao",$_1_u_mdfe_idtag);?>
                            </select>                        
                        </td>
                    </tr>    
                    <tr>
                        <td>Condutor:</td>
                        <td>
                            <select class="size20" name="_1_<?=$_acao?>_mdfe_idpessoa" vnulo>
                                    <option value=""></option>
                                    <?fillselect("select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome from pessoa p where p.idtipopessoa in (1,7)  ".share::otipo('cb::usr')::mdfeCondutor('p.idpessoa')."   and p.status='ATIVO' order by p.nome",$_1_u_mdfe_idpessoa);?>
                            </select>                        
                        </td>
                    </tr>  
                    <tr>
                        <td >Transportadora:</td> 
                        <td >
                            <select  class="size20" name="_1_<?=$_acao?>_mdfe_idtransportadora" vnulo >
                                <option value=""></option>
                                <?fillselect("select p.idpessoa,p.nome from pessoa p where p.idtipopessoa = 11 
                                ".share::otipo('cb::usr')::mdfeCondutor('p.idpessoa')." 
                                and p.status = 'ATIVO' order by p.nome",$_1_u_mdfe_idtransportadora);?>		
                            </select>                       
                        </td> 
                    </tr>             
                </table>
                
                </div>           
            </div>
            </div>
            <?}?>
        </div>
    </div>
</div>
<?if(!empty($_1_u_mdfe_idmdfe)){?>
<div class="row">
  
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                Notas Fiscais
            </div>
            <div class="panel-body">
                <table>
                    <tr>
                        <td>
                        <div class="col-sm-12">
                            <div class="col-sm-3">
                               Notas à Despachar                                
                            </div>
                            <?if(!empty($_1_u_mdfe_ufinicio) AND !empty($_1_u_mdfe_uffim) AND !empty($_1_u_mdfe_idtransportadora) ) {
                                $sqlvi="select idnf, concat(nnfe,' - ',p.nome,' [',nc.uf,']') as nome,nc.*
                                from nf n join pessoa p on(n.idpessoa=p.idpessoa)
                                join endereco e on(e.idendereco = n.idendrotulo)
                                join nfscidadesiaf nc ON (e.codcidade = nc.codcidade and nc.cuf=".$_1_u_mdfe_uffim.")
                                where n.status in ('ENVIAR','FATURAR','ENVIADO')  
                                and n.idtransportadora = ".$_1_u_mdfe_idtransportadora."
                                and not exists(select 1 from mdfenf m where m.idnf=n.idnf )                                                     
                                ".share::otipo('cb::usr')::mdfeNotas('n.idnf')." 
                                and n.tiponf='V' order by nome";    
                            ?>
                            <div class="col-sm-9">
                                <div class='hide'><?=$sqlvi?></div>
                                <select name="nfdespachar" onchange="inserirnf(this)">
                                    <option value=""></option>
                                    <?fillselect("select idnf, concat(nnfe,' - ',p.nome,' [',nc.uf,']') as nome,nc.*
                                                    from nf n join pessoa p on(n.idpessoa=p.idpessoa)
                                                    join endereco e on(e.idendereco = n.idendrotulo)
                                                    join nfscidadesiaf nc ON (e.codcidade = nc.codcidade and nc.cuf=".$_1_u_mdfe_uffim.")
                                                    where n.status in ('ENVIAR','FATURAR','ENVIADO')  
                                                    and n.idtransportadora = ".$_1_u_mdfe_idtransportadora."
                                                    and not exists(select 1 from mdfenf m where m.idnf=n.idnf )                                                     
                                                    ".share::otipo('cb::usr')::mdfeNotas('n.idnf')." 
                                                    and n.tiponf='V' order by nome");?>
                                </select>    
                                </div>
                            <?}?>
                        </div>
                        </td>
                    </tr>
                    <?if(!empty($_1_u_mdfe_ufinicio) AND !empty($_1_u_mdfe_uffim)){?>
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <?=listaNotas();?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?}?>
                </table>
            </div>
        </div>
    </div>
     <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                MDFe
            </div>
            <div class="panel-body">
                <table>
                    <tr>
                        <td>Emissão:</td>
                        <td><input   name="_1_<?=$_acao?>_mdfe_dhemi"   class="calendariodatahora size15"  value="<?=$_1_u_mdfe_dhemi?>" vnulo autocomplete="off"></td>
                        <td>MDFe:</td>
                        <td><input  readonly='readonly' class='size8' name="_1_<?=$_acao?>_mdfe_nmdfe"     value="<?=$_1_u_mdfe_nmdfe?>"  autocomplete="off"></td>               
                        <td>Status:</td>
                        <td title="<?=$_1_u_mdfe_mensagem?>" ><label class="alert-warning"><?=$_1_u_mdfe_statusmdfe?></label></td>
                    </tr>                    
                        
                        <?if( $_1_u_mdfe_statusmdfe != "CONCLUIDO" AND $_1_u_mdfe_statusmdfe != "CANCELADO"  AND $_1_u_mdfe_statusmdfe != "AUTORIZADO" and !empty($_1_u_mdfe_cmuncar)){?>		        
                            <tr>
                                <td align="right" nowrap>Gerar MDFe:</td>
                                <td class="tdbr" align="left">
                                <a class="fa fa-cloud-upload pointer hoverazul" title="Gerar MDFe" onClick="enviomdfe();"></a>
                                </td>
                                <?/*?>
                                <td align="right" nowrap>Consultar Protocolo:</td>
                                <td class="tdbr" align="left">
                                    <a class="fa fa-cloud-download pointer hoverazul" title="Consulta Protocolo" onClick="consultamdfe();"></a>
                                </td>   
                                <?*/?>                                    
                                <td align="right" nowrap>Alterar Recibo:</td>
                                <td class="tdbr" align="left">
                                    <div id="rotrecibo" style="display: block">
                                        <a class="fa fa-pencil-square-o pointer hoverazul" title="Editar Recibo" onClick="showdivrecibo();"></a>   
                                    </div>
                                    <div id="inputrecibo" style="display: none">                                                
                                        <input name="_1_<?=$_acao?>_mdfe_recibo"	type="text" size="15" value="<?=$_1_u_mdfe_recibo?>">
                                    </div>
                                </td> 
                            </tr>	
                            <?                
                                   }
                                    ?>
                            <tr>
                            <?if($_1_u_mdfe_statusmdfe=="CONCLUIDO" or  $_1_u_mdfe_statusmdfe =="AUTORIZADO" or  $_1_u_mdfe_statusmdfe =="CANCELADO"){?>
                                <td align="right">MDFe:</td>
                                <td  align="left"><a class="fa fa-print pointer hoverazul" title="MDFe"  onclick="janelamodal('inc/mdfe/func/mdfe.php?idmdfe=<?=$_1_u_mdfe_idmdfe?>')"></a></td>
                            <?}
                            if($_1_u_mdfe_statusmdfe=="AUTORIZADO"){?>                               
                                <td align="right" nowrap>Encerrar:</td>
                                <td><i class="fa fa-handshake-o verde pointer hoverazul"  title="Encerrrar " onClick="encerrar();"></i></td>
                        
                                <td align="right" nowrap>Cancelar:</td>
                                <td><i class="fa fa-minus-square vermelho pointer hoverazul" id="toggle_cancelanfe" title="Cancelar" onClick="cancelar();"></i></td>
                            <?}?>                            
                            </tr>
                                    
                   
                </table>
            </div>
        </div>
     </div>
</div>
     
<?
}
if(!empty($_1_u_mdfe_idmdfe)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_mdfe_idmdfe; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "mdfe"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<script>
    <?if(!empty($_1_u_mdfe_idmdfe)){?>

        var jEstados = <?=$jestados2?> || {}
        var jMunCarregamento = <?=$jcidades?> || {}
        var jMunDescarregamento = <?=$jcidades?> || {}

        $(document).ready(function(){
            $(window).ready(function(){
                

                jMunCarregamento = jMunCarregamento.filter(function(u){
                    if(u.cuf == $("[name*=_mdfe_ufinicio]").val()){
                        return true;
                    }else{
                        return false;
                    }
                });

                jMunDescarregamento = jMunDescarregamento.filter(function(u){
                    if(u.cuf == $("[name*=_mdfe_uffim]").val()){
                        return true;
                    }else{
                        return false;
                    }
                });

                $("#muncarregamento").autocomplete({
                    source: jMunCarregamento
                    ,delay: 0    
                    ,create: function(){
                        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                            return $('<li>').append("<a>"+item.cidade+"</a>").appendTo(ul);
                        };
                    }    
                    ,select: function(event, ui){
                        CB.post({
                            objetos: "_x_u_mdfe_idmdfe="+$("[name*=_mdfe_idmdfe]").val()+"&_x_u_mdfe_cmuncar="+ui.item.cmunfg
                            ,parcial:true
                        });
                    }
                });

                $("#mundescarregamento").autocomplete({
                    source: jMunDescarregamento
                    ,delay: 0    
                    ,create: function(){
                        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                            return $('<li>').append("<a>"+item.cidade+"</a>").appendTo(ul);
                        };
                    }    
                    ,select: function(event, ui){
                        CB.post({
                            objetos: "_x_i_mdfetrajeto_idmdfe="+$("[name*=_mdfe_idmdfe]").val()+"&_x_i_mdfetrajeto_tipo=C&_x_i_mdfetrajeto_uf="+$("[name*=_mdfe_ufinicio]").val()+"&_x_i_mdfetrajeto_cidade="+ui.item.cmunfg
                            ,parcial:true
                        });
                    }
                });
            });
        });
    
        function desvincular(vid){
            CB.post({
                objetos: "_x_d_mdfetrajeto_idmdfetrajeto="+vid
                ,parcial:true
            });
        }
    
        function inserirnf(vthis){
            CB.post({
                objetos: "_x_i_mdfenf_idmdfe="+$("[name*=_mdfe_idmdfe]").val()+"&_x_i_mdfenf_idnf="+$(vthis).val()
                ,parcial:true
            });
        }
        function inserirper(vthis){
            CB.post({
                objetos: "_x_i_mdfeufper_idmdfe="+$("[name*=_mdfe_idmdfe]").val()+"&_x_i_mdfeufper_uf="+$(vthis).val()
                ,parcial:true
            });
        }

        function desvincularper(vid){
            CB.post({
                objetos: "_x_d_mdfeufper_idmdfeufper="+vid
                ,parcial:true
            });
        }

        function desvincularnf(vid){
            CB.post({
                objetos: "_x_d_mdfenf_idmdfenf="+vid
                ,parcial:true
            });
        }
    

        function dcmuncar(){
            CB.post({
                objetos: "_x_u_mdfe_idmdfe="+$("[name*=_mdfe_idmdfe]").val()+"&_x_u_mdfe_cmuncar="
                ,parcial:true
            });
        }

    function enviomdfe(){	

     
        vurl = "inc/mdfe3a/func/enviamdfe.php?idmdfe="+$("[name*=_mdfe_idmdfe]").val();

        if(confirm("Deseja enviar as informações para gerar o MDFe?")){		
            $.ajax({
                type: "get",
                url : vurl,
                success: function(data){
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq){
                    alert('Erro:\n'+objxmlreq.status); 
                }
            })//$.ajax
        }
    }

    function consultamdfe(){

        vurl = "inc/mdfe/func/consulta.php?idmdfe="+$("[name*=_mdfe_idmdfe]").val();	
            $.ajax({
                type: "get",
                url : vurl,
                success: function(data){
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq){
                    alert('Erro:\n'+objxmlreq.status); 
                }
            })//$.ajax
       
    }

    function encerrar(){	

     
        vurl = "inc/mdfe/func/encerra.php?idmdfe="+$("[name*=_mdfe_idmdfe]").val();

        if(confirm("Deseja encerrar o MDFe?")){		
            $.ajax({
                type: "get",
                url : vurl,
                success: function(data){
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq){
                    alert('Erro:\n'+objxmlreq.status); 
                }
            })//$.ajax
        }
    }

function cancelar(){	

     
vurl = "inc/mdfe/func/cancela.php?idmdfe="+$("[name*=_mdfe_idmdfe]").val();

if(confirm("Deseja cancelar o MDFe?")){		
    $.ajax({
        type: "get",
        url : vurl,
        success: function(data){
            alert(data);
            document.location.reload();
        },
        error: function(objxmlreq){
            alert('Erro:\n'+objxmlreq.status); 
        }
    })//$.ajax
}
}
    function showdivrecibo(){
        document.getElementById('inputrecibo').style.display = "block";// 1 
        document.getElementById('rotrecibo').style.display = "none"; // 2
    }
    <?}?>
</script>