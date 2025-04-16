<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "etapa";
$pagvalcampos = array(
	"idetapa" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from etapa where idetapa = '#pkid'";
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
		   				<input name="_1_<?=$_acao?>_etapa_idetapa" type="hidden" value="<?=$_1_u_etapa_idetapa?>" readonly='readonly'>
					</td> 	
	    		</tr>
                <tr>   
					<td>Etapa:</td> 
					<td>
						<input name="_1_<?=$_acao?>_etapa_etapa" type="text" value="<?=$_1_u_etapa_etapa?>">
					</td> 
                </tr>                  
            </table>
        </div>
        <div class="panel-body"> 
            <table>                
                <tr> 	
                    <td>Módulo:</td> 
                    <td>
						<select name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_modulo" onchange="saveModulo(this, 'modulo')" vnulo class="selectpicker" data-live-search="true">
                            <option value=""></option>
                            <? fillselect("SELECT m.modulo, CONCAT(m.rotulomenu, ' - ', m.modulo) AS rotulo
                                             FROM "._DBCARBON."._modulo m,"._DBCARBON."._mtotabcol tc
                                            WHERE tc.primkey ='Y'
                                              AND exists (select 1 from "._DBCARBON."._mtotabcol t where t.tab = m.tab and col='alteradoem' )
                                              AND tc.tab = m.tab
                                              AND m.status = 'ATIVO'
                                              ORDER BY m.rotulomenu", $_1_u_etapa_modulo); ?>
                        </select>
					</td> 				
				</tr>	  
				<tr> 	
                    <td>Tipo:</td> 
                    <td>
						<select name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_tipoobjeto" onchange="saveModulo(this, 'tipoobjeto')" class="selectpicker" data-live-search="true">
                                <option value=""></option>
                                <? fillselect("SELECT mtc.col, CONCAT(mtc.col, ' - ', if(length(mtc.rotcurto)=0,mtc.col,mtc.rotcurto) )as rotcurto
                                                FROM "._DBCARBON."._modulo m 
                                                JOIN "._DBCARBON."._mtotabcol mtc ON mtc.tab=m.tab
                                            LEFT JOIN "._DBCARBON."._modulofiltros mf ON mf.modulo = m.modulo and mf.col = mtc.col
                                                WHERE m.modulo = '".$_1_u_etapa_modulo."' 
                                                AND exists(SELECT 1 FROM information_schema.tables it
                                                            WHERE it.table_name = m.tab)
                                            ORDER BY mtc.col", $_1_u_etapa_tipoobjeto); ?>                    
                            </select>		
					</td> 				
				</tr>
				<tr> 	
                    <td>Valor:</td> 
                    <td>
                        <? 
                        if($_1_u_etapa_modulo == 'formalizacao'){
                            $dropSql = "SELECT subtipo, descricao  FROM formalizacaosubtipo WHERE status = 'ATIVO'";
                        } else {
                            $sqlDropsql = "SELECT dropsql 
                                        FROM "._DBCARBON."._modulo m JOIN "._DBCARBON."._mtotabcol mtc ON mtc.tab=m.tab
                                       WHERE m.modulo = '".$_1_u_etapa_modulo."' AND mtc.col = '".$_1_u_etapa_tipoobjeto."'";
                            $rbDropsql = d::b()->query($sqlDropsql) or die("Erro ao buscar status do fluxo: ". mysql_error(d::b()));
                            $robDropsql = mysqli_fetch_assoc($rbDropsql);
                            if(empty($robDropsql['dropsql'])){ $disabled = 'disabled';}
                            $dropSql = str_replace('".getidempresa(\'idempresa\',\'tipoprodserv\')."', ' AND idempresa = '.$_SESSION["SESSAO"]["IDEMPRESA"], $robDropsql['dropsql']);
                        }
                        ?>
                        <select name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_idobjeto" onchange="saveModulo(this, 'idobjeto')" <?=$disabled?> class="selectpicker" data-live-search="true">
                            <option value=""></option>
                            
                            <? fillselect($dropSql, $_1_u_etapa_idobjeto); ?>                    
                        </select>
					</td> 				
				</tr>
				<tr> 	
                    <td>Ordem:</td> 
                    <td>
						<input name="_1_<?=$_acao?>_etapa_ordem" type="text" value="<?=$_1_u_etapa_ordem?>">
					</td> 				
				</tr>
				<tr> 
					<td>Status:</td> 
					<td>
						<select name="_1_<?=$_acao?>_etapa_status">
						    <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_etapa_status);?>		
						</select>
					</td> 
                </tr>
            </table>
        </div>
    </div>
</div>

<?
if(!empty($_1_u_etapa_idetapa)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_etapa_idetapa; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "etapa"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script language="javascript">
    $('.selectpicker').selectpicker();
	//Salva o Módulo para aparecer os Tipos De Documentos ou 
    function saveModulo(vthis, campo)
    {
        CB.post({
            objetos: '&_1_<?= $_acao ?>_<?= $pagvaltabela?>_idetapa=<?=$_1_u_etapa_idetapa?>'
                    +'&_1_<?= $_acao ?>_<?= $pagvaltabela?>_'+campo+'='+$(vthis).val()             
        })
    }
</script>