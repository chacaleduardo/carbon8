<?
/*#### HERMES PEDRO BORGES - 07/03/2013 ######
 * gerar xml e enviar o mesmo para o sefaz via webservice com metodo do nfephp
 * 
 */

//include_once("../validaacesso.php");
include_once("../../../php/functions.php");
//require_once('../libs/NFe/ToolsNFePHP.class.php');
//ini_set("display_errors","1");
//error_reporting(E_ALL);
//conectabanco();

//BIBLIOTECA PARA NFS4.0
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
require_once "../vendor/autoload.php";
session_start();

$qr = "SELECT 
			case n.idempresa 
				when 8 then 1
				else n.idempresa
			end as idempresa,n.idempresafat,o.natoptipo
	from nf n 
	join natop o on(o.idnatop=n.idnatop)
	where n.idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$rs = mysqli_fetch_assoc($rs);

$natoptipo = $rs["natoptipo"];

// _idempresa = isset($rs["idempresa"])?$rs["idempresa"]:$_GET["_idempresa"];
if(!empty($rs["idempresafat"]) and $rs["natoptipo"]!='transferencia'){
	$_idempresa=$rs["idempresafat"];
}else{	
	$_idempresa=$rs["idempresa"];
}


$_SESSION["idnotafiscal"] = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar
$_SESSION["idnfplote"] = 0;
$_SESSION["xml"] = "";
$_SESSION["xmlret"] = "";
$_SESSION["steperro"]="";
$_SESSION["strprocesso"]="procNfe";
$_SESSION["tagassinatura"]="infNFe";
$_SESSION["strprocesso"]="procNfe";

$sqlco="select * from empresa where idempresa=".$_idempresa;
$resco = d::b()->query($sqlco) or die("Erro ao buscar informações da empresa:\nSQL:".$sqlco."\nErro:".mysqli_error(d::b()));
$rowco=mysqli_fetch_assoc($resco);


$_SESSION["contigencia"]=$rowco['contingencia'];// ativar e desativar a contigência - deve se abaixo colocar a data de entrada no timestamp
$_SESSION["datacontingencia"]=$rowco['datacontingencia'];
$_SESSION["crt"]=$rowco['crt'];

