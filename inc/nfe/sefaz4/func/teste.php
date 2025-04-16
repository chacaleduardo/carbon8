<?
$pairs = array(
    "\x03" => "&#x03;",
    "\x05" => "&#x05;",
    "\x0E" => "&#x0E;",
    "\x16" => "&#x16;",
);
//$xml = strtr($xml, $pairs);
function remove_accent($str)
{
  $a = array('ÃƒÂ€', 'ÃƒÂ', 'ÃƒÂ‚', 'ÃƒÂƒ', 'ÃƒÂ„', 'ÃƒÂ…', 'ÃƒÂ†', 'ÃƒÂ‡', 'ÃƒÂˆ', 'ÃƒÂ‰', 'ÃƒÂŠ', 'ÃƒÂ‹', 'ÃƒÂŒ', 'ÃƒÂ', 'ÃƒÂŽ', 'ÃƒÂ', 'ÃƒÂ', 'ÃƒÂ‘', 'ÃƒÂ’', 'ÃƒÂ“', 'ÃƒÂ”', 'ÃƒÂ•', 'ÃƒÂ–', 'ÃƒÂ˜', 'ÃƒÂ™', 'ÃƒÂš', 'ÃƒÂ›', 'ÃƒÂœ', 'ÃƒÂ', 'ÃƒÂŸ', 'ÃƒÂ ', 'ÃƒÂ¡', 'ÃƒÂ¢', 'ÃƒÂ£', 'ÃƒÂ¤', 'ÃƒÂ¥', 'ÃƒÂ¦', 'ÃƒÂ§', 'ÃƒÂ¨', 'ÃƒÂ©', 'ÃƒÂª', 'ÃƒÂ«', 'ÃƒÂ¬', 'ÃƒÂ­', 'ÃƒÂ®', 'ÃƒÂ¯', 'ÃƒÂ±', 'ÃƒÂ²', 'ÃƒÂ³', 'ÃƒÂ´', 'ÃƒÂµ', 'ÃƒÂ¶', 'ÃƒÂ¸', 'ÃƒÂ¹', 'ÃƒÂº', 'ÃƒÂ»', 'ÃƒÂ¼', 'ÃƒÂ½', 'ÃƒÂ¿', 'Ã„Â€', 'Ã„Â', 'Ã„Â‚', 'Ã„Âƒ', 'Ã„Â„', 'Ã„Â…', 'Ã„Â†', 'Ã„Â‡', 'Ã„Âˆ', 'Ã„Â‰', 'Ã„ÂŠ', 'Ã„Â‹', 'Ã„ÂŒ', 'Ã„Â', 'Ã„ÂŽ', 'Ã„Â', 'Ã„Â', 'Ã„Â‘', 'Ã„Â’', 'Ã„Â“', 'Ã„Â”', 'Ã„Â•', 'Ã„Â–', 'Ã„Â—', 'Ã„Â˜', 'Ã„Â™', 'Ã„Âš', 'Ã„Â›', 'Ã„Âœ', 'Ã„Â', 'Ã„Âž', 'Ã„ÂŸ', 'Ã„Â ', 'Ã„Â¡', 'Ã„Â¢', 'Ã„Â£', 'Ã„Â¤', 'Ã„Â¥', 'Ã„Â¦', 'Ã„Â§', 'Ã„Â¨', 'Ã„Â©', 'Ã„Âª', 'Ã„Â«', 'Ã„Â¬', 'Ã„Â­', 'Ã„Â®', 'Ã„Â¯', 'Ã„Â°', 'Ã„Â±', 'Ã„Â²', 'Ã„Â³', 'Ã„Â´', 'Ã„Âµ', 'Ã„Â¶', 'Ã„Â·', 'Ã„Â¹', 'Ã„Âº', 'Ã„Â»', 'Ã„Â¼', 'Ã„Â½', 'Ã„Â¾', 'Ã„Â¿', 'Ã…Â€', 'Ã…Â', 'Ã…Â‚', 'Ã…Âƒ', 'Ã…Â„', 'Ã…Â…', 'Ã…Â†', 'Ã…Â‡', 'Ã…Âˆ', 'Ã…Â‰', 'Ã…ÂŒ', 'Ã…Â', 'Ã…ÂŽ', 'Ã…Â', 'Ã…Â', 'Ã…Â‘', 'Ã…Â’', 'Ã…Â“', 'Ã…Â”', 'Ã…Â•', 'Ã…Â–', 'Ã…Â—', 'Ã…Â˜', 'Ã…Â™', 'Ã…Âš', 'Ã…Â›', 'Ã…Âœ', 'Ã…Â', 'Ã…Âž', 'Ã…ÂŸ', 'Ã…Â ', 'Ã…Â¡', 'Ã…Â¢', 'Ã…Â£', 'Ã…Â¤', 'Ã…Â¥', 'Ã…Â¦', 'Ã…Â§', 'Ã…Â¨', 'Ã…Â©', 'Ã…Âª', 'Ã…Â«', 'Ã…Â¬', 'Ã…Â­', 'Ã…Â®', 'Ã…Â¯', 'Ã…Â°', 'Ã…Â±', 'Ã…Â²', 'Ã…Â³', 'Ã…Â´', 'Ã…Âµ', 'Ã…Â¶', 'Ã…Â·', 'Ã…Â¸', 'Ã…Â¹', 'Ã…Âº', 'Ã…Â»', 'Ã…Â¼', 'Ã…Â½', 'Ã…Â¾', 'Ã…Â¿', 'Ã†Â’', 'Ã†Â ', 'Ã†Â¡', 'Ã†Â¯', 'Ã†Â°', 'Ã‡Â', 'Ã‡ÂŽ', 'Ã‡Â', 'Ã‡Â', 'Ã‡Â‘', 'Ã‡Â’', 'Ã‡Â“', 'Ã‡Â”', 'Ã‡Â•', 'Ã‡Â–', 'Ã‡Â—', 'Ã‡Â˜', 'Ã‡Â™', 'Ã‡Âš', 'Ã‡Â›', 'Ã‡Âœ', 'Ã‡Âº', 'Ã‡Â»', 'Ã‡Â¼', 'Ã‡Â½', 'Ã‡Â¾', 'Ã‡Â¿');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $string);
}

