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
	 
	if (!$res){
		$msgerr = "Erro ao consultar nfsxmltree: ".mysqli_error(d::b())."\n SQL: ".$sql;
		STATUSLOTE($_SESSION["numerorps"],"ERRO");
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
		//die($sPublicKey);
		//die($_SESSION["certprivkey"]);
	//}
}

/*
 * maf050410: responsavel por gerar as tages de assinatura conforme padrao W3C/IETF
 * para XML Signature
 */
function assinaXML($sXML, $tagID){

	//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - assinaxml: assinando xml";
	$_SESSION["steperro"]="assinaxml";

	retcert();

	$dom = new  DOMDocument('1.0');
	$dom->formatOutput = false;
	$dom->loadXML($sXML);

	//echo $dom->saveXML(); 

	$root = $dom->documentElement;
	
	try{
    	$node = $dom->getElementsByTagName($tagID)->item(0);
    
    	$Id = trim($node->getAttribute("Id"));
	}catch(Exception $e){
	    STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"ASSINAXML","Tagid[".$tagID."] não encontrado, ou erro geral: ".$e->getMessage());
	}

	$idnome = preg_replace('[^0-9]', '', $Id);

	//extrai os dados da tag para uma string
	$dados = $node->C14N(FALSE, FALSE, NULL, NULL);

	//calcular o hash dos dados
	$hashValue = hash('sha1', $dados, TRUE);

	//converte o valor para base64 para serem colocados no xml
	$digValue = base64_encode($hashValue);

	//monta a tag da assinatura digital
	$Signature = $dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
	$root->appendChild($Signature);
	$SignedInfo = $dom->createElement('SignedInfo');
	$Signature->appendChild($SignedInfo);

	//Cannocalization
	$newNode = $dom->createElement('CanonicalizationMethod');
	$SignedInfo->appendChild($newNode);
	$newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');

	//SignatureMethod
	$newNode = $dom->createElement('SignatureMethod');
	$SignedInfo->appendChild($newNode);
	$newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');

	//Reference
	$Reference = $dom->createElement('Reference');
	$SignedInfo->appendChild($Reference);
	$Reference->setAttribute('URI', '#'.$Id);

	//Transforms
	$Transforms = $dom->createElement('Transforms');
	$Reference->appendChild($Transforms);

	//Transform
	$newNode = $dom->createElement('Transform');
	$Transforms->appendChild($newNode);
	$newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');

	//Transform
	$newNode = $dom->createElement('Transform');
	$Transforms->appendChild($newNode);
	$newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');

	//DigestMethod
	$newNode = $dom->createElement('DigestMethod');
	$Reference->appendChild($newNode);
	$newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');

	//DigestValue
	$newNode = $dom->createElement('DigestValue', $digValue);
	$Reference->appendChild($newNode);

	// extrai os dados a serem assinados para uma string
	$dados = $SignedInfo->C14N(FALSE, FALSE, NULL, NULL);

	//inicializa a variavel que vai receber a assinatura
	$signature = '';

	//executa a assinatura digital usando o resource da chave privada
	$resp = openssl_sign($dados, $signature, openssl_pkey_get_private($_SESSION["certprivkey"]));
	 
	 
	//codifica assinatura para o padrao base64
	$signatureValue = base64_encode($signature);

	//SignatureValue
	$newNode = $dom->createElement('SignatureValue', $signatureValue);
	$Signature->appendChild($newNode);

	//KeyInfo
	$KeyInfo = $dom->createElement('KeyInfo');
	$Signature->appendChild($KeyInfo);

	//X509Data
	$X509Data = $dom->createElement('X509Data');
	$KeyInfo->appendChild($X509Data);

	//X509Certificate
	$newNode = $dom->createElement('X509Certificate', $_SESSION["certpublkey"]);
	$X509Data->appendChild($newNode);

	//grava na string o objeto DOM
	return $dom-> saveXML();

}

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

	/*
	 * altera o status do lote
	 */
	$sqllote = "update "._DBNFE.".nfslote set loteprefeitura = '".$nlote."',status = '".$instatus."', xml = '".$xml."', xmlret = '".$xmlret."' where idempresa=".$idempresa." and  idnfslote  = ".$inidnfslote;

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
		$sqllote = "insert into "._DBNFE.".nfslote (idempresa,numerorps,status) values (".$idempresa.",'".$numerorps."','CRIADO')";
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
				$_SESSION["arrnfsidlote"]["Lote"]= "Id=\"lote:".$insidnfslote."\"";
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
	STATUSLOTE($_SESSION["numerorps"],"PROCESSANDO");

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
		
 	/*
 	 * Consulta dados dos Itens 
 	 */
 	$sqli = "SELECT 
		 		DiscriminacaoServico
		 		,Quantidade
		 		,ValorUnitario 
		 		,ValorTotal		 		
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
	geraxml($idrps,"ReqEnvioLoteRPS");

	/*
	 * maf270410: criado para poder enviar um XML manualmente. isto foi feito para permitir envio de notas fiscais para tomadores do exterior e o sistema nao era preparado para isso
	 *
	$_SESSION["xml"] = <<<XML
<ns1:ReqEnvioLoteRPS xmlns:ns1="http://localhost:8080/WsNFe2/lote" xmlns:tipos="http://localhost:8080/WsNFe2/tp" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://localhost:8080/WsNFe2/lote http://localhost:8080/WsNFe2/xsd/ReqEnvioLoteRPS.xsd">
	<Cabecalho>
		<CodCidade>5403</CodCidade>
		<CPFCNPJRemetente>23259427000104</CPFCNPJRemetente>
		<RazaoSocialRemetente>LAUDO LABORATÓRIO</RazaoSocialRemetente>
		<transacao>true</transacao>
		<dtInicio>2010-07-08</dtInicio>
		<dtFim>2010-07-08</dtFim>
		<QtdRPS>1</QtdRPS>
		<ValorTotalServicos>5586.22</ValorTotalServicos>
		<ValorTotalDeducoes>139.65</ValorTotalDeducoes>
		<Versao>1</Versao>
		<MetodoEnvio>WS</MetodoEnvio>
	</Cabecalho>
	<Lote Id="lote:682">
		<RPS Id="rps:375">
			<Assinatura>a37848eafa93df581ca924d2e07651c064f07ae2</Assinatura>
			<InscricaoMunicipalPrestador>05137200</InscricaoMunicipalPrestador>
			<RazaoSocialPrestador>LAUDO LABORATÓRIO</RazaoSocialPrestador>
			<TipoRPS>RPS</TipoRPS>
			<SerieRPS>NF</SerieRPS>
			<NumeroRPS>375</NumeroRPS>
			<DataEmissaoRPS>2010-07-08T00:00:00</DataEmissaoRPS>
			<SituacaoRPS>N</SituacaoRPS>
			<SerieRPSSubstituido></SerieRPSSubstituido>
			<DataEmissaoNFSeSubstituida></DataEmissaoNFSeSubstituida>
			<SeriePrestacao>99</SeriePrestacao>
			<InscricaoMunicipalTomador></InscricaoMunicipalTomador>
			<CPFCNPJTomador>77777777777</CPFCNPJTomador>
			<RazaoSocialTomador>IMBA S.A - RINA </RazaoSocialTomador>
			<TipoLogradouroTomador></TipoLogradouroTomador>
			<LogradouroTomador>Km 4½ CARRETERA ANTIGUA A QUILLACOLLO</LogradouroTomador>
			<NumeroEnderecoTomador></NumeroEnderecoTomador>
			<ComplementoEnderecoTomador></ComplementoEnderecoTomador>
			<TipoBairroTomador>Bairro</TipoBairroTomador>
			<BairroTomador></BairroTomador>
			<CidadeTomador>9999</CidadeTomador>
			<CidadeTomadorDescricao>COCHABAMBA</CidadeTomadorDescricao>
			<CEPTomador>00000000</CEPTomador>
			<EmailTomador>juancarlos.sanchez@imba.com.bo</EmailTomador>
			<CodigoAtividade>750010000</CodigoAtividade>
			<AliquotaAtividade>2.0000</AliquotaAtividade>
			<TipoRecolhimento>A</TipoRecolhimento>
			<MunicipioPrestacao>5403</MunicipioPrestacao>
			<MunicipioPrestacaoDescricao>Uberlândia</MunicipioPrestacaoDescricao>
			<Operacao>A</Operacao>
			<Tributacao>T</Tributacao>
			<ValorPIS>36.31</ValorPIS>
			<ValorCOFINS>167.59</ValorCOFINS>
			<ValorINSS>0.00</ValorINSS>
			<ValorIR>83.79</ValorIR>
			<ValorCSLL>55.86</ValorCSLL>
			<AliquotaPIS>0.6500</AliquotaPIS>
			<AliquotaCOFINS>3.0000</AliquotaCOFINS>
			<AliquotaINSS>0.0000</AliquotaINSS>
			<AliquotaIR>1.5000</AliquotaIR>
			<AliquotaCSLL>1.0000</AliquotaCSLL>
			<DescricaoRPS></DescricaoRPS>
			<DDDPrestador>34</DDDPrestador>
			<TelefonePrestador>32225700</TelefonePrestador>
			<DDDTomador></DDDTomador>
			<TelefoneTomador></TelefoneTomador>
			<MotCancelamento></MotCancelamento>
			<CPFCNPJIntermediario></CPFCNPJIntermediario>
			<Itens>
				<Item>
					<DiscriminacaoServico>ROYALTIES</DiscriminacaoServico>
					<Quantidade>1</Quantidade>
					<ValorUnitario>5586.22</ValorUnitario>
					<ValorTotal>5586.22</ValorTotal>
				</Item>
			</Itens>
		</RPS>
	</Lote>
</ns1:ReqEnvioLoteRPS>
XML;

/* */
	//die($_SESSION["xml"]);// quando for gerar o XML manualmente

	return true;

}

