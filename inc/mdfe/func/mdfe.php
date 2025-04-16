<?

include_once("../../php/functions.php");
require_once('../../sped-da/vendor/bootstrap.php');

use NFePHP\DA\MDFe\Damdfe;


$idmdfe= $_GET["idmdfe"]; //Id da nota fiscal que vem da acao de enviar


// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select xmlret,idempresa from "._DBAPP.".mdfe where protocolo is not null and idmdfe=".$idmdfe;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['xmlret'])){
	die("Não foi encontrado o xml para gerar a impressão.");
}

/*
$xml = '<?xml version="1.0" encoding="UTF-8"?><mdfeProc xmlns="http://www.portalfiscal.inf.br/mdfe" versao="3.00"><MDFe xmlns="http://www.portalfiscal.inf.br/mdfe"><infMDFe Id="MDFe31210323259427000104580260000000051876827167" versao="3.00"><ide><cUF>31</cUF><tpAmb>2</tpAmb><tpEmit>2</tpEmit><mod>58</mod><serie>26</serie><nMDF>5</nMDF><cMDF>87682716</cMDF><cDV>7</cDV><modal>1</modal><dhEmi>2021-03-10T12:00:00-03:00</dhEmi><tpEmis>1</tpEmis><procEmi>0</procEmi><verProc>3.9.8</verProc><UFIni>MG</UFIni><UFFim>MG</UFFim><infMunCarrega><cMunCarrega>3100104</cMunCarrega><xMunCarrega>ABADIA DOS DOURADOS</xMunCarrega></infMunCarrega></ide><emit><CNPJ>23259427000104</CNPJ><IE>7023871770001</IE><xNome>LAUDO LABORAT&#xD3;RIO AV&#xCD;COLA UBERL&#xC2;NDIA LTDA</xNome><xFant>LAUDO LABORAT&#xD3;RIO</xFant><enderEmit><xLgr>RODOVIA BR 365, KM 615</xLgr><nro>S/N</nro><xBairro>ALVORADA</xBairro><cMun>3170206</cMun><xMun>UBERL&#xC2;NDIA</xMun><CEP>38407180</CEP><UF>MG</UF><fone>3432225700</fone></enderEmit></emit><infModal versaoModal="3.00"><rodo xmlns="http://www.portalfiscal.inf.br/mdfe"><veicTracao><placa>ABC1011</placa><RENAVAM>32132132131</RENAVAM><tara>1000</tara><condutor><xNome>ADALTRO REIS DA PAZ</xNome><CPF>96632917615</CPF></condutor><tpRod>01</tpRod><tpCar>01</tpCar><UF>MG</UF></veicTracao></rodo></infModal><infDoc><infMunDescarga><cMunDescarga>3172004</cMunDescarga><xMunDescarga>VISCONDE DO RIO BRANCO</xMunDescarga><infNFe><chNFe>31210323259427000104550010000124291185093677</chNFe></infNFe></infMunDescarga></infDoc><tot><qNFe>1</qNFe><vCarga>11.70</vCarga><cUnid>01</cUnid><qCarga>1000</qCarga></tot></infMDFe><infMDFeSupl><qrCodMDFe><![CDATA[https://dfe-portal.svrs.rs.gov.br/mdfe/qrCode?chMDFe=31210323259427000104580260000000051876827167&tpAmb=2]]></qrCodMDFe></infMDFeSupl><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#MDFe31210323259427000104580260000000051876827167"><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/><Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>ZmjyvxQklSHByw0JeDeiuiTa0XI=</DigestValue></Reference></SignedInfo><SignatureValue>M8Bi4NNu5iTHf68zxvvHyTsFZ9cAw+MwOgspmDZFFKq3m0DCd+SAtFtrzDq5FECMV4zGYFZqqQAjl6TqAQj2j4Sj68HYKuTt4EYzE5ckP+YVL/yIaM73i2EzPliCmrMEqc77VqCtKdJRcooZqdiPHiefFhOazfEG2Xfz0sK3tGG/rWtiWFBrnNGIZBbiVbZV5ZT65PFmd/EUGqJoCCuvpi7fT8GIw+LJSvIA6afbrP9ht6tKY0JfjnsC6MiqJAFp6xbyHjVGFEZJ2EPhC9/bzhKEEfLEKpabTInmEPe4fEN7oo0TwybMItvNJAgjr37rslohRhgEzq5c2lfSzsLKEA==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></MDFe><protMDFe xmlns="http://www.portalfiscal.inf.br/mdfe" versao="3.00"><infProt Id="MDFe100320211514447000"><tpAmb>2</tpAmb><verAplic>RS20200812115905</verAplic><chMDFe>31210323259427000104580260000000051876827167</chMDFe><dhRecbto>2021-03-10T15:14:44-03:00</dhRecbto><nProt></nProt><digVal>ZmjyvxQklSHByw0JeDeiuiTa0XI=</digVal><cStat>611</cStat><xMotivo>Rejeição: Existe MDF-e não encerrado para esta placa, tipo de emitente e UF descarregamento [chMDFe Não Encerrada:31210323259427000104580260000000041794425351][NroProtocolo:931210000004880]</xMotivo></infProt></protMDFe></mdfeProc>';
*/
$xml=$row['xmlret'];
    // $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(realpath(__DIR__ . '/../images/tulipas.png')));

    $sqlimagemdanfe="select caminho from empresaimagem where idempresa = ".$row["idempresa"]." and tipoimagem = 'IMAGEMEMPRESADANFE'";
	$resimagemdanfe=d::b()->query($sqlimagemdanfe) or die("Erro ao buscar figura da danfe da empresa sql=".$sqlimagemdanfe);
	$rowimagemdanfe= mysqli_fetch_assoc($resimagemdanfe);
	if(!empty($rowimagemdanfe["caminho"])){
		$rowimagemdanfe["caminho"] = str_replace("../", "", $rowimagemdanfe["caminho"]);
		$logo = _CARBON_ROOT.$rowimagemdanfe["caminho"];
	}else{
		$logo = '';
	}

   

try {
   
    $damdfe = new Damdfe($xml);
    $damdfe->debugMode(true);
    $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
    $damdfe->logoParameters($logo, 'L');
    $pdf = $damdfe->render();
    header('Content-Type: application/pdf');
    echo $pdf;
} catch (Exception $e) {
    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
}