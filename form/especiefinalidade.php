<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "especiefinalidade";
$pagvalcampos = array(
	"idespeciefinalidade" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from especiefinalidade where idespeciefinalidade = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
 <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
                <td align="right">Divisão:</td>
                <td>
                    <input name="_1_<?=$_acao?>_especiefinalidade_idespeciefinalidade" type="hidden"	value="<?=$_1_u_especiefinalidade_idespeciefinalidade?>">
                <!-- 
                    <input name="_1_<?=$_acao?>_especiefinalidade_especie" type="text"	value="<?=$_1_u_especiefinalidade_especie?>">
                </td>
                <td>
                -->    
                    <select   name="_1_<?=$_acao?>_especiefinalidade_idplantel" vnulo>
                        <?fillselect("SELECT idplantel,plantel from plantel where status='ATIVO' ".getidempresa("idempresa",$_GET["_modulo"])." order by plantel ",$_1_u_especiefinalidade_idplantel);?>		
                    </select>                   
                </td>
                 
                 <td align="right">Espécie:</td>
                <td>
                    <input name="_1_<?=$_acao?>_especiefinalidade_tipoespecie" type="text" value="<?=$_1_u_especiefinalidade_tipoespecie?>">
                </td>
                      
                <td align="right">Finalidade:</td>
                <td>
                    <input name="_1_<?=$_acao?>_especiefinalidade_finalidade" type="text" value="<?=$_1_u_especiefinalidade_finalidade?>">
                </td>
                <td align="right">Status:</td>
                <td>
                    <select   name="_1_<?=$_acao?>_especiefinalidade_status">
                        <?fillselect("select 'A','Ativo' union select 'I','Inativo' ",$_1_u_especiefinalidade_status);?>		
                    </select>
                </td>
            </table>
        </div>
        <div class="panel-body">
            <div class="col-md-12">
                <table>
                    <tr>
                        <td>Rótulo:</td>
                        <td>
                            <select   name="_1_<?=$_acao?>_especiefinalidade_rotulo" class="size25 form-control" vnulo>
                                <?fillselect(array("NUCLEO"=>"Núcleo","INTEGRACAO"=>"Integração","PRODUTO"=>"Produto","PROP"=>"Propriedade","FASE"=>"Fase","DESCRICAO"=>"Descrição"),$_1_u_especiefinalidade_rotulo);?>		
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Rótulo Resultado:</td>
                        <td>
                            <input name="_1_<?=$_acao?>_especiefinalidade_rotuloresultado" type="text" value="<?=$_1_u_especiefinalidade_rotuloresultado?>">
                        </td>
                    </tr>
                </table>
            </div>
<?
            if(!empty($_1_u_especiefinalidade_idespeciefinalidade)){
			$sqlu="SELECT 
            u.unidade, u.idunidade, o.idunidadeobjeto
        FROM
            unidade u
                LEFT JOIN
            unidadeobjeto o ON (o.idunidade = u.idunidade
                AND o.tipoobjeto = 'especiefinalidade'
                AND o.idobjeto = ".$_1_u_especiefinalidade_idespeciefinalidade.")
        WHERE
            u.status = 'ATIVO'
            ".getidempresa('u.idempresa',$_GET['_modulo'])."
            order by unidade;";
			$resu = d::b()->query($sqlu) or die("A Consulta das Unidades falhou : " . mysqli_error() . "<p>SQL: $sqlv");
?>
        <table>		
            <tr>
                <td colspan="6">
                    <div class="panel panel-default"> 
                        <?while ($rowu = mysqli_fetch_assoc($resu)){?>
                        <div class="col-md-2" style="font-size:11px">	
                            <?if(!empty($rowu['idunidadeobjeto'])){?>                   
                                <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retiraund(<?=$rowu['idunidadeobjeto']?>);" alt="Retirar Unidade">&nbsp;&nbsp;<?echo($rowu['unidade']);?></i>
                            <?}else{?>                    
                                <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer"  onclick="inseriund(<?=$rowu['idunidade']?>);" alt="Inserir Unidade">&nbsp;&nbsp;<?echo($rowu['unidade']);?></i>
                            <?}?>
                        </div>
                        <?}//while ($rowu = mysqli_fetch_assoc($resu))?>		
                    </div>
                </td>	
            </tr>
        </table>
<?
            }//if(!empty($_1_u_especiefinalidade_idespeciefinalidade)){
?>
           <table>     
               <tr>
                    <td align="right">Tipo Lanagro:</td>
                    <td>                        
                        <select   name="_1_<?=$_acao?>_especiefinalidade_tipoavelanagro">
                            <option value=""></option>
                         <?fillselect("select 'AVESTRUZ','Avestruz' union select
                            'BEZERRO','Bezerro' union select
                            'BOI','Boi' union select
                            'CACHACO','Cachaço' union select
                            'CAMUNDONGO','Camundongo' union select
                            'CODORNA','Codorna' union select
                            'GALINHA','Galinha' union select
                            'GALINHA D ANGOLA','Galinha d Angola' union select
                            'LEITAO','Leitão' union select
                            'LEITOA','Leitoa' union select
                            'NOVILHOA','Novilha(o)' union select
                            'OVO','Ovo' union select
                            'PERU','Peru' union select
                            'PORCA','Porca' union select
                            'VACA','Vaca'",$_1_u_especiefinalidade_tipoavelanagro);?>		
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">Idade em:</td>
                    <td>
                        <select   name="_1_<?=$_acao?>_especiefinalidade_calculoidade">
                         <?fillselect(array('D' => 'Dias', 'G' => 'Dias de Gestação', 'M' => 'Meses', 'S' => 'Semanas'), $_1_u_especiefinalidade_calculoidade);?>		
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">Gestação/Incubação:</td>
                    <td class="nowrap">
                        <input name="_1_<?=$_acao?>_especiefinalidade_gestacao" type="text" value="<?=$_1_u_especiefinalidade_gestacao?>"> Dias 
                    </td>
                </tr>
                <tr>
                    <td align="right">Mortalidade máxima (MAPA):</td>
                    <td class="nowrap">
                        <input name="_1_<?=$_acao?>_especiefinalidade_mortalidademapa" type="text" value="<?=$_1_u_especiefinalidade_mortalidademapa?>">
                    </td>
                </tr>
                <tr>
                    <td align="right">Idade máxima (MAPA):</td>
                    <td class="nowrap">
                        <input name="_1_<?=$_acao?>_especiefinalidade_idademapa" type="text" value="<?=$_1_u_especiefinalidade_idademapa?>">
                    </td>
                </tr>
            </table>
        </div>
    </div>
 </div>
</div>
<?if(!empty($_1_u_especiefinalidade_idespeciefinalidade)){?>
<div class="row ">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">Serviços da Espécie</div>
            <div class="panel-body">
<?
	$sql = "SELECT c.idservicobioterioconf,c.dia,s.rotulo
                        FROM servicobioterioconf c,servicobioterio s 
                        where c.tipoobjeto = 'especiefinalidade' 
                        and idobjeto = ".$_1_u_especiefinalidade_idespeciefinalidade." 
                        and s.idservicobioterio= c.idservicobioterio order by c.dia";

	$res = d::b()->query($sql) or die("A Consulta dos serviçoes relacionados falhou: " . mysqli_error() . "<p>SQL: $sql");
        $qtdr= mysqli_num_rows($res);
        if($qtdr>0){
?>
                <table class="table table-striped planilha "  >
                    <tr>
                        <th>Serviço</th>
                        <th>Dia</th>
                        <th></th>
                    </tr>
<?

            $i=1;
            while($r = mysqli_fetch_assoc($res)) {
	    $i=$i+1;
?>
                <tr>
                    <td>
                    <?=$r['rotulo']?>						
                    </td>
                    <td>
                        <input name="_<?=$i?>_u_servicobioterioconf_idservicobioterioconf" type="hidden" value="<?=$r["idservicobioterioconf"]?>">
                        <input name="_<?=$i?>_u_servicobioterioconf_dia" class="size5" type="text" value="<?=$r["dia"]?>">
                    </td>
                    <td align="center">	
                        <i onclick="cbpost({objetos:'_ajax_d_servicobioterioconf_idservicobioterioconf=<?=$r["idservicobioterioconf"]?>'})"  class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable"></i>
                    </td>							
                </tr>
<?
            }//while($rarea = mysqli_fetch_assoc($resarea)) {
		?>			
                </table>
<?
        }
?>
                <table>
                    <tr>
                        <td><input id="servicobioterio" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                    </tr>
                </table>  
            </div>
        </div>
    </div>
</div>
<?
if(!empty($_1_u_especiefinalidade_idespeciefinalidade)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_especiefinalidade_idespeciefinalidade; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "especiefinalidade"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?}

function getJServbioterio(){
	global $JSON;
	$s = "select idservicobioterio,rotulo 
                from servicobioterio 
                where status='ATIVO' order by rotulo";

	$rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($rts)) {
	    $arrtmp[$i]["value"]=$r["idservicobioterio"];
	    $arrtmp[$i]["label"]= $r["rotulo"];
	    $i++;
	}

	return $JSON->encode($arrtmp);    
}

$jServicobioterio="null";

if(!empty($_1_u_especiefinalidade_idespeciefinalidade)){
$jServicobioterio=getJServbioterio();
}
?>
<script>
jServicobioterio = <?=$jServicobioterio?>;

//Autocomplete de Setores vinculados
$("#servicobioterio").autocomplete({
	source: jServicobioterio
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.label;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
                            "_x_i_servicobioterioconf_idobjeto": $(":input[name=_1_"+CB.acao+"_especiefinalidade_idespeciefinalidade]").val()
			    ,"_x_i_servicobioterioconf_tipoobjeto":'especiefinalidade'
                            ,"_x_i_servicobioterioconf_idservicobioterio":ui.item.value
			}
			,parcial: true
		});
	}
});

function retiraund(inidunidadeobjeto){
	CB.post({
		objetos: "_x_d_unidadeobjeto_idunidadeobjeto="+inidunidadeobjeto
	});
}
function inseriund(inidund){
	CB.post({
		objetos: "_x_i_unidadeobjeto_idobjeto="+$("[name=_1_u_especiefinalidade_idespeciefinalidade]").val()+"&_x_i_unidadeobjeto_idunidade="+inidund+"&_x_i_unidadeobjeto_tipoobjeto=especiefinalidade"
	});
}
</script>