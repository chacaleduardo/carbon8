<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$idcontapagar = $_GET["idcontapagar"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "contapagar";
$pagvalcampos = array(
    "idcontapagar" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contapagar where idcontapagar= '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$sql="select * from inadimplencia where idcontapagar = ".$_1_u_contapagar_idcontapagar;
$res = d::b()->query($sql) or die("Erro ao buscar inadimplencia sql=".$sql);
$qtd = mysqli_num_rows($res);
if($qtd<1){
    $_acao='i';
}else{
    $_acao='u';
}
$row=mysqli_fetch_assoc($res);
?>
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <table>
                    <tr>
                        <td></td>
                        <td>
                            <input name="_1_<?=$_acao?>_inadimplencia_idcontapagar" id="idcontapagar" type="hidden" readonly value="<?=$_1_u_contapagar_idcontapagar?>">
                            <input name="_1_<?=$_acao?>_inadimplencia_idinadimplencia" id="idinadimplencia" type="hidden" readonly value="<?=$row['idinadimplencia']?>">
                        </td>
                    </tr>
                    <tr>
                        <td >Obs:</td>
                    </tr>
                    <tr>
                        <td >
                            <textarea <?= $readonly2 ?> name="_1_<?=$_acao?>_inadimplencia_obs" style="width: 640px; height: 40px;"><?=$row['obs']?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Inadimplência:</td>                    
                    </tr>
                    <tr>
                        <td><select name="_1_<?=$_acao?>_inadimplencia_status" id="status" vnulo>
                                <?
                                fillselect("select 'PENDENTE','PENDENTE' union select 'PENDENTE NEGATIVADO','PENDENTE NEGATIVADO' union select 'PENDENTE RECUPERACAO JUDICIAL','PENDENTE RECUPERAÇÃO JUDÍCIAL' union select 'INATIVO','CONCLUIDA'", $row['status']);
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row w-100">
	<div class="col-xs-12 px-0">
		<div class="panel panel-default">		
			<div class="panel-body" style="padding-top: 8px !important;background:#e6e6e6">
				<div class="w-100 d-flex flex-wrap">
					<div class="col-xs-12 col-md-6 flex justify-content-center">
						<div class="flex">
							<span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
								Criação:
							</span>
							<span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
								<?=$row['criadopor']?>
							</span> 
							<span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
								<?=$row['criadoem']?>
							</span>
						</div>
					</div>     
					<div class="col-xs-12 col-md-6 flex justify-content-center">
						<div class="flex">
							<span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
								Alteração:
							</span>
							<span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
								<?=$row['alteradopor']?>
							</span> 
							<span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
								<?=$row['alteradoem']?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>