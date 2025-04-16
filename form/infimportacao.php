<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$idnfitem = $_GET['idnfitem'];

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nfitemimport";
$pagvalcampos = array(
	"idnfitemimport" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from nfitemimport where idnfitemimport = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_1_u_nfitemimport_idnfitem) and !empty($idnfitem)){
    $_1_u_nfitemimport_idnfitem=$idnfitem;
}elseif(empty($_1_u_nfitemimport_idnfitem) and empty($idnfitem)){
    die("Falta informa a NF de origem...");
}
?>
<div class='row'>
<div class="col-md-8">
    <div class="panel panel-default" >
        <div class="panel-heading">Declaração de Importação</div>
	 <div class="panel-body"> 
	    <table>
            <tr>
                <td>Status:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_nfitemimport_status">
			<?fillselect("select 'ATIVO','Ativo' union
                                    select 'INATIVO','Inativo'",$_1_u_nfitemimport_status);?>		
		    </select>
		</td> 
            </tr>
	    <tr> 		    
		<td>
		    <input name="_1_<?=$_acao?>_nfitemimport_idnfitemimport" type="hidden" value="<?=$_1_u_nfitemimport_idnfitemimport?>" readonly='readonly'>
                    <input name="_1_<?=$_acao?>_nfitemimport_idnfitem" type="hidden" value="<?=$_1_u_nfitemimport_idnfitem?>" readonly='readonly'>
                    N. Doc. de Importação:
                </td> 
                <td>
                     <input name="_1_<?=$_acao?>_nfitemimport_ndi" type="text" value="<?=$_1_u_nfitemimport_ndi?>" vnulo>
                </td>
            </tr>
            <tr>
                <td>Data de Registro:</td>
                <td><input class="calendario size8" name="_1_<?=$_acao?>_nfitemimport_ddi" type="text" value="<?=$_1_u_nfitemimport_ddi?>" vnulo></td>
            </tr>
            <tr>
                <td>Local de desembaraço:</td>
                <td><input name="_1_<?=$_acao?>_nfitemimport_xlocdesemb" type="text" value="<?=$_1_u_nfitemimport_xlocdesemb?>" vnulo></td>
            </tr>
            <tr>
                <td>UF  Desembaraço:</td>
                <td><input class='size4' name="_1_<?=$_acao?>_nfitemimport_ufdesemb" type="text" value="<?=$_1_u_nfitemimport_ufdesemb?>" vnulo></td>
            </tr>
            <tr>
                <td>Data do Desembaraço:</td>
                <td><input  class="calendario size8" name="_1_<?=$_acao?>_nfitemimport_ddesemb" type="text" value="<?=$_1_u_nfitemimport_ddesemb?>" vnulo></td>
            </tr>
            <tr>
                <td>Via de transporte internacional:</td> 
		<td>
		    <select name="_1_<?=$_acao?>_nfitemimport_tpviatransp" vnulo>
			<?fillselect("select '1','1-Marí­tima' union
                                    select '2','2-Fluvial' union
                                    select '3','3-Lacustre' union
                                    select '4','4-Aérea' union
                                    select '5','5-Postal' union
                                    select '6','6-Ferroviária' union
                                    select '7','7-Rodoviária' union
                                    select '8','8-Conduto / Rede Transmissão' union
                                    select '9','9-Meios Próprios' union
                                    select '10','10-Entrada / Saí­da ficta' union
                                    select '11','11-Courier' union
                                    select '12','12-Handcarry'",$_1_u_nfitemimport_tpviatransp);?>		
		    </select>
		</td> 
            </tr>
            <?if($_1_u_nfitemimport_tpviatransp==1){?>
            <tr>
                <td> <i class="fa fa-info-circle laranja fa-1x" title="Adicional ao Frete para Renovação da Marinha Mercante"></i>AFRMM:</td>
                <td>
                    <input name="_1_<?=$_acao?>_nfitemimport_vafrmm" type="text" value="<?=$_1_u_nfitemimport_vafrmm?>">
                   
                </td>
            </tr>
<?
            }
?>
            <tr>
                <td>Forma de importação :</td> 
		<td>
		    <select name="_1_<?=$_acao?>_nfitemimport_tpintermedio" vnulo>
			<?fillselect("select '1','1-Importação por conta própria' union
                                        select '2','2-Importação por conta e ordem' union
                                        select '3','3-Importação por encomenda'",$_1_u_nfitemimport_tpintermedio);?>		
		    </select>
		</td> 
            </tr>
            <tr>
                <td>Código do Exportador:</td>
                <td><input name="_1_<?=$_acao?>_nfitemimport_cexportador" type="text" value="<?=$_1_u_nfitemimport_cexportador?>" vnulo></td>
            </tr>
            <tr>
                <td>Código do fabricante :</td>
                <td><input name="_1_<?=$_acao?>_nfitemimport_cfabricante" type="text" value="<?=$_1_u_nfitemimport_cfabricante?>" vnulo></td>
            </tr>
            </table>
         </div>
    </div>
</div>
<div class="col-md-4">
    <div class="panel panel-default" >
        <div class="panel-heading">Imposto de Importação</div>
        <div class="panel-body">
            <table>
                <tr>
                    <td>Base de Calc.:</td>
                    <td><input class='size8' name="_1_<?=$_acao?>_nfitemimport_vbc" type="text" value="<?=$_1_u_nfitemimport_vbc?>" vnulo vdecimal></td>
                </tr>
                <tr>
                    <td>Despesas Aduaneiras :</td>
                    <td><input class='size8' name="_1_<?=$_acao?>_nfitemimport_vdespadu" type="text" value="<?=$_1_u_nfitemimport_vdespadu?>" vnulo vdecimal></td>
                </tr>
                <tr>
                    <td>Imposto de Importação:</td>
                    <td><input class='size8' name="_1_<?=$_acao?>_nfitemimport_vii" type="text" value="<?=$_1_u_nfitemimport_vii?>"  vnulo vdecimal></td>
                </tr>
                <tr>
                    <td>IOF:</td>
                    <td><input class='size8' name="_1_<?=$_acao?>_nfitemimport_viof" type="text" value="<?=$_1_u_nfitemimport_viof?>" vnulo vdecimal></td>
                </tr>
            </table>
        </div>
    </div>
</div>
</div>
<?
if(!empty($_1_u_nfitemimport_idnfitemimport)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_nfitemimport_idnfitemimport; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "nfitemimport"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>