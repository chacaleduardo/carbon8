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
$pagvaltabela = "natop";
$pagvalcampos = array(
	"idnatop" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from natop where idnatop = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
	    <table>
	    <tr> 		    
		<td>
		    <input 
			    name="_1_<?=$_acao?>_natop_idnatop" 
			    type="hidden" 			   
			    value="<?=$_1_u_natop_idnatop?>" 
			    readonly='readonly'					>
		</td> 
	   
		<td>Natureza OP:</td> 
		<td>
		    <input class="size60"
			    name="_1_<?=$_acao?>_natop_natop" 
			    type="text" 
			    value="<?=$_1_u_natop_natop?>" 
									>
		</td> 		 
		<td>Tipo:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_natop_natoptipo">
			<option value=""></option>
			<?fillselect("select 'bonificacao','Bonificação' union select 'compra','Compra' union select 'devolucao','Devolução' union select 'remessa','Remessa' union select 'venda','Venda'  union select 'transferencia','Transferência' union select 'estorno','Estorno de Crédito' ",$_1_u_natop_natoptipo);?>		
		    </select>
		</td>
        
		<td>Status</td> 
		<td>
		    <select name="_1_<?=$_acao?>_natop_status">
			<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_natop_status);?>		
		    </select>
		</td> 
	    </tr>	    
	    </table>
	</div>
	 <div class="panel-body"> 
		 <table>
			 <tr>
				<td>Finalidade:</td> 
				<td>
					<select name="_1_<?=$_acao?>_natop_finnfe">
					<?fillselect("select '1','[1]-NF-e normal' union select '2','[2]-NF-e complementar' union select '3','[3]-NF-e de ajuste' union select '4','[4]-Devolução de mercadoria'",$_1_u_natop_finnfe);?>		
					</select>
				</td> 
				<td>Tipo Operação:</td> 
				<td>
					<select name="_1_<?=$_acao?>_natop_tpnf">
					<?fillselect("select '0','Entrada' union select '1','Saída'",$_1_u_natop_tpnf);?>		
					</select>
				</td> 
			 </tr>
		 </table>
	 </div>
	</div>
</div>

<?
if(!empty($_1_u_natop_idnatop)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_natop_idnatop; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "natop"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>



<?/*
    <div class="col-md-12">
     <?$tabaud = "natop";?>
    <div class="panel panel-default">		
        <div class="panel-body">
            <div class="row col-md-12">		
                <div class="col-md-1 nowrap">Criado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                <div class="col-md-1 nowrap">Criado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
            </div>
            <div class="row col-md-12">            
                <div class="col-md-1 nowrap">Alterado Por:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                <div class="col-md-1 nowrap">Alterado Em:</div>     
                <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
            </div>
        </div>
    </div>
    </div>
	*/?>