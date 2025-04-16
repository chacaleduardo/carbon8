<?

include_once("../php/functions.php");
session_start();

$_SESSION["idnotafiscal"] = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
$_SESSION["numerorps"] = 0;

$_SESSION["idnfslote"] = 0;
$_SESSION["xml"] = "";
$_SESSION["xmlret"] = "";
$_SESSION["steperro"]="";
$_SESSION["urlws"] = _URLNFSWS;
$_SESSION["nlote"] = "";

$sqllnf = "select numerorps, idempresa from notafiscal where idnotafiscal = ".$_SESSION["idnotafiscal"];
$resnf = d::b()->query($sqllnf) or die("Erro pesquisando numerorps na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
$rnf = mysqli_fetch_assoc($resnf);
$idempresa = $rnf['idempresa'];


function geranumerorps(){
	global $idempresa;

	//if(_NFSECHOLOG)echo "- geranumerorps";

	$sqllnf = "select numerorps, idempresa from notafiscal where idnotafiscal = ".$_SESSION["idnotafiscal"];
	$resnf = d::b()->query($sqllnf) or die("Erro pesquisando numerorps na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
	$rnf = mysqli_fetch_assoc($resnf);

	if(empty($rnf["numerorps"])){

		//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - geranumerorps:gerando nova rps";

		### Tenta incrementar e recuperar o numerorps
		d::b()->query("LOCK TABLES sequence WRITE;");
		d::b()->query("update sequence set chave1 = (chave1 + 1) where idempresa = ".$idempresa." and sequence = 'numerorps'");

		$sql = "SELECT chave1 FROM sequence where idempresa = ".$idempresa." and sequence = 'numerorps';";

		$res = d::b()->query($sql);

		if(!$res){
			d::b()->query("UNLOCK TABLES;");
			echo "1-Falha Pesquisando Sequence [numerorps] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	
		$row = mysqli_fetch_array($res);
	
		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["chave1"])){
			if(!$row){
				d::b()->query("UNLOCK TABLES;");
				echo "2-Falha Pesquisando Sequence [numerorps] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
				die();
			}
		}
	
		d::b()->query("UNLOCK TABLES;");
	
		$_SESSION["numerorps"] = $row["chave1"];

		$sqlnf = "update notafiscal set numerorps = '".$_SESSION["numerorps"]."' where idnotafiscal = ".$_SESSION["idnotafiscal"];
		d::b()->query($sqlnf) or die("Erro atribuindo rps:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

	}else{
		//if(_NFSECHOLOG) //echo "\n".date("H:i:s")." - geranumerorps:capturando numero de rps existente[".$rnf["numerorps"]."]";
		$_SESSION["numerorps"] = $rnf["numerorps"];
	}


}

//ao clicar em enviar se a nota estiver com status consultando a mesmo voltará para pendente
function destravanota(){
	global $idempresa;
	$numerorps = $_SESSION["numerorps"];
	
		/*
	 * altera o status do lote
	 */
	$sqllote = "update "._DBNFE.".nfslote set status = 'PENDENTE' where idempresa = ".$idempresa." and status ='CONSULTANDO' and  numerorps = '".$numerorps."'";

	$retlote = d::b()->query($sqllote);
	
	return true;
	
}

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

			if($r['no']=='Cnpj' and  strlen($vlrfixo)==11){	    				
				return "\n".$t."<Cpf>".$vlrfixo;
			}else{
				return "\n".$t."<".$r['no'].$atrfixo.">".$vlrfixo;
			}
			
			
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
	 
	if (!$res){
		$msgerr = "Erro ao consultar nfsxmltree: ".mysqli_error(d::b())."\n SQL: ".$sql;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		echo($sqlerr);
		return false;
	}
	 
	while ($r = mysqli_fetch_assoc($res)) { 

		//verifica se o pai tem filhos
		$sqlleaf = "select count(*) as qtleaf from "._DBNFE.".nfsxmltree where pai = ".$r['idnfsxmltree'];
		$resassoc = d::b()->query($sqlleaf) or die("Erro ao consultar nodes/leafs: ".mysqli_error(d::b())."\n SQL: ".$sqlleaf);
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
			if($r["looptag"]=="N"){ //nao fecha o ultimo na pois ele foi montado pelo loop na montatag
				$vlrfixo =$_SESSION[$r["arrorigem"]][$r["no"]];				
				if($r['no']=='Cnpj' and  strlen($vlrfixo)==11){	    				
					$_SESSION["xml"] .= "</Cpf>";
				}else{
					$_SESSION["xml"] .= "</".$r['no'].">";
				}					
			}
		}
	} 
} 

