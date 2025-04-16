<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
$idpessoa = $_GET['idpessoa'];
$idprodservforn=$_GET['idprodservforn']; 
//die();

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodserv";
$pagvalcampos = array(
	"idprodserv" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from prodserv where idprodserv = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>



<script >
function novoobjeto(inobj){
    CB.post({
	objetos: "_x_i_"+inobj+"_idprodserv="+<?=$_1_u_prodserv_idprodserv?>+"&_x_i_"+inobj+"_idpessoa="+<?=$idpessoa?>+"&_x_i_"+inobj+"_idprodservforn="+<?=$idprodservforn?>
    });
    
}


//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//@ sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;

</script>
<style>
</style>

<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">Certificado de Análise</div>
	<div class="panel-body">

<?if($_1_u_prodserv_tipo == 'PRODUTO' and !empty($_1_u_prodserv_idprodserv)){?>	
	
		
		<?	
					$sql = "select *
					from analiseqst
					where idprodserv = ".$_1_u_prodserv_idprodserv." and idpessoa=".$idpessoa." and idprodservforn=".$idprodservforn." order by  ordem";
					
					$res = mysql_query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");
					$qtdrows2= mysql_num_rows($res);
		
					if($qtdrows2 > 0){	
					?><table class="normal">
						<tr class="header">
							<td align="center">Ordem</td>
							<td align="center">Teste</td>	
							<td align="center">Especificação</td>							
							<td align="center">Status</td>
							
						</tr>
		<?			
						$j=999;
						$i=2;
						while ($row = mysql_fetch_array($res)) {
							$i=$i+1;
		?>
					<tr class="respreto">
						<td><input  name="_<?=$i?>_u_analiseqst_ordem"  size="1" type="text"	value="<?=$row["ordem"]?>"></td>
						<td><input  name="_<?=$i?>_u_analiseqst_idanaliseqst" type="hidden"	value="<?=$row["idanaliseqst"]?>">
							<input  name="_<?=$i?>_u_analiseqst_qst"  size="50" type="text"	value="<?=$row["qst"]?>">
							
						</td>
						<td><input  name="_<?=$i?>_u_analiseqst_especificacao"  size="30" type="text"	value="<?=$row["especificacao"]?>"></td>						
						<td><select name="_<?=$i?>_u_analiseqst_status">
							<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$row["status"]);?></select>
						</td>								
					</tr>
			<?			} //end while?>
					  

		<?
					}
		?>			  
	
			<tr>
				<td colspan="5">
					<i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novoobjeto('analiseqst')" alt="Inserir novo fornecedor!"></i>
				</td>
			</tr>
			<tr>
				
			</tr>
		</table>	
			
	</div>
</div>
</div>
</div>

<?
	}
?>	