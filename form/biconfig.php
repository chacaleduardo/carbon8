<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/biconfig_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "_bi";
$pagvalcampos = array(
	"idbi" => "pk"
);
$_acao = $_GET['_acao'];

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from carbonnovo._bi where idbi = '#pkid'";
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
		   				<input name="_1_<?=$_acao?>__bi_idempresa" type="hidden" value="<?=$_GET['_idempresa']?>" readonly='readonly'>
		   				<input name="_1_<?=$_acao?>__bi_idbi" type="hidden" value="<?=$_1_u__bi_idbi?>" readonly='readonly'>
					</td> 	
	    		</tr>
                <tr>   
					<td>BI:</td> 
					<td>
						<input name="_1_<?=$_acao?>__bi_nome" type="text" value="<?=$_1_u__bi_nome?>" class="size40">
					</td> 
                </tr>                  
            </table>
        </div>
        <div class="panel-body"> 
            <table style="width: 40%;">                
                <tr> 	
                    <td>Dashboard:</td> 
                    <td>
						<select name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_bipai" class="selectpicker" data-live-search="true" onchange="alterarTipo(this)">                            <option value=""></option>
                            <? fillselect("SELECT b.idbi,b.nome
                                            FROM "._DBCARBON."._bi b
                                            WHERE 
                                                b.status = 'ATIVO' AND b.bipai IS NULL
                                                -- and b.idempresa in (".$_GET['_idempresa'].")
                                              ORDER BY b.ordem, b.nome", $_1_u__bi_bipai); ?>
                        </select>
					</td>
				</tr>
				<tr> 	
                    <td>Ordem:</td> 
                    <td>
						<input name="_1_<?=$_acao?>__bi_ordem" type="text" value="<?=$_1_u__bi_ordem?>">
                        <input name="_1_<?=$_acao?>__bi_tipo" id="tipoBi" type="hidden" value="<?=(empty($_1_u__bi_bipai)) ? 'dashboard' : 'pagina'; ?>">
					</td> 				
				</tr>
                <!--tr> 	
                    <td>Report ID:</td> 
                    <td>
						<input name="_1_<?=$_acao?>__bi_reportid" type="text" value="<?=$_1_u__bi_reportid?>">
					</td> 				
				</tr-->
				<tr> 
					<td>Status:</td> 
					<td>
						<select name="_1_<?=$_acao?>__bi_status">
						    <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u__bi_status);?>		
						</select>
					</td> 
                </tr>
				<? $display = ($_1_u__bi_tipo == 'pagina' && $_acao == 'u') ? '' : 'none'; ?>
                <tr class="empresaBi" style="display: <?=$display?>;">
					<td style="vertical-align: top; margin-top: 4px; padding-top: 8px;">Empresa:</td>		
					<td>
						<select name="empresa" onchange="inserirempresa(this);">
							<option value=""></option>
							<?
							fillselect(BigConfigController::buscarEmpresasVinculas($_1_u__bi_idbi));
							?>
						</select>	
					</td>									
				</tr>
				<tr class="empresaBi" style="display: <?=$display?>;">
					<td colspan="6">
						<table  style="width: 100%;">
							<tr>
								<?
								$rese = BigConfigController::buscarEmpresaBi($_1_u__bi_idbi);
								$qtde = count($rese);
								if($qtde>0)
								{
									$i = 0;
									?>
									<th style="text-align: center" colspan="2">Empresa</th>							
									<th style="text-align: center" colspan="2">Report ID</th>
									<?
									foreach($rese as $k => $rowe )
									{
										?>	
										<tr>											
											<td>	
												<?=$rowe["empresa"]?>
											</td>
											<td>
												<a title="Empresa" class="fa fa-bars fade pointer hoverazul" href="?_modulo=empresa&_acao=u&idempresa=<?=$rowe["idempresa"]?>" target="_blank"></a>
											</td>															
											<td>
												<input name="_l<?=$i?>_<?=$_acao?>__linkportalbi_reportid" type="text" value="<?=$rowe["reportid"]?>">
											</td>
											<td>
												<input type="hidden" name="_l<?=$i?>_<?=$_acao?>__linkportalbi_idlinkportalbi" id="_l<?=$i?>_<?=$_acao?>__linkportalbi_idlinkportalbi" value="<?=$rowe["idlinkportalbi"]?>">
											</td>
											<td>
												<span onclick="CB.post({objetos:'_ajax_d__linkportalbi_idlinkportalbi=<?=$rowu['idlinkportalbi']?>'})" class="pointer">
													<i class="fa fa-trash vermelho hoverpreto pointer"></i>
												</span>
											</td>																		
										</tr>   
										<?
										$i++;
									}
								}		
								?> 
							</tr>
						</table>
					</td>
				</tr>
            </table>
        </div>
    </div>
</div>

<?
if(!empty($_1_u__bi_idbi)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u__bi_idbi; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "_bi"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>

<script language="javascript">
    $('.selectpicker').selectpicker();
    var _acao = '<?=$_acao?>';
    var _idbi = '<?=$_1_u__bi_idbi?>';
    var idempresa = '<?=$_1_u__bi_idempresa?>';

	//Salva o Módulo para aparecer os Tipos De Documentos ou 
    function saveModulo(vthis, campo)
    {
        tipoCampo = ($('[name=_1_u__bi_bipai]').val() == '' || $('[name=_1_u__bi_bipai]').val() == undefined) ? 'dashboard' : 'pagina';
        CB.post({
            objetos: `&_1_${_acao}__bi_idbi=${_idbi}&_1_${_acao}__bi_${campo}=${$(vthis).val()}&_1_${_acao}__bi_tipo=${tipoCampo}`      
        })
    }

	function inserirempresa(vthis){
		$empresa = $(vthis).val();
		if($empresa != '') {
			CB.post({
				objetos: `_100_i__linkportalbi_idbi=${_idbi}&_100_i__linkportalbi_idempresa=${idempresa}&_100_i__linkportalbi_empresa=${$(vthis).val()}&_100_i__linkportalbi_status=ATIVO`,		
				parcial: true
			});
		}
	}

	function alterarTipo(vthis){
		if($(vthis).val() == ''){
			$('#tipoBi').val('dashboard');
			$('.empresaBi').css('display', 'none');

		} else {
			$('#tipoBi').val('pagina');
			$('.empresaBi').css('display', 'block');
		}
	}

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>