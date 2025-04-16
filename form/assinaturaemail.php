<?
session_start();
require_once("../inc/php/functions.php");
//require_once("../inc/php/validaacesso.php");

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "pessoa";
$pagvalcampos = array(
	"idpessoa" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pessoa where idpessoa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$sqlempresa = "SELECT * FROM empresa WHERE idempresa = ".$_1_u_pessoa_idempresa;
$resempresa=d::b()->query($sqlempresa) or die("Erro ao buscar dominio de produto da empresa sql=".$sqlempresa);
$rowempresa=mysqli_fetch_assoc($resempresa);

$sqlarq = "select a.* 
				from arquivo a 
				where 
					a.tipoobjeto = 'empresa'
					and a.idobjeto = ".$_1_u_pessoa_idempresa."
					and tipoarquivo = 'IMAGEMEMAIL'
				order by idarquivo desc limit 1";

		$resarq = d::b()->query($sqlarq);
		$rowarq=mysqli_fetch_assoc($resarq);
		$imagedata = file_get_contents($rowarq["caminho"]);
		if($imagedata){
			$imageb64 = base64_encode($imagedata);
//echo "<!--".$sqlempresa."-->";
?>
<table style="font-family: Arial, SANS-SERIF, Helvetica; width: 622px; vertical-align: top; background-repeat: no-repeat;background-size: 622px 86px; background:url('data:image/png;base64,<?=$imageb64?>')">
	<tbody>
	
	<tr>
		<td style="font-weight: bold; padding-left: 10px; font-size: 16px; padding-top:2px; padding-bottom: 0px; vertical-align: middle; line-height: 20px; height: 10px;">
	</td>
</tr>
<tr>
<td style="font-weight: bold; padding-left: 10px; font-size: 16px; padding-top:2px; padding-bottom: 0px; vertical-align: middle; line-height: 20px; height: 10px;"><?=strtoupper($_1_u_pessoa_nomecurto)?></td>

</tr>
<tr>
<td style=" padding-left: 10px; font-size: 12px;    vertical-align: middle; line-height: 10px; height: 0px; padding-top: 0px;"><?if(empty($_1_u_pessoa_rotuloass)){?>&nbsp;<?}else{?><?=$_1_u_pessoa_rotuloass?><?}?></td>

</tr>
<tr>
<td style="padding: 0px; padding-left: 10px;  padding-bottom: 25px;  font-size: 11px; font-weight: bold; vertical-align: top; line-height: 13px; color: #1866a5;">(<?=$rowempresa["DDDPrestador"]?>) <?=$rowempresa["TelefonePrestador"]?> <?if(!empty($_1_u_pessoa_ramalfixo)){?> - Ramal: <?=$_1_u_pessoa_ramalfixo?><?}?><br /><? if (!empty($_1_u_pessoa_dddass)){?>(<?=$_1_u_pessoa_dddass?>) <?=$_1_u_pessoa_telass?> &nbsp; <? } ?> </td>
</tr>
</tbody>
</table>
<table style="font-family: Arial, SANS-SERIF, Helvetica; width: 622px; vertical-align: top; background-repeat: no-repeat;">
<tbody>
<tr>
<td style="font-size: 10px; text-align: justify;" colspan="2"><?=$rowempresa["razaosocial"]?> <br />CNPJ: <?=formatarCPF_CNPJ($rowempresa["cnpj"],true)?> - I.E: <?=$rowempresa["inscestadual"]?> - <?=$rowempresa["xlgr"]?> - <?=$rowempresa["nro"]?> - <?=$rowempresa["xbairro"]?> - <?=formatarCEP($rowempresa["cep"],true)?> - <?=$rowempresa["xmun"]?>/<?=$rowempresa["uf"]?> <br />
As informa&ccedil;&otilde;es contidas neste email e documentos anexos s&atilde;o particulares, sigilosos e de propriedade do Laudo Laborat&oacute;rio Av&iacute;cola.<br /> Se voc&ecirc; n&atilde;o for o destinat&aacute;rio ou se recebeu esta mensagem irregularmente ou por erro, apague o e-mail e avise o remetente. <br />Este e-mail n&atilde;o pode ser divulgado, armazenado, utilizado, publicado ou copiado por qualquer um que n&atilde;o o(s) seu(s) destinat&aacute;rio(s).</td>
</tr>
<tr>
<td style="font-size: 11px; cursor: pointer;" colspan="2"><a href="http://www.laudolab.com.br">http://www.laudolab.com.br</a></td>
</tr>
</tbody>
</table>
<br>

<?
		}//if($imagedata)
$_a = imagemtipoemailempresa("ORCPROD",$rowempresa["idempresa"]);
$_b = imagemtipoemailempresa("NFP",$rowempresa["idempresa"]);
$_c = imagemtipoemailempresa("NFS",$rowempresa["idempresa"]);
$_d = imagemtipoemailempresa("COTACAO",$rowempresa["idempresa"]);
?>
<div id="teste0" style="display:none;">
	<p>Orçamento de Produto</p>
	<?=$_a?>
</div>
<div id="teste1" style="display:none;">
	<p>Nota Fiscal Produto</p>
	<?=$_b?>
</div>
<div id="teste2" style="display:none;">
	<p>Nota Fiscal Serviço</p>
	<?=$_c?>
</div>
<div id="teste3" style="display:none;">
	<p>Cotação</p>
	<?=$_d?>
</div>
<?
/*
$html = <<<HTML
<p><img style="width:620px; position: absolute; z-index: -1;" src="data:image/gif;base64,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" alt="" /></p>
<table style="font-family: Arial, SANS-SERIF, Helvetica; width: 610px; height: 126px; vertical-align: top; background-repeat: no-repeat;">
<tbody>
<tr>
<td style="font-weight: bold; padding-left: 90px; font-size: 17px; padding-top:5px; padding-bottom: 0px; vertical-align: middle; line-height: 20px; height: 10px;"><?=strtoupper($_1_u_pessoa_nomecurto)?></td>

</tr>
<tr>
<td style=" padding-left: 90px; font-size: 12px; padding-bottom: 5px;    vertical-align: middle; line-height: 10px; height: 0px; padding-top: 0px;"><?if(empty($_1_u_pessoa_rotuloass)){?>&nbsp;<?}else{?><?=$_1_u_pessoa_rotuloass?><?}?></td>

</tr>
<tr>
<td style="padding: 0px; padding-left: 90px;  padding-bottom: 3px;  font-size: 11px; font-weight: bold; vertical-align: top; line-height: 13px; color: #1866a5;">(34) 3222-5700 <?if(!empty($_1_u_pessoa_ramalfixo)){?> - Ramal: <?=$_1_u_pessoa_ramalfixo?><?}?><br />(<?=$_1_u_pessoa_dddass?>) <?=$_1_u_pessoa_telass?>&nbsp;</td>
</tr>
<tr>
<td style="font-size: 10px; text-align: justify;" colspan="2">Laudo Laborat&oacute;rio Av&iacute;cola Uberl&acirc;ndia Ltda <br />CNPJ: 23.259.427/0001-04 - I.E: 7023871770001 - Rod. BR 365, KM 615 - S/N&ordm; - Alvorada - 38.407-180 - Uberl&acirc;ndia/MG <br />As informa&ccedil;&otilde;es contidas neste email e documentos anexos s&atilde;o particulares, sigilosos e de propriedade do Laudo Laborat&oacute;rio Av&iacute;cola.<br /> Se voc&ecirc; n&atilde;o for o destinat&aacute;rio ou se recebeu esta mensagem irregularmente ou por erro, apague o e-mail e avise o remetente. <br />Este e-mail n&atilde;o pode ser divulgado, armazenado, utilizado, publicado ou copiado por qualquer um que n&atilde;o o(s) seu(s) destinat&aacute;rio(s).</td>
</tr>
<tr>
<td style="font-size: 11px; cursor: pointer;" colspan="2"><a href="http://www.laudolab.com.br">http://www.laudolab.com.br</a></td>
</tr>
</tbody>
</table>
HTML;
<textarea name="_1_<?=$acao?>_pessoa_formacao" rows="20" cols="150"><?=$html?></textarea>
*/
?>


