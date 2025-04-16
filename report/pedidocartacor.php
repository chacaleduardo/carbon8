<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];

//error_reporting(E_ALL);

//die();

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nfcorrecao";
$pagvalcampos = array(
	"idnfcorrecao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".nfcorrecao where idnfcorrecao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
ob_start();


?>

<html>
<head>
<title>Carta de Correção</title>



<style>

.rotulo{

font-size: 15px;
}
.texto{
font-size: 15px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}
.bordadiv {  
    margin: 0;	
    margin:0 auto;	
    height: 99%;	
    width: 100%;	
    border: 1px solid #000000; 
    border-radius: 10px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}
</style>

</head>
<body style="max-width:1100px;">
    <?
    	$sqlf="select SUBSTRING(n.idnfe,4) as idnfe,n.nnfe,e.cnpj 
			from nf n join empresa e on(e.idempresa=n.idempresa)
			where n.idnf=".$_1_u_nfcorrecao_idnf;	 
	$resf=d::b()->query($sqlf) or die("erro ao buscar informações da NF sql=".$sqlf);
        $i=0;
	$rowf=mysqli_fetch_assoc($resf);

	$cnpj=formatarCPF_CNPJ($rowf['cnpj'],true);

 ?>

    <div class="bordadiv">
	<p>	
	<table>
	<tr>		
		<td>
		<table>
		<tr>	
                    <td style="width: 820px;" align="center"><h1>CARTA DE CORREÇÃO</h1></td>						
		</tr>
		</table>
		</td>
		<td style="vertical-align: top; width: 200px;">				
                    <div align="right" style="font-size: 11px;" >
                        <font style="font-weight: bold;">CNPJ:</font> <BR><?=$cnpj?>
                    </div>                  
                    <div align="right" style="font-size: 11px;">
                        <font style="font-weight: bold;"> CHAVE DE ACESSO:</font><BR> <?=$rowf['idnfe']?> 
                    </div>	
		</td>		  
	</tr>
	</table>
		    

<?

    //$xml=($_1_u_nfcorrecao_xml);
    $xml=$_1_u_nfcorrecao_xml;
    $doc = DOMDocument::loadXML($xml);
    $cab = $doc->getElementsByTagName("procEventoNFe")->item(0);
    $retEvento = $cab->getElementsByTagName("retEvento")->item(0);
    $infEvento = $retEvento->getElementsByTagName("infEvento")->item(0);
    $nProt=$infEvento->getElementsByTagName("nProt")->item(0);
    $vnProt =($nProt->textContent); //PROTOCOLO

    $tpEvento=$infEvento->getElementsByTagName("tpEvento")->item(0);
    $vtpEvento =($tpEvento->textContent); //MOTIVO

    $dhRegEvento=$infEvento->getElementsByTagName("dhRegEvento")->item(0);
    $vdhRegEvento =($dhRegEvento->textContent); //DATAEVENTO               

    $evento = $cab->getElementsByTagName("evento")->item(0);
    $infEvento = $evento->getElementsByTagName("infEvento")->item(0);
    $detEvento = $infEvento->getElementsByTagName("detEvento")->item(0);
    $xCorrecao = $detEvento->getElementsByTagName("xCorrecao")->item(0);
    $vxCorrecao =($xCorrecao->textContent); //JUSTIFICATIVA

    $xCondUso = $detEvento->getElementsByTagName("xCondUso")->item(0);
    $vxCondUso =($xCondUso->textContent); //CONDIÇÃO DE USO
    
    
	?>

		<table   border=1 cellspacing=0 cellpadding=2 bordercolor="666633">                   
		<tr>	
                    <td  class="rotulo "><b>N.NFe:</b><br><?=$rowf['nnfe']?> </td> 
                    <td rowspan="5"><?=htmlentities($vxCorrecao)?></td>
		</tr>
		<tr>	
                    <td  class="rotulo "><b>ORGÃO:</b><br> MG</td> 
                    
		</tr>
                <tr>	
                    <td  class="rotulo "><b>PROTOCOLO:</b><br> <?=$vnProt?></td> 
                    
		</tr>
                <tr>	
                    <td class="rotulo "><b>TIPO EVENTO:</b><br> <?=$vtpEvento?></td> 
                    
		</tr>
                <tr>	
                    <td class="rotulo "><b>DATA:</b><br> <?=dmahms($vdhRegEvento);?> </td>                     
		</tr>
                <tr>	
                    <td colspan="2">
Atenção! A carta de correção eletrônica (CC-e) poderá ser emitida desde que o erro NÃO esteja relacionado com:
<br>
1 - As variáveis que determinam o valor do imposto tais como: base de cálculo, alíquota, diferença de preço, quantidade, <br>
    valor da operação (para estes casos deverá ser utilizada NF-e Complementar);<br>
2 - A correção de dados cadastrais que implique mudança do remetente ou do destinatário;<br>
3 - A data de emissão da NF-e ou a data de saída da mercadoria.
                    </td>                     
		</tr>
                
		</table>
		

	

</div>

</body>
</html>

<?
if($geraarquivo=='Y'){

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	//echo($html);die;


	// Incluímos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");
	 
	

	// Instanciamos a classe
	$dompdf = new DOMPDF();
	 
	// Passamos o conteúdo que será convertido para PDF
        $html=preg_match("//u", $html)?utf8_decode($html):$html; //MAF060519: Converter para ISO8859-1. @todo: executar upgrade no dompdf
	$dompdf->load_html($html);
	
	//$dompdf->option('defaultFont', 'Times New Roman?');

	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	$dompdf->render();

	if($gravaarquivo=='Y'){
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
    	file_put_contents("/var/www/laudo/tmp/nfe/cartacorrecao".$_1_u_nfcorrecao_idnf.".pdf",$output);
    	echo("OK");
	}else{
		// e exibido para o usuário
		$dompdf->stream("cartacorrecao".$_1_u_nfcorrecao_idnf.".pdf");
	}
}

?>
