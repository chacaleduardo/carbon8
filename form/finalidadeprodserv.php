<!-- Ciar Paramêtros para vincular Produtos e Serviços. Segunda Div lista os produtos. Criado em 10/01/2020 - Lidiane -->
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
$pagvaltabela = "finalidadeprodserv";
$pagvalcampos = array(
	"idfinalidadeprodserv" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "select * from finalidadeprodserv where idfinalidadeprodserv = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <table>
                <tr> 		    
                    <td>
                        <input name="_1_<?=$_acao?>_finalidadeprodserv_idfinalidadeprodserv" type="hidden" value="<?=$_1_u_finalidadeprodserv_idfinalidadeprodserv?>" readonly='readonly'>
                    </td> 
                    <td>Finalidade</td> 
                    <td>
                        <input name="_1_<?=$_acao?>_finalidadeprodserv_finalidadeprodserv" type="text" value="<?=$_1_u_finalidadeprodserv_finalidadeprodserv?>" class="size30">
                    </td>
                    <td>Tipo</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_finalidadeprodserv_tipoconsumo">
                            <?fillselect("select 'consumo','Consumo' union
                                            select 'comercio','Comercio' union
                                            select 'faticms','Industria' union 
                                            select 'imobilizado','Imobilizado' union
                                            select 'outro','Outros'",$_1_u_finalidadeprodserv_tipoconsumo);?>		
                        </select>
                    </td> 
                    <td>Status</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_finalidadeprodserv_status">
                            <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_finalidadeprodserv_status);?>		
                        </select>
                    </td> 
                </tr>	    
            </table>
        </div>
    </div>
</div>

 <?/*
if (!empty($_1_u_finalidadeprodserv_idfinalidadeprodserv)){
$sql = "SELECT PS.idprodserv, PS.tipo, PS.codprodserv, PS.descr, PS.descrcurta, PS.status, FPS.finalidadeprodserv 
        FROM finalidadeprodserv FPS INNER JOIN prodservfinprodserv PSFPS ON FPS.idfinalidadeprodserv =  PSFPS.idfinalidadeprodserv
     INNER JOIN prodserv PS ON PSFPS.idprodserv = PS.idprodserv
        WHERE FPS.idfinalidadeprodserv = ".$_1_u_finalidadeprodserv_idfinalidadeprodserv." ORDER BY PS.descr";
$res = d::b()->query($sql) or die("A Consulta falhou :".mysqli_error()."<br>Sql:".$sql); 
$rownum1= mysqli_num_rows($res);
if($rownum1>0){
?>
    <div class="col-md-12">
        <div class="panel panel-default">      
            <div class="panel-heading" data-toggle="collapse" href="#contatoInfo">(<?=$rownum1?>)- Produtos / Serviços Vinculados <?=$arrpessoa['razaosocial']?></div>
            <?
            if($rownum1>0){
            ?>
                <table class="table table-striped planilha collapse" id="contatoInfo" >  
                    <tr>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Descrição Curta</th>
                        <th>Status Produto / Serviço</th>
                        <th>Finalidade</th>
                        <th>Editar</th>
                    </tr>	 

                    <?	
                    while($row = mysqli_fetch_assoc($res)){
                        ?>                   		
                        <tr class="res">
                            <td nowrap><?=$row["tipo"]?></td>
                            <td nowrap><?=$row["codprodserv"]?></td>
                            <td nowrap><?=$row["descr"]?></td>
                            <td nowrap><?=$row["descrcurta"]?></td>
                            <td nowrap><?=$row["status"]?></td>
                            <td nowrap><?=$row["finalidadeprodserv"]?></td>
                            <td><i class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=prodserv&amp;_acao=u&amp;idprodserv=<?=$row["idprodserv"]?>');"></i></td>
                        </tr>
                    <?
                    }//while($row = mysqli_fetch_array($res)){
                    ?>
                </table>
            <?
            }//if($rownum1>0){
            ?>
        </div> 
    </div>  
<? }}
*/ ?>

<div class="col-md-12">
    <?$tabaud = "finalidadeprodserv";?>
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

<script>
	function altcheck(vtab,vcampo,vid,vcheck){
		CB.post({
			objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
		}); 
	}
</script>