/*
 * maf050410: Responsavel por ler o arquivo original do SERASA 
 * e extrair as chaves publicas e privadas de encriptacao
 */
function retcert(){
	global $idempresa;
	//if(_NFSECHOLOG)echo " - retcert:certificado";

	$_SESSION["steperro"]="retcert";
	
	//if(empty($_SESSION["certprivkey"]) or empty($_SESSION["certpublkey"])){
        
		
		$_SESSION["certprivkey"]="";
		$_SESSION["certpublkey"]="";
                
		$sqlc="SELECT senha, SUBSTRING(certificado,10) as certificado
		from empresa where idempresa = ".$idempresa;
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
		$_SESSION["certpass"]=$passpfx;
		//die($sPublicKey);
		//die($_SESSION["certprivkey"]);
	//}
}
/*
 * maf050410: responsavel por gerar as tages de assinatura conforme padrao W3C/IETF
 * para XML Signature
 * hermesp ataualizacao 16/07/2024
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

    	$newNode->setAttribute('Id', "ID_ASSINATURA_$_ID");
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
 * maf010410: esta funcao armazena os logs de cada etapa do processo de geracao da NFE.
 * As primeiras etapas e o proprio LOG, em caso de erro, geram somente erros simples de PHP, que podem ser
 * tratados com die() simples para serem visualizados na tela.
 * A funcao mysqli_real_escape_string() foi utilizada para evitar caracteres especiais no log (ex: aspas)
 */
function LOGETAPA($inidnfslote,$inetapa,$inerro='',$incoderro=0){
	global $idempresa;
	//if(_NFSECHOLOG)echo " -ETAPA [".$inetapa."]";
	$_SESSION["steperro"]="logetapa";
	
	$sqllog = "insert into "._DBNFE.".nfslog (
                                idempresa
				,idnfslote
				,etapa
				,erro
				,coderro) 
				values (
                                ".$idempresa."
					,".$inidnfslote."
					,'".$inetapa."'
					,'".mysqli_real_escape_string($inerro)."'
					,".$incoderro.")";
	$retlog = d::b()->query($sqllog);
	if(!$retlog){
		die("Erro ao inserir LOG: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllog);
	}
}
/*
 * maf010410: altera o status do lote conforme o fluxo vai evoluindo
 */
