<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

// |||||||||||||||||||||||||||||||||||||| 04/12/2019 POR GABRIEL TIBURCIO ||||||||||||||||||||||||||||||||||||||||||||||||| //

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "dashgrupo";
$pagvalcampos = array(
	"iddashgrupo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from dashgrupo where iddashgrupo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once("./controllers/_modulo_controller.php");
?>
<link rel="stylesheet" href="./../form/css/padrao.css" />
<div class="row">
    <div class="col-xs-12 px-0">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row d-flex flex-between m-0 flex-wrap">
					<? if($_1_u_dashgrupo_iddashgrupo) { ?>
						<input 
							name="_1_<?=$_acao?>_dashgrupo_iddashgrupo" 
							type="hidden" 			   
							value="<?=$_1_u_dashgrupo_iddashgrupo?>" 
							readonly='readonly'					>
					<? } ?>
					<div class="col-xs-6 col-md-3 form-group">
						<label for="" class="text-gray-10">Rótulo</label>
						<input name="_1_<?=$_acao?>_dashgrupo_rotulo"  type="text" value="<?=$_1_u_dashgrupo_rotulo?>" class="form-control" />
					</div>
					<div class="col-xs-6 col-md-3 form-group">
						<label for="" class="text-gray-10">Ordem</label>
						<input 
							name="_1_<?= $_acao ?>_dashgrupo_ordem" 
							type="text" 
							value="<?= $_1_u_dashgrupo_ordem ?>"
							class="form-control" />
					</div>
					<div class="col-xs-6 col-md-3 form-group">
						<label for="" class="text-gray-10">Modulo</label>
						<select name="_1_<?=$_acao?>_dashgrupo_modulo" class="form-control">
							<option value="">Selecionar modulo</option>
							<?fillselect(_moduloController::buscarModulosPorTipo('DROP', true),$_1_u_dashgrupo_modulo);?>		
						</select>
					</div>
					<div class="col-xs-6 col-md-3 form-group">
						<label for="" class="text-gray-10">Status</label>
						<select name="_1_<?=$_acao?>_dashgrupo_status" class="form-control">
							<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_dashgrupo_status);?>		
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?
 if(!empty($_1_u_dashgrupo_iddashgrupo)){
        $sqle="select iddashpanel, concat(e.sigla,' - ', paneltitle) as paneltitle , p.status, ordem from dashpanel p join empresa e on e.idempresa = p.idempresa where iddashgrupo = '".$_1_u_dashgrupo_iddashgrupo."' order by  p.status, e.idempresa,paneltitle,ordem";
        $rese = d::b()->query($sqle) or die("A Consulta do dashcard falhou :".mysql_error()."<br>Sql:".$sqle);
        $qtde=mysqli_num_rows($rese);
        if($qtde>0){
    ?>
        <div class="col-md-4">
           <div class="panel panel-default">   
               <div class="panel-heading" >Painéis</div>
               <div class="panel-body">
                   <table  class="table table-striped planilha "  > 
				   		<tr>
						   	<td style="text-align:left">Nome</td>
							   <td style="text-align:left">Ordem</td>
							<td style="text-align:right">Status</td>
						
						</tr>
                       <?
                       while($rowe=mysqli_fetch_assoc($rese)){
                       ?>
                       <tr>
					   		<td style="text-align:left"><a href="?_modulo=dashpanel&_acao=u&iddashpanel=<?=$rowe['iddashpanel']?>"><?=$rowe['paneltitle']?></a></td>
							   <td style="text-align:left"><?=$rowe['ordem']?></td>
						   	<td style="text-align:right"><?=$rowe['status']?></td>
                          
                           
                       </tr>
                        <?
                       }
                        ?>
						
                   </table>
               </div>            
           </div>   
        </div> 
        <?        
        }// if($qtde>0){
 }
        ?>
<?
if(!empty($_1_u_dashgrupo_iddashgrupo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_dashgrupo_iddashgrupo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "dashgrupo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
