<?/*
Cancelar nota-fiscal de serviço na prefeitura - 16-10-2013 - hermesp
*/
include_once("../php/functions.php");

session_start();

$_SESSION["idnotafiscal"] = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
$_SESSION["xml"] = "";
$_SESSION["xmlret"] = "";
$_SESSION["steperro"]="";
$_SESSION["urlws"] = _URLNFSWS;

if(empty($_GET["idnotafiscal"])){
	die("É necessário informar o id da nota fiscal para cancelamento");
}

$sql="select motivoc,idempresa from notafiscal where idnotafiscal=".$_GET["idnotafiscal"];
$res=d::b()->query($sql) or die("Erro ao buscar motivo do cancelamento sql=".$sql);
$row=mysqli_fetch_assoc($res);
$_idempresa = $row['idempresa'];

if(empty($row['motivoc'])){
	die("Favor informar o motivo para o cancelamento");
}

$sqlr="select c.status,i.idremessa
		from contapagar c left join remessaitem i on (i.idcontapagar=c.idcontapagar)
		where c.idobjeto = ".$_GET["idnotafiscal"]."
		and c.tipoobjeto = 'notafiscal'";

$resr=d::b()->query($sqlr) or die("Erro ao buscar se existe remessa sql=".$sqlr);
while($rowr=mysqli_fetch_assoc($resr)){
	if($rowr['status']=='QUITADO'){
		die("Nota não pode ser cancelada pois possui fatura Quitada.");
	}
	if(!empty($rowr['idremessa'])){
		die("Nota não pode ser cancelada pois possui remessa vinculada.");
	}
}

retcert();

/*
 * maf010410: funcao criada para efetuar a montagem das tags XML
 * As condicoes para montagem estao na tabela nfsxmltree
 * Esta funcao eh chamada pela etapa de geracao do XML
 */
function montatag($inidnf,$t,$r){
	$_SESSION["steperro"]="montatag";

	$strret = "";
	//Insere o atributo das tags caso tenha
	if(!empty($r["atrfixo"])){
		$atrfixo = " ".trim($r['atrfixo']);
	}
	if(!empty($r["atrvararr"])){
		$atrfixo =" ".$_SESSION[$r["atrvararr"]][$r["no"]];
	}
	/*
	 * maf300310:Verifica qual VALOR sera inserido dentro da TAG.
	 * Valores possiveis:
	 * - valor fixo: esta escrito na tabela de configuracao
	 * - simples: pega somente 1 linha de um array conforme configuracao da tablea
	 * - loop: efetua LOOP em array pre-executado e escreve, para cada chave e valor encontrado, as tags e valor 
	 */

	if(!empty($r["vlrfixo"])){//possui valor fixo
		$vlrfixo = trim($r['vlrfixo']);
		return "\n".$t."<".$r['no'].$atrfixo.">".$vlrfixo;

	}elseif(!empty($r["arrorigem"])){//tem que buscar num array de dados
		if($r["looptag"]=="N"){
			$vlrfixo =$_SESSION[$r["arrorigem"]][$r["no"]];
			return "\n".$t."<".$r['no'].$atrfixo.">".$vlrfixo;
		}elseif($r["looptag"]=="Y"){
			//print_r($r["arrorigem"]);
			//print_r($_SESSION[$r["arrorigem"]]);
			foreach ($_SESSION[$r["arrorigem"]]as $linha => $arr) {//Linhas de resultado
				$strret.="\n".$t."<".$r["no"].">";
				foreach ($arr as $campo => $valor) {//Linhas de resultado
				$strret.= "\n".$t.chr(9)."<".$campo.">".$valor."</".$campo.">";
				}
				$strret.="\n".$t."</".$r["no"].">";
			}
			return $strret;
		}else{
			return "ERRO: looptag sem valor!";
		}
	}else{
		return "\n".$t."<".$r['no'].$atrfixo.">";//nao possui valor ou pode ser NO
	}
  
}

/*
 * maf010410: funcao recursiva que le a estrutura do xml na tabela nfsxmltree
 * Com os dados existentes na tabela, percorre recursivamente os NODES encontrados e 
 * vai montando os valores conforme a configuracao (colunas da tabela nfsxmltree) informada 
 */
function geraxml($inidnf,$proc="",$node=0,$level=0){ 

	$_SESSION["steperro"]="geraxml[".$node."]";
	
	for ($i = 1; $i <= $level; $i++) { 
		$t1 .= chr(9);//Indenta Estrutura 
	} 
	$level++; 
	
	$sql = "SELECT * FROM "._DBNFE.".nfsxmltree where ativo = 'Y' and processo='".$proc."' and pai = ".$node." order by ord";
 	$res = d::b()->query($sql); 
	// die($sql);
	if (!$res){
		$msgerr = "Erro ao consultar nfsxmltree: ".mysqli_error()."\n SQL: ".$sql;
		//STATUSLOTE($_SESSION["numerorps"],"ERRO");
		//LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		echo($sqlerr);
		return false;
	}
	 
	while ($r = mysqli_fetch_assoc($res)) { 

		//verifica se o pai tem filhos
		$sqlleaf = "select count(*) as qtleaf from "._DBNFE.".nfsxmltree where pai = ".$r['idnfsxmltree'];
		$resassoc = d::b()->query($sqlleaf) or die("Erro ao consultar nodes/leafs: ".mysqli_error()."\n SQL: ".$sqlleaf);
		$rleaf = mysqli_fetch_assoc($resassoc);
		$qtleaf = $rleaf["qtleaf"];
		
		if($qtleaf > 0){ //NODE
			//echo $t1."<".$r['no'].">\n"; 
			$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
			geraxml($inidnf,$proc,$r['idnfsxmltree'],$level);
			$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";
		}else{
			//echo $t1."<".$r['no'].">\n"; 
			$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
			if($r["looptag"]=="N"){ //não fecha o ultimo nó pois ele foi montado pelo loop na montatag
				$_SESSION["xml"] .= "</".$r['no'].">";
			}
		}
	} 
} 

/*
 * maf050410: Responsavel por ler o arquivo original do SERASA 
 * e extrair as chaves publicas e privadas de encriptacao
 */