function STATUSLOTE($inidnfslote, $instatus){
	global $idempresa;
	//if(_NFSECHOLOG)echo " - STATUSLOTE: alterando [".$instatus."]";

	$_SESSION["steperro"]="statuslote";

	//verifica se o status esta devidamente preenchido
	if(!$instatus){
		echo("Erro ao alterar status do LOTE: \n- O status informado esta VAZIO.");
		return false;
	}

	//armazena o texto existente na variavel xml
	//$xml = mysqli_real_escape_string($_SESSION["xml"]);
	$xml =$_SESSION["xml"];
	
	//if(_NFSECHOLOGXML)echo "- STATUSLOTE: XML a ser gravado: [\n- ".$_SESSION["xml"]."]";

	//armazena o texto existente na variavel xml
	//$xmlret = mysqli_real_escape_string($_SESSION["xmlret"]);
	$xmlret = $_SESSION["xmlret"];

	//armazena o numero do lote gerado no xml de retorno
	$nlote = $_SESSION["nlote"];

	//armazena o numero do lote gerado no xml de retorno
	$nprotocolo = $_SESSION["protocolo"];

	/*
	 * altera o status do lote
	 */
	$sqllote = "update "._DBNFE.".nfslote set loteprefeitura = '".$nlote."'
	,status = '".$instatus."',
	 xml = '".$xml."',
	  xmlret = '".$xmlret."',
	  protocoloprefeitura = '".$nprotocolo."'
	   where idempresa=".$idempresa."
	    and  idnfslote  = ".$inidnfslote;

	$retlote = d::b()->query($sqllote);
	if(!$retlote){
		//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
		echo("Erro ao alterar LOTE: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllog);
		return false;
	}else{
		//esvazia variveis com texto de xml
		//unset($_SESSION["xml"]);
		//unset($_SESSION["xmlret"]);
		return true;
	}
}

/*
 * maf010410: Esta etapa insere 1 registro na tabela de lote, gerando a numeracao pelo autonumber da tabela
 * Caso haja erro na insercao, o processo deve simplesmente PARAR, porque sem o lote nenhuma etapa
 * posterior pode ser executada
 */
function LOTE(){
	global $idempresa;
	//if(_NFSECHOLOG)echo " - NOVO LOTE";

	$_SESSION["steperro"]="lote";
	
//recupera a session com o  id da RPS
	$numerorps = $_SESSION["numerorps"];

	//verifica se o numerorps foi devidamente informado
	if(!$numerorps or ($numerorps == 0)){
		echo("Erro 1 ao gerar LOTE: \nNUMERORPS RPS nao informado.");
		return false;
	}else{

		/*
		 * insere novo lote
		 */
		$sqllote = "insert into "._DBNFE.".nfslote (idempresa,idnotafiscal,numerorps,status) values (".$idempresa.",".$_SESSION["idnotafiscal"] .",'".$numerorps."','CRIADO')";
		$retlote = d::b()->query($sqllote);
	
		if(!$retlote){
			//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
			echo("Erro 2 ao gerar LOTE: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllog);
			return false;
		}else{
			//captura id (autonumber) do lote gerado
			$insidnfslote = mysqli_insert_id(d::b());

			//verifica se o id do lote foi devidamente recuperado pela funcao do MYSQL
			if(empty($insidnfslote) or ($insidnfslote == 0)){
				echo("Erro 3 ao gerar LOTE: \n- Id do LOTE nao foi recuperado apos insercao.");
				return false;
			}else{  
				$_SESSION["arrnfsidlote"]["LoteRps"]= "Id=\"ID_".$insidnfslote."\" versao=\"2.04\"";
				//Armazena o id do lote em SESSION
				$_SESSION["idnfslote"] = $insidnfslote;
				LOGETAPA($insidnfslote,'LOTE');
				//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE: lote gerado: [".$insidnfslote."]";
				return true;
			}
	
		}
	}
}

/*
 * maf010410: esta funcao consulta os dados das notas fiscais no banco de origem 
 * e abre arrays contendo as informacoes padronizadas com a tabela nfsxmltree
 */
function CONSULTAORI(){

	//if(_NFSECHOLOG)echo " - CONSULTAORI: dados de origem";
	$_SESSION["steperro"]="consultaori";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI");
	STATUSLOTE($_SESSION["idnfslote"],"PROCESSANDO");

	//recupera a session com o id da RPS
	$idrps = $_SESSION["idnotafiscal"];

	/*
	 * Consulta dados do cabeçalho
	 */
 	$sqlcab = "SELECT * 
 				FROM "._DBAPP.".vwnfscabecalho 
 				where idnotafiscal=".$idrps;
	$rescab = d::b()->query($sqlcab);
	$nrows= mysqli_num_rows($rescab);
	//die($sqlcab);

	if($nrows > 1){
		$msgerr = "Erro possivelmente existe mais de um endereço de Cobrança:  \n".date("H:i:s");
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
		return false;
	}
	
	if(!$rescab){
		$msgerr = "Erro ao consultar Cabecalho: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlcab;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
		return false;
	}else{
		$cab = mysqli_fetch_assoc($rescab);
		$_SESSION["vwnfscabecalho"]= $cab;
		//print_r($_SESSION["vwnfscabecalho"]["Assinatura"]);
		//print_r($_SESSION["arrdados"]["vwnfscabecalho"]["CPFCNPJRemetente"]);
		//print_r($_SESSION["arrdados"]["CPFCNPJRemetente"]);		die;
	}

	$_SESSION["vwnfscabecalho"]["NumeroLote"]=$_SESSION["idnfslote"];	



	/*
	 * Consulta dados do cabeçalho
	 */
	$sqlcab = "SELECT * 
		FROM "._DBAPP.".vwnfstomador 
		where idnotafiscal=".$idrps;
	$rescab = d::b()->query($sqlcab);
	$nrows= mysqli_num_rows($rescab);
	//die($sqlcab);

	if($nrows > 1){
		$msgerr = "Erro possivelmente existe mais de um endereço de Cobrança:  \n".date("H:i:s");
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
		return false;
	}

	if(!$rescab){
		$msgerr = "Erro ao consultar Tomador: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlcab;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
	return false;
	}else{
		$cab = mysqli_fetch_assoc($rescab);
		$_SESSION["vwnfstomador"]= $cab;
	}


	$sqli="SELECT 
				CASE
					WHEN idempresa = 1 THEN concat('ANALISE LABORATORIAL') 
					ELSE concat('SERVIÇOS PRESTADOS') 
				END AS Discriminacao
			FROM
				notafiscal
			WHERE
			idnotafiscal =".$idrps;

		
	$resi = d::b()->query($sqli);
	$nrows= mysqli_num_rows($resi);
	//die($sqlcab);

	if($nrows > 1){
		$msgerr = "Erro ao consultar Itens: \n".date("H:i:s");
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
		return false;
	}

	if(!$resi){
		$msgerr = "Erro ao consultar Itens: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlcab;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		//echo($sqlerr);
	return false;
	}else{
		$cab = mysqli_fetch_assoc($resi);
		$_SESSION["notafiscalitens"]= $cab;
	}
		
		
 	/*
 	 * Consulta dados dos Itens 
 	 */
	 
 	$sqli = "SELECT 
		 		DiscriminacaoServico AS Descricao
		 		,Quantidade
		 		,ValorUnitario		 	
                ,1 AS OpcaoItemTributavel	 		
			FROM "._DBAPP.".vwnfsitens 
		 		where idnotafiscal=".$idrps;
	//die($sqli);
	$resi = d::b()->query($sqli);
	if(!$resi){
		$msgerr = "Erro ao consultar Itens: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqli;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONSULTAORI",$msgerr);
		echo($sqlerr);
		return false;
	}else{
		$i=0;
		$_SESSION["vwnfsitens"]=array();		
		while ($itens = mysqli_fetch_assoc($resi)) {
			$i++;
			$_SESSION["vwnfsitens"][$i]= $itens;
		}
	}
	
	//print_r($_SESSION["vwnfscabecalho"]);


	return true;
}

/*
 * maf010410: funcao responsavel por GERAR o texto do XML que ira ser enviado
 */
function GERACAO(){
	//if(_NFSECHOLOG)echo " - GERACAO: gerando xml";
	$_SESSION["steperro"]="geracao";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"GERACAO");
	STATUSLOTE($_SESSION["idnfslote"],"PROCESSANDO");

	//recupera a session com o id da RPS
	$idrps = $_SESSION["idnotafiscal"];
	
	/*
	 * inicia funcao recursiva de montagem
	 */
	geraxml($idrps,"RecepcionarLoteRps");


	//echo($_SESSION["xml"]);
	//die();

	/*
	 * maf270410: criado para poder enviar um XML manualmente. isto foi feito para permitir envio de notas fiscais para tomadores do exterior e o sistema nao era preparado para isso
	 *
	 */
	//$_SESSION["xml"] = '<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#"><LoteRps Id="lote:43990" versao="2.04"><NumeroLote>7875888</NumeroLote><Prestador><CpfCnpj><Cnpj>23259427000104</Cnpj></CpfCnpj><InscricaoMunicipal>05137200</InscricaoMunicipal></Prestador><QuantidadeRps>1</QuantidadeRps><ListaRps><Rps><InfDeclaracaoPrestacaoServico Id="ID_39596881ABCDE1"><Rps Id="rps"><IdentificacaoRps><Numero>39596881</Numero><Serie>ABCDE</Serie><Tipo>1</Tipo></IdentificacaoRps><DataEmissao>2023-06-21</DataEmissao><Status>1</Status></Rps><Competencia>2023-06-21</Competencia><Servico><Valores><ValorServicos>10</ValorServicos></Valores><IssRetido>2</IssRetido><ItemListaServico>04.02</ItemListaServico><CodigoTributacaoMunicipio>864020400</CodigoTributacaoMunicipio><Discriminacao>teste</Discriminacao><CodigoMunicipio>3170206</CodigoMunicipio><ExigibilidadeISS>1</ExigibilidadeISS><MunicipioIncidencia>3170206</MunicipioIncidencia></Servico><Prestador><CpfCnpj><Cnpj>23259427000104</Cnpj></CpfCnpj><InscricaoMunicipal>05137200</InscricaoMunicipal></Prestador><TomadorServico><IdentificacaoTomador><CpfCnpj><Cpf>43260062009</Cpf></CpfCnpj></IdentificacaoTomador><RazaoSocial>José Silva</RazaoSocial><Endereco><Endereco>RUA JOSÉ PASSOS</Endereco><Numero>564</Numero><Bairro>CENTRO</Bairro><CodigoMunicipio>3170206</CodigoMunicipio><Uf>MG</Uf><Cep>35858555</Cep></Endereco></TomadorServico><OptanteSimplesNacional>2</OptanteSimplesNacional><IncentivoFiscal>2</IncentivoFiscal></InfDeclaracaoPrestacaoServico></Rps></ListaRps></LoteRps></EnviarLoteRpsEnvio>';

	
	
/* */
	//die($_SESSION["xml"]);// quando for gerar o XML manualmente

	return true;

}

