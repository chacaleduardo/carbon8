<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "interpretacao";
$pagvalcampos = array(
	"idinterpretacao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from interpretacao where idinterpretacao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


function getJteste(){
	global $JSON, $_1_u_interpretacao_idinterpretacao;
	$s = "select t.idtipoteste,t.sigla from vwtipoteste t
                                where  t.status = 'ATIVO'
                                and not exists(
                                        select 1 
                                        from intertipoteste mtm 
                                        where mtm.idtipoteste = t.idtipoteste
                                        and mtm.idinterpretacao = ".$_1_u_interpretacao_idinterpretacao." 
                                )
                                order by sigla";

	$rts = d::b()->query($s) or die("getJteste: ". mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($rts)) {
	    $arrtmp[$i]["value"]=$r["idtipoteste"];
	    $arrtmp[$i]["label"]= $r["sigla"];
	    $i++;
	}

	return $JSON->encode($arrtmp);    
}


function listatesteinter(){
    	global $_1_u_interpretacao_idinterpretacao;
	$s = "select it.idintertipoteste,tt.sigla,tt.idtipoteste
                from intertipoteste it,vwtipoteste tt
		where tt.idtipoteste = it.idtipoteste
		and it.idinterpretacao =".$_1_u_interpretacao_idinterpretacao." order by sigla";

	$rts = d::b()->query($s) or die("listatestes: ". mysqli_error(d::b()));

	
	while ($r = mysqli_fetch_assoc($rts)) {
		$title="Editar Teste";
        echo "<tr><td><a title='".$title."' target='_blank' href='?_modulo=prodserv&_acao=u&idprodserv=".$r["idtipoteste"]."'>".$r["sigla"]."</a></td>
                <td align='center'>	
                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retirateste(".$r['idintertipoteste'].")' title='Excluir!'></i>
                </td>
                </tr>";
    }
	
}
?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
                <td> Tà­tulo:</td>
                <td>
                    <input name="_1_<?=$_acao?>_interpretacao_idinterpretacao" type="hidden" value="<?=$_1_u_interpretacao_idinterpretacao?>" readonly='readonly'>	
                    <input name="_1_<?=$_acao?>_interpretacao_titulo"  class="size30" type="text" value="<?=$_1_u_interpretacao_titulo?>" >
                </td>
                <td>Status:</td>
                <td>
                     <select name="_1_<?=$_acao?>_interpretacao_status" style="background-color: #EFEFEE;font-weight:bold;font-size:12px;">
                                <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_interpretacao_status);?>		
                     </select>
                </td>
            </table>
           
                
        </div>
        <div class="panel-body"> 
        <table>
        <tr> 
            <td>Frase:</td> 
            <td colspan="5"><textarea cols="50" rows="4" name="_1_<?=$_acao?>_interpretacao_frase"  vnulo><?=$_1_u_interpretacao_frase?></textarea></td>
        </tr>     
        </table>
        </div>
    </div>
    </div>
</div>

<?if(!empty($_1_u_interpretacao_idinterpretacao)){?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Teste(s) Relacionado(s)</div>
         <div class="panel-body">
            <table>
                <tr>
                    <td><input id="idprodserv" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                </tr>
            </table>
            <table class='table-hover'>
                <tbody>
                 <?=listatesteinter()?>
               </tbody>
            </table>
            <hr>        
 		
	</div>	
    </div>
    </div>
</div>
<?
}
$jTeste="null";

if(!empty($_1_u_interpretacao_idinterpretacao)){    
    $jTeste=getJteste();
}
$tabaud = "interpretacao";
require 'viewCriadoAlterado.php';
?>


<script>

jTeste = <?=$jTeste?>;

//Autocomplete de funcionarios vinculados
$("#idprodserv").autocomplete({
	source: jTeste
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
				"_x_i_intertipoteste_idinterpretacao":	$(":input[name=_1_"+CB.acao+"_interpretacao_idinterpretacao]").val()
                               ,"_x_i_intertipoteste_idtipoteste":	ui.item.value
			}
			,parcial: true
		});

	}
});

function retirateste(inidintertipoteste){
    CB.post({
	    objetos: "_x_d_intertipoteste_idintertipoteste="+inidintertipoteste
	    ,parcial:true
	});    
}


</script>