function retcert(){
	global $_idempresa;
	//if(_NFSECHOLOG)echo " - retcert:certificado";

	$_SESSION["steperro"]="retcert";
	
	//if(empty($_SESSION["certprivkey"]) or empty($_SESSION["certpublkey"])){
        
		
		$_SESSION["certprivkey"]="";
		$_SESSION["certpublkey"]="";
                
		$sqlc="SELECT senha, SUBSTRING(certificado,10) as certificado
		from empresa where idempresa = ".$_idempresa;
		$resc=d::b()->query($sqlc) or die("A Consulta do certificado falhou :".mysqli_error(d::b())."<br>Sql:".$sqlc);
		$rowc=mysqli_fetch_assoc($resc);
		
		if(empty($rowc['certificado']) or empty($rowc['senha'])){
			die("Verificar o certificado no cadastro da empresa.");
		}
		
		$arqpfx = "sefaz4/certs/".$rowc['certificado'];
		$passpfx = $rowc['senha'];
		
                
                /*
		if($_SESSION["SESSAO"]["IDEMPRESA"]==1){
                    $arqpfx = "cert/laudolaboratorio.pfx";
                    $passpfx = "37383738";	//	senha
                }elseif($_SESSION["SESSAO"]["IDEMPRESA"]==4){                    
                    $arqpfx = "cert/hubiobiopar.pfx";
                    $passpfx = "37383738";
                }                 
                 */
                
                
		$sPrivateKey;//string	da	chave	privada
		$sPublicKey;//string	do	certificado	(chave	publica)
	
		$strcompini = '-----BEGIN CERTIFICATE';
		$strcompfim = '-----END CERTIFICATE';
		
		$contarq = file_get_contents($arqpfx);
		
		if(!$contarq){
			die("Erro ao abrir certificado [".$arqpfx."]");
		}
		
		//passa a chave publica para um array
		openssl_pkcs12_read($contarq, $x509cert, $passpfx);
	
		//chave	publica	(certificado)
		$aCert	=	explode("\n",	$x509cert['cert']);
		foreach	($aCert	as	$curData)	{
			if	(strncmp($curData, $strcompini,	22) != 0 && strncmp($curData, $strcompfim, 20) != 0){
				
				$sPublicKey .= trim($curData);
				//echo "<br>passei";
				//$sPublicKey = str_ireplace($strcompini, "", $sPublicKey);
				//$sPublicKey = str_ireplace($strcompfim, "", $sPublicKey);
			}
		}
	//-----BEGIN	CERTIFICATE
	//-----BEGIN CERTIFICATE-----
		//	chave	privada
		$sPrivateKey	=	$x509cert['pkey'];
		//echo  $sPublicKey;
		$_SESSION["certprivkey"]=$sPrivateKey;
		$_SESSION["certpublkey"]=$sPublicKey;
		$_SESSION["certunexplode"]=$x509cert['cert'];
		$_SESSION["certpass"]=$passpfx;
		//die($sPublicKey);
		//die($_SESSION["certprivkey"]);
	//}
}

/*
 * maf050410: responsavel por gerar as tages de assinatura conforme padrao W3C/IETF
 * para XML Signature
 */
function assinaXML($docxml, $tagid){
	$URLdsig='http://www.w3.org/2000/09/xmldsig#';
	$URLCanonMeth='http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
	$URLSigMeth='http://www.w3.org/2000/09/xmldsig#rsa-sha1';
	$URLTransfMeth_1='http://www.w3.org/2000/09/xmldsig#enveloped-signature';
	$URLTransfMeth_2='http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
	$URLDigestMeth='http://www.w3.org/2000/09/xmldsig#sha1';
	
	
	try {
		$xml = $docxml;
		// obter o chave privada para a ssinatura
		$prikeyid = openssl_pkey_get_private($_SESSION["certprivkey"], $_SESSION["certpass"]);
		// limpeza do xml com a retirada dos CR, LF e TAB
		$order = array("\r\n", "\n", "\r", "\t");
		$replace = '';
		$xml = str_replace($order, $replace, $xml);
		// Habilita a manipulação de erros da libxml
		libxml_use_internal_errors(true);
		//limpar erros anteriores que possam estar em memória
		libxml_clear_errors();
		// carrega o documento no DOM
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$xmldoc->preservWhiteSpace = true; //elimina espaços em branco
		$xmldoc->formatOutput = false;
		// muito importante deixar ativadas as opções para limpar os espacos em branco
		// e as tags vazias
		if ($xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)) {
			$root = $xmldoc->documentElement;
		} else {
			$msg = "Erro ao carregar XML, provavel erro na passagem do parametro docxml ou no proprio xml!!";
			$errors = libxml_get_errors();
			if (!empty($errors)) {
				$i = 1;
				foreach ($errors as $error) {
					$msg .= "\n  [$i]-" . trim($error->message);
				}
				libxml_clear_errors();
			}
			throw new Exception($msg);
		}
		//extrair a tag com os dados a serem assinados
		$node = $xmldoc->getElementsByTagName($tagid)->item(0);
		if (!isset($node)) {
			$msg = "A tag < $tagid > não existe no XML!!";
			throw new Exception($msg);
		}
		$id = trim($node->getAttribute("Id"));
		$idnome = preg_replace('/[^0-9]/', '', $id);
		//extrai os dados da tag para uma string
		$dados = $node->C14N(false, false, NULL, NULL);
		//calcular o hash dos dados
		$hashValue = hash('sha1', $dados, true);
		//converte o valor para base64 para serem colocados no xml
		$digValue = base64_encode($hashValue);
		//monta a tag da assinatura digital
		$Signature = $xmldoc->createElement('Signature');
		//adiciona a assinatura
		$node->parentNode->appendChild($Signature);
		$Signature->setAttribute('xmlns', $URLdsig);
		
		$SignedInfo = $xmldoc->createElement('SignedInfo');
		$Signature->appendChild($SignedInfo);
		//Cannocalization
		$newNode = $xmldoc->createElement('CanonicalizationMethod');
		$SignedInfo->appendChild($newNode);
		$newNode->setAttribute('Algorithm', $URLCanonMeth);
		//SignatureMethod
		$newNode = $xmldoc->createElement('SignatureMethod');
		$SignedInfo->appendChild($newNode);
		$newNode->setAttribute('Algorithm', $URLSigMeth);
		//Reference
		$Reference = $xmldoc->createElement('Reference');
		$SignedInfo->appendChild($Reference);
		$Reference->setAttribute('URI', '#' . $id);
		//Transforms
		$Transforms = $xmldoc->createElement('Transforms');
		$Reference->appendChild($Transforms);
		//Transform
		$newNode = $xmldoc->createElement('Transform');
		$Transforms->appendChild($newNode);
		$newNode->setAttribute('Algorithm', $URLTransfMeth_1);
		//Transform
		$newNode = $xmldoc->createElement('Transform');
		$Transforms->appendChild($newNode);
		$newNode->setAttribute('Algorithm', $URLTransfMeth_2);
		//DigestMethod
		$newNode = $xmldoc->createElement('DigestMethod');
		$Reference->appendChild($newNode);
		$newNode->setAttribute('Algorithm', $URLDigestMeth);
		//DigestValue
		$newNode = $xmldoc->createElement('DigestValue', $digValue);
		$Reference->appendChild($newNode);
		// extrai os dados a serem assinados para uma string
		$dados = $SignedInfo->C14N(false, false, NULL, NULL);
		//inicializa a variavel que irá receber a assinatura
		$signature = '';
		//executa a assinatura digital usando o resource da chave privada
		$resp = openssl_sign($dados, $signature, $prikeyid);
		//codifica assinatura para o padrao base64
		$signatureValue = base64_encode($signature);
		//SignatureValue
		$newNode = $xmldoc->createElement('SignatureValue', $signatureValue);
		$Signature->appendChild($newNode);
		$_ID=str_replace('ID_','',$idnome);

    	$newNode->setAttribute('Id', "ID_ASSINATURA_PEDIDO_CANCELAMENTO_$_ID");
		//KeyInfo
		$KeyInfo = $xmldoc->createElement('KeyInfo');
		$Signature->appendChild($KeyInfo);
		//X509Data
		$X509Data = $xmldoc->createElement('X509Data');
		$KeyInfo->appendChild($X509Data);

		//X509Certificate
		$newNode = $xmldoc->createElement('X509Certificate', $_SESSION["certpublkey"]);
		$X509Data->appendChild($newNode);
		//grava na string o objeto DOM
		$xml = $xmldoc->saveXML($xmldoc->documentElement);
		// libera a memoria
		openssl_free_key($prikeyid);
	} catch (Exception $e) {
		throw $e;
	}
	//retorna o documento assinado
	return $xml;
} //fim signXML


