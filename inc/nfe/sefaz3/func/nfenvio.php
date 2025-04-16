<?
/*#### HERMES PEDRO BORGES - 07/03/2013 ######
 * gerar xml e enviar o mesmo para o sefaz via webservice com metodo do nfephp
 * 
 */
//include_once("../validaacesso.php");
include_once("../../../php/functions.php");
require_once('../libs/NFe/ToolsNFePHP.class.php');
//ini_set("display_errors","1");
//error_reporting(E_ALL);
//conectabanco();
session_start();

$_SESSION["idnotafiscal"] = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
$_SESSION["idnfplote"] = 0;
$_SESSION["xml"] = "";
$_SESSION["xmlret"] = "";
$_SESSION["steperro"]="";
$_SESSION["strprocesso"]="procNfe";
$_SESSION["tagassinatura"]="infNFe";

$_SESSION["strprocesso"]="procNfe";

function geranumeronfe(){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronfe";

	$sqllnf = "select nnfe from nf where idnf = ".$_SESSION["idnotafiscal"];
	$resnf = d::b()->query($sqllnf) or die("Erro pesquisando nnfe na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
	$rnf = mysqli_fetch_assoc($resnf);

	if(empty($rnf["nnfe"])){

		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:gerando nova nfe";

		### Tenta incrementar e recuperar o numerorps
		d::b()->query("LOCK TABLES sequence WRITE;");
		d::b()->query("update sequence set chave1 = (chave1 + 1) where sequence = 'nnfe'");

		$sql = "SELECT convert( lpad(chave1, '9', '0') using latin1) as chave1 FROM sequence where sequence = 'nnfe';";

		$res = d::b()->query($sql);

		if(!$res){
			d::b()->query("UNLOCK TABLES;");
			echo "1-Falha Pesquisando Sequence [nnfe] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	
		$row = mysqli_fetch_array($res);
	
		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["chave1"])){
			if(!$resexercicio){
				d::b()->query("UNLOCK TABLES;");
				echo "2-Falha Pesquisando Sequence [nnfe] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
				die();
			}
		}
	
		d::b()->query("UNLOCK TABLES;");
	
		$_SESSION["nnfe"] = $row["chave1"];

		$sqlnf = "update nf set nnfe = '".$_SESSION["nnfe"]."' where idnf = ".$_SESSION["idnotafiscal"];
		d::b()->query($sqlnf) or die("Erro atribuindo nnfe:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

	}else{
		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:capturando numero da nfe existente[".$rnf["nnfe"]."]";
		$_SESSION["nnfe"] = $rnf["nnfe"];
	}


}

/*
 * maf010410: funcao criada para efetuar a montagem das tags XML
 * As condicoes para montagem estao na tabela nfpxmltree
 * Esta funcao eh chamada pela etapa de geracao do XML
 * par $r: Cont�m informacoes de cada linha da nfpxmltree para cada tag
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
	 * 	- valor fixo: esta escrito na tabela de configuracao
	 *	- simples: pega somente 1 linha de um array conforme configuracao da tablea
	 *	- loop: efetua LOOP em array pre-executado e escreve, para cada chave e valor encontrado, as tags e valor
	 * maf201211: Cria nova coluna para controlar o tipo da criacao da tag: [tipo  ]
	 * maf201211: 2 novos comportamentos para o loop: 
	 * 	1 - Cria um atributo autoinc conforme coluna [atrautoinc] 2
	 * 	2 - Verifica se o array � multidimensional [arraymultidim] . 
	 * 		Caso positivo, l� os valores e gera os xml de acordo com a views
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
			STATUSLOTE($_SESSION["idnfplote"],"ERRO");
			LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
			die;
		}
	}elseif($r["tipo"]=="arraymultidim"){// no caso dos itens da NF busca os dados dos items nas views
		
		$sqldi="select * from vwnfdadositem where idnf=".$inidnf;
		$resdi =d::b()->query($sqldi);
			
		if(!$resdi){
			$msgerr = "falha ao listar os itens  \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqldi;
			STATUSLOTE($_SESSION["idnfplote"],"ERRO");
			LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
			echo($msgerr);
			return false;
		}
		//monta os nomes das tags
		    $arrColunas = mysqli_fetch_fields($resdi);			
		    $i=0;
		    $data=array();
		    while($r = mysqli_fetch_assoc($resdi)){
			    $i=$i+1;
			    //para cada coluna resultante do select cria-se um item no array
			    foreach ($arrColunas as $col) {
				    //$arrret[$i][$col->name]=$robj[$col->name];
				    $data[$r["idnfitem"]][$col->name]=$r[$col->name];
			    }
		    }
		/*
		   unset($data);
			for ($i = 0; $i < mysqli_num_fields($resdi); $i++) {
			$meta = mysqli_fetch_field($resdi, $i);
			$data[$meta->name] = $row[$i];	        
		    }
		*/
	   
		//print_r($data); die;
	    //coloca o valor nas tags 
		$det=0;
		$sqldi="select * from vwnfdadositem where idnf=".$inidnf;
		$resdi =d::b()->query($sqldi);
	    while($rowv=mysqli_fetch_assoc($resdi)){
	    	foreach ($data[$rowv["idnfitem"]]as $k => $v) {
	    		
	    		if($k=="idnf"){
	    			$det=$det+1;
	    			$strret.="\n".$t."<det nItem=\"".$det."\">";
	    			$strret.="\n".$t."<prod>";
	    		}elseif($k!="idnfitem" and $k!="indiedest"){
	    			//item n�o pode ter frete com valor 0 se for zero a tag ira vazia para ser retirada
	    			if($k=='vFrete' and $v<1){
	    				$strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
	    			}elseif($k=='vDesc' and $rowv[$k]<1){
	    				$strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
	    			}else{
	    				$strret.=("\n".$t.chr(9)."<".$k.">".$v."</".$k.">");
	    			}
	    		}			   
			}
			$strret.="\n".$t."</prod>";	
			//TAG IMPOSTO
			$strret.="\n".$t."<imposto>";
			
			//INICIO ICMS verifica qual o cst do item
			$sqlcst="select i.cst,p.piscst,p.confinscst from nfitem i,prodserv p where p.idprodserv=i.idprodserv and i.idnfitem=".$rowv["idnfitem"];
			$rescst =d::b()->query($sqlcst);
	    	if(!$rescst){
				$msgerr = "falha ao listar o CST do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlcst;
				STATUSLOTE($_SESSION["idnfplote"],"ERRO");
				LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
				echo($msgerr);
				return false;
			}
		
			$rowcst=mysqli_fetch_assoc($rescst);
			if(empty($rowcst['cst'])){
				$cst="00";
			}else{
				$cst=$rowcst['cst'];
			}
			
			//so os do if est�o configurados abaixo (e necessario criar uma view para cada cst 
			if($cst!="00" and $cst!="20" and $cst!="40" and $cst!="50" and $cst!="60" and $cst!="41" and $cst!="500"){
				$msgerr = "Erro o CST ".$cst." ainda n�o foi desenvolvido favor entrar e contato com o ADM do sistema.";
				STATUSLOTE($_SESSION["idnfplote"],"ERRO"); 
				LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
				
			}
			
		    $sqlicms="select * from vwnficms".$cst." where idnfitem=".$rowv["idnfitem"];
			$resicms =d::b()->query($sqlicms);	    	
			if(!$resicms){
				$msgerr = "falha ao listar o ICMS do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlicms;
				STATUSLOTE($_SESSION["idnfplote"],"ERRO");
				LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
				echo($msgerr);
				return false;
			}
			//especialmente para o cst 41;hermes 14042015
			if($cst=="41"){
				$cst="ST";
			}
			if($cst=="500"){
				$cst="SN500";
			}
				
			
			//gera o nome das tags com os campos da view
			
		    $arrColunas = mysqli_fetch_fields($resicms);			
		    $i=0;
		    $dataicms=array();
		    while($r = mysqli_fetch_assoc($resicms)){
			    $i=$i+1;
			    //para cada coluna resultante do select cria-se um item no array
			    foreach ($arrColunas as $col) {
				    //$arrret[$i][$col->name]=$robj[$col->name];
				    $dataicms[$col->name]=$r[$col->name];
			    }
		    }
		/*
		    unset($dataicms);	
		    for ($y = 0; $y < mysqli_num_fields($resicms); $y++) {
		        $metai = mysqli_fetch_field($resicms, $y);
		        $dataicms[$metai->name] = $row[$y];	        
		    }
		*/
		    if($cst=='50'){
			$cst='40';
		    }
		    //preeche as tags com os valores da view
		    $strret.="\n".$t."<ICMS>";
		    $strret.="\n".$t."<ICMS".$cst.">";
		   //  while($rowicms=mysqli_fetch_assoc($resicms)){
		    	foreach ($dataicms as $j => $vicms) {
		    		if($j!="idnfitem"){
		    			$strret.=("\n".$t.chr(9)."<".$j.">".$vicms."</".$j.">");
		    		}			   
				}
		    // }
		    $strret.="\n".$t."</ICMS".$cst.">";
		   	$strret.="\n".$t."</ICMS>";	
		   	
		   	
			//INICIO IPI
		   	$sqlipi="select * from vwnfipi where idnfitem=".$rowv["idnfitem"];
		   	$resipi =d::b()->query($sqlipi);
	    	if(!$resipi){
				$msgerr = "falha ao listar o IPI do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlipi;
				STATUSLOTE($_SESSION["idnfplote"],"ERRO");
				LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
				echo($msgerr);
				return false;
			}
			
		   	$rowipi=mysqli_fetch_assoc($resipi);
		   	$strret.="\n".$t."<IPI>";
		   	$strret.=("\n".$t.chr(9)."<clEnq>".$rowipi["clEnq"]."</clEnq>");
		   	$strret.=("\n".$t.chr(9)."<cEnq>".$rowipi["cEnq"]."</cEnq>");
		   	$strret.="\n".$t."<IPINT>";
		   	$strret.=("\n".$t.chr(9)."<CST>".$rowipi["CST"]."</CST>");
		   	$strret.="\n".$t."</IPINT>";
		   	$strret.="\n".$t."</IPI>";
		   
		   	//INICIO PIS
		   	if($rowcst['piscst'] =='01'){
			   	$sqlpis="select * from vwnfpis where idnfitem=".$rowv["idnfitem"];
			   	$respis =d::b()->query($sqlpis);
		    	if(!$respis){
					$msgerr = "falha ao listar o PIS do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlpis;
					STATUSLOTE($_SESSION["idnfplote"],"ERRO");
					LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
					echo($msgerr);
					return false;
				}
			   	$rowpis=mysqli_fetch_assoc($respis);
			   	$strret.="\n".$t."<PIS>";
			   	$strret.="\n".$t."<PISAliq>";
			   	$strret.=("\n".$t.chr(9)."<CST>".$rowpis["CST"]."</CST>");
			   	$strret.=("\n".$t.chr(9)."<vBC>".$rowpis["vBC"]."</vBC>");
			   	$strret.=("\n".$t.chr(9)."<pPIS>".$rowpis["pPIS"]."</pPIS>");
			   	$strret.=("\n".$t.chr(9)."<vPIS>".$rowpis["vPIS"]."</vPIS>");		   	
			   	$strret.="\n".$t."</PISAliq>";
			   	$strret.="\n".$t."</PIS>";
		   	}else{
		   		
		   		$strret.="\n".$t."<PIS>";
			   	$strret.="\n".$t."<PISNT>";
			   	$strret.=("\n".$t.chr(9)."<CST>".$rowcst['piscst']."</CST>");
			   	$strret.="\n".$t."</PISNT>";
			   	$strret.="\n".$t."</PIS>";
		   		
		   	}
		 	//COFINS
		   	if($rowcst['confinscst'] =='01'){
			 	//<COFINS><COFINSAliq><CST>01</CST><vBC>1500.00</vBC><pCOFINS>3.00</pCOFINS><vCOFINS>45.00</vCOFINS></COFINSAliq></COFINS> 
			 	$sqlcof="select * from vwnfcofins where idnfitem=".$rowv["idnfitem"];
			   	$rescof =d::b()->query($sqlcof);
		    	if(!$rescof){
					$msgerr = "falha ao listar o COFINS do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlcof;
					STATUSLOTE($_SESSION["idnfplote"],"ERRO");
					LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
					echo($msgerr);
					return false;
				}
			   	$rowcof=mysqli_fetch_assoc($rescof);
			   	$strret.="\n".$t."<COFINS>";
			   	$strret.="\n".$t."<COFINSAliq>";
			   	$strret.=("\n".$t.chr(9)."<CST>".$rowcof["CST"]."</CST>");
			   	$strret.=("\n".$t.chr(9)."<vBC>".$rowcof["vBC"]."</vBC>");
			   	$strret.=("\n".$t.chr(9)."<pCOFINS>".$rowcof["pCOFINS"]."</pCOFINS>");
			   	$strret.=("\n".$t.chr(9)."<vCOFINS>".$rowcof["vCOFINS"]."</vCOFINS>");		   	
			   	$strret.="\n".$t."</COFINSAliq>";
			   	$strret.="\n".$t."</COFINS>"; 			   	
			   	

		   	}else{
		   		
		   		$strret.="\n".$t."<COFINS>";
			   	$strret.="\n".$t."<COFINSNT>";
			   	$strret.=("\n".$t.chr(9)."<CST>".$rowcst['confinscst']."</CST>");
			   	$strret.="\n".$t."</COFINSNT>";
			   	$strret.="\n".$t."</COFINS>";
		   		
		   	}
		   	
		   	
		   	if($rowv['indiedest']==9){
		   		
		   		$sqlicmsuf="select * from vwnficmsuf where idnfitem=".$rowv["idnfitem"];
		   		$resicmsuf =d::b()->query($sqlicmsuf);	
		   		$rowicmsuf = mysqli_fetch_assoc($resicmsuf);	   		
		   	
			   	$strret.="\n".$t."<ICMSUFDest>";
			   	$strret.=("\n".$t.chr(9)."<vBCUFDest>".$rowicmsuf['vBCUFDest']."</vBCUFDest>");
			   	$strret.=("\n".$t.chr(9)."<pFCPUFDest>".$rowicmsuf['pFCPUFDest']."</pFCPUFDest>");
			   	$strret.=("\n".$t.chr(9)."<pICMSUFDest>".$rowicmsuf['pICMSUFDest']."</pICMSUFDest>");
			   	$strret.=("\n".$t.chr(9)."<pICMSInter>".$rowicmsuf['pICMSInter']."</pICMSInter>");
			   	$strret.=("\n".$t.chr(9)."<pICMSInterPart>".$rowicmsuf['pICMSInterPart']."</pICMSInterPart>");
			   	$strret.=("\n".$t.chr(9)."<vFCPUFDest>".$rowicmsuf['vFCPUFDest']."</vFCPUFDest>");
			   	$strret.=("\n".$t.chr(9)."<vICMSUFDest>".$rowicmsuf['vICMSUFDest']."</vICMSUFDest>");
			   	$strret.=("\n".$t.chr(9)."<vICMSUFRemet>".$rowicmsuf['vICMSUFRemet']."</vICMSUFRemet>");
			   	$strret.="\n".$t."</ICMSUFDest>";
		   	
		   	}
		   	
		   	$strret.="\n".$t."</imposto>";
		   	//FIM TAG IMPOSTO
		   	
		   	//INFORMA��ES ADICIONAIS DO ITEM
		   	$sqladic="select convert(lpad(replace(l.partida,l.spartida,''),'3', '0')using latin1) as partida
					,l.exercicio 
					,dma(vencimento) as vencimento
				    from lotecons i,lote l
				    where l.idlote = i.idlote
				    and i.tipoobjeto='nfitem'
				    and i.qtdd > 0
				    and i.idobjeto =  ".$rowv['idnfitem']." limit 1";
		   	$resadic=d::b()->query($sqladic);
		   	$rowadic=mysqli_fetch_assoc($resadic);
		   	if(!empty($rowadic['partida']) and !empty($rowadic['vencimento'])){
		   		/*$partidaext = str_replace("/", " ", $rowadic['partidaext']);
		   		$partidaext = str_replace("-", " ", $partidaext);
		   		$vencimento = str_replace("/", ".", $rowadic['vencimento']);*/
		   		$vencimento = substr($rowadic['vencimento'], 3);
		   		
		   		//adiciona a linha com as informa��es adicionais
		   		$strret.="\n".$t."<infAdProd>Part: ".$rowadic['partida']."/".$rowadic['exercicio']." Venc: ".$vencimento."</infAdProd>";
		   	}//FIM INFORMA��ES ADICIONAIS DO ITEM		   	
					
			$strret.="\n".$t."</det>";
	    }	    		    	
			//$strret="<det nItem=\"1\"><prod><cProd>03</cProd><cEAN/><xProd>Antígeno MS INATA - 300 testes</xProd><NCM>30029010</NCM><CFOP>5101</CFOP><uCom>FR</uCom><qCom>5.0000</qCom><vUnCom>300.0000000000</vUnCom><vProd>1500.00</vProd><cEANTrib/><uTrib>FR</uTrib><qTrib>5.0000</qTrib><vUnTrib>300.0000000000</vUnTrib><indTot>1</indTot><xPed>13-0100408</xPed></prod><imposto></imposto></det><det nItem=\"2\"><prod><cProd>04</cProd><cEAN/><xProd>Antígeno PUL INATA - 300 testes</xProd><NCM>30029010</NCM><CFOP>5101</CFOP><uCom>FR</uCom><qCom>10.0000</qCom><vUnCom>64.0000000000</vUnCom><vProd>640.00</vProd><cEANTrib/><uTrib>FR</uTrib><qTrib>10.0000</qTrib><vUnTrib>64.0000000000</vUnTrib><indTot>1</indTot><xPed>13-0100407</xPed></prod><imposto></imposto></det>";
	    //echo($strret); die;	
	    return $strret;
	}else{
		return "\n".$t."<".$r['no'].$atrfixo.">";//nao possui valor ou pode ser NO
	}  
}

