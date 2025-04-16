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
$pagvaltabela = "plantel";
$pagvalcampos = array(
	"idplantel" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from plantel where idplantel = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" >
            <div class="panel-heading">
                <table>
                <tr> 		    
                    <td>
                        <input name="_1_<?=$_acao?>_plantel_idplantel" type="hidden" value="<?=$_1_u_plantel_idplantel?>" readonly='readonly'>
                    </td> 	   
                    <td  align="right">Divisão:</td> 
                    <td>
                        <input name="_1_<?=$_acao?>_plantel_plantel" type="text" value="<?=$_1_u_plantel_plantel?>">
                    </td>
                    <td align="right">Unidade:</td>
                    <td class="nowrap">
                        <select name="_1_<?=$_acao?>_plantel_idunidade" class="size25" vnulo>
                        <option value=""></option>
                        <?
                        fillselect("select idunidade,unidade from unidade where status = 'ATIVO' and idempresa =".cb::idempresa()." order by unidade",$_1_u_plantel_idunidade);
                        ?>
                        </select>
                    </td>
                    <td  align="right">Status:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_plantel_status">
                            <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_plantel_status);?>		
                        </select>
                    </td> 
                </tr>	    
                </table>
            </div>
            <div class="panel-body"> 
<?
if(!empty($_1_u_plantel_idplantel)){
    $sql="SELECT p.idprodserv,ifnull(p.descrcurta,p.descr) as descr,
        concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.*
        FROM prodservformula f 
            join prodserv p on(p.idprodserv =f.idprodserv and p.venda='Y')
        where f.idplantel=".$_1_u_plantel_idplantel." 
        and f.status='ATIVO' order by descr";
    $res = d::b()->query($sql) or die("A Consulta dos produtos falhou : " . mysqli_error() . "<p>SQL: $sql");

    $rownum= mysqli_num_rows($res);
    if($rownum>0){
        ?>
    <table class="table table-striped planilha " >
        <tr>
            <th>Produto</th>	   
            <th>Formula</th>   
            <th>Valor R$</th>   
            <th>Comissão %  <input title='Alterar a comissão' style='background-color: white'  type="text" id='comissaogest'	value="" class='size3' onchange='alteracom(this);' >
            <a class="fa fa-download verde pointer hoverazul" title="Alterar valores" onclick="alteracom(this);"></a>
            </th>      
           
        </tr>
<?
            $i=1;
	    while ($row = mysqli_fetch_assoc($res)){
                $i=$i+1;
?>
        <tr>
            <td>
                  <input name="_<?=$i?>_u_prodservformula_idprodservformula" type="hidden"	value="<?=$row['idprodservformula']?>"	readonly='readonly'>
                <a class=" pointer hoverazul" title="Cadastro de Produto" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idprodserv"]?>')"> <?=$row['descr']?></a>
            </td>
            <td><?=$row['rotulo']?></td>
            <td><input name="_<?=$i?>_u_prodservformula_vlrvenda" placeholder="0.00" type="text" class="size5" value="<?=$row['vlrvenda']?>"	></td>
            <td><input name="_<?=$i?>_u_prodservformula_comissao" placeholder="0.00" type="text" class="size5 valcomissao" value="<?=$row['comissao']?>"	></td>
             
        </tr>
        
<?        
            }
?>
    </table>
<?          
    }
}
?>

            </div>
        </div>
    </div>
</div>
<?
    $tabaud = "plantel"; //pegar a tabela do criado/alterado em antigo
    require '../form/viewCriadoAlterado.php';
?>
<script>
function alteracom(vthis){
    var com = $("#comissaogest").val();
    $('.valcomissao').val(com);
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>