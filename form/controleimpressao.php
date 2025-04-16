<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/controleimpressao_controller.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "controleimpressao";
$pagvalcampos = array(
	"idcontroleimpressao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from controleimpressao where idcontroleimpressao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading"> Controle de Versão</div>
        <div class="panel-body"> 
        <table>
        <tr> 
            <td></td> 
            <td><input name="_1_<?=$_acao?>_controleimpressao_idcontroleimpressao" type="hidden" value="<?=$_1_u_controleimpressao_idcontroleimpressao?>"	readonly='readonly'></td> 
        </tr>
        <tr> 
            <td></td> 
            <td><input name="_1_<?=$_acao?>_controleimpressao_idempresa" type="hidden"	value="<?=$_1_u_controleimpressao_idempresa?>" vnulo></td> 
        </tr>
        <?
        if(!empty($_1_u_controleimpressao_numerorps)){
            $rowc=ControleImpressaoController::buscarNotaFiscalPorNumeroRPS($_1_u_controleimpressao_numerorps);
        ?>
        <tr> 
            <td align="right">Nº NFe:</td> 
            <td><?=$rowc['nnfe']?></td> 
        </tr>

        <tr>
            <td align="right">Cliente:</td> 
            <td><?=$rowc['nome']?></td> 
        </tr>
        <?
        }else{
        ?>
        <tr> 
            <td align="right">Id:</td> 
            <td><?=$_1_u_controleimpressao_idregistro?></td> 
        </tr>
		 <tr> 
            <td align="right">Exercício:</td> 
            <td><?=$_1_u_controleimpressao_exercicio?></td> 
        </tr>
        <?}?>
        <tr> 
            <td align="right">Oficial:</td> 
            <td><?=$_1_u_controleimpressao_oficial?></td> 
        </tr>
        <tr> 
            <td align="right">Via:</td> 
            <td><?=$_1_u_controleimpressao_via?></td> 
        </tr>
        <tr> 
            <td align="right">Status:</td> 
            <td><?=$_1_u_controleimpressao_status?></td> 
        </tr>
        <tr> 
            <td align="right">Por:</td> 
            <td><?=$_1_u_controleimpressao_criadopor?></td> 
        </tr>
        <tr> 
            <td align="right">Em:</td> 
            <td><?=$_1_u_controleimpressao_criadoem?></td> 
        </tr>
        </table>
        </div>
    </div>
    </div>
</div>
    <?$res = ControleImpressaoController::buscarItensPorControleDeImpressao($_1_u_controleimpressao_idcontroleimpressao);
    $disp = count($res);

    if($disp>0){?>
        <div class="row">
            <div class="col-md-12" >
            <div class="panel panel-default" >
                <div class="panel-heading"> Versàµes Impressas</div>
                <div class="panel-body">

            <table class="table table-striped planilha">
            <tr class="header">
                    <th align="center">Registro</th>
                    <th align="center">Teste</th>
                    <th>Status</th>
                    <th>Via</th>
                    <th>Oficial</th>
                    <th>Por</th>
                    <th>Em</th>
                    <th>
                        <button  type="button" class="btn btn-danger btn-xs inativar" onclick="liberartodos(<?=$_1_u_controleimpressao_idcontroleimpressao?>)" title="Inativar Todos">
                            <i class="fa fa-circle"></i>Inativar Todos
                        </button>
                    </th>
            </tr>
        <?

            foreach($res as $k => $row){
                if($row["oficial"]=='S'){
                    $oficial = 'SIM';
                }else{
                    $oficial = 'NÃO';
                }
        ?>	
                <tr class="respreto">
                <td align="center"><?=$row["idregistro"]?></td>
                <td align="center"><?=$row["exercicio"]?></td>
                <td><?=$row["descr"]?></td>
                <td><?=$row["status"]?></td>
                <td><?=$row["via"]?></td>
                <td><?=$oficial?></td>
                <td><?=$row["criadopor"]?></td>
                <td><?=$row["criadoem"]?></td>
            <?if($row["status"]=='ATIVO'){?>	
                <td>
                        <button  type="button" class="btn btn-danger btn-xs inativar" onclick="inativa(<?=$row["idcontroleimpressaoitem"]?>,<?=$_1_u_controleimpressao_idcontroleimpressao?>)" title="Inativar">
                            <i class="fa fa-circle"></i>Inativar
                        </button>
                        </td>
            <?}?>	
                
            </tr>


        <?
            }
        ?>
                </div>
            </div>
            </div>
        </div>
    <?}?>
<?
require_once(__DIR__."/js/controleimpressao_js.php");
require_once '../inc/php/readonly.php';
?>