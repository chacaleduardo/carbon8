<?
include_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-lock fa-lg" alt="" border="0"></i>&nbsp;&nbsp;Informe a Senha atual e uma Nova Senha:
	</div>	
	<div class="panel-body">
		<div class="col-md-12">
			<div class="row">
				<table class="normal">
					<?if(!empty($_SESSION["SESSAO"]["LOGMSGSENHA"])){?>
						<tr class="resverm">
							<td colspan="15" style="padding: 5px;"><?=$_SESSION["SESSAO"]["LOGMSGSENHA"]?></td>
						</tr>
					<?}
					$_SESSION["SESSAO"]["LOGMSGSENHA"]="";
					?>
						<tr>
							<td colspan="1" rowspan="6" style="border:0px solid silver;">
								
							<td style="border:0px solid silver;padding: 10px;" class="labelroxopalido" align="right">Usu&aacute;rio:</td>
							<td style="border:0px solid silver;" class="10preto"><?=$_SESSION["SESSAO"]["USUARIO"]?></td>
						</tr>
					<tr>
						<?
						if($_SESSION["SESSAO"]["FORCAALTERACAOSENHA"]){
							?>
							<td colspan="2" align="center">
								<input type="hidden" name="_1_u_pessoa_senha" vnulo value="<?=$_SESSION["SESSAO"]["FORCAALTERACAOSENHA"]?>">
								<div class="alert alert-danger" role="alert">
								<h5><strong>Voc&ecirc; est&aacute; recuperando sua senha!</strong><br>Informe uma nova senha para acesso ao sistema:</h5></div>
							<td>
						<?}elseif($_SESSION["SESSAO"]["SENHAALTERADACOMSUCESSO"] == 'Y'){?>
							<td colspan="2" align="center">
								<input type="hidden" name="_1_u_pessoa_senha" vnulo value="<?=$_SESSION["SESSAO"]["FORCAALTERACAOSENHA"]?>">
								<div class="alert alert-info" role="alert">
								<h5><strong>Senha Alterada Com Sucesso!</h5></div>
							<td>
						<? } else { ?>
							<td style="border:0px solid silver;padding: 5px;" class="labelroxopalido" align="right">Senha Atual:</td>
							<td style="border:0px solid silver;">
								<input type="password" name="_1_u_pessoa_senha" vnulo>
							</td>
						<?}?>
					</tr>
					<? if(empty($_SESSION["SESSAO"]["SENHAALTERADACOMSUCESSO"])){ ?>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td style="border:0px solid silver;padding: 5px;" class="labelroxopalido" align="right">NOVA SENHA:</td>
							<td style="border:0px solid silver;">
								<input type="password" name="senhanova" vnulo vpwd1>
							</td>
						</tr>
						<tr>
								<td style="border:0px solid silver;padding: 5px;" class="labelroxopalido" align="right">CONFIRME A NOVA SENHA:</td>
								<td style="border:0px solid silver;">
									<input type="password" name="senhanova2" vnulo vpwd2>
								</td>
						</tr>
						<tr>
							<td style="border:0px solid silver;padding: 5px;"></td>
							<td align="center" style="border:0px solid silver;padding-top: 5px;">

								<button type="button" class="btn btn-danger" onclick="CB.post()">
									<i class="fa fa-circle"></i>Salvar
								</button>
							</td>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>
	</div>
<? unset($_SESSION["SESSAO"]["SENHAALTERADACOMSUCESSO"]); ?>
</div>
