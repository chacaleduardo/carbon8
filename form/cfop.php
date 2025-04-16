<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "cfop";
$pagvalcampos = array(
	"idcfop" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from cfop where idcfop = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<script type="text/javascript" src="../inc/js/jscolor/jscolor.js"></script>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
	    <table>
	    <tr> 		    
		<td>
		    <input 
			    name="_1_<?=$_acao?>_cfop_idcfop" 
			    type="hidden" 			   
			    value="<?=$_1_u_cfop_idcfop?>" 
			    readonly='readonly'					>
		</td> 
	   
		<td>CFOP</td> 
		<td>
		    <input 
			    name="_1_<?=$_acao?>_cfop_cfop" 
			    type="text" 
			    value="<?=$_1_u_cfop_cfop?>" 
									>
		</td> 
                <!--
                <td>Tipo NFe:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_cfop_tpn">
			<?fillselect("select 0,'Entrada' union select 1,'Saà­da'",$_1_u_cfop_tpn);?>		
		    </select>
		</td> 
                -->
                <td>Estado MG:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_cfop_origem">
			<?fillselect("select 'DENTRO','Dentro' union select 'FORA','Fora'",$_1_u_cfop_origem);?>		
		    </select>
		</td> 
		<td>Status</td> 
		<td>
		    <select name="_1_<?=$_acao?>_cfop_status">
			<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_cfop_status);?>		
		    </select>
		</td> 
	    </tr>	    
	    </table>
	</div>
	 <div class="panel-body"> 
	    <table>
	    <tr> 
		<td>Nat. Oper.:</td> 
		<td>
                    
            <select name="_1_<?=$_acao?>_cfop_idnatop">
			<?fillselect("select idnatop,natop from natop where status  = 'ATIVO' order by natop;",$_1_u_cfop_idnatop);?>		
		    </select>
		</td> 
		<td>
			<?if($_1_u_cfop_idnatop){?>
				<a class="fa fa-bars pointer hoverazul" title="Cadastro de Natureza da operecao" onclick="janelamodal('?_modulo=natop&_acao=u&idnatop=<?=$_1_u_cfop_idnatop?>')"></a>
			<?}?>
		</td>
		<td><a class="fa fa-plus-circle fa-1x pointer hoverazul" title="Nova Natureza da operecao" onclick="janelamodal('?_modulo=natop&_acao=i')"></a></td>
	    </tr>
            <!--
	    <tr> 
		<td>Finalidade:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_cfop_finnfe">
			<?fillselect("select 1,'NF-e Normal' union select 4,'NF-e Devolução'",$_1_u_cfop_finnfe);?>		
		    </select>
		</td> 
	    </tr>
            -->
	    <tr>
		<td>Definição:</td> 
		<td>	
                    <textarea  rows="4"  cols="60"  style=font-size:medium;  name="_1_<?=$_acao?>_cfop_definicao" ><?=$_1_u_cfop_definicao?></textarea>
                </td> 
	    </tr>
	    
	    </table>
	 </div>
    </div>
</div>

<?
if(!empty($_1_u_cfop_idcfop)){
?>

	<div class="col-md-12">
	    <div class="panel panel-default">  
	    <div class="panel-heading">CFOPs de Entrada</div>
	    <div class="panel-body">
<?
	 $sql1 = "select  *
										from cfopentrada
											where idcfop = ".$_1_u_cfop_idcfop." order by cfop";

	$res1 = d::b()->query($sql1) or die("A Consulta de Conta itens falhou :".mysql_error()."<br>Sql:".$sql1);	
?>	
	    <table class="table table-striped planilha" > 
		<tr><td>CFOP</td><td>Fim NFe</td></tr>
<?
		$i=9977;
		while($row1 = mysqli_fetch_assoc($res1)){
		    $i++;	
?>
		 <tr><td><input name="_<?=$i?>_<?=$_acao?>_cfopentrada_idcfopentrada"type="hidden" value="<?=$row1["idcfopentrada"]?>"><input 
			    name="_<?=$i?>_<?=$_acao?>_cfopentrada_cfop" 
			    type="text" 
			    size="2"
			    value="<?=$row1['cfop']?>" ></td>
		     <td>
			
			<select name="_<?=$i?>_<?=$_acao?>_cfopentrada_fimnfe" > 
				<?fillselect("select 'IMOBILIZADO','Imobilizado' UNION select 'INDUSTRIA','Industria' union select 'CONSUMO','Consumo'", $row1['fimnfe']);?>
			</select>			   
		    </td>
		  	
		    <td align="center">	
			<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('cfopentrada',<?=$row1["idcfopentrada"]?>)" alt="Excluir !"></i>
		    </td>							
		</tr>		
<?
		}
?>
		<tr>
		    <td colspan="5">
			    <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novo('cfopentrada')" alt="Inserir novo!"></i>
		    </td>
		</tr>
	    </table>
	    </div>
	</div>
	</div>
<?
}
?>

<?
if(!empty($_1_u_cfop_idcartao)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_cfop_idcartao; // trocar p/ cada tela o id da tabela
	require '../form/viewAssinaturas.php';
}
	$tabaud = "cfop"; //pegar a tabela do criado/alterado em antigo
	require '../form/viewCriadoAlterado.php';
?>


<script >
function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
        objetos: "_x_d_"+tab+"_id"+tab+"="+inid
        });
    }
    
}

function novo(inobj){
    CB.post({
	objetos: "_x_i_"+inobj+"_idcfop="+$("[name=_1_u_cfop_idcfop]").val()
    });
    
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>