/*
 * maf010410: esta funcao consulta os dados das notas fiscais no banco de origem 
 * e abre arrays contendo as informacoes padronizadas com a tabela nfsxmltree
 */
function CONSULTAORI()
{
	$insidnfslote=5;
	$varrnota=sdfas;
	//recupera a session com o id da RPS
	$idnotafiscal = $_SESSION["idnotafiscal"];
	$_SESSION["arrnfsidlote"]["Lote"]= "Id=\"lote:".$insidnfslote."\"";
	$_SESSION["arrnota"]["Nota"]= "Id=\"Nota:".$varrnota."\"";
	/*
	 * Consulta dados do cabeçalho
	 */
 	$sqlcab = "SELECT * 
 				FROM "._DBAPP.".vwnfcancel 
 				where idnotafiscal=".$idnotafiscal;
	$rescab = d::b()->query($sqlcab);
	$nrows= mysqli_num_rows($rescab);
	//die($sqlcab);

	if($nrows > 1){
		$msgerr = "Erro possivelmente existe mais de um endereço de Cobrança: \n- ".mysqli_error()."\n".date("H:i:s")." - ".$sqlcab;
		echo($sqlerr);
		return false;
	}
	
	if(!$rescab){
		$msgerr = "Erro ao consultar Cabecalho: \n- ".mysqli_error()."\n".date("H:i:s")." - ".$sqlcab;
		echo($sqlerr);
		return false;
	}else{
		$cab = mysqli_fetch_assoc($rescab);
		$_SESSION["vwnfcancel"]= $cab;
	} 	

 //print_r($_SESSION["vwnfcancel"]); 

	return true;
}

/*
 * maf010410: funcao responsavel por GERAR o texto do XML que ira ser enviado
 */
function GERACAO(){
	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - GERACAO: gerando xml";
	$_SESSION["steperro"]="geracao";	

	$idnotafiscal = $_SESSION["idnotafiscal"];
	//recupera a session com o id 
	$sql = "SELECT * FROM vwnfcancel where idnotafiscal = " .$idnotafiscal;

	$res = d::b()->query($sql); 

	if (!$res){
		$msgerr = "Erro ao consultar vwnfcancel: ".mysqli_error()."\n SQL: ".$sql;
		//STATUSLOTE($_SESSION["numerorps"],"ERRO");
		//LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		echo($sqlerr);
		return false;
	}

	$row = mysqli_fetch_assoc($res);

	$xmldoc = new DOMDocument('1.0', 'utf-8');
	$xmldoc->preservWhiteSpace = true; //elimina espaços em branco
	$xmldoc->formatOutput = false;
	$xmldoc->loadXML("<CancelarNfseEnvio xmlns=\"http://www.abrasf.org.br/nfse.xsd\" xmlns:ns2=\"http://www.w3.org/2000/09/xmldsig#\"></CancelarNfseEnvio>");

	$root = $xmldoc->documentElement;

	$pedido = $xmldoc->createElement('Pedido');
	$root->appendChild($pedido);

	$infPedidoCancelamento = $xmldoc->createElement('InfPedidoCancelamento');
	$pedido->appendChild($infPedidoCancelamento);
	$infPedidoCancelamento->setAttribute('Id', 'ID_PEDIDO_CANCELAMENTO_'.$row['NumeroNota']);

	$identificacaoNfse = $xmldoc->createElement('IdentificacaoNfse');
	$infPedidoCancelamento->appendChild($identificacaoNfse);

	$numero = $xmldoc->createElement('Numero', $row['NumeroNota']);
	$identificacaoNfse->appendChild($numero);

	$cpfCnpj = $xmldoc->createElement('CpfCnpj');
	$identificacaoNfse->appendChild($cpfCnpj);

	$cnpj = $xmldoc->createElement('Cnpj', $row['CPFCNPJRemetente']);
	$cpfCnpj->appendChild($cnpj);

	$inscricaoMunicipal = $xmldoc->createElement('InscricaoMunicipal', $row['InscricaoMunicipalPrestador']);
	$identificacaoNfse->appendChild($inscricaoMunicipal);

	$codigoMunicipio = $xmldoc->createElement('CodigoMunicipio', $row['CodCidade']);
	$identificacaoNfse->appendChild($codigoMunicipio);

	$codigoCancelamento = $xmldoc->createElement('CodigoCancelamento', 2);
	$infPedidoCancelamento->appendChild($codigoCancelamento);

	$_SESSION["xml"] = $xmldoc->saveXML($xmldoc->documentElement);

	return true;
}

/*
 * maf010410: funcao responsavel por ASSINAR o xml gerado
 */
function ASSINATURA(){

	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ASSINATURA: gerando assinatura";
	$_SESSION["steperro"]="assinatura";
	
	/*
	 * inicia funcao recursiva de montagem
	 */
	$xmtlutf = $_SESSION["xml"];
	$_SESSION["xml"] = assinaXML($xmtlutf,"InfPedidoCancelamento");
	
//	die($_SESSION["xml"]);
	return true; 
}

/*
 * maf010410: funcao responsavel por efetuar a conexao com o WebService da prefeitura
 */