/*
 * maf010410: funcao responsavel por ASSINAR o xml gerado
 */
function ASSINATURA(){
		
	return true; 
}


/*
 * maf010410: funcao responsavel por GRAVAR na tabela de controle o texto do XML gerado
 */
function GRAVACAO(){
	//if(_NFSECHOLOG)echo " - GRAVACAO: armazenando xml";
	$_SESSION["steperro"]="gravacao";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"GRAVACAO");
	STATUSLOTE($_SESSION["idnfslote"],"PROCESSANDO");
}

/*
 * maf010410: funcao responsavel por efetuar a conexao com o WebService da prefeitura
 */
function ASSINATURA_CONEXAO(){
	global $idempresa;
	
	$_SESSION["xml"] = str_replace('<InscricaoMunicipal></InscricaoMunicipal>','', $_SESSION["xml"]);//substituir tag se estiver vazia.

	//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - CONEXAO: conetando ao ws [".$_SESSION["urlws"]."]";
	$_SESSION["steperro"]="conexao";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"CONEXAO");
	STATUSLOTE($_SESSION["idnfslote"],"CONECTANDO");

	
	ini_set("soap.wsdl_cache_enabled", "0");


	$sqlc="SELECT senha, SUBSTRING(certificado,10) as certificado
	from empresa where idempresa = ".$idempresa;

	$resc=d::b()->query($sqlc) or die("A Consulta do certificado falhou :".mysqli_error(d::b())."<br>Sql:".$sqlc);
	$rowc=mysqli_fetch_assoc($resc);
	
	if(empty($rowc['certificado']) or empty($rowc['senha'])){
		die("Verificar o certificado no cadastro da empresa.");
	}
	
	$certificadopfx = "sefaz4/certs/".$rowc['certificado'];
	$passpfx = $rowc['senha'];

	// URL do WSDL para verificar os métodos
	$urlwsdl = 'https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse.wsdl';
	// URL do ambiente de homologação
	$urlsoap = 'https://nfsews.uberlandia.mg.gov.br:8003/nfse-ws/soap/nfse';

	try {
		// Carrega o arquivo PFX e extrai o certificado e a chave privada
		$pfx = file_get_contents($certificadopfx);
		if ($pfx === false) {
			throw new Exception("Não foi possível ler o arquivo PFX.");
		}

		$certs = array();
		if (!openssl_pkcs12_read($pfx, $certs, $passpfx)) {
			throw new Exception("Não foi possível extrair o certificado e a chave privada do arquivo PFX.");
		}

		// Extraí o certificado e a chave privada
		$certificado_crt = $certs['cert'];
		$chave_privada_key = $certs['pkey'];

		// Cria um arquivo temporário para o certificado
		$tempCertFile = tempnam(sys_get_temp_dir(), 'cert');
		file_put_contents($tempCertFile, $certificado_crt . $chave_privada_key);

		// Define as opções do cliente SOAP
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
					'passphrase' => $passpfx
				)
			))
		);


		//if(_NFSECHOLOG)echo " - ASSINATURA";
		$_SESSION["steperro"]="assinatura";
		
		//Inicia a Etapa
		LOGETAPA($_SESSION["idnfslote"],"ASSINATURA");
		STATUSLOTE($_SESSION["idnfslote"],"PROCESSANDO");
		
		/*
		* inicia funcao recursiva de montagem
		*/
		$xmtlutf = $_SESSION["xml"];
		retcert();	
		$_SESSION["xml"] = assinaXML($xmtlutf,"InfDeclaracaoPrestacaoServico");
		$xmtlutf = str_ireplace('<?xml version="1.0" encoding="UTF-8"?>', '', $_SESSION["xml"]);	
		$_SESSION["xml"] = assinaXML($xmtlutf,"LoteRps");	


		// XML de exemplo para envio
		$dadosMsg = $_SESSION["xml"];

		$cabecalhoCdata = '<?xml version="1.0" encoding="UTF-8"?><cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04"><versaoDados>2.04</versaoDados></cabecalho>';


		// Initialize SOAP client
		$client = new SoapClient($urlwsdl, $options);
		$client->__setLocation($urlsoap);
		// Create the request parameters
		$params = new stdClass();
		$params->nfseCabecMsg = $cabecalhoCdata; // Add appropriate header if required
		$params->nfseDadosMsg = $dadosMsg;


		// Make the SOAP call
		$res = $client->__soapCall('RecepcionarLoteRps', array($params));

		// Trate a resposta do serviço
		// echo "Resposta: " . var_export($res, true); // Apenas para debug, ajuste conforme necessário

	
	}catch(SoapFault $e){//Se nao conseguir enviar grava o erro e retorna falso
		$msgerr = "Erro [1] ao enviar XML: \n".$e;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"ENVIO",$msgerr);
		echo($msgerr);
		return false;
	}	

	if (!$res){
		$msgerr = "Erro [2] ao enviar XML:\n".$e;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"ENVIO",$msgerr);
		echo($msgerr);
		return false;
	}
	//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - ANALISE";
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"ANALISE");
	STATUSLOTE($_SESSION["idnfslote"],"ANALISANDO");
	//print_r($res);
	$doc = DOMDocument::loadXML($res->outputXML);
	$xpath = new DOMXPath($doc);
	$xpath->registerNamespace('ns', 'http://www.abrasf.org.br/nfse.xsd');
		
	if (!$doc){
		$msgerr = "O WS nao retornou XML válido:\n".$res->outputXML;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"ANALISE",$msgerr);
		echo($msgerr);
		return false;
	}else{
		//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONCLUSAO";
		$_SESSION["xmlret"] = $res->outputXML;
		LOGETAPA($_SESSION["idnfslote"],"CONCLUSAO");		
		
		if($xpath->query('//ns:Protocolo')->length){
			//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para NumeroLote do xml de retorno
			$_SESSION["nlote"] = $xpath->query('//ns:NumeroLote')->item(0)->textContent;;
			//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para NumeroLote do xml de retorno
			$_SESSION["protocolo"]=$$vnProtocolo = $xpath->query('//ns:Protocolo')->item(0)->textContent;
			STATUSLOTE($_SESSION["idnfslote"],"PENDENTE");
			return true;
		}else{
			$msgerr = "O XML de retorno está com valor ".$vsucesso;
			STATUSLOTE($_SESSION["idnfslote"],"ERRO");
			LOGETAPA($_SESSION["idnfslote"],"CONCLUSAO",$msgerr);
			echo $msgerr;
			return false;
		}	
		
	}	
}

//print_r($_SESSION["vwnfsitens"]);die;

geranumerorps();

destravanota();

if(	LOTE($idrps) 	== true
	and CONSULTAORI() 	== true
	and GERACAO() 		== true
	and ASSINATURA_CONEXAO()		== true
	){
	die("\n\n- Rps [".$_SESSION["numerorps"]."] enviada com sucesso! Lote para consulta: [".$_SESSION["nlote"]."] ");
}else{
	die("".date("H:i:s")." - Erro:".$_SESSION["steperro"]);
}
?>