/*
 * maf010410: funcao responsavel por ASSINAR o xml gerado
 */
function ASSINATURA(){

	//if(_NFSECHOLOG)echo " - ASSINATURA";
	$_SESSION["steperro"]="assinatura";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"ASSINATURA");
	STATUSLOTE($_SESSION["idnfslote"],"PROCESSANDO");
	
	/*
	 * inicia funcao recursiva de montagem
	 */
	$xmtlutf = $_SESSION["xml"];
	$_SESSION["xml"] = assinaXML($xmtlutf,"Lote");
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
function CONEXAO(){
	
	//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - CONEXAO: conetando ao ws [".$_SESSION["urlws"]."]";
	$_SESSION["steperro"]="conexao";
	
	//Inicia a Etapa
	LOGETAPA($_SESSION["idnfslote"],"CONEXAO");
	STATUSLOTE($_SESSION["idnfslote"],"CONECTANDO");
	
	ini_set("soap.wsdl_cache_enabled", "0");


	try {
		//$_SESSION["ws"] = new SoapClient($_SESSION["urlws"],array('trace' => 1));
		$WS = new SoapClient($_SESSION["urlws"],array('trace' => 1));
	}catch(Exception $e){//Se nao conseguir conectar grava o erro e retorna falso
		$msgerr = "Erro ao conectar WS: \n".$e;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"CONEXAO",$msgerr);
		echo($msgerr);
		return false;
	}	

	//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ENVIO";
	LOGETAPA($_SESSION["idnfslote"],"ENVIO");
	STATUSLOTE($_SESSION["idnfslote"],"ENVIANDO");
	
	//$_xml = $_SESSION["xml"];
	$_xml = $_SESSION["xml"];
	
	try {
		//envia o XML atraves da conexao SOAP
		$res = $WS->__soapCall("enviar",array($_xml));
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
	$doc = DOMDocument::loadXML($res);
		
	if (!$doc){
		$msgerr = "O WS nao retornou XML válido:\n".$res;
		STATUSLOTE($_SESSION["idnfslote"],"ERRO");
		LOGETAPA($_SESSION["idnfslote"],"ANALISE",$msgerr);
		echo($msgerr);
		return false;
	}else{
		//if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONCLUSAO";
		$_SESSION["xmlret"] = $res;
		LOGETAPA($_SESSION["idnfslote"],"CONCLUSAO");

		//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para CABECALHO no xml de retorno
		$cab = $doc->getElementsByTagName("Cabecalho")->item(0);
		
		//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para SUCESSO do xml de retorno
		$sucesso = $cab->getElementsByTagName("Sucesso")->item(0);
		//$cab = $ns1->item;
		$vsucesso =($sucesso->textContent); //->length;
		
		//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para NumeroLote do xml de retorno
		$nlote = $cab->getElementsByTagName("NumeroLote")->item(0);
		//Pega o valor da tag NumeroLote
		$vnlote =($nlote->textContent); //->length;
		
		if($vsucesso=="true"){
			$_SESSION["nlote"]=$vnlote;
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
	and ASSINATURA() 	== true
	and CONEXAO()		== true
	){
	die("\n\n- Rps [".$_SESSION["numerorps"]."] enviada com sucesso! Lote para consulta: [".$_SESSION["nlote"]."] ");
}else{
	die("".date("H:i:s")." - Erro:".$_SESSION["steperro"]);
}
?>