function CONEXAO()
{
	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONEXAO: conetando ao ws [".$_SESSION["urlws"]."]";
	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONEXAO";
	$_SESSION["steperro"]="conexao";
		
	ini_set("soap.wsdl_cache_enabled", "0");

	// URL do WSDL para verificar os métodos
	$urlwsdl = 'https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse.wsdl';
	// URL do ambiente de homologação
	$urlsoap = 'https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse';

	$tempCertFile = tempnam(sys_get_temp_dir(), 'cert');
	file_put_contents($tempCertFile, $_SESSION["certunexplode"] . $_SESSION["certprivkey"]);

	$options = array(
		'trace' => 1,
		'cache_wsdl' => WSDL_CACHE_NONE,
		'exceptions' => 1,
		'use' => SOAP_LITERAL,
		'stream_context' => stream_context_create(array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'local_cert' => $tempCertFile,
				'passphrase' => $_SESSION["certpass"]
			)
		))
	);

	$dadosMsg = $_SESSION["xml"];
	$cabecalhoCdata = '<?xml version="1.0" encoding="UTF-8"?><cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04"><versaoDados>2.04</versaoDados></cabecalho>';

	try {
		//$_SESSION["ws"] = new SoapClient($_SESSION["urlws"],array('trace' => 1));
		$client = new SoapClient($urlwsdl,$options);
		$client->__setLocation($urlsoap);
	}catch(Exception $e){//Se não conseguir conectar grava o erro e retorna falso
		$msgerr = "Erro ao conectar WS: \n".$e;
		echo($msgerr);
		return false;
	}

	$params = new stdClass();
	$params->nfseCabecMsg = $cabecalhoCdata;
	$params->nfseDadosMsg = $dadosMsg;

	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ENVIO";
	
	//$_xml = $_SESSION["xml"];
	try {
		//envia o XML atraves da conexão SOAP
		$res = $client->__soapCall("CancelarNfse",array($params));
	}catch(SoapFault $e){//Se não conseguir enviar grava o erro e retorna falso
		$msgerr = "Erro [1] ao enviar XML: \n".$e;
		
		echo($msgerr);
		return false;
	}

	if (!$res){
		$msgerr = "Erro [2] ao enviar XML:\n".$e;
	
		echo($msgerr);
		return false;
	}

	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ANALISE";

	$doc = DOMDocument::loadXML($res->outputXML);
	
	//echo($res);die;
		
	if (!$doc){
		$msgerr = "O WS não retornou XML válido:\n".$res;

		echo($msgerr);
		return false;
	}else{
		//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONCLUSAO";
		$_SESSION["xmlret"] = $res->outputXML;
		
		//print_r($doc); 

		//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para CABECALHO no xml de retorno
		$confirmacao = $doc->getElementsByTagName("Confirmacao")->item(0);
		
				
		if($confirmacao){
			
				$sqli="select l.idnfslote from  notafiscal n,nfslote l 
					where l.status = 'SUCESSO'
					and l.numerorps = n.numerorps
					and n.idnotafiscal=".$_SESSION["idnotafiscal"];
				$resi=d::b()->query($sqli) or die("erro ao buscar idnfslote");
				$rowi=mysqli_fetch_assoc($resi);

				//LTM - 22-07-2021: Inserido o Fluxo para Cancelado
				$sqlu="UPDATE notafiscal  n
						  SET n.status='CANCELADO', n.idfluxostatus = '829', n.alteradoem=sysdate()
						WHERE n.idnotafiscal=".$_SESSION["idnotafiscal"];
				$retu = d::b()->query($sqlu) or die("erro ao cancelar notafiscal sql:".$sqlu);
				
				$sqld="delete from notafiscalitens where idnotafiscal=".$_SESSION["idnotafiscal"];
				$retd = d::b()->query($sqld) or die("erro ao retirar itens da notafiscal sql:".$sqld);
				
				$sqlu="update nfslote l
				SET l.status ='CANCELADO',l.alteradoem=sysdate()
				where l.status = 'SUCESSO'
				and l.idnfslote=".$rowi["idnfslote"];
				
				$retu = d::b()->query($sqlu) or die("erro ao atualizar status do log da nfe sql:".$sqlu);		
				
				$sql="select i.* from nfitem i join nf n on(n.idnf=i.idnf)
				join contapagaritem ci on(ci.idobjetoorigem = n.idnf and ci.tipoobjetoorigem ='nf' and ci.status!='QUITADO')
								where i.idobjetoitem =".$_SESSION["idnotafiscal"]." 
								and i.tipoobjetoitem = 'notafiscal'";
		
				$resf=d::b()->query($sql) or die("[nfcancel] - Erro 1 ao buscar se ja exite o item do imposto sql=".$sql." mysql".mysqli_error(d::b()));      
				$Vqtdnfitem=mysqli_num_rows($resf);
				if($Vqtdnfitem>0){
					while($row=mysqli_fetch_assoc($resf)){
						$sd="delete from nfitem where idnfitem=".$row['idnfitem'];
						$rd=d::b()->query($sd) or die('[poschange_pedido] - Falha ao excluir nf item imposto sql='.$sd);
				
					}
				}
				
				
			if(!$retu){
				//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
				echo("Erro ao alterar LOTE: \n- ".mysqli_error()."\n".date("H:i:s")." - ".$sqlu);
				return false;
			}else{			

				//esvazia variveis com texto de xml
				//unset($_SESSION["xml"]);
				//unset($_SESSION["xmlret"]);
				return true;
			}
					
			
		}else{
			//echo($res);
		    $Alertas = $doc->getElementsByTagName("ListaMensagemRetorno")->item(0);
			if($Alertas!=null){
		    	$Alerta = $Alertas->getElementsByTagName("MensagemRetorno")->item(0);
				if($Alerta!=null){
					
					$Descricao= $Alerta->getElementsByTagName("Mensagem")->item(0);
					
					$vDescricao =($Descricao->textContent); //->length;
				//	$codigo= $Alerta->getElementsByTagName("codigo")->item(0);

					// $vcodigo =($codigo->textContent); //->length;
				}
				else
					$vDescricao =($Alerta); //->length;
			}else{
				$vDescricao =($Alertas);
			}
		    //codigo 1475 A Solicitacao de aceite para esta nota ja existe

		    // Para caso de notas ja canceladas
		    $findme = 'NFSe ja cancelada';
		    $pos = strpos($vDescricao, $findme);
		    if ($pos === false) {
		       // echo "A string '$findme' não foi encontrada na string '$vDescricao'";
			
			$msgerr = "\n".$vDescricao;
			
			echo $msgerr;

			$sqli="select l.idnfslote from  notafiscal n,nfslote l 
				where l.status = 'SUCESSO'
				and l.numerorps = n.numerorps
				and n.idnotafiscal=".$_SESSION["idnotafiscal"];
			$resi=d::b()->query($sqli) or die("erro ao buscar idnfslote");
			$rowi=mysqli_fetch_assoc($resi);

			$sqllog = "insert into nfslog (
						idnfslote
						,etapa
						,erro
						,coderro) 
						values (
							".$rowi['idnfslote']."
							,'CANCELAMENTO'
							,'".$msgerr."'
							,1)";
			$retlog = d::b()->query($sqllog);
			if(!$retlog){
				die("Erro ao inserir LOG: \n- ".mysqli_error()."\n".date("H:i:s")." - ".$sqllog);
			}
	
			return false;
		    } else {

			//verifica se tem imposto para deletar
			$sql="select * from nfitem 
			where idobjetoitem = ".$_SESSION["idnotafiscal"]." 
			and tipoobjetoitem = 'notafiscal'";

			$resf=d::b()->query($sql) or die("[nfcancel] - Erro 1 ao buscar se ja exite o item do imposto sql=".$sql." mysql".mysqli_error(d::b()));      
			$Vqtdnfitem=mysqli_num_rows($resf);
			if($Vqtdnfitem>0){
				while($row=mysqli_fetch_assoc($resf)){
					$sd="delete from nfitem where idnfitem=".$row['idnfitem'];
					$rd=d::b()->query($sd) or die('[poschange_pedido] - Falha ao excluir nf item imposto sql='.$sd);
					atualizavalornf($row['idnf']);
					atualizafat($row['idnf']);
				}
			}


			//echo "A string '$findme' foi encontrada na string '$vDescricao'";
			$sqli="select l.idnfslote from  notafiscal n,nfslote l 
				where l.status = 'SUCESSO'
				and l.numerorps = n.numerorps
				and n.idnotafiscal=".$_SESSION["idnotafiscal"];
			$resi=d::b()->query($sqli) or die("erro ao buscar idnfslote");
			$rowi=mysqli_fetch_assoc($resi);
	
			//LTM - 22-07-2021: Inserido o Fluxo para Cancelado
			$sqlu="UPDATE notafiscal  n
					  SET n.status = 'CANCELADO', n.idfluxostatus = '829', n.alteradoem = sysdate()
				    WHERE n.idnotafiscal = ".$_SESSION["idnotafiscal"];
			$retu = d::b()->query($sqlu) or die("erro ao cancelar notafiscal sql:".$sqlu);

			$sqld="delete from notafiscalitens where idnotafiscal=".$_SESSION["idnotafiscal"];
			$retd = d::b()->query($sqld) or die("erro ao retirar itens da notafiscal sql:".$sqld);

			$sqlu="update nfslote l
			SET l.status ='CANCELADO',l.alteradoem=sysdate()
			where l.status = 'SUCESSO'
			and l.idnfslote=".$rowi["idnfslote"];

			$retu = d::b()->query($sqlu) or die("erro ao atualizar status do log da nfe sql:".$sqlu);			

				
			if(!$retu){
				//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
				echo("Erro ao alterar LOTE: \n- ".mysqli_error()."\n".date("H:i:s")." - ".$sqlu);
				return false;
			}else{
				//esvazia variveis com texto de xml
				//unset($_SESSION["xml"]);
				//unset($_SESSION["xmlret"]);
				return true;
			}
			   
		    }
		}	
		
	}	
}

