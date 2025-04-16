<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rhevento";
$pagvalcampos = array(
	"idrhevento" => "pk"
);

if($_GET['_acao'] == 'i')
{
	$_1_u_rhevento_status = 'PENDENTE';
}

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rhevento where idrhevento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

 $_idpessoa=$_GET['idpessoa'];
 $_dataevento=$_GET['dataevento'];
 $_idrhtipoevento=$_GET['idrhtipoevento'];
 
 if($_acao=='i' and !empty($_idpessoa) and !empty($_dataevento) and !empty($_idrhtipoevento)){
    $_1_u_rhevento_idpessoa=$_idpessoa;
    $_1_u_rhevento_dataevento=$_dataevento;
    $_1_u_rhevento_idrhtipoevento=$_idrhtipoevento;
 }

function getPessoaF(){

    $sql= "SELECT
                p.idpessoa,
               p.nomecurto
        FROM pessoa p			
        WHERE p.status in('ATIVO','AFASTADO','PENDENTE')
                AND p.idtipopessoa  =1
               ".share::otipo("cb::usr")::rheventopessoas('p.idpessoa')."
        ORDER BY p.nomecurto";

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        
        $arrret[$r["idpessoa"]]["nome"]=$r["nomecurto"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrFunc=getPessoaF();
//print_r($arrCli); die;
$jFunc=$JSON->encode($arrFunc);

function getRhtev(){
    $sql= "SELECT
                p.idrhtipoevento,
               p.evento,
               case formato when 'H' then 'Horas'
                            when 'HI' then  'Horas Inicio' 
                            when 'HIF' then  'Horas Inicio Fim'
                            when 'SH' then  'Horas Somatório'
                            when 'I' then  'Input' 
                            when 'D' then  'Dinheiro' 
                            when 'SD' then  'Dinheiro Somatório' END as formato  
        FROM rhtipoevento p      
        ORDER BY evento";

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        
        $arrret[$r["idrhtipoevento"]]["evento"]=$r["evento"];
        $arrret[$r["idrhtipoevento"]]["formato"]=$r["formato"];
    }
	return $arrret;    
}

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrTipoE=getRhtev();
//print_r($arrCli); die;
$jTev=$JSON->encode($arrTipoE);
if(!empty($_1_u_rhevento_idrhtipoevento)){
    $arrTev=getObjeto("rhtipoevento",$_1_u_rhevento_idrhtipoevento);
}
?> 
<script>
<?if($_1_u_rhevento_status=="QUITADO" OR $_1_u_rhevento_status=="INATIVO" or $_1_u_rhevento_status=="QUITADO TRANSFERENCIA" ){?>

$("#cbModuloForm").find('input').prop( "disabled", true );
$("#cbModuloForm").find("select" ).prop( "disabled", true );
$("#cbModuloForm").find("textarea").prop( "disabled", true );
 
<?}?>
    
 </script> 
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
      
            
	    <table>
                <tr>
                    <td align="right">Funcionário:</td>
                    <td>
                        <input name="_1_<?=$_acao?>_rhevento_idrhevento" type="hidden"	value="<?=$_1_u_rhevento_idrhevento?>">
                        <input  type="text" name="_1_<?=$_acao?>_rhevento_idpessoa" cbvalue="<?=$_1_u_rhevento_idpessoa?>" value="<?=$arrFunc[$_1_u_rhevento_idpessoa]["nome"]?>" style="width: 15em;" vnulo>
                    </td>
		    <td align="right">Tipo Evento:</td>
		    <td>
                <input name="_1_<?=$_acao?>_rhevento_idrhtipoevento" type="text"	cbvalue="<?=$_1_u_rhevento_idrhtipoevento?>" value="<?=$arrTipoE[$_1_u_rhevento_idrhtipoevento]["evento"]?> <?=$arrTipoE[$_1_u_rhevento_idrhtipoevento]["formato"]?>" style="width: 40em;" vnulo >
               <?if(!empty($_1_u_rhevento_idrhtipoevento)){?>
                <a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=rhtipoevento&_acao=u&idrhtipoevento=<?=$_1_u_rhevento_idrhtipoevento?>');"></a>
               <?}?>
            </td>
                    <td align="right">Status:</td> 
                    <td>
                        <? $rotulo = getStatusFluxo($pagvaltabela, 'idrhevento', $_1_u_rhevento_idrhevento)?>                                              
                        <label class="alert-warning" title="<?=$_1_u_rhevento_status?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'],'UTF-8')?> </label>
                        <input name="_1_<?=$_acao?>_rhevento_status" type="hidden" value="<?=$_1_u_rhevento_status?>">
                    </td>
                </tr>
	    </table> 

	</div>
 <?
                    if(!empty($_1_u_rhevento_idrhtipoevento)){
                        
?> 
        
        <div class="panel-body"> 

            <div class="row">
                          <?
                    if($arrTev['formato']=='HI' or $arrTev['formato']=='H' or $arrTev['formato']=='D'){
                    ?>
                    <?
                    if($_1_u_rhevento_parcelas>1){ //nao pode gerar parcelas
                        $desab_parc="disabled='disabled'";
                    }
                    $desabilitado="disabled='disabled'";
                    ?>
                   
                    
                <div class="col-md-12"> 

                    <table>
                        <?if(!empty($_1_u_rhevento_valor) and !empty($_1_u_rhevento_dataevento)){?>  
                    <tr>
                        <td align="right">Evento:</td>
                        <td> 
                            <?=$_1_u_rhevento_parcela?> de 
                            <input <?=$desab_parc?> name="_1_<?=$_acao?>_rhevento_parcelas" type="text" class="size5" value="<?=$_1_u_rhevento_parcelas?>" onchange="ativacampos()" >                        
                        </td>
                  
                        <td align="right" id="tdtipo" class="hidden">
                        Tipo Intervalo:
                        </td>
                        <td align="right" id="valtipo" class="hidden">
                        <select <?=$desabilitado?> id="rhevento_tipointervalo" name="rhevento_tipointervalo" >
                            <?
                            fillselect("select 'D','Dias' union select 'M','Mês' union select 'Y','Ano'");
                            ?>
                        </select>   
                        </td>                                          
                        <td align="right" id="tdintervalo" class="hidden" >Intervalo:</td>
                        <td id="valintervalo" class="hidden"><input  <?=$desabilitado?> id="rhevento_intervalo" name="rhevento_intervalo"  type="text" class="size5" value="" ></td>
                  
                        <td id="tdfimsemana" class="hidden" align="right">Gerar no final de semana?</td>
                        <td>
                            <input id="valfimsemana" class="hidden" title="Gerar no final de semana" name='rhevento_flgfimdesemana' type="checkbox" checked id="rhevento_flgfimdesemana" >
                        </td>                        
                    </tr>
                    <?}?>
                    </table>    
                    
                </div>
                <?}?>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <table>
                        <tr>
                            <td align="right">Data:</td>
                            <td>
                                <?
                                if(empty($_1_u_rhevento_dataevento)){
                                    $_1_u_rhevento_dataevento= date("d/m/Y");	
                                }
                                ?>
                                <input name="_1_<?=$_acao?>_rhevento_dataevento" class="calendario" size="10"	value="<?=$_1_u_rhevento_dataevento?>" vnulo>
                            </td>
                            <?
                            if($arrTev['formato']=='HIF' and $_1_u_rhevento_parcela<2 and empty($_1_u_rhevento_dataeventof)){
                            ?>
                            <td align="right">Data Fim:</td>
                            <td>
                                <?
                                if(empty($_1_u_rhevento_dataeventof)){
                                    $_1_u_rhevento_dataeventof= date("d/m/Y");	
                                }else{
                                    $disabled="disabled='disabled'";
                                }
                                ?>
                                <input <?=$disabled?> name="_1_<?=$_acao?>_rhevento_dataeventof" class="calendario" size="10"	value="<?=$_1_u_rhevento_dataeventof?>" vnulo>
                            </td>
                            
                            <?
                            }
                            ?>
                        </tr>
                        <?
                            if($arrTev['formato']=='HIF'  or $arrTev['formato']=='D' or $arrTev['formato']=='H' or $arrTev['formato']=='HI' ){
                        ?>
                        <tr>
                            <?if($arrTev['formato']=='D' or $arrTev['formato']=='H'){
                                if(empty($_1_u_rhevento_valor) and !empty($arrTev['valor']) ){
                                    $_1_u_rhevento_valor=$arrTev['valor'];
                                }    
                            ?>
                            <td align="right">Valor:</td>
                            <td><input type="text" name="_1_<?=$_acao?>_rhevento_valor"  size="10"	value="<?=$_1_u_rhevento_valor?>" vdecimal></td>
                            <?}else{?>
                            <td align="right">Hora:</td>
                            <td><input type="text" placeholder="00:00" name="_1_<?=$_acao?>_rhevento_hora"  size="10"	value="<?=$_1_u_rhevento_hora?>" ></td>
                     
                       <?
                            }
                            if($arrTev['formato']=='HIF'  and $_1_u_rhevento_parcela<2 and empty($_1_u_rhevento_horaf)){ 
                        ?>
                        
                             <td align="right">Hora Fim:</td>
                            <td><input <?=$disabled?> type="text" placeholder="00:00" name="_1_<?=$_acao?>_rhevento_horaf"  size="10"	value="<?=$_1_u_rhevento_horaf?>" ></td>
                       
                        <?
                                }
                        ?>
                         </tr>
                        <?
                                if($arrTev['flgponto']=='Y' and ($arrTev['formato']=='HI' or  $arrTev['formato']=='HIF')){
                                    
                         ?>
                        <tr>
                            <td align="right">Tipo:</td>
                            <td>
                                 <select   name="_1_<?=$_acao?>_rhevento_entsaida">
                                        <?fillselect("select 'E','Entrada' union select 'S','Saída'",$_1_u_rhevento_entsaida);?>		
                                </select>
                            </td>
                        </tr>
                        <?        
                                }
                            }else{
                                if(empty($_1_u_rhevento_valor) and !empty($arrTev['valor']) ){
                                    $_1_u_rhevento_valor=$arrTev['valor'];
                                }
                        ?>
                        <tr>
                            <td align="right">Valor:</td>
                            <td><input type="text" name="_1_<?=$_acao?>_rhevento_valor"  size="10"	value="<?=$_1_u_rhevento_valor?>" vdecimal></td>
                        </tr>
                                <?
                            }
                                ?> 
                    </table>
                </div>        
                <div class="col-md-8" >
                    <table>
                        <tr>
                            <td>Obs:</td>
                            <td><textarea name="_1_<?=$_acao?>_rhevento_obs"  style="width: 320px; height: 110px;" ><?=$_1_u_rhevento_obs?></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
<?
                    }
?>
    
    </div>
    </div>

<?
if(!empty($_1_u_rhevento_idrhevento)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_rhevento_idrhevento; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "rhevento"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
if(!empty($_1_u_rhevento_idrhevento)){
    if(!empty($_1_u_rhevento_idobjetoori) and $_1_u_rhevento_tipoobjetoori=='rhevento'){
        $_idrhevento=$_1_u_rhevento_idobjetoori;
    }else{
        $_idrhevento=$_1_u_rhevento_idrhevento;
    }
    $sql="SELECT 
                te.evento,e.idrhevento,e.dataevento,e.hora,e.valor,e.entsaida,IF(e.situacao='A', 'APROVADO', 'PENDENTE') AS situacao,e.status,e.parcela,e.parcelas
            FROM
                rhevento e,rhtipoevento te
            where te.idrhtipoevento=e.idrhtipoevento
            and  e.idobjetoori=".$_idrhevento."
            and e.tipoobjetoori='rhevento' order by e.dataevento,e.hora ";
    
    $res =  d::b()->query($sql) or die("Error ao buscar eventos vinculados.".$sql);
    $qtd= mysqli_num_rows($res);
    if($qtd>0){
?>
<div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Evento(s) Vinculado(s)</div>
        <div class="panel-body">
            <table class="table table-striped planilha">
                <tr>
                    <th>Evento</th>
                    <th>Parcela</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Valor</th>                    
                    <th>Situação</th>
                    <th>Status</th>
                    <th></th>
                    <th></th>
                </tr>
                
            
<?
        while($row=mysqli_fetch_assoc($res)){
?>      
                <tr>
                    <td><?=$row['evento']?></td>
                     <td><?=$row['parcela']?>/<?=$row['parcelas']?></td>
                    <td><?=dma($row['dataevento'])?></td>
                    <td><?=$row['hora']?></td>
                    <td><?=$row['valor']?></td>
                   
                    <td><?=$row['situacao']?></td>
                    <td><?=$row['status']?></td>
                    <td>
                        <?if($row['status']=="PENDENTE"){?>
                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="CB.post({objetos:'_ajax_u_rhevento_idrhevento=<?=$row["idrhevento"]?>&_ajax_u_rhevento_status=INATIVO',parcial:true})" title="Inativar"></i>
                        <?}?>
                    </td>
                    <td> <a class="fa fa-bars pointer hoverazul " title="Evento" onclick="janelamodal('?_modulo=rhevento&_acao=u&idrhevento=<?=$row["idrhevento"]?>')"></a></td>
                </tr>
<?
        }//while($row=mysqli_fetch_assoc($res)){
?>   
            </table>
        </div>
    </div>
</div>

<? 
    }//if($qtd>0){
}//if(!empty($_1_u_rhevento_idrhevento)){
?>


<script>
jFunc=<?=$jFunc?>;// autocomplete cliente    
//mapear autocomplete de funcionarios
jFunc = jQuery.map(jFunc, function(o, id) {
    return {"label": o.nome, value:id+""}
});

//autocomplete de clientes
$("[name*=_rhevento_idpessoa]").autocomplete({
    source: jFunc
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

jTev=<?=$jTev?>;// autocomplete cliente    
//mapear autocomplete de tipo evento
jTev = jQuery.map(jTev, function(o, id) {
    return {"label": o.evento, value:id+"","formato":o.formato}
});

//autocomplete de clientes
$("[name*=_rhevento_idrhtipoevento]").autocomplete({
    source: jTev
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.formato+"</span></a>").appendTo(ul);
        };
    }	
});


function criaevento(){
    if($("[name=rhevento_flgfimdesemana]").prop( "checked" )==false){
        var rhevento_flgfimdesemana ='N';
    }else{
        var rhevento_flgfimdesemana ='Y';
    }
    
    var str="&rhevento_tipointervalo="+$("[name=rhevento_tipointervalo]").val()+
            "&rhevento_intervalo="+$("[name=rhevento_intervalo]").val()+
            "&rhevento_flgfimdesemana="+rhevento_flgfimdesemana;
    CB.post({
        objetos: str              
        ,posPost: function(resp,status,ajax){
            if(status="success"){
                $("#cbModalCorpo").html("");
                $('#cbModal').modal('hide');
            }else{
                alert(resp);
            }
        }
    });
}


function ativacampos(){
    $('#rhevento_tipointervalo').prop('disabled', false);
    $('#rhevento_intervalo').prop('disabled', false);
      
    $('#tdtipo').removeClass( "hidden" );
    $('#valtipo').removeClass( "hidden" );
    $('#valintervalo').removeClass( "hidden" );
    $('#tdintervalo').removeClass( "hidden" );
    $('#tdfimsemana').removeClass( "hidden" );
    $('#valfimsemana').removeClass( "hidden" );
    
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>