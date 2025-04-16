<?
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//header("Content-type: text/plain");
include_once("../../php/functions.php");
require_once('../vendor/nfephp-org/sped-mdfe/bootstrap.php');


use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;



session_start();

$_SESSION["idmdfe"] = $_GET["idmdfe"]; //Id da nota fiscal que vem da acao de enviar
$_SESSION["idmdfelote"] = 0;
$_SESSION["xml"] = "";
$_SESSION["xmlret"] = "";
$_SESSION["steperro"]="";
$_SESSION["strprocesso"]="mdfe";
$_SESSION["tagassinatura"]="infMDFe";

$idmdfe= $_GET["idmdfe"];


$sini="select * from "._DBAPP.".mdfe where  idmdfe=".$idmdfe;
$resini=d::b()->query($sini) or die(mysqli_error(d::b())." erro ao buscar o mdfe ".$sini);
$rowini=mysqli_fetch_assoc($resini);

$_SESSION["idempresa"]=$rowini["idempresa"];

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$rowini["idempresa"];
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);



function LOGETAPA($inidmdfelote,$inetapa,$inerro='',$incoderro=0){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOGETAPA [".$inetapa."]";
	$_SESSION["steperro"]="logetapa";
	
	$sqllog = "insert into "._DBNFE.".mdfelog (
				idmdfelote				
				,etapa
				,erro
				,xml
				,coderro) 
				values (
					".$inidmdfelote."
					,'".$inetapa."'
					,'".mysqli_real_escape_string($inerro)."'
					,'".$_SESSION["xml"]."'
					,".$incoderro.")";
	$retlog = d::b()->query($sqllog);
	if(!$retlog){
		die("Erro ao inserir LOG: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllog);
	}
}
/*
 * maf010410: altera o status do lote conforme o fluxo vai evoluindo
*/
function STATUSLOTE($inidmdfelote, $instatus){

    if(_NFSECHOLOG)echo "\n".date("H:i:s")." - STATUSLOTE: alterando status para [".$instatus."]";

    $_SESSION["steperro"]="statuslote";

    //verifica se o status esta devidamente preenchido
    if(!$instatus){
	    echo("Erro ao alterar status do LOTE: \n- O status informado esta VAZIO.");
	    return false;
    }

    //armazena o texto existente na variavel xml
    $xml = mysqli_real_escape_string(d::b(),$_SESSION["xml"]);

    //if(_NFSECHOLOGXML)echo "\n".date("H:i:s")." - STATUSLOTE: XML a ser gravado: [XML]";//$_SESSION["xml"]

    //armazena o texto existente na variavel xml
   	// echo 'aqui';
    $xmlret = mysqli_real_escape_string(d::b(), $_SESSION["xmlret"]);

    //armazena o numero do lote gerado no xml de retorno
    $nlote = $_SESSION["nlote"];

    $sqllote = "update "._DBNFE.".mdfelote set recibo = '".$nlote."',status = '".$instatus."', xml = '".$xml."', xmlret = '".$xmlret."' where idmdfelote = ".$inidmdfelote;

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



function geranumeronfe(){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronfe";

	$sqllnf = "select nmdfe from mdfe where idmdfe = ".$_SESSION["idmdfe"];
	$resnf = d::b()->query($sqllnf) or die("Erro pesquisando nmdfe na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
	$rnf = mysqli_fetch_assoc($resnf);

	if(empty($rnf["nmdfe"])){

		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:gerando nova nfe";

		### Tenta incrementar e recuperar o numerorps
		d::b()->query("LOCK TABLES sequence WRITE;");
		d::b()->query("update sequence set chave1 = (chave1 + 1) where idempresa=".$_SESSION["idempresa"]." and sequence = 'nmdfe'");

		$sql = "SELECT chave1 as nmdf, convert( lpad(chave1, '9', '0') using latin1) as chave1 FROM sequence where idempresa=".$_SESSION["idempresa"]." and sequence = 'nmdfe';";

		$res = d::b()->query($sql);

		if(!$res){
			d::b()->query("UNLOCK TABLES;");
			echo "1-Falha Pesquisando Sequence [nmdfe] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	
		$row = mysqli_fetch_array($res);
	
		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["chave1"])){
			if(!$resexercicio){
				d::b()->query("UNLOCK TABLES;");
				echo "2-Falha Pesquisando Sequence [nmdfe] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
				die();
			}
		}
	
		d::b()->query("UNLOCK TABLES;");
	
		$_SESSION["nmdfe"] = $row["chave1"];

		$sqlnf = "update mdfe set nmdfe = '".$row["chave1"]."',nmdf=".$row['nmdf']." where idmdfe = ".$_SESSION["idmdfe"];
		d::b()->query($sqlnf) or die("Erro atribuindo nnfe:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

	}else{
		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:capturando numero mdfe nfe existente[".$rnf["nnfe"]."]";
		$_SESSION["nmdfe"] = $rnf["nmdfe"];
	}


}


function LOTE(){
	 
    //$sql="update "._DBAPP.".nf set envionfe='PROCESSANDO' where idmdfe = ".$_SESSION["idmdfe"];
    //$retx = d::b()->query($sql);

    if(_NFSECHOLOG) echo "\n".date("H:i:s")." - LOTE: gerando novo lote";

    $_SESSION["steperro"]="lote";	
    /*
     * insere novo lote
     */
    $sqllote = "insert into "._DBNFE.".mdfelote (idmdfe,status) values (".$_SESSION["idmdfe"].",'CRIADO')";
    $retlote = d::b()->query($sqllote);

    if(!$retlote){
	    //LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	    echo("Erro 2 ao gerar LOTE: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllote);		
	    return false;
    }else{
	//captura id (autonumber) do lote gerado
	$insidmdfelote = mysqli_insert_id(d::b());

	//verifica se o id do lote foi devidamente recuperado pela funcao do MYSQL
	if(empty($insidmdfelote) or ($insidmdfelote == 0)){
		echo("Erro 3 ao gerar LOTE: \n- Id do LOTE nao foi recuperado apos insercao.");
		return false;
	}else{  
	    /*
	     * hermesp 15112011
	     * gerar o cNF o ID da nota fiscal e consequentemente o cDV
	     */
	    $sqllnf = "select  chave ,nmdfe from mdfe where idmdfe = ".$_SESSION["idmdfe"];
	    $resnf = d::b()->query($sqllnf) or die("Erro pesquisando cNF na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
	    $rnf = mysqli_fetch_assoc($resnf);

	    if(empty($rnf["chave"])){
		//GERA O NNFE
		geranumeronfe();

		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE:gerando novo cNF";					
		//gera um numero randomico para a cNF e gerar o ID da NF
		$sql = "SELECT FLOOR(10000000 + (RAND() * 89999999)) as cmdfe;";					
		$res = d::b()->query($sql);					
		if(!$res){
		    d::b()->query("UNLOCK TABLES;");
		    echo "1-Falha ao gerar cmdfe randomico: " . mysqli_error(d::b()) . "<p>SQL: $sql";
		    die();
		}					
		$row = mysqli_fetch_array($res);

		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["cmdfe"])){
		    if(!$resexercicio){
			d::b()->query("UNLOCK TABLES;");
			echo "2-Falha ao gerar cmdfe randomico : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		    }
		}

		$_SESSION["cmdfe"] = $row["cmdfe"];

		$sqlnf = "update mdfe set cmdfe = '".$_SESSION["cmdfe"]."' where idmdfe = ".$_SESSION["idmdfe"];
		d::b()->query($sqlnf) or die("Erro atribuindo cnf:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

		//concat(e.cuf,SUBSTRING(replace(n.dtemissao,'-',''),3,4),e.cnpj,55,'001',n.controle,n.cnf) as preid
		$sqlpi = "select * from vwmdfeid where idmdfe =".$_SESSION["idmdfe"];
		$respi = d::b()->query($sqlpi) or die("Erro pesquisar pré ID NF:\nSQL:".$sqlpi."\nErro:".mysqli_error(d::b()));
		$rnpi = mysqli_fetch_assoc($respi);
		if(empty($rnpi["preid"])){
		    d::b()->query("UNLOCK TABLES;");
		    echo " 2-Falha ao  pesquisar pré ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlpi";
		    die();							
		}else{
		    $sqlid = "select geraidmdfe(".$rnpi["preid"].",".$_SESSION["idmdfe"].") as chave";
		    $resid = d::b()->query($sqlid) or die("Erro pesquisar pré ID NF:\nSQL:".$sqlid."\nErro:".mysqli_error(d::b()));
		    $rnid = mysqli_fetch_assoc($resid);
		    if(empty($rnid["chave"])){
				    d::b()->query("UNLOCK TABLES;");
				    echo "2-Falha ao  gerar ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlid";
				    die();
		    }else{
			    $_SESSION["arrinfid"]["infMDFe"]= "Id=\"".$rnid["chave"]."\" versao=\"3.00\"";								
		    }							
		}					
	    }else{
                if(empty($rnf["chave"])){                    
                    $_SESSION["chave"]=substr($rnf["chave"], 28, 9);

                    $sqlnf = "update mdfe set chave = '".$_SESSION["chave"]."' where idmdfe = ".$_SESSION["idmdfe"];
                    d::b()->query($sqlnf) or die("Erro atribuindo nnfe:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));
                }
		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - gerachavenfe:capturando numero de cNF existente[".$rnf["chave"]."]";
		$_SESSION["arrinfid"]["infMDFe"]= "Id=\"".$rnf["chave"]."\" versao=\"3.00\"";									
	    }			
	    //Armazena o id do lote em SESSION
	    $_SESSION["idmdfelote"] = $insidmdfelote;
	    LOGETAPA($insidmdfelote,'LOTE','SUCESSO');
	    if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE: lote gerado: [".$insidmdfelote."]";
	    return true;
	}
    }	
}//LOTE

function CONSULTAORI(){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONSULTAORI: consultando dados do sistema de origem";
	$_SESSION["steperro"]="consultaori";
	
	//Inicia a Etapa
	STATUSLOTE($_SESSION["idmdfe"],"PROCESSANDO");

	//recupera a session com o id da RPS
	$idmdfe = $_SESSION["idmdfe"];
		
	/*
	 * hermesp151211
	 * Buscar dados para o xml da nota fisca de produtos
	 */
	$sqldados = "SELECT *
	FROM "._DBAPP.".vwmdfedados
	where idmdfe=".$idmdfe;
	$resdados = d::b()->query($sqldados);

	if(!$resdados){
	    $msgerr = "Erro ao consultar dados da NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqldados;
	    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
	    LOGETAPA($_SESSION["idmdfelote"],"CONSULTAORI",$msgerr);
	    echo($sqlerr);
	    return false;
	}else{
	    $dados = mysqli_fetch_assoc($resdados);
	    $_SESSION["vwmdfedados"]= $dados;			
	}
	
	$sqldest = "SELECT *
	FROM "._DBAPP.".vwmdfecondutor
	where idmdfe=".$idmdfe;
	$resdest = d::b()->query($sqldest);

	if(!$resdest){
	    $msgerr = "Erro ao consultar dados do condutor: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqldest;
	    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
	    LOGETAPA($_SESSION["idmdfelote"],"CONSULTAORI",$msgerr);
	    echo($sqlerr);
	    return false;
	}else{
	    $dest = mysqli_fetch_assoc($resdest);
	    $_SESSION["vwmdfecondutor"]= $dest;			
	}

	

	$sqld = "SELECT *
	FROM "._DBAPP.".vwmdfeveiculo
	where idmdfe=".$idmdfe;
	$resd = d::b()->query($sqld);

	if(!$resd){
	    $msgerr = "Erro ao consultar veiculo da NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqld;
	    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
	    LOGETAPA($_SESSION["idmdfelote"],"CONSULTAORI",$msgerr);
	    echo($sqlerr);
	    return false;
	}else{
	    $dadosv = mysqli_fetch_assoc($resd);
	    $_SESSION["vwmdfeveiculo"]= $dadosv;			
	}


	$sqlt = "SELECT *
	FROM "._DBAPP.".vwmdferodape
	where idmdfe=".$idmdfe;
	$rest = d::b()->query($sqlt);

	if(!$rest){
	    $msgerr = "Erro ao consultar dados da rodape NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlt;
	    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
	    LOGETAPA($_SESSION["idmdfelote"],"CONSULTAORI",$msgerr);
	    echo($sqlerr);
	    return false;
	}else{
	    $dadost = mysqli_fetch_assoc($rest);
	    $_SESSION["vwmdferodape"]= $dadost;			
	}
		
	LOGETAPA($_SESSION["idmdfelote"],"CONSULTAORI","SUCESSO");

	return true;
}

function GERACAO(){
	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - GERACAO: gerando xml";
	$_SESSION["steperro"]="geracao";
	
	//recupera a session com o id da NF
	$idmdfe = $_SESSION["idmdfe"];
	/*
	 * inicia funcao recursiva de montagem
	 */
	geraxml($idmdfe,$_SESSION["strprocesso"]);
	
	//grava na tabela nf o xml que foi enviado por ultimo
	$sql="update "._DBAPP.".mdfe set xml='".$_SESSION["xml"]."' where idmdfe = ".$idmdfe;
	$retx = d::b()->query($sql);

	if(!$retx){
	    //LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	    echo("Erro ao gravar xml na NF : \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sql);
	    $msgerr="Erro ao gravar xml na NF : \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sql;
	    LOGETAPA($_SESSION["idmdfelote"],"GERACAO",$msgerr);
	    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
	    return false;
	}else{
	    //esvazia variveis com texto de xml
	    //unset($_SESSION["xml"]);
	    //unset($_SESSION["xmlret"]);
	    //die($_SESSION["xml"]);// quando for gerar o XML manualmente
	    LOGETAPA($_SESSION["idmdfelote"],"GERACAO","SUCESSO");
	    return true;
	} 
	//return true;
}//function GERACAO(){

function geraxml($inidmdfe,$proc="",$node=0,$level=0){ 

	$_SESSION["steperro"]="geraxml[".$node."]";
	
	for ($i = 1; $i <= $level; $i++) { 
		$t1 .= chr(9);//Indenta Estrutura 
	} 
	$level++; 
	
	$sql = "SELECT * FROM "._DBNFE.".mdfexmltree where ativo = 'Y' and processo='".$proc."' and pai = ".$node." order by ord";
 	$res = d::b()->query($sql); 
	if (!$res){
		$msgerr = "Erro ao consultar mdfexmltree: ".mysqli_error(d::b())."\n SQL: ".$sql;
		STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
		LOGETAPA($_SESSION["idmdfelote"],"GERACAO",$msgerr);
		echo($sqlerr);
		return false;
	}
	 
	while ($r = mysqli_fetch_assoc($res)) { 
		//verifica se o pai tem filhos
		$sqlleaf = "select count(*) as qtleaf from "._DBNFE.".mdfexmltree where pai = ".$r['idmdfexmltree']. " and ativo = 'Y'";
		$resassoc = d::b()->query($sqlleaf) or die("Erro ao consultar nodes/leafs: ".mysqli_error(d::b())."\n SQL: ".$sqlleaf);
		$rleaf = mysqli_fetch_assoc($resassoc);
		$qtleaf = $rleaf["qtleaf"];		
		//echo "\n".$r['no']."-qtleaf: ".$qtleaf;
		
		//em alguns casos e necessario ver se aquela tag pai possui filhos com valor para preencher os mesmos - hermesp <tag> ex:<transportadora>.
		if($r['verificaorig']=="Y" and !empty($_SESSION[$r["arrorigem"]])){
			//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidmdfe,$t1,$r);
				geraxml($inidmdfe,$proc,$r['idmdfexmltree'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";			
		}elseif($r['verificaorig']=="N"){
			if($qtleaf > 0){ //NODE
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidmdfe,$t1,$r);
				geraxml($inidmdfe,$proc,$r['idmdfexmltree'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";
			}else{
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidmdfe,$t1,$r);
				if($r["tipo"]=="tag"){ //não fecha o ultimo nó pois ele foi montado pelo loop na montatag
					$vlrfixo =$_SESSION[$r["arrorigem"]][$r["no"]];				
					if($r['no']=='CNPJ' and  strlen($vlrfixo)==11){	    				
			    		$_SESSION["xml"] .= "</CPF>";
			    	}else{
						$_SESSION["xml"] .= "</".$r['no'].">";
			    	}					
					
				}
			}
		}
	} 
}//function geraxml($inidmdfe,$proc="",$node=0,$level=0){ 


function montatag($inidmdfe,$t,$r){
	
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
	 * 	- valor fixo: esta escrito na tabela de configuracao
	 *	- simples: pega somente 1 linha de um array conforme configuracao da tablea
	 *	- loop: efetua LOOP em array pre-executado e escreve, para cada chave e valor encontrado, as tags e valor
	 * maf201211: Cria nova coluna para controlar o tipo da criacao da tag: [tipo  ]
	 * maf201211: 2 novos comportamentos para o loop: 
	 * 	1 - Cria um atributo autoinc conforme coluna [atrautoinc] 2
	 * 	2 - Verifica se o array é multidimensional [arraymultidim] . 
	 * 		Caso positivo, lê os valores e gera os xml de acordo com a views
	 */
	if(!empty($r["vlrfixo"])){//possui valor fixo
		$vlrfixo = trim($r['vlrfixo']);
		return "\n".$t."<".$r['no'].$atrfixo.">".$vlrfixo;
	}elseif(!empty($r["arrorigem"])){//tem que buscar num array de dados	
		
	    //print_r($_SESSION[$r["arrorigem"]]);		
	    if($r["tipo"]=="tag" ){//Cria a tag conforme configuracao do campo da xmltree
		    $vlrfixo =$_SESSION[$r["arrorigem"]][$r["no"]];	

		    if($r['no']=='CNPJ' and  strlen($vlrfixo)==11){	    				
		    return "\n".$t."<CPF>".$vlrfixo;
	    }else{
			    return "\n".$t."<".$r['no'].$atrfixo.">".$vlrfixo;
	    }

	    }elseif($r["tipo"]=="array"){//Loop conforme os registro de encontrados no array
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
		    echo "ERRO: Configurar xmltree.tipo [".$r['tipo']."] para a tag [".$r['no']."]!";
		    $msgerr="ERRO: Configurar xmltree.tipo [".$r['tipo']."] para a tag [".$r['no']."]!";
		    STATUSLOTE($_SESSION["idmdfelote"],"ERRO");
		    LOGETAPA($_SESSION["idmdfelote"],"GERACAO",$msgerr);
		    die;
	    }
	}elseif($r["tipo"]=="arraymultidim"){// no caso dos itens da NF busca os dados dos items nas views
			   
	    //print_r($data); die;
	    //coloca o valor nas tags 
	   if($r["no"] =='infMunDescarga'){
			$sqldi="select idmdfe,cMunDescarga,xMunDescarga,GROUP_CONCAT( chNFE SEPARATOR ';') strchNFe
					from vwmdfedescarga where idmdfe=".$inidmdfe." group by cMunDescarga";
			$resdi =d::b()->query($sqldi);
			while($rowv=mysqli_fetch_assoc($resdi)){
				$strret.="\n".$t."<infMunDescarga>";
				$strret.=("\n".$t.chr(9)."<cMunDescarga>".$rowv['cMunDescarga']."</cMunDescarga>");
				$strret.=("\n".$t.chr(9)."<xMunDescarga>".$rowv['xMunDescarga']."</xMunDescarga>");
				$notas = explode(";",$rowv['strchNFe']);
				foreach($notas as $chNFe) {
				$strret.=("\n".$t.chr(9)."<infNFe>");
				$strret.=("\n".$t.chr(9).chr(9)."<chNFe>".$chNFe."</chNFe>");
				$strret.=("\n".$t.chr(9)."</infNFe>");
				}
				$strret.="\n".$t."</infMunDescarga>";
			}
	   }

	   if($r["no"] =='infPercurso'){
		$sqldi="select * from mdfeufper where idmdfe=".$inidmdfe;
		$resdi =d::b()->query($sqldi);
		$qtdi=mysqli_num_rows($resdi);
		if($qtdi>0){
			
			while($rowv=mysqli_fetch_assoc($resdi)){
				$strret.="\n".$t."<infPercurso>";
				$strret.=("\n".$t.chr(9)."<UFPer>".$rowv['uf']."</UFPer>");
				$strret.="\n".$t."</infPercurso>";
			}
			
		}
		
   }
	   	    		    	
	   	
	    return $strret;
	}else{
	    return "\n".$t."<".$r['no'].$atrfixo.">";//nao possui valor ou pode ser NO
	}  
}//function montatag($inidmdfe,$t,$r){

	function assina_envia_XML($sXML, $tagID){	
		//die($sXML);
		//maf199619: slug retirado
		//$sXML = string2Slug($sXML);
	
		//$sXML=("<NFe xmlns=\"http://www.portalfiscal.inf.br/nfe\"><infNFe Id=\"NFe31170523259427000104550010000033731790970782\" versao=\"4.00\"><ide><cUF>31</cUF><cNF>79097078</cNF><natOp>REMESSA DE MATERIAL PARA ANÁLISE</natOp><mod>55</mod><serie>1</serie><nNF>3373</nNF><dhEmi>2017-05-29T10:57:00-02:00</dhEmi><tpNF>1</tpNF><idDest>2</idDest><cMunFG>3170206</cMunFG><tpImp>1</tpImp><tpEmis>1</tpEmis><cDV>2</cDV><tpAmb>1</tpAmb><finNFe>1</finNFe><indFinal>1</indFinal><indPres>9</indPres><procEmi>3</procEmi><verProc>3.10.49</verProc></ide><emit><CNPJ>23259427000104</CNPJ><xNome>LAUDO LABORATÓRIO AVÍCOLA UBERLÂNDIA LTDA</xNome><xFant>LAUDO LABORATÓRIO</xFant><enderEmit><xLgr>RODOVIA BR 365, KM 615</xLgr><nro>S/N</nro><xBairro>ALVORADA</xBairro><cMun>3170206</cMun><xMun>Uberlandia</xMun><UF>MG</UF><CEP>38407180</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>3432225700</fone></enderEmit><IE>7023871770001</IE><CRT>3</CRT></emit><dest><CNPJ>37020260000309</CNPJ><xNome>NUTRIZA AGROINDUSTRIAL DE ALIMENTOS S/A</xNome><enderDest><xLgr>RODOVIA-GO 020</xLgr><nro>S/N</nro><xBairro>ZONA RURAL</xBairro><cMun>5217401</cMun><xMun>PIRES DO RIO</xMun><UF>GO</UF><CEP>75200000</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>6434617969</fone></enderDest><indIEDest>1</indIEDest><IE>102649189</IE><email>valderez.limberger@friato.com.br</email></dest><det nItem=\"1\"><prod><cProd>MCA</cProd><cEAN></cEAN><xProd>MATERIAL PARA COLETA DE AMOSTRA(S)</xProd><NCM>23099010</NCM><CFOP>6949</CFOP><uCom>UN</uCom><qCom>1.00</qCom><vUnCom>1.9900</vUnCom><vProd>1.99</vProd><cEANTrib></cEANTrib><uTrib>UN</uTrib><qTrib>1.00</qTrib><vUnTrib>1.9900</vUnTrib><indTot>1</indTot><xPed>000000</xPed><nItemPed>00000</nItemPed></prod><imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>1.99</vBC><pICMS>7.00</pICMS><vICMS>0.14</vICMS></ICMS00></ICMS><IPI><cEnq>999</cEnq><IPINT><CST>51</CST></IPINT></IPI><PIS><PISNT><CST>07</CST></PISNT></PIS><COFINS><COFINSNT><CST>07</CST></COFINSNT></COFINS></imposto></det><total><ICMSTot><vBC>1.99</vBC><vICMS>0.14</vICMS><vICMSDeson>0.00</vICMSDeson><vFCPUFDest>0.00</vFCPUFDest><vICMSUFDest>0.00</vICMSUFDest><vICMSUFRemet>0.00</vICMSUFRemet><vFCP>1</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>1</vFCPST><vFCPSTRet>1</vFCPSTRet><vProd>1.99</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>1</vIPIDevol><vPIS>0.00</vPIS><vCOFINS>0.00</vCOFINS><vOutro>0.00</vOutro><vNF>1.99</vNF></ICMSTot></total><transp><modFrete>0</modFrete><transporta><CNPJ>18260422000161</CNPJ><xNome>NACIONAL EXPRESSO LTDA</xNome><IE>7021867530016</IE><xEnder>PRAÇA DA BÍBLIA S/N</xEnder><xMun>UBERLANDIA</xMun><UF>MG</UF></transporta></transp><pag><detPag><tPag>01</tPag><vPag>1.00</vPag><card><tpIntegra>1</tpIntegra><CNPJ>63322115000112</CNPJ><tBand>01</tBand><cAut>01</cAut></card></detPag><vTroco>1.00</vTroco></pag><infAdic><infCpl>111</infCpl></infAdic></infNFe></NFe>");
		//echo $sXML;
		STATUSLOTE($_SESSION["idmdfelote"],"ENVIO");
		LOGETAPA($_SESSION["idmdfelote"],"ENVIO");
		
		//if(_NFSECHOLOG) echo "\n".date("H:i:s")." - assinaxml: assinando xml";
		$_SESSION["steperro"]="assinaxml";
	
		$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_SESSION["idempresa"];
		$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
		$_row=mysqli_fetch_assoc($_res);
	
		$config = [
			"atualizacao" => date('Y-m-d H:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $_row["nfrazaosocial"],
			"cnpj" => $_row["nfcnpj"],
			"ie" => '',
			"siglaUF" => $_row["nfsiglaUF"],
			"versao" => '3.00'
		];


		$arquivo=str_replace("../","../../nfe/sefaz4/",$_row["certificado"]);

	//	echo("certificado=".$arquivo);

		$certificadoDigital = file_get_contents($arquivo);

		//$certificadoDigital = file_get_contents('../../nfe/sefaz4/certs/1001845428certificadolaudolaboratorio10fb4b9_56044.08fb4b9_56044.21fb4b9_56044.pfx');
		
	
		//echo('Certificado='.$_row["senha"]);
		
		try {
			$certificate = Certificate::readPfx($certificadoDigital, $_row["senha"]);
		
			$xml=$_SESSION["xml"];

			//echo($xml); die;

			$tools = new Tools(json_encode($config), $certificate);
			
			$xmlAssinado = $tools->signMDFe($xml);
		
			header('Content-type: text/plain; charset=UTF-8');
			//echo $xmlAssinado;
			
			$resp = $tools->sefazEnviaLote([$xmlAssinado], rand(1, 10000));
		 	ECHO($resp);
			
			$st = new Standardize();
			$std = $st->toStd($resp);
		
			sleep(3);

			$recibo=$std->infRec->nRec;	
			$xMotivo=$std->xMotivo;	


			//if ($cStat != 100) {
				
				echo('\n'.$xMotivo.'\n');
				
			//	exit("$xMotivo");
				//erro registrar e voltar
		//	}

			$_SESSION["nlote"] = $recibo;


			$sql="update "._DBAPP.".mdfe set recibo='".$recibo."',statusmdfe='ENVIADO' where statusmdfe !='CONCLUIDO' and  idmdfe = ".$_SESSION["idmdfe"];
			$retx = d::b()->query($sql);
		

			STATUSLOTE($_SESSION["idmdfelote"],"SUCESSO");
			LOGETAPA($_SESSION["idmdfelote"],"SUCESSO");

			return $xmlAssinado;
		
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}

}

function ASSINATURA_ENVIA(){

		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ASSINATURA: gerando assinatura";
		$_SESSION["steperro"]="assinatura";	
	
		//Inicia a Etapa	
		STATUSLOTE($_SESSION["idmdfelote"],"ASSINATURA");
		LOGETAPA($_SESSION["idmdfelote"],"ASSINATURA");
		//echo($_SESSION["xml"]);
		//retirar todos os espaço do xml antes de assinar
		$order = array("\r\n", "\n", "\r", "\t");
		$replace = '';
		$_SESSION["xml"] = str_replace($order, $replace, $_SESSION["xml"]);
		$_SESSION["xml"] = str_replace('<procEmi></procEmi>','<procEmi>0</procEmi>', $_SESSION["xml"]);  
		$_SESSION["xml"] = str_replace('<UFPer></UFPer>','', $_SESSION["xml"]);
		$_SESSION["xml"] = str_replace('<infPercurso></infPercurso>','', $_SESSION["xml"]);
		
		
		/*
		 * inicia funcao recursiva de montagem
		 */
	
		//echo $_SESSION["xml"];
		//$xmtlutf = ($_SESSION["xml"]);	
		
		//Assinar o XML
		$_SESSION["xml"] = assina_envia_XML($_SESSION["xml"], $_SESSION["tagassinatura"]);
	//	LOGETAPA($_SESSION["idmdfelote"],"ASSINATURA1");
		/*Retirar o <?xml version="1.0" adicionado na assinatura pois o metodo de envio da toolsNfephp adiciona ao encapsular no soap 
		 * REtirei para não ficar duplicado hermesp
		 */
	
		$_SESSION["xml"] = str_replace('<?xml version="1.0"?>','', $_SESSION["xml"]);
	
		//LOGETAPA($_SESSION["idmdfelote"],"ASSINATURA2");
	
		$idmdfe = $_SESSION["idmdfe"];		
	
		
		$sql="update "._DBAPP.".mdfe 
		set xmlret= '".$_SESSION["xml"]."'
		where  idmdfe = ".$_SESSION["idmdfe"];
		$retx = d::b()->query($sql) or die("Erro ao atualizar mdfe sql:".$sql);



		
	
		//echo($_SESSION["xml"]);
		//die();	
		return true; 
	}//assina e envia


	if(LOTE($idmdfe) 	== true
    and CONSULTAORI() 	== true
    and GERACAO() 		== true	
	and ASSINATURA_ENVIA() 	== true
     ){
		// print_r($_SESSION["xml"]); die;
    die("\n- mdfe [".$_SESSION["nmdfe"]."] enviada com sucesso! Recibo para consulta: [". $_SESSION["nlote"]."] ");
}else{
    die("\n".date("H:i:s")." - Erro Fatal:".$_SESSION["steperro"]);
} 


/*


$config = [
    "atualizacao" => date('Y-m-d H:i:s'),
    "tpAmb" => 2,
    "razaosocial" => 'FÁBRICA DE SOFTWARE MATRIZ',
    "cnpj" => '',
    "ie" => '',
    "siglaUF" => 'PR',
    "versao" => '3.00'
];

$certificadoDigital = file_get_contents('../../nfe/sefaz4/certs/1001845428certificadolaudolaboratorio10fb4b9_56044.08fb4b9_56044.21fb4b9_56044.pfx');

//echo('Certificado='.$_row["senha"]);

try {
    $certificate = Certificate::readPfx($certificadoDigital, $_row["senha"]);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><MDFe xmlns="http://www.portalfiscal.inf.br/mdfe"><infMDFe Id="MDFe41190822545265000108580260000000081326846774" versao="3.00"><ide><cUF>41</cUF><tpAmb>2</tpAmb><tpEmit>2</tpEmit><mod>58</mod><serie>26</serie><nMDF>8</nMDF><cMDF>32684677</cMDF><cDV>4</cDV><modal>1</modal><dhEmi>2019-08-14T11:35:01-03:00</dhEmi><tpEmis>2</tpEmis><procEmi>0</procEmi><verProc>3.9.8</verProc><UFIni>PR</UFIni><UFFim>RS</UFFim><infMunCarrega><cMunCarrega>4108403</cMunCarrega><xMunCarrega>Francisco Beltrao</xMunCarrega></infMunCarrega><infPercurso><UFPer>SC</UFPer></infPercurso></ide><emit><CNPJ>22545265000108</CNPJ><IE>9069531021</IE><xNome>EMPRESA DEMONSTRACAO LTDA</xNome><xFant>FABRICA DE SOFTWARE MATRIZ</xFant><enderEmit><xLgr>AVENIDA JULIO ASSIS CAVALHEIRO</xLgr><nro>1</nro><xBairro>CENTRO</xBairro><cMun>4108403</cMun><xMun>Francisco Beltrao</xMun><CEP>85601000</CEP><UF>PR</UF><fone>4635230686</fone></enderEmit></emit><infModal versaoModal="3.00"><rodo xmlns="http://www.portalfiscal.inf.br/mdfe"><infANTT><RNTRC>12345678</RNTRC><infContratante><CPF>01234567890</CPF></infContratante></infANTT><veicTracao><placa>ABC1011</placa><RENAVAM>32132132131</RENAVAM><tara>0</tara><prop><CPF>01234567890</CPF><RNTRC>88888888</RNTRC><xNome>ALISSON</xNome><IE/><UF>PR</UF><tpProp>0</tpProp></prop><condutor><xNome>CLEITON</xNome><CPF>06844990960</CPF></condutor><tpRod>01</tpRod><tpCar>01</tpCar><UF>PR</UF></veicTracao><veicReboque><placa>ABC1012</placa><RENAVAM>12313213213</RENAVAM><tara>0</tara><capKG>20000</capKG><capM3>180</capM3><prop><CPF>01234567890</CPF><RNTRC>88888888</RNTRC><xNome>ALISSON</xNome><IE/><UF>PR</UF><tpProp>0</tpProp></prop><tpCar>03</tpCar><UF>PR</UF></veicReboque></rodo></infModal><infDoc><infMunDescarga><cMunDescarga>4314902</cMunDescarga><xMunDescarga>Porto Alegre</xMunDescarga><infNFe><chNFe>41190122545265000108550270000004491369658540</chNFe></infNFe></infMunDescarga><infMunDescarga><cMunDescarga>4300208</cMunDescarga><xMunDescarga>Ajuricaba</xMunDescarga><infNFe><chNFe>41190522545265000108550270000005731334929373</chNFe></infNFe></infMunDescarga></infDoc><tot><qNFe>2</qNFe><vCarga>72.04</vCarga><cUnid>01</cUnid><qCarga>3.0000</qCarga></tot><lacres><nLacre>3113213213213213213213</nLacre></lacres></infMDFe></MDFe>';

    $tools = new Tools(json_encode($config), $certificate);
    
    $xmlAssinado = $tools->signMDFe($xml);

    header('Content-type: text/plain; charset=UTF-8');
    echo $xmlAssinado;
    
    $resp = $tools->sefazEnviaLote([$xmlAssinado], rand(1, 10000));

    
    $st = new Standardize();
    $std = $st->toStd($resp);

    sleep(3);

    $resp = $tools->sefazConsultaRecibo($std->infRec->nRec);
    $std = $st->toStd($resp);

    echo '<pre>';
    print_r($std);
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
*/
?>