function string2Slug($str){


	$str = str_replace("ÃƒÂ’","O",$str);
	$str = str_replace("ÃƒÂ","I",$str);
	$str = str_replace("ÃƒÂŽ","I",$str);
	$str = str_replace("ÃƒÂ","I",$str);
	$str = str_replace("ÃƒÂŒ","I",$str);
	$str = str_replace("ÃƒÂ‹","E",$str);
	$str = str_replace("ÃƒÂŠ","E",$str);
	$str = str_replace("ÃƒÂ‰","E",$str);
	$str = str_replace("ÃƒÂˆ","E",$str);
	$str = str_replace("ÃƒÂ‡","C",$str);
	$str = str_replace("ÃƒÂ†","A",$str);
	$str = str_replace("ÃƒÂ…","A",$str);
	$str = str_replace("ÃƒÂ„","A",$str);
	$str = str_replace("ÃƒÂƒ","A",$str);
	$str = str_replace("ÃƒÂ‚","A",$str);
	$str = str_replace("ÃƒÂ","A",$str);
	$str = str_replace("ÃƒÂ€","A",$str);
    $str = str_replace("ÃƒÂ»","U",$str);
	$str = str_replace("ÃƒÂº","U",$str);
	$str = str_replace("ÃƒÂ¹","U",$str);
	$str = str_replace("ÃƒÂ¸","O",$str);
	$str = str_replace("ÃƒÂ¶","O",$str);
	$str = str_replace("ÃƒÂµ","O",$str);
	$str = str_replace("ÃƒÂ´","O",$str);
	$str = str_replace("ÃƒÂ³","O",$str);
	$str = str_replace("ÃƒÂ²","O",$str);
	$str = str_replace("ÃƒÂ°","O",$str);
	$str = str_replace("ÃƒÂ¯","I",$str);
	$str = str_replace("ÃƒÂ®","I",$str);
	$str = str_replace("ÃƒÂ­","I",$str);
	$str = str_replace("ÃƒÂ¬","I",$str);
	$str = str_replace("ÃƒÂ«","E",$str);
	$str = str_replace("ÃƒÂª","E",$str);
	$str = str_replace("ÃƒÂ©","E",$str);
	$str = str_replace("ÃƒÂ¨","E",$str);
	$str = str_replace("ÃƒÂ§","C",$str);
	$str = str_replace("ÃƒÂ ","A",$str);
	$str = str_replace("ÃƒÂ¥","A",$str);
	$str = str_replace("ÃƒÂ¤","A",$str);
	$str = str_replace("ÃƒÂ£","A",$str);
	$str = str_replace("ÃƒÂ¢","A",$str);
	$str = str_replace("ÃƒÂ¡","A",$str);
	$str = str_replace("ÃƒÂœ","U",$str);
	$str = str_replace("ÃƒÂ›","U",$str);
	$str = str_replace("ÃƒÂš","U",$str);
	$str = str_replace("ÃƒÂ™","U",$str);
	$str = str_replace("ÃƒÂ˜","O",$str);
	$str = str_replace("ÃƒÂ–","O",$str);
	$str = str_replace("ÃƒÂ•","O",$str);
	$str = str_replace("ÃƒÂ”","O",$str);
	$str = str_replace("ÃƒÂ“","O",$str);
	$str = str_replace("ÃƒÂ","A",$str);
	
    

    return $str;

}