function atualizavalornf($idnotafiscal){

	$sql="select ifnull(sum(i.total),0) as total from  nfitem i 
		where i.idnf=".$idnotafiscal;
		$re= d::b()->query($sql)or die("[index:atualizavalornf] Erro 1 ao buscar valor da nf : ". mysql_error() . "<p>SQL: ".$sql);
	$row=mysqli_fetch_assoc($re);

	$sqlu="update nf set total='".$row['total']."' where idnf=".$idnotafiscal;
	$resu= d::b()->query($sqlu) or die("[index:atualizavalornf] Erro 2 ao atualizar nf  : ". mysql_error() . "<p>SQL: ".$sqlu);
	//gerar faturamento
	return $idnf;

}

function atualizafat($idnotafiscal,$idformapagamento=null){

	if(empty($idformapagamento)){
		$idformapagamento=traduzid('nf','idnf','idformapagamento',$idnotafiscal);
		if(empty($idformapagamento)){
			die('[api/nf]-forma de pagamento não encontrada');
		}
	}        

	//BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
	$sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota from formapagamento where idformapagamento=".$idformapagamento;
	$rf=d::b()->query($sf) or die("[api/nf]-Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
	$formapagamento=mysqli_fetch_assoc($rf);
	//echo($sf."<br>");

	$arrParcelas= recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
	$qtParcelas =$arrParcelas['quant'];
  //  echo($qtParcelas." parcelas<br>");
	$arrParcelasFechado= recuperaParcelas($idnotafiscal,'FECHADO','nf');//Contapagar fechado
	$qtParcelasFechadas =$arrParcelasFechado['quant'];
   // echo($qtParcelasFechadas." qtParcelasFechadas<br>");
/*impostos da erro se usar
	$arrParcelasPendente= cnf::recuperaParcelas($idnotafiscal,'PENDENTE','nf');//Contapagar fechado
	$qtParcelasPendente =$arrParcelasPendente['quant'];
*/      
	$arrParcelasIV= recuperaParcelasItensVinc($idnotafiscal,'nf');
	$qtParcelasIV =$arrParcelasIV['quant'];
  //  echo($arrParcelasIV." arrParcelasIV<br>");
			
	$arrlinhasbol=  verificaboleto($idnotafiscal);
	$qtdlinhasbol=$arrlinhasbol['quant'];
  //  echo($qtdlinhasbol." qtdlinhasbol<br>");
	//die($qtParcelas);
	$arrParcItens= getParcelaItens($idnotafiscal);
	$qtParcelasitem = $arrParcItens['quant'];
   // echo($qtParcelasitem." qtParcelasitem<br>");
	
	$arrParcItensFechada= getParcelaItensfechada($idnotafiscal,$formapagamento['agrupnota']);
	$qtParcelasitemFechada = $arrParcItensFechada['quant'];   
	//echo($qtParcelasitemFechada." qtParcelasitemFechada<br>");

	$qtdprog=recuperaParcelasProg($idnotafiscal,'nf');
	// echo($qtdprog." qtdprog<br>");
	//echo($arrParcelas['quant']." - ".$arrlinhasbol['quant']." - ".$qtParcelasitem ." - ".$qtParcelasIV);die;
	if ($qtParcelas == 0  and  $qtdprog <1 and $qtdlinhasbol== 0 and $qtParcelasitem==0 and $qtParcelasIV==0 and $qtParcelasFechadas==0 and $qtParcelasitemFechada==0){
	//deleta as parcelas existentes.
	   // echo($deleta." deleta:".$idnotafiscal."<br>");
		deletaParcelasExistentes($idnotafiscal);
		//echo(" deletaParcelasExistentes<br>");
		//gerarContapagar($idnotafiscal);
	
			
		$sql="select * from nfitem 
				where idobjetoitem = ".$idnotafiscal." 
				and tipoobjetoitem = 'notafiscal'";

		$resf=d::b()->query($sql) or die("[nfcancel] - Erro 1 ao buscar se ja exite o item do imposto sql=".$sql." mysql".mysqli_error(d::b()));      
		$Vqtdnfitem=mysqli_num_rows($resf);
		if($Vqtdnfitem>0){
			while($row=mysqli_fetch_assoc($resf)){
				$sd="delete from nfitem where idnfitem=".$row['idnfitem'];
				$rd=d::b()->query($sd) or die('[poschange_pedido] - Falha ao excluir nf item imposto sql='.$sd);
				atualizavalornf($row['idnf']);
				atualizafat($row['idnf']);
			}
		}

		
		
		
	   // echo(" gerarContapagar<br>");
		//agrupaCP(); 
		//echo(" agrupaCP<br>");
	}
  //  echo('fim atualizafat <br>');
}

function gerarContapagar($idnotafiscal){
	

	$sql="select * from nf where idnf=".$idnotafiscal;
	$res= d::b()->query($sql) or die("[Laudo:] Erro gerarContapagaritem ao busca dados da nf  : ". mysql_error() . "<p>SQL: ".$sql);
	$row=mysqli_fetch_assoc($res);

	if($row['geracontapagar']=="Y"){	

		$sf="select idagencia,agruppessoa,agrupado,agrupfpagamento,agrupnota from formapagamento where idformapagamento=".$row['idformapagamento'];
		$rf=d::b()->query($sf) or die("Erro ao buscar configuração da forma de pagamento: sql=".$sf." mysql".mysqli_error(d::b()));
		$formapagamento=mysqli_fetch_assoc($rf);

		$sqlf="select ifnull(sum(frete),0) as sumfrete
		from nfitem
		where idnf =".$idnotafiscal;
		$resf=d::b()->query($sqlf) or die("erro ao verificar iten frete da notafiscal sql=".$sqlf);
		$rowf= mysqli_fetch_assoc($resf);

		$sqlcx="select * from nfconfpagar where idnf=".$idnotafiscal;
		$rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
		$qtdparcelas= mysqli_num_rows($rescx);
		if($row['tiponf']=="V"){
			$visivel="S";
			$tipo="C";
		}elseif($row['tiponf']=="C" or$row['tiponf']=="T" or $row['tiponf']=="S" or $row['tiponf']=="E" or $row['tiponf']=="M" or $row['tiponf'] =="B"){//if($tiponf=="V"){
			$visivel="S";
			$tipo="D";	
		}elseif( $row['tiponf']=="D" or $row['tiponf']=="R"){		
			$visivel="N";	
			$tipo="D";
		}else{		
			$visivel='N';
			$tipo="D";
		}

		$index = 0;
		while($rowcx=mysqli_fetch_assoc($rescx)){
			$index++;		 
		
			//Insere novas parcelas
			$valorparcela = $row['total']*($rowcx['proporcao']/100);

			$valorparcelarep =(($row['total']-$rowf['sumfrete'])/($rowcx['proporcao']/100));

			$vencimentocalc = $rowcx['datareceb'];
			$recebcalc = $rowcx['datareceb'];

			if($formapagamento['tipo']=='COMISSAO'){
				$status='ABERTO';
			}else{
				$status='PENDENTE';
			}

			if($formapagamento['agrupado']=='Y'){//se for agrupado	

				$insnfcp[1]['status']=$status;	
				$insnfcp[1]['idpessoa']=$row['idpessoa'];
				$insnfcp[1]['idempresa']=$row['idempresa'];
				$insnfcp[1]['idobjetoorigem']=$idnotafiscal;
				$insnfcp[1]['tipoobjetoorigem']='nf';
				$insnfcp[1]['tipo']=$tipo;
				$insnfcp[1]['visivel']=$visivel;
				$insnfcp[1]['parcela']=$index;
				$insnfcp[1]['parcelas']=$row['parcelas'];
				$insnfcp[1]['datapagto']=$recebcalc;
				$insnfcp[1]['valor']=$valorparcela;
				$insnfcp[1]['obs']=$rowcx['obs'];
				$insnfcp[1]['idformapagamento']=$row['idformapagamento'];
				$insnfcp[1]['criadopor']='cron_processapendentes';
				$insnfcp[1]['criadoem']=date("Y-m-d H:i:s");
				$insnfcp[1]['alteradopor']='cron_processapendentes';
				$insnfcp[1]['alteradoem']=date("Y-m-d H:i:s");	

				$idnfcp=inseredb($insnfcp,'contapagaritem');
				
			}else{	
				$idfluxostatus = getId_FluxoStatus('contapagar', $status);
				$insnfcp[1]['status']=$status;
				$insnfcp[1]['idfluxostatus'] = $idfluxostatus;
				$insnfcp[1]['idformapagamento']=$row['idformapagamento'];
				$insnfcp[1]['idempresa']=$row['idempresa'];
				$insnfcp[1]['idpessoa']=$row['idpessoa'];
				$insnfcp[1]['idobjeto']=$idnotafiscal;
				$insnfcp[1]['tipoobjeto']='nf';
				$insnfcp[1]['tipo']=$tipo;
				$insnfcp[1]['visivel']=$visivel;
				$insnfcp[1]['parcela']=$index;
				$insnfcp[1]['parcelas']=$row['parcelas'];
				$insnfcp[1]['datapagto']=$vencimentocalc;
				$insnfcp[1]['datareceb']=$recebcalc;
				$insnfcp[1]['valor']=$valorparcela;
				$insnfcp[1]['intervalo']=$row['intervalo'];
				$insnfcp[1]['obs']=$rowcx['obs'];
				$insnfcp[1]['criadopor']='cron_processapendentes';
				$insnfcp[1]['criadoem']=date("Y-m-d H:i:s");
				$insnfcp[1]['alteradopor']='cron_processapendentes';
				$insnfcp[1]['alteradoem']=date("Y-m-d H:i:s");	


				$idnfcp = inseredb($insnfcp,'contapagar');
				$modulo='contapagar';
				$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
				VALUES (".$row["idempresa"].", '$idfluxostatus', '$idnfcp', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
				$res = d::b()->query($sqlEtapaHistInsert);
			}            

		}//for ($index = 1; $index <= $qtdparcelas; $index++) {
	   
	}
}//function gerarContapagaritem($idnotafiscal){



	function agrupaCP(){
              
       
    
        $sql="select i.idcontapagaritem,i.idpessoa,i.idformapagamento,i.idagencia,i.idcontaitem,
                    month(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as mes,
                    year(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as ano,
                    (LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY) as datavencimento,
                    DATE_ADD((LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY), INTERVAL 1 MONTH) as datavencimentoseq,
                    (LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as inicio,
                    LAST_DAY(LAST_DAY(i.datapagto) + INTERVAL 1 day) as fim,
                    i.datapagto,
                    f.agruppessoa,
                    f.agrupfpagamento,
                    f.agrupnota,
                    i.idobjetoorigem,               
                    i.tipoobjetoorigem,
                    i.valor,
                    i.parcela,
                    i.parcelas,
                    i.tipo,
                    i.visivel,
                    f.previsao,
                    i.status,
                    i.obs,
					i.idempresa,f.tipoespecifico
            from contapagaritem i join 
                    formapagamento f on(i.idformapagamento=f.idformapagamento)
                where i.status IN ('ABERTO','PENDENTE','PAGAR')
                    and (idcontapagar is null or  idcontapagar='')
                    and i.idpessoa is not null and i.idpessoa !=''
                    and i.idformapagamento is not null and i.idformapagamento !=''                   
                    and i.idagencia is not null and i.idagencia !=''";
                   // echo($sql."<br>");
        $res= d::b()->query($sql) or die($sql."Erro ao buscar contapagaritem agrupado por pessoa para agrupamento: <br>".mysqli_error());
        
        while($row=mysqli_fetch_assoc($res)){
            //se for comissao o tipo da conta agrupadora e REPRESENTACAO por comportar de forma diferente das demais
			/*
            $sqlfo="select * from confcontapagar where idformapagamento =".$row['idformapagamento']." and tipo='COMISSAO' and status='ATIVO'";
           // echo($sqlfo."<br>");
            $resfo= d::b()->query($sqlfo) or die($sql."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error());
            $qtdfo=mysqli_num_rows($resfo);
            if($qtdfo>0){$tipoespecifico='REPRESENTACAO';}else{$tipoespecifico='AGRUPAMENTO';}
            */
			$tipoespecifico=$row['tipoespecifico'];


            if($row['agrupnota']=='Y'){
                $qtd1=0;
            }elseif($row['agruppessoa']=='Y'){
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select * from contapagar c 
                        where c.idpessoa = ".$row['idpessoa']."
                        and c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."
                        and c.idempresa = ".$row['idempresa']."
                        and c.status='ABERTO'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['datavencimento']."' 
                        -- and '".$row['fim']."'  
                        order by c.datareceb asc limit 1";
                      //  echo('eo1:'.$sql1."<br>");  
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
                $qtd1=mysqli_num_rows($res1);
            }else{
                //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
                $sql1="select * from contapagar c 
                        where c.idformapagamento= ".$row['idformapagamento']."
                        and c.idagencia = ".$row['idagencia']."                   
                        and c.idempresa = ".$row['idempresa']."
                        and c.status='ABERTO'
                        and c.tipoespecifico='".$tipoespecifico."'
                        and c.datareceb >= '".$row['datapagto']."' 
                       -- and '".$row['fim']."' 
                        order by c.datareceb asc limit 1";  
                       // echo('eo2:'.$sql1."<br>");
                $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
                $qtd1=mysqli_num_rows($res1);
      
            }
                
                if($qtd1>0){
                   // echo($sql1."<br>");
                    $row1=mysqli_fetch_assoc($res1);
                    $squ="update contapagaritem set idcontapagar=".$row1['idcontapagar']." where idcontapagaritem=".$row['idcontapagaritem'];
                    $reu= d::b()->query($squ) or die($squ."Erro vincular contapagaritem na contapagar: <br>".mysqli_error());
                }else{
                    /* 
                    * Fatura cartão: ao lançar um item de conta, 
                    * verificar se ha  uma fatura "pendente e/ou quitado"
                    * no mes do lançamento. Caso haja, jogar para o proximo mes.                     * 
                    */
                    if($row['agrupnota']=='Y'){
                        
                        $datavencimento=$row['datapagto'];
                        
                    }else{
                        $datavencimento=$row['datavencimento'];
                    }
                    
                   // echo('new insert <br>');
                    $inscontapagar = new Insert();
                    $inscontapagar->setTable("contapagar");
                    
                    $inscontapagar->idagencia=$row['idagencia'];
                   // echo('depos new insert <br>');           

                    if($row['agruppessoa']=='Y'){
                        $inscontapagar->idpessoa=$row['idpessoa'];
                        $inscontapagar->status='ABERTO';

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = getId_FluxoStatus('contapagar', 'ABERTO');
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        $inscontapagar->parcela=1;                                
                        $inscontapagar->parcelas=1;
                        if(!empty($row['idcontaitem'])){
                            $inscontapagar->idcontaitem=$row['idcontaitem'];
                        }
                    }elseif($row['agrupnota']=='Y'){
                        $inscontapagar->idpessoa=$row['idpessoa'];
                        $inscontapagar->tipoobjeto=$row['tipoobjetoorigem'];
                        $inscontapagar->idobjeto=$row['idobjetoorigem'];
                        $inscontapagar->parcela=$row['parcela'];
                        $inscontapagar->parcelas=$row['parcelas'];
                        $inscontapagar->valor=$row['valor'];
                        $inscontapagar->status=$row['status'];
					
		
                       // echo('getId_FluxoStatus <br>'); 
                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = getId_FluxoStatus('contapagar', $row['status']);
                        $inscontapagar->idfluxostatus = $idfluxostatus;
                      //  echo('depois getId_FluxoStatus <br>'); 
                        if(!empty($row['idcontaitem'])){
                            $inscontapagar->idcontaitem=$row['idcontaitem'];
                        }
                    }else{
                        $inscontapagar->idcontaitem=46;
                        $inscontapagar->status='ABERTO';

                        //LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
                        $idfluxostatus = getId_FluxoStatus('contapagar', 'ABERTO');
                        $inscontapagar->idfluxostatus = $idfluxostatus;

                        $inscontapagar->parcela=1;                                
                        $inscontapagar->parcelas=1;
                    }
                    $inscontapagar->idformapagamento=$row['idformapagamento'];
                    if(!empty($row['previsao']) and $row['agrupnota']!='Y'){
                        $inscontapagar->valor=$row['previsao'];
                    }
                 
                    $inscontapagar->tipo=$row['tipo'];
                    $inscontapagar->visivel=$row['visivel'];
                    $inscontapagar->obs=$row['obs'];
                    $inscontapagar->tipoespecifico=$tipoespecifico;
                                if($row['agruppessoa']=='Y'){
                    $inscontapagar->idpessoa=$row['idpessoa'];
                                    $inscontapagar->status='ABERTO';
                                    $inscontapagar->parcela=1;                                
                    $inscontapagar->parcelas=1;
                                    if(!empty($row['idcontaitem'])){
                                        $inscontapagar->idcontaitem=$row['idcontaitem'];
                                    }
                                }elseif($row['agrupnota']=='Y'){
                                    $inscontapagar->idpessoa=$row['idpessoa'];
                                    $inscontapagar->tipoobjeto=$row['tipoobjetoorigem'];
                                    $inscontapagar->idobjeto=$row['idobjetoorigem'];
                                    $inscontapagar->parcela=$row['parcela'];
                                    $inscontapagar->parcelas=$row['parcelas'];
                                    $inscontapagar->valor=$row['valor'];
                                    $inscontapagar->status=$row['status'];
                                    if(!empty($row['idcontaitem'])){
                                        $inscontapagar->idcontaitem=$row['idcontaitem'];
                                    }
                                }else{
                                     $inscontapagar->idcontaitem=46;
                                     $inscontapagar->status='ABERTO';
                                     $inscontapagar->parcela=1;                                
                    $inscontapagar->parcelas=1;
                                }
                                    $inscontapagar->idformapagamento=$row['idformapagamento'];
                                if(!empty($row['previsao']) and $row['agrupnota']!='Y'){
                                    $inscontapagar->valor=$row['previsao'];
                                }
                                    
                                    
                                    $inscontapagar->tipo=$row['tipo'];
                                    $inscontapagar->visivel=$row['visivel'];
                                    $inscontapagar->obs=$row['obs'];
                                    $inscontapagar->tipoespecifico=$tipoespecifico;

                    $inscontapagar->datapagto=$datavencimento;
                    $inscontapagar->datareceb=$datavencimento;

					$inscontapagar->criadopor='cron_processapendentes';
					$inscontapagar->criadoem=date("Y-m-d H:i:s");
					$inscontapagar->alteradopor='cron_processapendentes';
					$inscontapagar->alteradoem=date("Y-m-d H:i:s");	
                   // echo('insertcontapagar <br>');
                   // print_r($inscontapagar);
                    $idcontapagar=$inscontapagar->save();  

                                  
                    $sqlu="update contapagaritem set idcontapagar =".$idcontapagar."
                                            where idcontapagaritem =".$row['idcontapagaritem']."  and idempresa = ".$row['idempresa']."";
                   //echo('<br>'. $sqlu);
                    d::b()->query($sqlu) or die("erro ao atualizar contapagaritem com novo contapagar sql=".$sqlu);

                    //LTM - 31-03-2021: Retorna o Idfluxo Hist
                    if(!empty($idfluxostatus))
                    {
                      
						$modulo='contapagar';
						$sqlEtapaHistInsert = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
						VALUES (".$row["idempresa"].", '$idfluxostatus', '$idcontapagar', '". $modulo."', 'ATIVO', 'cron', now(), 'cron', now())"; 
					//echo('<br>'. $sqlEtapaHistInsert);
                    $res = d::b()->query($sqlEtapaHistInsert);
                    }
                    
                }
           // echo('fim 1');
         
        }// while($row=mysqli_fetch_assoc($res)){ 
            //echo('fim 2');
    }

function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
        
	/*
	* verifica se existe alguma parcela quitada. se existir, nao alterar nada.
	*/
	$sqlverifquit = "select count(*) as quant from contapagar where status = '".$instatus."'    and tipoobjeto='".$intipoobjeto."' and idobjeto = ".$inidobj;

	$resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar parcelas da nota: <br>".mysqli_error(d::b()));
	$rowverif = mysqli_fetch_array($resverif);

	return  $rowverif;
}
function recuperaParcelasItensVinc($inidobj,$intipoobjeto){
	/*
	* verifica se existe algum contaitem vinculado a conta
	*/
	$sqlverifquit = "select count(*) as quant from contapagar c
		where c.tipoobjeto='".$intipoobjeto."' 
		and c.idobjeto = ".$inidobj." and exists (select 1 from contapagaritem i where i.idcontapagar = c.idcontapagar and i.tipoobjetoorigem='contapagar')";

	$resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe contaitem vinculada: <br>".mysqli_error(d::b()));
	$rowverif = mysqli_fetch_array($resverif);

	return  $rowverif;
	
}

function verificaboleto($inidnf){
	$sqlqtdbol="select count(*) as quant
		from remessaitem i,remessa r,contapagar c
		where i.idremessa = r.idremessa 
		and i.idcontapagar =c.idcontapagar
		and c.tipoobjeto ='nf'
		and c.idobjeto=".$inidnf;
	//echo $sqlverifquit;
	$resqtdbol = d::b()->query($sqlqtdbol) or die($sqlqtdbol."Erro ao consultar boletos da nota: <br>".mysqli_error(d::b()));
	$rowqtdbol = mysqli_fetch_array($resqtdbol);
	
	return  $rowqtdbol;
}

function getParcelaItens($idnotafiscal){
	/*
	* verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
	*/
	$sqlveritem = "select count(*) as quant from contapagaritem where  idobjetoorigem= ".$idnotafiscal." and tipoobjetoorigem = 'nf' and status in('QUITADO')";
	
	$resveritem = d::b()->query($sqlveritem) or die($sqlveritem."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
	$rowverifitem = mysqli_fetch_array($resveritem);
	return  $rowverifitem;
}
function getParcelaItensfechada($idnotafiscal,$agrupnota){
	/*
	* verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
	*/
	if($agrupnota=='Y'){
		$instatus="('FECHADO')";
	}else{
		$instatus="('FECHADO','PENDENTE')";
	}
	$sqlveritem = "select count(*) as quant
			from contapagaritem i join contapagar c on(c.idcontapagar=i.idcontapagar and c.status in ".$instatus.")
			where  i.idobjetoorigem= ".$idnotafiscal." 
				and i.tipoobjetoorigem = 'nf' ";
	
	$resveritem = d::b()->query($sqlveritem) or die($sqlveritem."Erro ao consultar parcelas item do cte: <br>".mysqli_error(d::b()));
	$rowverifitem = mysqli_fetch_array($resveritem);
	return  $rowverifitem;
}

function recuperaParcelasProg($idnotafiscal,$intipoobjeto){
	/*
	* verifica se existe algum contaitem vinculado a conta
	*/
   $sqlverifquit = "select c.* from contapagar c
			   where c.tipoobjeto='".$intipoobjeto."' 
			   and c.progpagamento='S'
			   and c.idobjeto = ".$idnotafiscal;
  
   $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe parcela programada: <br>".mysqli_error(d::b()));
   $qtd = mysqli_num_rows($resverif);
   if($qtd<1){
	   $sqlverifquit = "select c.*
				   from contapagar c join contapagaritem i on(i.idcontapagar = c.idcontapagar)
					   where i.tipoobjetoorigem='nf' 
					   and i.idobjetoorigem=".$idnotafiscal." 
					   and c.progpagamento='S'";

	   $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe parcela vinculada programada : <br>".mysqli_error(d::b()));
	   $qtd = mysqli_num_rows($resverif);  
	   if($qtd<1){
		   $tmpsqldel = "select cc.* 
			   from contapagar c,contapagaritem cc
			   where c.tipoobjeto = 'nf' 
			   and c.idobjeto =".$idnotafiscal."
			   and c.progpagamento = 'S'
			   and cc.idobjetoorigem = c.idcontapagar
			   and cc.tipoobjetoorigem ='contapagar'
			   and cc.status in ('INICIO','ABERTO','PENDENTE')";
		   $resverif = d::b()->query($sqlverifquit) or die($sqlverifquit."Erro ao consultar se existe comissao vinculada programada : <br>".mysqli_error(d::b()));
		   $qtd = mysqli_num_rows($resverif); 
	   }
	 
   }
  
   return   $qtd;   
}

function deletaParcelasExistentes($idnotafiscal){
	/*
	* deleta as parcelas existentes.
	*/
		$tmpsqldel = "delete cc.* 
						from contapagar c,contapagaritem cc
						where c.tipoobjeto = 'nf' 
						and c.idobjeto =".$idnotafiscal."
						and cc.idobjetoorigem = c.idcontapagar
			and cc.tipoobjetoorigem ='contapagar'
			and cc.status in ('ABERTO','PENDENTE')";
			//echo('1:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas de comissão da Nota Fiscal : <br>".mysqli_error(d::b()));


	//if($contapagaritem=="Y"){
	$tmpsqldel = "delete from contapagaritem where tipoobjetoorigem =  'nf' and idobjetoorigem = ".$idnotafiscal."  and status in ('ABERTO','PENDENTE')";
	//echo('2:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
	//}
		
	$tmpsqldel = "delete c.* from contapagar c 
		where c.tipoobjeto = 'nf' 
		and c.status!='QUITADO'
				and not exists(select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem='contapagar')
		and c.idobjeto = ".$idnotafiscal;
	   // echo('3:'.$tmpsqldel."<br>");
	d::b()->query($tmpsqldel) or die("Erro ao retirar parcelas da Nota Fiscal: <br>".mysqli_error(d::b()));
		
}

//inserir informação no banco de dados
function inseredb($arrvalor,$tabela){   
	//print_r( $arrnfitem);die;
	$i=0;
	foreach ($arrvalor as $arritem ) {

		$insval = new Insert();
		$insval->setTable($tabela);
		foreach ($arritem as $key => $value) {		
			$insval->$key=$value;                             
		}
		$idvalor[$i]=$insval->save();
		$i++;	
	}
	return $idvalor;       
}

function getId_FluxoStatus($_modulo, $status, $id = NULL, $tipo = NULL)
		{

		$sqlFluxo = "SELECT idfluxostatus
                        FROM fluxo f JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo AND f.status = 'ATIVO'
                        JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = '$status'
                    WHERE f.modulo = '$_modulo'";
		
	
		$resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." Erro ao buscar fluxo getIdFluxoStatus ".$sqlFluxo);
		$rowFluxo = mysqli_fetch_assoc($resFluxo);
	
		return $rowFluxo['idfluxostatus'];
}

if( CONSULTAORI() 	== true
	and GERACAO() 		== true
	and ASSINATURA() 	== true
	and CONEXAO()		== true
	){
	die("\n\n- Nota Cancelada com sucesso!!!");
}else{
	//die("\n".date("H:i:s")." - Erro Fatal1:".$_SESSION["steperro"]);
	die("\n");
}
?>