function geranumeronfe(){
	global $_idempresa,$natoptipo;

	if($natoptipo=='estorno'){
		$nnfe = 'nnfeserie2';
	}else{
		$nnfe = 'nnfe';
	}

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronfe";

	$sqllnf = "select nnfe from nf where idnf = ".$_SESSION["idnotafiscal"];
	$resnf = d::b()->query($sqllnf) or die("Erro pesquisando nnfe na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
	$rnf = mysqli_fetch_assoc($resnf);

	if(empty($rnf["nnfe"])){

		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:gerando nova nfe";

		### Tenta incrementar e recuperar o numerorps
		d::b()->query("LOCK TABLES sequence WRITE;");
		d::b()->query("update sequence set chave1 = (chave1 + 1) where idempresa=".$_idempresa." and sequence = '".$nnfe."'");

		$sql = "SELECT convert( lpad(chave1, '9', '0') using latin1) as chave1 FROM sequence where idempresa=".$_idempresa." and sequence = '".$nnfe."';";

		$res = d::b()->query($sql);

		if(!$res){
			d::b()->query("UNLOCK TABLES;");
			echo "1-Falha Pesquisando Sequence [".$nnfe."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	
		$row = mysqli_fetch_array($res);
	
		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["chave1"])){
			if(!$resexercicio){
				d::b()->query("UNLOCK TABLES;");
				echo "2-Falha Pesquisando Sequence [".$nnfe."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
				die();
			}
		}
	
		d::b()->query("UNLOCK TABLES;");
	
		$_SESSION["nnfe"] = $row["chave1"];

		$sqlnf = "update nf set nnfe = '".$row["chave1"]."' where idnf = ".$_SESSION["idnotafiscal"];
		d::b()->query($sqlnf) or die("Erro atribuindo nnfe:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

	}else{
		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - geranumeronnfe:capturando numero da nfe existente[".$rnf["nnfe"]."]";
		$_SESSION["nnfe"] = $rnf["nnfe"];
	}


}

/*
 * maf010410: funcao criada para efetuar a montagem das tags XML
 * As condicoes para montagem estao na tabela nfpxmltreenova
 * Esta funcao eh chamada pela etapa de geracao do XML
 * par $r: Contém informacoes de cada linha da nfpxmltreenova para cada tag
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
	 * 	2 - Verifica se o array é multidimensional [arraymultidim] . 
	 * 		Caso positivo, lê os valores e gera os xml de acordo com a views
	 */
	if(isset($r["vlrfixo"])){//possui valor fixo
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
	    $sqldi="select * from vwnfdadositem where idnf=".$inidnf." ORDER BY xprod";
	    $resdi =d::b()->query($sqldi);
	    while($rowv=mysqli_fetch_assoc($resdi)){
                //importacao alterado por hermesp 16-05-2019
                $sqlimp="select * from  nfitemimport where status='ATIVO' and idnfitem =".$rowv["idnfitem"];
                $resimp =d::b()->query($sqlimp);
                $qtdimp= mysqli_num_rows($resimp);
                
	    	foreach ($data[$rowv["idnfitem"]]as $k => $v) {
		    if($k=="idnf"){
			    $det=$det+1;
			    $strret.="\n".$t."<det nItem=\"".$det."\">";
			    $strret.="\n".$t."<prod>";
		    }elseif($k!="idnfitem" and $k!="indiedest"  and $k!="pDevol" and $k!="vIPIDevol" and $k!="obs" and $k!="uf" ){
                        
                        if( ($k=="xPed" or $k=="nItemPed") and $qtdimp<1){
                            $strret.=("\n".$t.chr(9)."<".$k.">".$v."</".$k.">");
                        }elseif($k!="xPed" and $k!="nItemPed"){
			    //item não pode ter frete com valor 0 se for zero a tag ira vazia para ser retirada
			    if($k=='vFrete' and $v<1){
				    $strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
			    }elseif($k=='vDesc' and $rowv[$k]<1){
				    $strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
			    }elseif($k=='vOutro' and $rowv[$k]<1){
				   // $strret.=("\n".$t.chr(9)."<".$k.">0.00</".$k.">");
				   echo('');
			    }elseif($k=='vSeg' and $rowv[$k]<1){
					//$strret.=("\n".$t.chr(9)."<".$k.">0.00</".$k.">");
					echo('');
			    }elseif($k=='CEST' and $rowv[$k]<1){
				    $strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
                }elseif($k=='cBenef' and empty($rowv[$k])){
				    $strret.=("\n".$t.chr(9)."<".$k."></".$k.">");
                }else{
				    $strret.=("\n".$t.chr(9)."<".$k.">".$v."</".$k.">");
			    }
                        } 
		    }			   
		}
		$varpncm=substr($data[$rowv["idnfitem"]]['NCM'], 0, 4);  
		if($varpncm=='3001' or  $varpncm=='3002' or $varpncm=='3004'  or $varpncm=='3005' or $varpncm=='3006' ){//NCM de medicamento

			$sqllt="SELECT 
				l.partida, l.fabricacao, l.vencimento,round(c.qtdd,3) as qtdd
			FROM
				lotecons c
					JOIN
				lote l ON (l.idlote = c.idlote)
			WHERE
				c.idobjeto = ".$rowv["idnfitem"]."
					AND c.tipoobjeto = 'nfitem'
					AND c.qtdd > 0 ";

			$reslt =d::b()->query($sqllt);
			$qtdreslt=mysqli_num_rows($reslt);
			// @545777 - ERRO AO EMITIR NOTA FISCAL - OPERAÇÃO TRIANGULAR
			if($qtdreslt<1){

				$sqllt="SELECT 
							l.partida,
							l.fabricacao,
							l.vencimento,
							ROUND(i.qtd, 3) AS qtdd
						FROM
							nfitem i
								JOIN
							nf n ON (i.idnf = n.idnf
								AND n.tipoobjetosolipor = 'nf')
								JOIN
							nfitem e ON (e.idnf = n.idobjetosolipor
								AND i.idprodserv = e.idprodserv
								AND e.idprodservformula = i.idprodservformula)
								JOIN
							lotecons c ON (c.idobjeto = e.idnfitem
								AND c.tipoobjeto = 'nfitem'
								AND c.qtdd > 0)
								JOIN
							lote l ON (l.idlote = c.idlote)
						WHERE
							i.idnfitem =".$rowv["idnfitem"];
				$reslt =d::b()->query($sqllt);
			}
			while($rwlt=mysqli_fetch_assoc($reslt)){

				$strret.="\n".$t.chr(9)."<rastro>";
				$strret.=("\n".$t.chr(9).chr(9)."<nLote>".$rwlt["partida"]."</nLote>");
				$strret.=("\n".$t.chr(9).chr(9)."<qLote>".$rwlt["qtdd"]."</qLote>");
				$strret.=("\n".$t.chr(9).chr(9)."<dFab>".$rwlt["fabricacao"]."</dFab>");
				$strret.=("\n".$t.chr(9).chr(9)."<dVal>".$rwlt["vencimento"]."</dVal>");
				$strret.="\n".$t.chr(9)."</rastro>";
			}

			$strret.="\n".$t.chr(9)."<med>";
			$strret.=("\n".$t.chr(9).chr(9)."<cProdANVISA>ISENTO</cProdANVISA>");
			$strret.=("\n".$t.chr(9).chr(9)."<xMotivoIsencao>PRODUTO REGULAMENTADO PELO MAPA</xMotivoIsencao>");		
			$strret.=("\n".$t.chr(9).chr(9)."<vPMC>1000.00</vPMC>");
			$strret.="\n".$t.chr(9)."</med>";

		}// fim  //NCM de medicamento
                
                //importacao alterado por hermesp 16-05-2019
                if($qtdimp>0){
                    $rowimp=mysqli_fetch_assoc($resimp);
                    $strret.="\n".$t.chr(9)."<DI>";
                    $strret.=("\n".$t.chr(9).chr(9)."<nDI>".$rowimp["ndi"]."</nDI>");
                    $strret.=("\n".$t.chr(9).chr(9)."<dDI>".$rowimp["ddi"]."</dDI>");
                    $strret.=("\n".$t.chr(9).chr(9)."<xLocDesemb>".$rowimp["xlocdesemb"]."</xLocDesemb>");
                    $strret.=("\n".$t.chr(9).chr(9)."<UFDesemb>".$rowimp["ufdesemb"]."</UFDesemb>");
                    $strret.=("\n".$t.chr(9).chr(9)."<dDesemb>".$rowimp["ddesemb"]."</dDesemb>");
                    $strret.=("\n".$t.chr(9).chr(9)."<tpViaTransp>".$rowimp["tpviatransp"]."</tpViaTransp>");
					if(!empty($rowimp["vafrmm"])){
						$strret.=("\n".$t.chr(9).chr(9)."<vAFRMM>".$rowimp["vafrmm"]."</vAFRMM>");
					}					
                    $strret.=("\n".$t.chr(9).chr(9)."<tpIntermedio>".$rowimp["tpintermedio"]."</tpIntermedio>");
                    $strret.=("\n".$t.chr(9).chr(9)."<cExportador>".$rowimp["cexportador"]."</cExportador>");
		    $strret.="\n".$t.chr(9).chr(9)."<adi>";
		    $strret.=("\n".$t.chr(9).$t."<nAdicao>".$rowimp["nadicao"]."</nAdicao>");
		    $strret.=("\n".$t.chr(9).$t."<nSeqAdic>".$rowimp["nseqadic"]."</nSeqAdic>");
		    $strret.=("\n".$t.chr(9).$t."<cFabricante>".$rowimp["cfabricante"]."</cFabricante>");
        	    $strret.="\n".$t.chr(9).chr(9)."</adi>";
		    $strret.="\n".$t.chr(9)."</DI>"; 
                }//if($qtdimp>0){
		$strret.="\n".$t."</prod>";	
		//TAG IMPOSTO
		$strret.="\n".$t."<imposto>";

		//INICIO ICMS verifica qual o cst do item
		$sqlcst="select i.cst,i.piscst,i.confinscst from nfitem i where i.idnfitem=".$rowv["idnfitem"];
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

		//so os do if estão configurados abaixo (e necessario criar uma view para cada cst 
		if($cst!="00" and $cst!="20" and $cst!="40" and $cst!="50" and $cst!="51"  and $cst!="60" and $cst!="41" and $cst!="500" and $cst!="101" and $cst!="102" and  $cst!="900"){
			$msgerr = "Erro o CST ".$cst." ainda não foi desenvolvido favor entrar e contato com o ADM do sistema.";
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
		if($cst=="900"){
			$cst="SN900";
		}
				
			
		//gera o nome das tags com os campos da view
			
		$arrColunas = mysqli_fetch_fields($resicms);			
		$i=0;
		$dataicms=array();
		while($r = mysqli_fetch_assoc($resicms)){

			if($cst=="20" and (empty($r['vICMSDeson']) or $r['vICMSDeson']=='0.00')){
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					//$arrret[$i][$col->name]=$robj[$col->name];
					if($col->name != 'vICMSDeson' and $col->name != 'motDesICMS' ){
						$dataicms[$col->name]=$r[$col->name];
					}
					
				}

			}else{
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					//$arrret[$i][$col->name]=$robj[$col->name];
					$dataicms[$col->name]=$r[$col->name];
				}

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
		//$strret.=("\n".$t.chr(9)."<clEnq>".$rowipi["clEnq"]."</clEnq>");
		$strret.=("\n".$t.chr(9)."<cEnq>".$rowipi["cEnq"]."</cEnq>");
	if($rowipi['CST']!='50' and $rowipi['CST']!='99' and $rowipi['CST']!='49'){
		$strret.="\n".$t."<IPINT>";
		$strret.=("\n".$t.chr(9)."<CST>".$rowipi["CST"]."</CST>");
		$strret.="\n".$t."</IPINT>";
	}else{
		$strret.="\n".$t."<IPITrib>";
		$strret.=("\n".$t.chr(9)."<CST>".$rowipi["CST"]."</CST>");
		$strret.=("\n".$t.chr(9)."<vBC>".$rowipi["vBC"]."</vBC>");
		$strret.=("\n".$t.chr(9)."<pIPI>".$rowipi["pIPI"]."</pIPI>");
		$strret.=("\n".$t.chr(9)."<vIPI>".$rowipi["vIPI"]."</vIPI>");
		$strret.="\n".$t."</IPITrib>";
		
	}	
		
		$strret.="\n".$t."</IPI>";
                
                
                //importacao imposto alterado por hermesp 16-05-2019
                $sqlimp="select * from  nfitemimport where status='ATIVO' and idnfitem =".$rowv["idnfitem"];
                $resimp =d::b()->query($sqlimp);
                $qtdimp= mysqli_num_rows($resimp);
                if($qtdimp>0){
                    $rowimp=mysqli_fetch_assoc($resimp);
                    $strret.="\n".$t."<II>";
                    $strret.=("\n".$t.chr(9)."<vBC>".$rowimp["vbc"]."</vBC>");
                    $strret.=("\n".$t.chr(9)."<vDespAdu>".$rowimp["vdespadu"]."</vDespAdu>");
                    $strret.=("\n".$t.chr(9)."<vII>".$rowimp["vii"]."</vII>");
                    $strret.=("\n".$t.chr(9)."<vIOF>".$rowimp["viof"]."</vIOF>");                
		    $strret.="\n".$t."</II>";                                       
                }//if($qtdimp>0){
                
                $sqlpis="select * from vwnfpis where idnfitem=".$rowv["idnfitem"];
                $respis =d::b()->query($sqlpis);
                $rowpis=mysqli_fetch_assoc($respis);
		//INICIO PIS
		if($rowcst['piscst'] =='01' or $rowcst['piscst'] =='02'){
			
                    if(!$respis){
				$msgerr = "falha ao listar o PIS do iten \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlpis;
				STATUSLOTE($_SESSION["idnfplote"],"ERRO");
				LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
				echo($msgerr);
				return false;
                    }
		    
		    $strret.="\n".$t."<PIS>";
		    $strret.="\n".$t."<PISAliq>";
		    $strret.=("\n".$t.chr(9)."<CST>".$rowpis["CST"]."</CST>");
		    $strret.=("\n".$t.chr(9)."<vBC>".$rowpis["vBC"]."</vBC>");
		    $strret.=("\n".$t.chr(9)."<pPIS>".$rowpis["pPIS"]."</pPIS>");
		    $strret.=("\n".$t.chr(9)."<vPIS>".$rowpis["vPIS"]."</vPIS>");		   	
		    $strret.="\n".$t."</PISAliq>";
		    $strret.="\n".$t."</PIS>";
		}elseif($rowcst['piscst'] =='04' or $rowcst['piscst'] =='05' or $rowcst['piscst'] =='06' or $rowcst['piscst'] =='07' or $rowcst['piscst'] =='08' or $rowcst['piscst'] =='09'){

		    $strret.="\n".$t."<PIS>";
		    $strret.="\n".$t."<PISNT>";
		    $strret.=("\n".$t.chr(9)."<CST>".$rowcst['piscst']."</CST>");
		    $strret.="\n".$t."</PISNT>";
		    $strret.="\n".$t."</PIS>";

		}elseif($rowcst['piscst'] =='03' ){

		    $strret.="\n".$t."<PIS>";
		    $strret.="\n".$t."<PISQtde>";
		    $strret.=("\n".$t.chr(9)."<CST>".$rowpis["CST"]."</CST>");
		    $strret.=("\n".$t.chr(9)."<qBCProd>".$rowpis["vBC"]."</qBCProd>");
		    $strret.=("\n".$t.chr(9)."<vAliqProd>".$rowpis["pPIS"]."</vAliqProd>");
		    $strret.=("\n".$t.chr(9)."<vPIS>".$rowpis["vPIS"]."</vPIS>");
		    $strret.="\n".$t."</PISQtde>";
		    $strret.="\n".$t."</PIS>";

		}else{

		   $strret.="\n".$t."<PIS>";
		    $strret.="\n".$t."<PISOutr>";
		    $strret.=("\n".$t.chr(9)."<CST>".$rowpis["CST"]."</CST>");
		    $strret.=("\n".$t.chr(9)."<vBC>".$rowpis["vBC"]."</vBC>");
		    $strret.=("\n".$t.chr(9)."<pPIS>".$rowpis["pPIS"]."</pPIS>");
		    $strret.=("\n".$t.chr(9)."<vPIS>".$rowpis["vPIS"]."</vPIS>");		   	
		    $strret.="\n".$t."</PISOutr>";
		    $strret.="\n".$t."</PIS>";

		}
                
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
		//COFINS
		if($rowcst['confinscst'] =='01' or $rowcst['confinscst'] =='02'){

			$strret.="\n".$t."<COFINS>";
			$strret.="\n".$t."<COFINSAliq>";
			$strret.=("\n".$t.chr(9)."<CST>".$rowcof["CST"]."</CST>");
			$strret.=("\n".$t.chr(9)."<vBC>".$rowcof["vBC"]."</vBC>");
			$strret.=("\n".$t.chr(9)."<pCOFINS>".$rowcof["pCOFINS"]."</pCOFINS>");
			$strret.=("\n".$t.chr(9)."<vCOFINS>".$rowcof["vCOFINS"]."</vCOFINS>");		   	
			$strret.="\n".$t."</COFINSAliq>";
			$strret.="\n".$t."</COFINS>"; 			   	
		}elseif($rowcst['confinscst'] =='04' or $rowcst['confinscst'] =='05' or $rowcst['confinscst'] =='06' or $rowcst['confinscst'] =='07' or $rowcst['confinscst'] =='08' or $rowcst['confinscst'] =='09'){
			$strret.="\n".$t."<COFINS>";
			$strret.="\n".$t."<COFINSNT>";
			$strret.=("\n".$t.chr(9)."<CST>".$rowcst['confinscst']."</CST>");
			$strret.="\n".$t."</COFINSNT>";
			$strret.="\n".$t."</COFINS>";
                }elseif($rowcst['confinscst'] =='03'){
                        $strret.="\n".$t."<COFINS>";
			$strret.="\n".$t."<COFINSQtde>";
			$strret.=("\n".$t.chr(9)."<CST>".$rowcof["CST"]."</CST>");
			$strret.=("\n".$t.chr(9)."<qBCProd>".$rowcof["vBC"]."</qBCProd>");
			$strret.=("\n".$t.chr(9)."<vAliqProd>".$rowcof["pCOFINS"]."</vAliqProd>");
			$strret.=("\n".$t.chr(9)."<vCOFINS>".$rowcof["vCOFINS"]."</vCOFINS>");		   	
			$strret.="\n".$t."</COFINSQtde>";
			$strret.="\n".$t."</COFINS>"; 
                }else{
                        $strret.="\n".$t."<COFINS>";
			$strret.="\n".$t."<COFINSOutr>";
			$strret.=("\n".$t.chr(9)."<CST>".$rowcof["CST"]."</CST>");
			$strret.=("\n".$t.chr(9)."<vBC>".$rowcof["vBC"]."</vBC>");
			$strret.=("\n".$t.chr(9)."<pCOFINS>".$rowcof["pCOFINS"]."</pCOFINS>");
			$strret.=("\n".$t.chr(9)."<vCOFINS>".$rowcof["vCOFINS"]."</vCOFINS>");		   	
			$strret.="\n".$t."</COFINSOutr>";
			$strret.="\n".$t."</COFINS>"; 
                }
		   	
		   	
		if($rowv['indiedest']==9 and $rowv['uf']!='EX'){
		   		
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
                if(!empty($rowv['pDevol']) and !empty($rowv['vIPIDevol'])){
                    $strret.="\n".$t."<impostoDevol>";
                    $strret.="\n".$t.chr(9)."<pDevol>".$rowv['pDevol']."</pDevol>";
                    $strret.="\n".$t.chr(9)."<IPI>";
                    $strret.=("\n".$t.chr(9).chr(9)."<vIPIDevol>".$rowv['vIPIDevol']."</vIPIDevol>");
                    $strret.="\n".$t.chr(9)."</IPI>";                  
                    $strret.="\n".$t."</impostoDevol>";
                     /*
                <impostoDevol>
                    <pDevol>100.00</pDevol>
                    <IPI>
                        <vIPIDevol>0.00</vIPIDevol>
                    </IPI>
                </impostoDevol>
                            
                 */   
                    $strret.="\n".$t."<infAdProd>".$rowv['obs']."</infAdProd>";
                }else{

                    //INFORMAÇÕES ADICIONAIS DO ITEM
                    $sqladic="select i.qtdd,convert(lpad(replace(l.partida,l.spartida,''),'3', '0')using latin1) as partida
									,right(l.exercicio,2) as exercicio 
                                    ,dma(vencimento) as vencimento,retiraAcentos(Concat(f.rotulo,'(',f.volumeformula,' ',f.un,')')) as rotulo
                                from lotecons i,lote l left join prodservformula f on(l.idprodservformula = f.idprodservformula)
                                where l.idlote = i.idlote
                                and i.tipoobjeto='nfitem'
                                and (i.qtdd > 0 or i.qtdc > 0)
                                and i.idobjeto =  ".$rowv['idnfitem']."";
                    $resadic=d::b()->query($sqladic);
					$strlote='';
					$virg="";
                    while($rowadic=mysqli_fetch_assoc($resadic)){
						if(!empty($rowadic['partida']) and !empty($rowadic['vencimento'])){
							/*$partidaext = str_replace("/", " ", $rowadic['partidaext']);
							$partidaext = str_replace("-", " ", $partidaext);
							$vencimento = str_replace("/", ".", $rowadic['vencimento']);*/
							$vencimento = substr($rowadic['vencimento'], 3,2).'/'.substr($rowadic['vencimento'], 8,2);
							$strlote.=$virg."Qt:".$rowadic['qtdd']." ".$rowadic['rotulo']." P:".$rowadic['partida']."/".$rowadic['exercicio']." V:".$vencimento;
							//$strlote.=$virg."L:".$rowadic['partida']."/21";
							$virg=', ';
						}//FIM INFORMAÇÕES ADICIONAIS DO ITEM
					}	
					if (strlen($strlote) > 500) {
						echo("A quantidade de partidas consumidas no item '".$rowv['xProd']."', excede o total permitido. Sugerimos inserir o mesmo item no pedido mais vezes e dividir os consumos. Geralmente são permitidas 7 partidas distintas em cada item do pedido.");
						die();
					}
					 //adiciona a linha com as informações adicionais
					 $strret.="\n".$t."<infAdProd>".$strlote."</infAdProd>";
                	   	
                }
		$strret.="\n".$t."</det>";
	    }	    		    	
	    //$strret="<det nItem=\"1\"><prod><cProd>03</cProd><cEAN/><xProd>AntÃ­geno MS INATA - 300 testes</xProd><NCM>30029010</NCM><CFOP>5101</CFOP><uCom>FR</uCom><qCom>5.0000</qCom><vUnCom>300.0000000000</vUnCom><vProd>1500.00</vProd><cEANTrib/><uTrib>FR</uTrib><qTrib>5.0000</qTrib><vUnTrib>300.0000000000</vUnTrib><indTot>1</indTot><xPed>13-0100408</xPed></prod><imposto></imposto></det><det nItem=\"2\"><prod><cProd>04</cProd><cEAN/><xProd>AntÃ­geno PUL INATA - 300 testes</xProd><NCM>30029010</NCM><CFOP>5101</CFOP><uCom>FR</uCom><qCom>10.0000</qCom><vUnCom>64.0000000000</vUnCom><vProd>640.00</vProd><cEANTrib/><uTrib>FR</uTrib><qTrib>10.0000</qTrib><vUnTrib>64.0000000000</vUnTrib><indTot>1</indTot><xPed>13-0100407</xPed></prod><imposto></imposto></det>";
	    //echo($strret); die;	
	    return $strret;
	}else{
	    return "\n".$t."<".$r['no'].$atrfixo.">";//nao possui valor ou pode ser NO
	}  
}

/*
 * maf010410: funcao recursiva que le a estrutura do xml na tabela nfpxmltreenova
 * Com os dados existentes na tabela, percorre recursivamente os NODES encontrados e 
 * vai montando os valores conforme a configuracao (colunas da tabela nfpxmltreenova) informada 
 */
function geraxml($inidnf,$proc="",$node=0,$level=0){ 

	$_SESSION["steperro"]="geraxml[".$node."]";
	
	for ($i = 1; $i <= $level; $i++) { 
		$t1 .= chr(9);//Indenta Estrutura 
	} 
	$level++; 
	
	$sql = "SELECT * FROM "._DBNFE.".nfpxmltreenova where ativo = 'Y' and processo='".$proc."' and pai = ".$node." order by ord";
 	$res = d::b()->query($sql); 
	if (!$res){
		$msgerr = "Erro ao consultar nfpxmltreenova: ".mysqli_error(d::b())."\n SQL: ".$sql;
		STATUSLOTE($_SESSION["idnfplote"],"ERRO");
		LOGETAPA($_SESSION["idnfplote"],"GERACAO",$msgerr);
		echo($sqlerr);
		return false;
	}
	 
	while ($r = mysqli_fetch_assoc($res)) { 
		//verifica se o pai tem filhos
		$sqlleaf = "select count(*) as qtleaf from "._DBNFE.".nfpxmltreenova where pai = ".$r['idnfpxmltreenova']. " and ativo = 'Y'";
		$resassoc = d::b()->query($sqlleaf) or die("Erro ao consultar nodes/leafs: ".mysqli_error(d::b())."\n SQL: ".$sqlleaf);
		$rleaf = mysqli_fetch_assoc($resassoc);
		$qtleaf = $rleaf["qtleaf"];		
		//echo "\n".$r['no']."-qtleaf: ".$qtleaf;
		
		//em alguns casos e necessario ver se aquela tag pai possui filhos com valor para preencher os mesmos - hermesp <tag> ex:<transportadora>.
		if($r['verificaorig']=="Y" and !empty($_SESSION[$r["arrorigem"]])){
			//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
				geraxml($inidnf,$proc,$r['idnfpxmltreenova'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";			
		}elseif($r['verificaorig']=="N"){
			if($qtleaf > 0){ //NODE
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
				geraxml($inidnf,$proc,$r['idnfpxmltreenova'],$level);
				$_SESSION["xml"] .= "\n".$t1."</".$r['no'].">";
			}else{
				//echo $t1."<".$r['no'].">\n"; 
				$_SESSION["xml"] .= montatag($inidnf,$t1,$r);
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
}
/*
 * maf010410: esta funcao armazena os logs de cada etapa do processo de geracao da NFE.
 * As primeiras etapas e o proprio LOG, em caso de erro, geram somente erros simples de PHP, que podem ser
 * tratados com die() simples para serem visualizados na tela.
 * A funcao mysql_real_escape_string() foi utilizada para evitar caracteres especiais no log (ex: aspas)
 * maf040819: Foi adicionado o XML para debug
 */
function LOGETAPA($inidnfplote,$inetapa,$inerro='',$incoderro=0){

	if(_NFSECHOLOG)echo "\n".date("H:i:s")." - LOGETAPA [".$inetapa."] / ".$inerro;
	$_SESSION["steperro"]="logetapa";
	
	$sqllog = "insert into "._DBNFE.".nfplog (
				idnfplote				
				,etapa
				,erro
				,xml
				,coderro) 
				values (
					".$inidnfplote."
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
function STATUSLOTE($inidnfplote, $instatus){

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
	    $sqllnf = "select idnfe,nnfe from nf where idnf = ".$_SESSION["idnotafiscal"];
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
		$respi = d::b()->query($sqlpi) or die("Erro pesquisar pré ID NF:\nSQL:".$sqlpi."\nErro:".mysqli_error(d::b()));
		$rnpi = mysqli_fetch_assoc($respi);
		if(empty($rnpi["preid"])){
		    d::b()->query("UNLOCK TABLES;");
		    echo " 2-Falha ao  pesquisar pré ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlpi";
		    die();							
		}else{
		    $sqlid = "select geraidnf(".$rnpi["preid"].",".$_SESSION["idnotafiscal"].") as idnfe";
		    $resid = d::b()->query($sqlid) or die("Erro pesquisar pré ID NF:\nSQL:".$sqlid."\nErro:".mysqli_error(d::b()));
		    $rnid = mysqli_fetch_assoc($resid);
		    if(empty($rnid["idnfe"])){
				    d::b()->query("UNLOCK TABLES;");
				    echo "2-Falha ao  gerar ID : " . mysqli_error(d::b()) . "<p>SQL: $sqlid";
				    die();
		    }else{
			    $_SESSION["arrnfsidlote"]["infNFe"]= "Id=\"".$rnid["idnfe"]."\" versao=\"4.00\"";								
		    }							
		}					
	    }else{
                if(empty($rnf["nnfe"])){                    
                    $_SESSION["nnfe"]=substr($rnf["idnfe"], 28, 9);

                    $sqlnf = "update nf set nnfe = '".$_SESSION["nnfe"]."' where idnf = ".$_SESSION["idnotafiscal"];
                    d::b()->query($sqlnf) or die("Erro atribuindo nnfe:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));
                }
		if(_NFSECHOLOG)echo "\n".date("H:i:s")." - gerachavenfe:capturando numero de cNF existente[".$rnf["idnfe"]."]";
		$_SESSION["arrnfsidlote"]["infNFe"]= "Id=\"".$rnf["idnfe"]."\" versao=\"4.00\"";									
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
 * e abre arrays contendo as informacoes padronizadas com a tabela nfpxmltreenova
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

	$sqlinftech = "SELECT *
	FROM "._DBAPP.".vwinfRespTec";
	$resinftech = d::b()->query($sqlinftech);

	if(!$resinftech){
	    $msgerr = "Erro ao consultar responsavel pela emissão da NF: \n- ".mysqli_error(d::b())."\n".date("H:i:s")." - ".$sqlinftech;
	    STATUSLOTE($_SESSION["idnfplote"],"ERRO");
	    LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI",$msgerr);
	    echo($sqlerr);
	    return false;
	}else{
	    $inftech = mysqli_fetch_assoc($resinftech);
	    $_SESSION["vwinfRespTec"]= $inftech;			
	}

		
	LOGETAPA($_SESSION["idnfplote"],"CONSULTAORI","SUCESSO");

	return true;
}
/*
 * mcc130618: funcao responsavel por GERAR o texto do XML que ira ser enviado
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

//mcc 21092018
//remove os caracteres especiais do xml
function string2Slug($str){
	$str = str_replace("ÃƒÂ’","O",$str);
	
	$str = str_replace("ÃƒÂŽ","I",$str);
	//$str = str_replace("ÃƒÂ","I",$str);
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
	//$str = str_replace("ÃƒÂ","I",$str);

    return $str;

}


/*
 * mcc130618: funcao responsavel por ASSINAR e ENVIAR o XML da NF
 */
function assina_envia_XML($sXML, $tagID){	
	global $_idempresa;
    //die($sXML);
	//maf199619: slug retirado
    //$sXML = string2Slug($sXML);

    //$sXML=("<NFe xmlns=\"http://www.portalfiscal.inf.br/nfe\"><infNFe Id=\"NFe31170523259427000104550010000033731790970782\" versao=\"4.00\"><ide><cUF>31</cUF><cNF>79097078</cNF><natOp>REMESSA DE MATERIAL PARA ANÁLISE</natOp><mod>55</mod><serie>1</serie><nNF>3373</nNF><dhEmi>2017-05-29T10:57:00-02:00</dhEmi><tpNF>1</tpNF><idDest>2</idDest><cMunFG>3170206</cMunFG><tpImp>1</tpImp><tpEmis>1</tpEmis><cDV>2</cDV><tpAmb>1</tpAmb><finNFe>1</finNFe><indFinal>1</indFinal><indPres>9</indPres><procEmi>3</procEmi><verProc>3.10.49</verProc></ide><emit><CNPJ>23259427000104</CNPJ><xNome>LAUDO LABORATÓRIO AVÍCOLA UBERLÂNDIA LTDA</xNome><xFant>LAUDO LABORATÓRIO</xFant><enderEmit><xLgr>RODOVIA BR 365, KM 615</xLgr><nro>S/N</nro><xBairro>ALVORADA</xBairro><cMun>3170206</cMun><xMun>Uberlandia</xMun><UF>MG</UF><CEP>38407180</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>3432225700</fone></enderEmit><IE>7023871770001</IE><CRT>3</CRT></emit><dest><CNPJ>37020260000309</CNPJ><xNome>NUTRIZA AGROINDUSTRIAL DE ALIMENTOS S/A</xNome><enderDest><xLgr>RODOVIA-GO 020</xLgr><nro>S/N</nro><xBairro>ZONA RURAL</xBairro><cMun>5217401</cMun><xMun>PIRES DO RIO</xMun><UF>GO</UF><CEP>75200000</CEP><cPais>1058</cPais><xPais>BRASIL</xPais><fone>6434617969</fone></enderDest><indIEDest>1</indIEDest><IE>102649189</IE><email>valderez.limberger@friato.com.br</email></dest><det nItem=\"1\"><prod><cProd>MCA</cProd><cEAN></cEAN><xProd>MATERIAL PARA COLETA DE AMOSTRA(S)</xProd><NCM>23099010</NCM><CFOP>6949</CFOP><uCom>UN</uCom><qCom>1.00</qCom><vUnCom>1.9900</vUnCom><vProd>1.99</vProd><cEANTrib></cEANTrib><uTrib>UN</uTrib><qTrib>1.00</qTrib><vUnTrib>1.9900</vUnTrib><indTot>1</indTot><xPed>000000</xPed><nItemPed>00000</nItemPed></prod><imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>1.99</vBC><pICMS>7.00</pICMS><vICMS>0.14</vICMS></ICMS00></ICMS><IPI><cEnq>999</cEnq><IPINT><CST>51</CST></IPINT></IPI><PIS><PISNT><CST>07</CST></PISNT></PIS><COFINS><COFINSNT><CST>07</CST></COFINSNT></COFINS></imposto></det><total><ICMSTot><vBC>1.99</vBC><vICMS>0.14</vICMS><vICMSDeson>0.00</vICMSDeson><vFCPUFDest>0.00</vFCPUFDest><vICMSUFDest>0.00</vICMSUFDest><vICMSUFRemet>0.00</vICMSUFRemet><vFCP>1</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>1</vFCPST><vFCPSTRet>1</vFCPSTRet><vProd>1.99</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>1</vIPIDevol><vPIS>0.00</vPIS><vCOFINS>0.00</vCOFINS><vOutro>0.00</vOutro><vNF>1.99</vNF></ICMSTot></total><transp><modFrete>0</modFrete><transporta><CNPJ>18260422000161</CNPJ><xNome>NACIONAL EXPRESSO LTDA</xNome><IE>7021867530016</IE><xEnder>PRAÇA DA BÍBLIA S/N</xEnder><xMun>UBERLANDIA</xMun><UF>MG</UF></transporta></transp><pag><detPag><tPag>01</tPag><vPag>1.00</vPag><card><tpIntegra>1</tpIntegra><CNPJ>63322115000112</CNPJ><tBand>01</tBand><cAut>01</cAut></card></detPag><vTroco>1.00</vTroco></pag><infAdic><infCpl>111</infCpl></infAdic></infNFe></NFe>");
    //echo $sXML;
    if(_NFSECHOLOG)echo "\n".date("H:i:s")." - assinaxml: assinando xml";
    $_SESSION["steperro"]="assinaxml";

    $_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
	$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
	$_row=mysqli_fetch_assoc($_res);

	$config = [
    "atualizacao" => $_row["nfatt"],
    "tpAmb" => 1, // Se deixar o tpAmb como 2 você emitirá a nota em ambiente de homologação(teste) e as notas fiscais aqui não tem valor fiscal
    "razaosocial" => $_row["nfrazaosocial"],
    "siglaUF" => $_row["nfsiglaUF"],
    "cnpj" => $_row["nfcnpj"],
    "schemes" => $_row["nfschemes"], 
    "versao" => $_row["nfversao"],
    "tokenIBPT" => $_row["nftokenIBPT"]
    ];


    $configJson = json_encode($config);



	if(!$certificadoDigital = file_get_contents($_row["certificado"])){
        STATUSLOTE($_SESSION["idnfplote"],"ERRO");
        LOGETAPA($_SESSION["idnfplote"],"FILE_GET_CONTENTS","Erro recuperando certificado: ".$_row["certificado"]);
    }else{
        echo $_row["certificado"];
    }

    // $tools = new Tools($configJson, Certificate::readPfx($certificadoDigital, '010787'));

    LOGETAPA($_SESSION["idnfplote"],"ASSINA_ENVIA1");

    try {
        $cert = NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]);
    } catch (\Exception $e) {
        STATUSLOTE($_SESSION["idnfplote"],"ERRO");
        LOGETAPA($_SESSION["idnfplote"],"READPFX","Erro readPfx: ".$e->getMessage());
    }

    if ($cert->isExpired()) {
        STATUSLOTE($_SESSION["idnfplote"],"ERRO");
        LOGETAPA($_SESSION["idnfplote"],"ISEXPIRED","Certificado VENCIDO! Não é possivel mais usá-lo: Idempresa: ".$_idempresa);
    }

	LOGETAPA($_SESSION["idnfplote"],"ASSINA_ENVIA1");

    //@todo: tratar erro aqui:
    $tools = new NFePHP\NFe\Tools($configJson, $cert);

    LOGETAPA($_SESSION["idnfplote"],"ASSINA_ENVIA2");

    //CONTIGENCIA
	if($_SESSION["contigencia"]=="Y"){
            if(empty($_SESSION["datacontingencia"])){
               die('Preencher a data de inicio da contingencia no cadastro da empresa.'); 
            }
		//NOTA: esse json pode ser criado com Contingency::class
		//$timestamp = time();//1484747583
                $timestamp=strtotime($_SESSION["datacontingencia"]);
		$contingency = '{
			"motive":"SEFAZ fora do AR",
			  "timestamp":'.$timestamp.',
			"type":"SVCAN",
			"tpEmis":6
		}';
		//indica que estamos em modo de contingência, se nada for passado estaremos em modo normal
		$tools->contingency->activate("MG", "SEFAZ fora do AR", $tipo = '');
		$tools->contingency->load($contingency);
	}
	
    try {
	$xmlAssinado = $tools->signNFe($sXML); // O conteÃºdo do XML assinado fica armazenado na variÃ¡vel $xmlAssinado
	LOGETAPA($_SESSION["idnfplote"],"ASSINA_ENVIA3");
    } catch (\Exception $e) {
	//die($e->getMessage());
	//aqui vocÃª trata possÃ­veis exceptions da assinatura
	exit($e->getMessage());
    }

    try {
	$idLote = str_pad(100, 15, '0', STR_PAD_LEFT); // Identificador do lote
	$resp = $tools->sefazEnviaLote([$xmlAssinado], $idLote);
	LOGETAPA($_SESSION["idnfplote"],"ASSINA_ENVIA4");
	//die($tools->soapDebug);
	$_SESSION["xmlret"] = htmlspecialchars($tools->soapDebug);
	$st = new NFePHP\NFe\Common\Standardize();
	$std = $st->toStd($resp);
	//print_r($std);
	if ($std->cStat != 103) {
	    //erro registrar e voltar
	    exit("[$std->cStat] $std->xMotivo");
	}
	$recibo = $std->infRec->nRec; // Vamos usar a variÃ¡vel $recibo para consultar o status da nota

	$_SESSION["nlote"] = $recibo;

	$sql="update "._DBAPP.".nf set recibo='".$recibo."',envionfe='ENVIADO' where idnf = ".$_SESSION["idnotafiscal"];
	$retx = d::b()->query($sql);

	STATUSLOTE($_SESSION["idnfplote"],"SUCESSO");
	LOGETAPA($_SESSION["idnfplote"],"ENVIO","SUCESSO");

    } catch (\Exception $e) {
	//aqui vocÃª trata possiveis exceptions do envio
	exit($e->getMessage());
    }
	if($_SESSION["contigencia"]=="Y"){
		$tools->contingency->deactivate();
	}
    return $xmlAssinado;
}
/*
 * maf01010: funcao responsavel por ASSINAR o xml gerado
 */
function ASSINATURA_ENVIA(){

    if(_NFSECHOLOG)echo "\n".date("H:i:s")." - ASSINATURA: gerando assinatura";
    $_SESSION["steperro"]="assinatura";	

    //Inicia a Etapa	
    STATUSLOTE($_SESSION["idnfplote"],"ASSINATURA");
    LOGETAPA($_SESSION["idnfplote"],"ASSINATURA");
    //echo($_SESSION["xml"]);
    //retirar todos os espaço do xml antes de assinar
    $order = array("\r\n", "\n", "\r", "\t");
    $replace = '';
    $_SESSION["xml"] = str_replace($order, $replace, $_SESSION["xml"]);
    //Estas tag não são permitidas vazias por isso foram retiradas antes da assinatura do XML
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
	$_SESSION["xml"] = str_replace('<dhSaiEnt></dhSaiEnt>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<NFref></NFref>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<CEST></CEST>','', $_SESSION["xml"]);
	$_SESSION["xml"] = str_replace('<cBenef></cBenef>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<UFSaidaPais></UFSaidaPais>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<xLocExporta></xLocExporta>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<xLocDespacho></xLocDespacho>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<exporta></exporta>','', $_SESSION["xml"]);
    $_SESSION["xml"] = str_replace('<CNPJ></CNPJ>','<idEstrangeiro>EXTERIOR</idEstrangeiro>', $_SESSION["xml"]);
	$_SESSION["xml"] = str_replace('<infAdProd></infAdProd>','', $_SESSION["xml"]);
	$_SESSION["xml"] = str_replace('<dhCont></dhCont>','', $_SESSION["xml"]);
	$_SESSION["xml"] = str_replace('<xJust></xJust>','', $_SESSION["xml"]);	 
	$_SESSION["xml"] = str_replace('<tPag>90</tPag><vPag>0.00</vPag></detPag><vTroco>0.00</vTroco>','<tPag>90</tPag><vPag>0.00</vPag></detPag>', $_SESSION["xml"]);
   

    //$_SESSION["xml"] = str_replace('<vDesc>0.00</vDesc>','', $_SESSION["xml"]);    
    /*
     * inicia funcao recursiva de montagem
     */

    //echo $_SESSION["xml"];
    //$xmtlutf = ($_SESSION["xml"]);

    LOGETAPA($_SESSION["idnfplote"],"ASSINATURA0");
    //Assinar o XML
    $_SESSION["xml"] = assina_envia_XML($_SESSION["xml"], $_SESSION["tagassinatura"]);
    LOGETAPA($_SESSION["idnfplote"],"ASSINATURA1");
    /*Retirar o <?xml version="1.0" adicionado na assinatura pois o metodo de envio da toolsNfephp adiciona ao encapsular no soap 
     * REtirei para não ficar duplicado hermesp
     */

    $_SESSION["xml"] = str_replace('<?xml version="1.0" encoding="UTF-8"?>','', $_SESSION["xml"]);

    LOGETAPA($_SESSION["idnfplote"],"ASSINATURA2");

    $idnf = $_SESSION["idnotafiscal"];
	//if($_SESSION["contigencia"]=="Y"){
	//	$sql="update "._DBAPP.".nf set obsint=infcpl,xml='".$_SESSION["xml"]."', xmlret='".$_SESSION["xml"]."',envionfe='CONCLUIDA' where idnf = ".$idnf;
	//	$retx = d::b()->query($sql);		
	//}else{
		$sql="update "._DBAPP.".nf set xml='".$_SESSION["xml"]."' where idnf = ".$idnf;
		$retx = d::b()->query($sql);
	//}

    //echo($_SESSION["xml"]);
    //die();	
    return true; 
}

//geranumeronfe();

if(LOTE($idnf) 	== true
    and CONSULTAORI() 	== true
    and GERACAO() 		== true	
    and ASSINATURA_ENVIA() 	== true
    ){
    die("\n- idnf [".$_SESSION["idnotafiscal"]."] enviada com sucesso! Recibo para consulta: [". $_SESSION["nlote"]."] ");
}else{
    die("\n".date("H:i:s")." - Erro Fatal:".$_SESSION["steperro"]);
} 
?>