function cleanStr($value){
    $value = str_replace('Â', '', $value);
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    return $value;
}
ini_set('default_charset', 'utf-8');
$sXML = '<NFe xmlns="http://www.portalfiscal.inf.br/nfe"><infNFe Id="NFe31180923259427000104550010000052311651925842" versao="4.00"><ide><cUF>31</cUF><cNF>65192584</cNF><natOp>REMESSA DE MATERIAL PARA ANALISE</natOp><mod>55</mod><serie>1</serie><nNF>5231</nNF><dhEmi>2018-09-19T10:00:00-03:00</dhEmi><tpNF>1</tpNF><idDest>2</idDest><cMunFG>3170206</cMunFG><tpImp>1</tpImp><tpEmis>1</tpEmis><cDV>2</cDV><tpAmb>1</tpAmb><finNFe>1</finNFe><indFinal>1</indFinal><indPres>9</indPres><procEmi>3</procEmi><verProc>4.00</verProc></ide><emit><CNPJ>23259427000104</CNPJ><xNome>LAUDO LABORATÃƒÂ“RIO AVÃƒÂCOLA UBERLÃƒÂ‚NDIA LTDA</xNome><xFant>LAUDO LABORATÃƒÂ“RIO</xFant><enderEmit><xLgr>RODOVIA BR 365, KM 615</xLgr><nro>S/N</nro><xBairro>ALVORADA</xBairro><cMun>3170206</cMun><xMun>Uberlandia</xMun><UF>MG</UF><CEP>38407180</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>3432225700</fone></enderEmit><IE>7023871770001</IE><CRT>3</CRT></emit><dest><CNPJ>03387396000160</CNPJ><xNome>SÃƒÂƒO SALVADOR ALIMENTOS S.A</xNome><enderDest><xLgr>RODOVIA-GO 156 - KM 0,6</xLgr><nro>S/NÃ‚Âº</nro><xBairro>ZONA RURAL</xBairro><cMun>5210406</cMun><xMun>ITABERAI</xMun><UF>GO</UF><CEP>76630000</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>6233757100</fone></enderDest><indIEDest>1</indIEDest><IE>101651899</IE><email>roberta.galvao@ssa-br.com</email></dest><det nItem="1"><prod><cProd>MCA</cProd><cEAN/><xProd>MATERIAL PARA COLETA DE AMOSTRA(S)</xProd><NCM>23099010</NCM><CFOP>6949</CFOP><uCom>UN</uCom><qCom>1.00</qCom><vUnCom>1.9900</vUnCom><vProd>1.99</vProd><cEANTrib/><uTrib>UN</uTrib><qTrib>1.00</qTrib><vUnTrib>1.9900</vUnTrib><indTot>1</indTot><xPed>000000</xPed><nItemPed>00000</nItemPed></prod><imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>1.99</vBC><pICMS>7.00</pICMS><vICMS>0.14</vICMS></ICMS00></ICMS><IPI><cEnq>999</cEnq><IPINT><CST>51</CST></IPINT></IPI><PIS><PISAliq><CST>01</CST><vBC>1.99</vBC><pPIS>0.65</pPIS><vPIS>0.01</vPIS></PISAliq></PIS><COFINS><COFINSAliq><CST>01</CST><vBC>1.99</vBC><pCOFINS>3.00</pCOFINS><vCOFINS>0.06</vCOFINS></COFINSAliq></COFINS></imposto></det><total><ICMSTot><vBC>1.99</vBC><vICMS>0.14</vICMS><vICMSDeson>0.00</vICMSDeson><vFCPUFDest>0.00</vFCPUFDest><vICMSUFDest>0.00</vICMSUFDest><vICMSUFRemet>0.00</vICMSUFRemet><vFCP>0.00</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>0.00</vFCPST><vFCPSTRet>0.00</vFCPSTRet><vProd>1.99</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>0.00</vIPIDevol><vPIS>0.01</vPIS><vCOFINS>0.06</vCOFINS><vOutro>0.00</vOutro><vNF>1.99</vNF></ICMSTot></total><transp><modFrete>0</modFrete><transporta><CNPJ>42975391000100</CNPJ><xNome>BMU FRANSHING LTDA (CORREIOS)</xNome><IE>0017931280091</IE><xEnder>AV. COMENDADOR ALEXANDRINO GARCIA 1588</xEnder><xMun>UBERLANDIA</xMun><UF>MG</UF></transporta></transp><pag><detPag><tPag>90</tPag><vPag>1.99</vPag></detPag><vTroco>0.00</vTroco></pag><infAdic><infCpl>REMESSA DE MATERIAL PARA ANALISE A/C DE FABIANA.</infCpl></infAdic></infNFe><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#NFe31180923259427000104550010000052311651925842"><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/><Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>9JfikhZQzFvUzu3THysMkSOHFgs=</DigestValue></Reference></SignedInfo><SignatureValue>qQ/vuMfG0T0Virm6NKtqkLpU41nITuvkKcZaml0GuZsr5yogVDUU9Ky/7EGCdGpQjIXb1DfeZleucQIhiupm/qtDit5dyJtF7t9mu7LWz87n1t/Jg356ZXgM3qBj76pRTznsfTEmyWxD3YMn6NsAGppXHX6G+Xq6U5LintIlNn1h3KE6SnUzd2L/yDIXleSNtTQbPdICVZnkCMxenZ8p4OpL8Toj2sXPXM/1TGPXtN90ShW+0XlZNcrJQ/WlExpLL/nBZOA+ZthE5P5cqPvay8cN22JFPK6PYCymFVAanYqE7Ri9PlHyoF9OSk+xEHqOzCjAxuyldkCy7tivIjAYfQ==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></NFe>';
//echo $sXML = $sXML;
//echo preg_replace('/[^(\x20-\x7F)\x0A\x0D]*/','', $sXML);
//echo string2Slug($sXML);
//echo $xml = iconv("ISO-8859-1", "UTF-8//IGNORE", $sXML );

    //$sXML=("<NFe xmlns=\"http://www.portalfiscal.inf.br/nfe\"><infNFe Id=\"NFe31170523259427000104550010000033731790970782\" versao=\"4.00\"><ide><cUF>31</cUF><cNF>79097078</cNF><natOp>REMESSA DE MATERIAL PARA ANÁLISE</natOp><mod>55</mod><serie>1</serie><nNF>3373</nNF><dhEmi>2017-05-29T10:57:00-02:00</dhEmi><tpNF>1</tpNF><idDest>2</idDest><cMunFG>3170206</cMunFG><tpImp>1</tpImp><tpEmis>1</tpEmis><cDV>2</cDV><tpAmb>1</tpAmb><finNFe>1</finNFe><indFinal>1</indFinal><indPres>9</indPres><procEmi>3</procEmi><verProc>3.10.49</verProc></ide><emit><CNPJ>23259427000104</CNPJ><xNome>LAUDO LABORATÓRIO AVÍCOLA UBERLÂNDIA LTDA</xNome><xFant>LAUDO LABORATÓRIO</xFant><enderEmit><xLgr>RODOVIA BR 365, KM 615</xLgr><nro>S/N</nro><xBairro>ALVORADA</xBairro><cMun>3170206</cMun><xMun>Uberlandia</xMun><UF>MG</UF><CEP>38407180</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>3432225700</fone></enderEmit><IE>7023871770001</IE><CRT>3</CRT></emit><dest><CNPJ>37020260000309</CNPJ><xNome>NUTRIZA AGROINDUSTRIAL DE ALIMENTOS S/A</xNome><enderDest><xLgr>RODOVIA-GO 020</xLgr><nro>S/N</nro><xBairro>ZONA RURAL</xBairro><cMun>5217401</cMun><xMun>PIRES DO RIO</xMun><UF>GO</UF><CEP>75200000</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>6434617969</fone></enderDest><indIEDest>1</indIEDest><IE>102649189</IE><email>valderez.limberger@friato.com.br</email></dest><det nItem=\"1\"><prod><cProd>MCA</cProd><cEAN></cEAN><xProd>MATERIAL PARA COLETA DE AMOSTRA(S)</xProd><NCM>23099010</NCM><CFOP>6949</CFOP><uCom>UN</uCom><qCom>1.00</qCom><vUnCom>1.9900</vUnCom><vProd>1.99</vProd><cEANTrib></cEANTrib><uTrib>UN</uTrib><qTrib>1.00</qTrib><vUnTrib>1.9900</vUnTrib><indTot>1</indTot><xPed>000000</xPed><nItemPed>00000</nItemPed></prod><imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>1.99</vBC><pICMS>7.00</pICMS><vICMS>0.14</vICMS></ICMS00></ICMS><IPI><cEnq>999</cEnq><IPINT><CST>51</CST></IPINT></IPI><PIS><PISNT><CST>07</CST></PISNT></PIS><COFINS><COFINSNT><CST>07</CST></COFINSNT></COFINS></imposto></det><total><ICMSTot><vBC>1.99</vBC><vICMS>0.14</vICMS><vICMSDeson>0.00</vICMSDeson><vFCPUFDest>0.00</vFCPUFDest><vICMSUFDest>0.00</vICMSUFDest><vICMSUFRemet>0.00</vICMSUFRemet><vFCP>1</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>1</vFCPST><vFCPSTRet>1</vFCPSTRet><vProd>1.99</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>1</vIPIDevol><vPIS>0.00</vPIS><vCOFINS>0.00</vCOFINS><vOutro>0.00</vOutro><vNF>1.99</vNF></ICMSTot></total><transp><modFrete>0</modFrete><transporta><CNPJ>18260422000161</CNPJ><xNome>NACIONAL EXPRESSO LTDA</xNome><IE>7021867530016</IE><xEnder>PRAÇA DA BÍBLIA S/N</xEnder><xMun>UBERLANDIA</xMun><UF>MG</UF></transporta></transp><pag><detPag><tPag>01</tPag><vPag>1.00</vPag><card><tpIntegra>1</tpIntegra><CNPJ>63322115000112</CNPJ><tBand>01</tBand><cAut>01</cAut></card></detPag><vTroco>1.00</vTroco></pag><infAdic><infCpl>111</infCpl></infAdic></infNFe></NFe>");
    //echo $sXML;
	ECHO  string2Slug($sXML);
	
	?>