/*
 * maf010410: funcao recursiva que le a estrutura do xml na tabela nfpxmltree
 * Com os dados existentes na tabela, percorre recursivamente os NODES encontrados e 
 * vai montando os valores conforme a configuracao (colunas da tabela nfpxmltree) informada 
 */
function geraxml($inidnf,$proc="",$node=0,$level=0){ 

	$_SESSION["steperro"]="geraxml[".$node."]";
	
	for ($i = 1; $i <= $level; $i++) { 
		$t1 .= chr(9);//Indenta Estrutura 
	} 
	$level++; 
	
	$sql = "SELECT * FROM "._DBNFE.".nfpxmltree where ativo = 'Y' and processo='".$proc."' and pai = ".$node." order by ord";
 	$res = d::b()->query($sql); 
	if (!$res){
		$msgerr = "Erro ao consultar nfpxmltree: ".mysqli_error(d::b())."\n SQL: ".$sql;
		STATUSLOTE($_SESSION["idnfplote"],"ERRO");
		LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
		echo($sqlerr);
		return false;
	}
	 
	while ($r = mysqli_fetch_assoc($res)) { 
		//verifica se o pai tem filhos
		$sqlleaf = "select count(*) as qtleaf from "._DBNFE.".nfpxmltree where pai = ".$r['idnfpxmltree']. " and ativo = 'Y'";
		$resassoc = d::b()->query($sqlleaf) or die("Erro ao consultar nodes/leafs: ".mysqli_error(d::b())."\n SQL: ".$sqlleaf);
		$rleaf = mysqli_fetch_assoc($resassoc);
		$qtleaf = $rleaf["qtleaf"];		
		//echo "\n".$r['no']."-qtleaf: ".$qtleaf;
		
		//em alguns casos e necessario ver se aquela tag pai possui filhos com valor para preencher os mesmos - hermesp <tag> ex:<transportadora>.
		if($r['verificaorig']=="Y" and !empty($_SESSION[$r["arrorigem"]])){
			//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
				geraxml($inidnf,$proc,$r['idnfpxmltree'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";			
		}elseif($r['verificaorig']=="N"){
			if($qtleaf > 0){ //NODE
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
				geraxml($inidnf,$proc,$r['idnfpxmltree'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";
			}else{
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
				if($r["tipo"]=="tag"){ //n�o fecha o ultimo n� pois ele foi montado pelo loop na montatag
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
}
/*
 * maf010410: esta funcao armazena os logs de cada etapa do processo de geracao da NFE.
 * As primeiras etapas e o proprio LOG, em caso de erro, geram somente erros simples de PHP, que podem ser
 * tratados com die() simples para serem visualizados na tela.
 * A funcao mysql_real_escape_string() foi utilizada para evitar caracteres especiais no log (ex: aspas)
 */
function LOGETAPA($inidnfplote,$inetapa,$inerro='',$incoderro=0){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOGETAPA [".$inetapa."]";
	$_SESSION["steperro"]="logetapa";
	
	$sqllog = "insert into "._DBNFE.".nfplog (
				idnfplote				
				,etapa
				,erro
				,coderro) 
				values (
					".$inidnfplote."
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
function STATUSLOTE($inidnfplote, $instatus){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - STATUSLOTE: alterando status para [".$instatus."]";

	$_SESSION["steperro"]="statuslote";

	//verifica se o status esta devidamente preenchido
	if(!$instatus){
		echo("Erro ao alterar status do LOTE: \n- O status informado esta VAZIO.");
		return false;
	}

	//armazena o texto existente na variavel xml
	$xml = mysqli_real_escape_string($_SESSION["xml"]);
	
	//if(_NFSECHOLOGXML)echo "\n".date("H:i:s")." - STATUSLOTE: XML a ser gravado: [XML]";//$_SESSION["xml"]

	//armazena o texto existente na variavel xml
	$xmlret = mysqli_real_escape_string($_SESSION["xmlret"]);

	//armazena o numero do lote gerado no xml de retorno
	$nlote = $_SESSION["nlote"];

	$sqllote = "update "._DBNFE.".nfplote set recibo = '".$nlote."',status = '".$instatus."', xml = '".$xml."', xmlret = '".$xmlret."' where idnfplote = ".$inidnfplote;

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
 * hermesp151211 Gera o cNf o idNFE e o cDV para nota fiscal de PRODUTO
 */
function LOTE(){
	 
	//$sql="update "._DBAPP.".nf set envionfe='PROCESSANDO' where idnf = ".$_SESSION["idnotafiscal"];
    //$retx = d::b()->query($sql);

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE: gerando novo lote";

	$_SESSION["steperro"]="lote";	
	/*
	 * insere novo lote
	 */
	$sqllote = "insert into "._DBNFE.".nfplote (idnf,status) values (".$_SESSION["idnotafiscal"].",'CRIADO')";
	$retlote = d::b()->query($sqllote);

	if(!$retlote){
		//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
		echo("Erro 2 ao gerar LOTE: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqllote);		
		return false;
	}else{
		//captura id (autonumber) do lote gerado
		$insidnfplote = mysqli_insert_id(d::b());

		//verifica se o id do lote foi devidamente recuperado pela funcao do MYSQL
		if(empty($insidnfplote) or ($insidnfplote == 0)){
			echo("Erro 3 ao gerar LOTE: \n- Id do LOTE nao foi recuperado apos insercao.");
			return false;
		}else{  
			/*
			 * hermesp 15112011
			 * gerar o cNF o ID da nota fiscal e consequentemente o cDV
			 */
			$sqllnf = "select idnfe from nf where idnf = ".$_SESSION["idnotafiscal"];
			$resnf = d::b()->query($sqllnf) or die("Erro pesquisando cNF na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
			$rnf = mysqli_fetch_assoc($resnf);
			
			if(empty($rnf["idnfe"])){
				//GERA O NNFE
				geranumeronfe();
			
				if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE:gerando novo cNF";					
				//gera um numero randomico para a cNF e gerar o ID da NF
				$sql = "SELECT FLOOR(10000000 + (RAND() * 89999999)) as cnf;";					
				$res = d::b()->query($sql);					
				if(!$res){
					d::b()->query("UNLOCK TABLES;");
					echo "1-Falha ao gerar cnf randomico: " . mysqli_error(d::b()) . "<p>SQL: $sql";
					die();
				}					
				$row = mysqli_fetch_array($res);
			
				### Caso nao retorne nenhuma linha ou retorn valor vazio
				if(empty($row["cnf"])){
					if(!$resexercicio){
						d::b()->query("UNLOCK TABLES;");
						echo "2-Falha ao gerar cnf randomico : " . mysqli_error(d::b()) . "<p>SQL: $sql";
						die();
					}
				}
			
				$_SESSION["cnf"] = $row["cnf"];
			
				$sqlnf = "update nf set cnf = '".$_SESSION["cnf"]."' where idnf = ".$_SESSION["idnotafiscal"];
				d::b()->query($sqlnf) or die("Erro atribuindo cnf:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));
				
				//concat(e.cuf,SUBSTRING(replace(n.dtemissao,'-',''),3,4),e.cnpj,55,'001',n.controle,n.cnf) as preid
				$sqlpi = "select * from vwnfid where idnf =".$_SESSION["idnotafiscal"];
				$respi = d::b()->query($sqlpi) or die("Erro pesquisar pr� ID NF:\nSQL:".$sqlpi."\nErro:".mysqli_error(d::b()));
				$rnpi = mysqli_fetch_assoc($respi);
				if(empty($rnpi["preid"])){
						d::b()->query("UNLOCK TABLES;");
						echo " 2-Falha ao  pesquisar pr� ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlpi";
						die();							
				}else{
					$sqlid = "select geraidnf(".$rnpi["preid"].",".$_SESSION["idnotafiscal"].") as idnfe";
					$resid = d::b()->query($sqlid) or die("Erro pesquisar pr� ID NF:\nSQL:".$sqlid."\nErro:".mysqli_error(d::b()));
					$rnid = mysqli_fetch_assoc($resid);
					if(empty($rnid["idnfe"])){
							d::b()->query("UNLOCK TABLES;");
							echo "2-Falha ao  gerar ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlid";
							die();
					}else{
						$_SESSION["arrnfsidlote"]["infNFe"]= "Id=\"".$rnid["idnfe"]."\" versao=\"3.10\"";								
					}							
				}					
			}else{
				if(_NFSECHOLOG)echo "\n".date("H:i:s")." - gerachavenfe:capturando numero de cNF existente[".$rnf["idnfe"]."]";
				$_SESSION["arrnfsidlote"]["infNFe"]= "Id=\"".$rnf["idnfe"]."\" versao=\"3.10\"";									
			}			
			//Armazena o id do lote em SESSION
			$_SESSION["idnfplote"] = $insidnfplote;
			LOGETAPA($insidnfplote,'LOTE','SUCESSO');
			if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOTE: lote gerado: [".$insidnfplote."]";
			return true;
		}
	}	
}

/*
 * maf010410: esta funcao consulta os dados das notas fiscais no banco de origem 
 * e abre arrays contendo as informacoes padronizadas com a tabela nfpxmltree
 */
function CONSULTAORI(){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - CONSULTAORI: consultando dados do sistema de origem";
	$_SESSION["steperro"]="consultaori";
	
	//Inicia a Etapa
	STATUSLOTE($_SESSION["idnotafiscal"],"PROCESSANDO");

	//recupera a session com o id da RPS
	$idnf = $_SESSION["idnotafiscal"];
		
		/*
		 * hermesp151211
		 * Buscar dados para o xml da nota fisca de produtos
		 */
		$sqldados = "SELECT *
		FROM "._DBAPP.".vwnfdados
		where idnf=".$idnf;
		$resdados = d::b()->query($sqldados);
	
		if(!$resdados){
			$msgerr = "Erro ao consultar dados da NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqldados;
			STATUSLOTE($_SESSION["idnfplote"],"ERRO");
			LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI",$msgerr);
			echo($sqlerr);
			return false;
		}else{
			$dados = mysqli_fetch_assoc($resdados);
			$_SESSION["vwnfdados"]= $dados;			
		}
			
		//dados do destinatario
		$sqldest = "SELECT *
		FROM "._DBAPP.".vwnfdestinatario
		where idnf=".$idnf;
		$resdest = d::b()->query($sqldest);
						
		if(!$resdest){
			$msgerr = "Erro ao consultar dados do destinatario: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqldest;
			STATUSLOTE($_SESSION["idnfplote"],"ERRO");
			LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI",$msgerr);
			echo($sqlerr);
			return false;
		}else{
			$dest = mysqli_fetch_assoc($resdest);
			$_SESSION["vwnfdestinatario"]= $dest;			
		}
		
		//Transportadora
		$sqlt = "SELECT *
		FROM "._DBAPP.".vwnftransportadora
		where idnf=".$idnf;
		$rest = d::b()->query($sqlt);
	 
		if(!$rest){
			$msgerr = "Erro ao consultar dados da tranportadora NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlt;
			STATUSLOTE($_SESSION["idnfplote"],"ERRO");
			LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI",$msgerr);
			echo($sqlerr);
			return false;
		}else{
			$dadost = mysqli_fetch_assoc($rest);
			$_SESSION["vwnftransportadora"]= $dadost;			
		}
		
	LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI","SUCESSO");

	return true;
}
/*
 * maf010410: funcao responsavel por GERAR o texto do XML que ira ser enviado
 */
function GERACAO(){
	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - GERACAO: gerando xml";
	$_SESSION["steperro"]="geracao";
	
	//recupera a session com o id da NF
	$idnf = $_SESSION["idnotafiscal"];
	/*
	 * inicia funcao recursiva de montagem
	 */
	geraxml($idnf,$_SESSION["strprocesso"]);
	//para caso de XML hardcode.... 3>
	/*
	$_SESSION["xml"]="<NFe xmlns=\"http://www.portalfiscal.inf.br/nfe\">
	<infNFe Id=\"NFe31130723259427000104550010000007531455096206\" versao=\"2.00\">
		<ide>
			<cUF>31</cUF>
			<cNF>45509620</cNF>
			<natOp>DEVOLU��O MERC. USO OU CONSUMO OP. SUJ. REG. ST</natOp>
			<indPag>1</indPag>
			<mod>55</mod>
			<serie>1</serie>
			<nNF>753</nNF>
			<dEmi>2013-07-17</dEmi>
			<tpNF>1</tpNF>
			<cMunFG>3170206</cMunFG>
			<tpImp>1</tpImp>
			<tpEmis>1</tpEmis>
			<cDV>6</cDV>
			<tpAmb>1</tpAmb>
			<finNFe>1</finNFe>
			<procEmi>3</procEmi>
			<verProc>2.2.5</verProc>
		</ide>
		<emit>
			<CNPJ>23259427000104</CNPJ>
			<xNome>LAUDO LABORAT�RIO AV�COLA UBERL�NDIA LTDA</xNome>
			<xFant>LAUDO LABOTAT�RIO</xFant>
			<enderEmit>
				<xLgr>RODOVIA BR 365, KM 615</xLgr>
				<nro>S/N</nro>
				<xBairro>ALVORADA</xBairro>
				<cMun>3170206</cMun>
				<xMun>Uberlandia</xMun>
				<UF>MG</UF>
				<CEP>38407180</CEP>
				<cPais>1058</cPais>
				<xPais>BRASIL</xPais>
				<fone>3432225700</fone>
			</enderEmit>
			<IE>7023871770001</IE>
			<CRT>3</CRT>
		</emit>
		<dest>
			<CNPJ>66294976000122</CNPJ>
			<xNome>JD COM DE DERIVADOS DE BORRACHA LTDA</xNome>
			<enderDest>
				<xLgr>AV. RAULINO COTTA PACHECO</xLgr>
				<nro>652</nro>
				<xCpl></xCpl>
				<xBairro>VL OSVALDO</xBairro>
				<cMun>3170206</cMun>
				<xMun>UBERLANDIA</xMun>
				<UF>MG</UF>
				<CEP>38400370</CEP>
				<cPais>1058</cPais>
				<xPais>BRASIL</xPais>
				<fone>3432332122</fone>
			</enderDest>
			<IE>7027699780095</IE>
			<email>casaborracha@casaborracha.com.br</email>
		</dest>
		<det nItem=\"1\">
		<prod>
			<cProd>30018</cProd>
			<cEAN></cEAN>
			<xProd>ESP M FIXO 1\" NPT X 1/2\"</xProd>
			<NCM>73071920</NCM>
			<CFOP>5413</CFOP>
			<uCom>PC</uCom>
			<qCom>4.00</qCom>
			<vUnCom>25.2000</vUnCom>
			<vProd>100.80</vProd>
			<cEANTrib></cEANTrib>
			<uTrib>PC</uTrib>
			<qTrib>4.00</qTrib>
			<vUnTrib>25.2000</vUnTrib>
			<vFrete></vFrete>
			<vDesc>0.67</vDesc>
			<indTot>1</indTot>
			<xPed>000000</xPed>
			<nItemPed>00000</nItemPed>
		</prod>
		<imposto>
		<ICMS>
		<ICMS60>
			<orig>0</orig>
			<CST>60</CST>
		</ICMS60>
		</ICMS>
		<IPI>
			<clEnq>0</clEnq>
			<cEnq>999</cEnq>
		<IPINT>
			<CST>51</CST>
		</IPINT>
		</IPI>
		<PIS>
		<PISAliq>
			<CST>01</CST>
			<vBC>100.13</vBC>
			<pPIS>0.65</pPIS>
			<vPIS>0.65</vPIS>
		</PISAliq>
		</PIS>
		<COFINS>
		<COFINSAliq>
			<CST>01</CST>
			<vBC>100.13</vBC>
			<pCOFINS>3.00</pCOFINS>
			<vCOFINS>3.00</vCOFINS>
		</COFINSAliq>
		</COFINS>
		</imposto>
		</det>
		<total>
			<ICMSTot>
				<vBC>0.00</vBC>
				<vICMS>0.00</vICMS>
				<vBCST>0.00</vBCST>
				<vST>0.00</vST>
				<vProd>100.80</vProd>
				<vFrete>0.00</vFrete>
				<vSeg>0.00</vSeg>
				<vDesc>0.67</vDesc>
				<vII>0.00</vII>
				<vIPI>0.00</vIPI>
				<vPIS>0.65</vPIS>
				<vCOFINS>3.00</vCOFINS>
				<vOutro>0.00</vOutro>
				<vNF>100.13</vNF>
			</ICMSTot>
		</total>
		<transp>
			<modFrete>9</modFrete>
		</transp>
		<infAdic>
			<infCpl>Nota fiscal de devolu��o PARCIAL referente a NFe 105.383, s�rie 1, emitida em 12/07/2013 - item c�d. prod. 30018.</infCpl>
		</infAdic>
	</infNFe>
</NFe>";
*/

/* */
	
	//grava na tabela nf o xml que foi enviado por ultimo
	$sql="update "._DBAPP.".nf set xml='".$_SESSION["xml"]."' where idnf = ".$idnf;
	$retx = d::b()->query($sql);
	if(!$retx){
		//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
		echo("Erro ao gravar xml na NF : \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sql);
		$msgerr="Erro ao gravar xml na NF : \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sql;
		LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
		STATUSLOTE($_SESSION["idnfplote"],"ERRO");
		return false;
	}else{
		//esvazia variveis com texto de xml
		//unset($_SESSION["xml"]);
		//unset($_SESSION["xmlret"]);
		//die($_SESSION["xml"]);// quando for gerar o XML manualmente
		LOGETAPA($_SESSION["idnfplote"],"GERACAO","SUCESSO");
		return true;
	} 
	//return true;
}
/*
 * maf050410: Responsavel por ler o arquivo original do SERASA 
 * e extrair as chaves publicas e privadas de encriptacao
 */
function retcert(){
	
	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - retcert: gerandocertificado";

	$_SESSION["steperro"]="retcert";

	//if(empty($_SESSION["certprivkey"]) or empty($_SESSION["certpublkey"])){
		
		$_SESSION["certprivkey"]="";
		$_SESSION["certpublkey"]="";
		
		$arqpfx = "../certs/laudo17082017.p12";
		//$passpfx = "37380755";	//	senha
		$passpfx = "010787";	//	senha
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
	
	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - assinaxml: assinando xml";
	$_SESSION["steperro"]="assinaxml";

	retcert();
	
	$dom = new  DOMDocument('1.0', 'utf-8');	
	$dom->formatOutput = false;
	
	/*
	 * Mostra erro na estrutura do XML
	 */
	if ($dom->loadXML($sXML) === false){
		echo "\nassinaXML: Falha na geracao das tags XML\n";
		
		ini_set("display_errors","1");
		error_reporting(E_WARNING);
		
		$dom->loadXML($sXML);
		
		die;
	}

	//echo $dom->saveXML(); 

	$root = $dom->documentElement;
	$node = $dom->getElementsByTagName($tagID)->item(0);
	
	$Id = trim($node->getAttribute("Id"));
		
	$idnome = preg_replace('/[^0-9]/', '', $Id);

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

	//$noxml = $dom.getElementsByTagName("xml");

	//$dom.parentNode.removeChild($noxml); 
	//grava na string o objeto DOM

	return $dom-> saveXML();
}
/*
 * maf01010: funcao responsavel por ASSINAR o xml gerado
 */
function ASSINATURA(){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ASSINATURA: gerando assinatura";
	$_SESSION["steperro"]="assinatura";	
	
	//Inicia a Etapa	
	STATUSLOTE($_SESSION["idnfplote"],"ASSINATURA");
	LOGETAPA($_SESSION["idnfplote"],"ASSINATURA");
	//echo($_SESSION["xml"]);
	//retirar todos os espa�o do xml antes de assinar
	$order = array("\r\n", "\n", "\r", "\t");
    $replace = '';
    $_SESSION["xml"] = str_replace($order, $replace, $_SESSION["xml"]);
    //Estas tag n�o s�o permitidas vazias por isso foram retiradas antes da assinatura do XML
   // $_SESSION["xml"] = str_replace(<vFrete>0.00</vFrete>','', $_SESSION["xml"]); 
    $_SESSION["xml"] = str_replace('<vFrete></vFrete>','', $_SESSION["xml"]);   
    $_SESSION["xml"] = str_replace('<xCpl></xCpl>','', $_SESSION["xml"]); 
    $_SESSION["xml"] = str_replace('<IE></IE>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<vDesc></vDesc>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<nVol></nVol>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<pesoL></pesoL>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<pesoB></pesoB>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<qVol></qVol>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<esp></esp>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<marca></marca>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<vol></vol>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<refNFe></refNFe>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<NFref></NFref>','', $_SESSION["xml"]);
   
    
    //$_SESSION["xml"] = str_replace('<vDesc>0.00</vDesc>','', $_SESSION["xml"]);    
    /*
	 * inicia funcao recursiva de montagem
	 */
	$xmtlutf = utf8_encode($_SESSION["xml"]);
	//echo($_SESSION["xml"]);
	// die();	
	//Assinar o XML
	$_SESSION["xml"] = assinaXML($xmtlutf,$_SESSION["tagassinatura"]);	
	/*Retirar o <?xml version="1.0" adicionado na assinatura pois o metodo de envio da toolsNfephp adiciona ao encapsular no soap 
	 * REtirei para n�o ficar duplicado hermesp
	 */	
	$_SESSION["xml"] = str_replace('<?xml version="1.0"?>','', $_SESSION["xml"]);	

	$idnf = $_SESSION["idnotafiscal"];
	$sql="update "._DBAPP.".nf set xml='".$_SESSION["xml"]."' where idnf = ".$idnf;
	$retx = d::b()->query($sql);
	
	//echo($_SESSION["xml"]);
	//die();	
	return true; 
}
/*
 * fun��o nfephp que valida os campos do xml para possiveis erros de extrutura hermesp
 */
//Validar o XMl para busca de possiveis erros de estrutura
function VALIDA(){
//$arq = './35101158716523000119550010000000011003000000-nfe.xml';
$_SESSION["steperro"]="validacao";
$nfe = new ToolsNFePHP;
//$docxml = file_get_contents($arq);
$docxml=$_SESSION["xml"];
$xsdFile = '../schemes/PL_008h2/nfe_v3.10.xsd';
$aErro = '';
$c = $nfe->validXML($docxml,$xsdFile,$aErro);
if (!$c){
    echo 'Houve erro --- <br>';
    foreach ($aErro as $er){
        echo $er .'<br>';
        $errov.="\n".$er;
    }
   
	STATUSLOTE($_SESSION["idnfplote"],"ERRO");
	LOGETAPA($_SESSION["idnfplote"],"VALIDACAO",$errov);
	echo "XML INVALIDO ".$errov;
    return false;
} else {
    echo 'VALIDADA!';   
	LOGETAPA($_SESSION["idnfplote"],"VALIDACAO","SUCESSO");
    return true;
}
}
/*
 * Fun��o do nfephp que envia o xml para o webservice hermesp
 */
//Enviar o XML para o Webservice do SEFAZ com o metodo importado do grupo nfePHP
function ENVIO(){
/*
 * de envio de Nfe 
ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set('soap.wsdl_cache_enabled',0);
ini_set('soap.wsdl_cache_ttl',0); 
*/
$nfe = new ToolsNFePHP;
$modSOAP = '2'; //usando cURL
//echo($_SESSION["xml"]);
/*
 * Lendo de arquivo - como vem do NFephp
 * $filename = './31130123259427000104550010000005231000637010-nfe.xml';
 * $aNFe = array(0=>file_get_contents($filename)); 
 */
//obter um numero de lote
$lote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
// montar o array com a NFe
$sNFe =$_SESSION["xml"];
//enviar o lote
//if ($aResp = $nfe->sendLot($aNFe, $lote, $modSOAP)){ antigo versao 2.0
/*
if ($aResp = $nfe->autoriza($sNFe, $lote)) {
	print_r($aResp);
	if ($aResp['bStat']) {
		echo "Numero do Recibo : ".$aResp['nRec'].", use este numero para obter o protocolo ou informa��es de erro no xml com testaRecibo.php.";
	} else {
		echo "Houve erro !! $nfe->errMsg";
	}
	} else {
	echo "houve erro !!  $nfe->errMsg";
	}
	echo '<BR><BR><h1>DEBUG DA COMUNICA��O SOAP</h1><BR><BR>';
echo '<PRE>';
echo htmlspecialchars($nfe->soapDebug);
echo '</PRE><BR>';
*/
if ($aResp = $nfe->autoriza($sNFe, $lote)) {
	$_SESSION["xmlret"] = htmlspecialchars($nfe->soapDebug);
	//print_r($aResp);
    if ($aResp['bStat']){
    	$doc = DOMDocument::loadXML($aResp);
    	$cab = $doc->getElementsByTagName("nfeAutorizacaoLoteResult")->item(0);
    	$cab2 = $cab->getElementsByTagName("retEnviNFe")->item(0);
    	$cab3 = $cab2->getElementsByTagName("infRec")->item(0);
    	$nRec = $cab3->getElementsByTagName("nRec")->item(0);
    	$recibo =($nRec->textContent); //->length;
    	
    	
        //echo "Numero do Recibo : " . $aResp['nRec'] .", use este numero para obter o protocolo ou informa��es de erro no xml com Recibo.php.";  
        $_SESSION["nlote"]=$recibo;

        $sql="update "._DBAPP.".nf set recibo='".$recibo."',envionfe='ENVIADO' where idnf = ".$_SESSION["idnotafiscal"];
		$retx = d::b()->query($sql);
		
		STATUSLOTE($_SESSION["idnfplote"],"SUCESSO");
		LOGETAPA($_SESSION["idnfplote"],"ENVIO","SUCESSO");
		return true;
    } else {
        echo "Houve erro !! $nfe->errMsg";
        $msgerr = "Houve erro !! $nfe->errMsg";
        $sql="update "._DBAPP.".nf set envionfe='ERRO' where idnf = ".$_SESSION["idnotafiscal"];
        $retx = d::b()->query($sql);
		STATUSLOTE($_SESSION["idnfplote"],"ERRO");
		LOGETAPA($_SESSION["idnfplote"],"ENVIO",$msgerr);
		//echo '<BR><BR><h1>DEBUG DA COMUNICA��O SOAP</h1><BR><BR>';
		echo '<PRE>';
		echo htmlspecialchars($nfe->soapDebug);
		echo '</PRE><BR>';
		return false;
    }
} else {
   		echo "Houve erro !! $nfe->errMsg";
        $msgerr = "Houve erro !! $nfe->errMsg";
        $sql="update "._DBAPP.".nf set envionfe='ERRO' where idnf = ".$_SESSION["idnotafiscal"];
        $retx = d::b()->query($sql);
		STATUSLOTE($_SESSION["idnfplote"],"ERRO");
		LOGETAPA($_SESSION["idnfplote"],"ENVIO",$msgerr);
		//echo '<BR><BR><h1>DEBUG DA COMUNICA��O SOAP</h1><BR><BR>';
		echo '<PRE>';
		echo htmlspecialchars($nfe->soapDebug);
		echo '</PRE><BR>';
		return false;
}

//ou isso 
//este modo interno e vai enviar todoas as nf que estiverem na pasta validadas
/*
if ($recibo  = $nfe->autoEnvNFe()){
    echo "Numero do Recibo : " . $recibo .", use este numero para obter o protocolo ou informa��es de erro no xml.";  
} else {
    echo "Houve erro !! $nfe->errMsg";
}
*/	
}


//geranumeronfe();

if(LOTE($idnf) 	== true
	and CONSULTAORI() 	== true
	and GERACAO() 		== true	
	and ASSINATURA() 	== true
	and VALIDA()        == true
	and ENVIO()         == true
	){
	die("\n- idnf [".$_SESSION["idnotafiscal"]."] enviada com sucesso! Recibo para consulta: [". $_SESSION["nlote"]."] ");
}else{
	die("\n".date("H:i:s")." - Erro Fatal:".$_SESSION["steperro"]);
}
?>
