<?
/*HERMES 08032013
 * GERAS SPED FISCAL -  ICMS
 * PRODUTOS  
 */
//ini_set("display_errors","1");
//error_reporting(E_ALL);
require_once("../inc/php/functions.php");

//$mes=$_GET["mes"];
$inventario=$_GET["inventario"];
$ano=$_GET["ano"];
$dtInicio=$_GET["dtInicio"];
$dtFim=$_GET["dtFim"];
$veridnf=$_GET["veridnf"];
$idempresa=$_GET["_idempresa"];
$anoinventario= $ano - 1;


function ajustarIE($vnumero,$UF='') {
    // Defina o comprimento desejado
	IF(trim($UF)=='MG'){
		$comprimentoDesejado = 13;
	}elseif(trim($UF)=='RJ'){
		$comprimentoDesejado = 8;
	}elseif(trim($UF)=='RS'){
		$comprimentoDesejado = 10;
	}else{
		$comprimentoDesejado = 9;
	}
    

	$numero=trim($vnumero);

	if(empty($numero)){
		$numeroFormatado='';
	}else{
		// Use str_pad para preencher com zeros à esquerda
		$numeroFormatado = str_pad($numero, $comprimentoDesejado, '0', STR_PAD_LEFT);
	}


    return $numeroFormatado;
}

function rmAc($string) {

	$string=str_replace('|', '', $string);

    // Converte os caracteres acentuados para suas versões não acentuadas
	$stringSemAcentos = strtr(utf8_decode($string), utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÑñ¹²³µ'),
    'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuun123u');
	
    return $stringSemAcentos;
}


if (!empty($dtInicio) or !empty($dtFim)){
	$dataini = validadate($dtInicio);
	$datafim = validadate($dtFim);

    $mes = date("m",strtotime($datafim));
   

	if ($dataini and $datafim){
		$dtInicio=$dataini;
		$dtFim=$datafim;
	}else{
			die ("Datas n&atilde;o V&aacute;lidas!");
	}
}else{
	die("É necessário informar o periodo.");
}

//Montar a variavel mesano de faturamento do ICMS
if(!empty($dtInicio)){
	$arr_dt = explode("-", $dtInicio);
	$mes=$arr_dt[1];
	$ano=$arr_dt[0];
}else{
 die("Informar o mes de vencimento ano do ICMS");
}

####################### gerar o txt ######################################## inicio
ob_end_clean();//não envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$_header="SPED_FISCAL".$mes."_".$ano;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $_header);	
	//gera o csv
	//header("Content-type: text/txt; charset=iso-8859-1");
    header('Content-type: text/html; charset=UTF-8');
	header("Content-Disposition: attachment; filename=".$infilename.".txt");
	header("Pragma: no-cache");
	header("Expires: 0");
######### FIM GERAR TXT ##########################################################	

//Limpar Sessoes anteriores
unset($_SESSION['0000']); 
unset($_SESSION['0001']); 
unset($_SESSION['0005']); 
unset($_SESSION['0100']); 
unset($_SESSION['0150']); 
unset($_SESSION['0190']);
unset($_SESSION['0200']);
unset($_SESSION['0205']);
unset($_SESSION['0210']);
unset($_SESSION['0220']);
unset($_SESSION['0990']); 
unset($_SESSION['C001']); 
unset($_SESSION['C100']);
unset($_SESSION['C101']); 
unset($_SESSION['C170']); 
unset($_SESSION['C190']);
unset($_SESSION['C500']);
unset($_SESSION['C590']);
unset($_SESSION['C990']); 
unset($_SESSION['D001']); 
unset($_SESSION['D100']);
unset($_SESSION['D190']);
unset($_SESSION['D990']); 
unset($_SESSION['E001']); 
unset($_SESSION['E100']); 
unset($_SESSION['E110']); 
unset($_SESSION['E116']);   
unset($_SESSION['E300']);
unset($_SESSION['E310']);
unset($_SESSION['E316']);
unset($_SESSION['E500']);
unset($_SESSION['E510']);
unset($_SESSION['E520']); 
unset($_SESSION['E990']); 
unset($_SESSION['H005']);
unset($_SESSION['H010']);
unset($_SESSION['G001']); 
unset($_SESSION['G990']); 
unset($_SESSION['H001']); 
unset($_SESSION['H990']); 
unset($_SESSION['K001']);
unset($_SESSION['K010']);
unset($_SESSION['K100']);
unset($_SESSION['K230']);
unset($_SESSION['K235']);
unset($_SESSION['K990']);
unset($_SESSION['1001']); 
unset($_SESSION['1990']); 
unset($_SESSION['uf']);


//função para comparar se a primeira data e maior que a segunda
function comparadata($dataI,$dataII){
	// trabalhando a primeira data
	$I= strtotime($dataI);

	// trabalhando a segunda data
	$II= strtotime($dataII);

	if($I == $II){
		$vretorno="I";
	}elseif($I > $II){
		$vretorno="S";
	}elseif($II > $I){
		$vretorno="N";
	}
	return($vretorno);
}

//Montar a variavel mesano de faturamento do ICMS
if(!empty($dtInicio)){
	$arr_dt = explode("-", $dtInicio);
	$mes=$arr_dt[1];
	$ano=$arr_dt[0];
}else{
 die("Informar o mes de vencimento ano do ICMS");
}


//Formatar datas de inicio e fim
if(!empty($mes) and !empty($ano)){
    
        $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
        $inicio="01".$mes.$ano;
		$fim=$ultimo_dia.$mes.$ano;
        
      //  $dtInicio=$ano."-".$mes.'-01';
     //   $dtFim=$ano."-".$mes.'-'.$ultimo_dia;
}else{
 die("É necessario informar o inicio o e fim do envio");
}

//Montar a variavel mesano de faturamento do ICMS
if(!empty($mes) and !empty($ano)){	
	$mesano=$mes.$ano;
}else{
 die("Informar o mes de vencimento do ICMS");
}
/* http://www.fazenda.gov.br/confaz/confaz/atos/atos_cotepe/2008/ac009_08.htm
BLOCO 0 Abertura, Identificação e Referências
BLOCO C Documentos Fiscais I  - Mercadorias (ICMS/IPI)
BLOCO D Documentos Fiscais II - ServiÃ§os (ICMS)
BLOCO E Apuração do ICMS e do IPI
BLOCO G Controle do Crédito de ICMS do Ativo Permanente - CIAP - 
BLOCO H Inventário Fí­sico
BLOCO 1 Outras Informações
BLOCO 9 Controle e Encerramento do Arquivo Digital
*/
class sped{  
	public $inicio;
	public $fim;
	public $dtInicio;
	public $dtFim;
	public $mes;
	public $nf;
	public $mesAno;
	public $codPart;
	public $vlrTotalIcms;
	public $vlrTotalded;
	public $vlrTotalIPI;
	public $vlrTotalIPIded;
	public $inventario;
	public $ano;
	public $anoinventario;
	public $veridnf;
		  
    //// BLOCO 0 - Abertura do Arquivo Digital e Identificação da entidade
    public function bloco_0($idempresa){

		if($this->ano=='2023'){
			$versao='017';
		}else{
			$versao='018';
		}

    	$sql="select idempresa as id,concat ('|0000|".$versao."|0|".$this->inicio."|".$this->fim."|',ifnull(e.razaosocial,''),'|',
		ifnull(e.cnpj,''),'||',ifnull(e.uf,''),'|',ifnull(e.inscestadual,''),
		'|',ifnull(e.cmun,''),'|',ifnull(e.inscricaoMunicipalPrestador,''),
		'||B|0|') as inf
		from empresa e where idempresa = ".$idempresa;
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0000".mysql_error());    	
    	while ($row=mysql_fetch_assoc($res)){    	
      		$_SESSION['0000']=$row['inf']."\n";
      		$l0000=$l0000+1;
    	}      
    	echo($_SESSION['0000']);
    	
    	$l0000=$l0000+1;
    	$_SESSION['0001']="|0001|0|\n";
    	echo($_SESSION['0001']);
        $l0000=$l0000+1;
        $_SESSION['0002']="|0002|00|\n";
    	echo($_SESSION['0002']);
        
    	
    	//$l0000=$l0000+1;
    	//$_SESSION['0007']="|0007|||\n";
    	//echo($_SESSION['0007']);
    	
    	//Dados Complementares da entidade
    	$sql="select idempresa as id,
		concat ('|0005|',nomefantasia,'|',cep,'|',xlgr,'|',nro,'||',xbairro,'|',DDDprestador,TelefonePrestador,'|',DDDprestador,TelefonePrestador,'|',email,'|') as inf
    	from empresa e where idempresa=".$idempresa;
    	 
	   	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0005".mysql_error());    	
    	while ($row=mysql_fetch_assoc($res)){       		
    		$_SESSION['0005']=$row['inf']."\n";
    		$l0000=$l0000+1;
    	}    	
		echo($_SESSION['0005']);  
		
		/*
		*JLAL - 14-08-20 
		*Dados Complementares do contador
		*/
		$sql="select idempresa as id,
		concat ('|0100|',nomecontador,'|',cpfcontador,'|',crccontador,'|',cnpjcontador,'|',cepcontador,'|',enderecocontador,'|',numcontador,'|',complementocontador,'|',bairrocontador,'|',fonecontador,'|',faxcontador,'|',emailcontador,'|',codmuncontador,'|') as infcont
    	from empresa e where idempresa=".$idempresa;
    	 
	   	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0005".mysql_error());    	
    	while ($row=mysql_fetch_assoc($res)){       		
    		$_SESSION['0100']=$row['infcont']."\n";
    		$l0000=$l0000+1;
    	}    	
    	echo($_SESSION['0100']);  
    	/*  	
    	$l0000=$l0000+1;
    	$_SESSION['0150']['0']="|0150|06981180000116|CEMIG|1058|06981180000116|||3106200||Av. Barcelona, Belo Horizonte MG|1200|||";
    	echo($_SESSION['0150']['0']."\n");
     	*/
    	//PARTICIPANTES POR NOTA FISCAL DE C0MPRA E VENDA
    	$sqld="select * from (	
								select idempresa as id,xmlret as xml,tiponf,faticms,idnf,nnfe,idpessoa
										from nf 
										where sped = 'Y'
										and tiponf ='V' 
										and tpnf !='0'
										and status in ('CONCLUIDO','ENVIADO','ENVIAR','DEVOLVIDO')
										and envionfe = 'CONCLUIDA'
										and idempresa = ".$idempresa."
										and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'
								UNION ALL
								select idempresa as id,xmlret as xml,tiponf,faticms,idnf,nnfe,idpessoa
										from nf 
										where sped = 'Y'
										and tiponf ='C'
										and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
										and envionfe = 'CONCLUIDA'
										and idempresa = ".$idempresa."
										and prazo between '".$this->dtInicio."' and '".$this->dtFim."' 
						) as u group by idpessoa";  
    	$resd=mysql_query($sqld) or die($sqld." erro ao buscar informações do bloco 0150".mysql_error());
    	$p=0;
    	$arrcodpar=array();
    	
    //	echo($sqld);
    	while($rowd=mysql_fetch_assoc($resd)){
    	 	if($this->veridnf=="Y"){
    			echo("NNFe =".$rowd['nnfe']."\n");
    		}
    	// passar string para UTF-8
      	$xml=$rowd['xml'];
		$xml = mb_convert_encoding($xml, 'UTF-8', 'SUA_CODIFICACAO_ATUAL');
      
      	//Carregar o XML em UTF-8      			
      	$doc = DOMDocument::loadXML($xml);	
		$doc->encoding = 'UTF-8';
      	//inicia lendo as principais tags do xml da nfe
	    $nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
		if($nfeProc!=null){
			$NFe = $nfeProc->getElementsByTagName("NFe")->item(0);
		}else{
			$NFe = $doc->getElementsByTagName("NFe")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
		}
	    	      		
	    $infNFe = $NFe->getElementsByTagName("infNFe")->item(0);      			
      	    
		    if($rowd['tiponf']=='C'){
		    	//buscando informações do destinatario / emitente
		    	$dest = $infNFe->getElementsByTagName("emit")->item(0);
		    	$enderDest = $dest->getElementsByTagName("enderEmit")->item(0);
		    }else{
		    	//buscando informações do destinatario / Participante
		    	$dest = $infNFe->getElementsByTagName("dest")->item(0);
		    	$enderDest = $dest->getElementsByTagName("enderDest")->item(0);	    
		    }
	    	    
		$UF = $enderDest->getElementsByTagName("UF")->item(0); 
		$vUF =($UF->textContent); //pegar o valor da tag <UF> 	

	    //COD_PART 
	    $CNPJ = $dest->getElementsByTagName("CNPJ")->item(0); 
	    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
		if(empty($CNPJ) or empty($vCNPJ)){
			$vIE="";
		}else{
			//IE
			$IE = $dest->getElementsByTagName("IE")->item(0);
			$vIE =($IE->textContent); //pegar o valor da tag <IE> 
			if($vIE=="ISENTO"){
				$vIE="";
			}else{
				$vIE=ajustarIE($vIE,$vUF);
			}
		}
		
		if(empty($CNPJ) or empty($vCNPJ)){
				//COD_PART 
			$CNPJ = $dest->getElementsByTagName("CPF")->item(0); 			    
			$vCPF =($CNPJ->textContent); //pegar o valor da tag <CPF> 
			$vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
		}

	    //xNome
	    $xNome = $dest->getElementsByTagName("xNome")->item(0); 
	    $vNome =($xNome->textContent); //pegar o valor da tag <xNome>	    
	    
		//COD_PAIS
		$cPais = $enderDest->getElementsByTagName("cPais")->item(0); 
	    $vcPais =($cPais->textContent); //pegar o valor da tag <cPais> 
	    
		if(empty($vcPais) or $vcPais==" "){
			$vcPais=1058;//Brasil
		}
	      
	    //COD_MUN
	    $cMun = $enderDest->getElementsByTagName("cMun")->item(0); 
	    $vcMun =($cMun->textContent); //pegar o valor da tag <cMun> 
	    //END
	    $xLgr = $enderDest->getElementsByTagName("xLgr")->item(0); 
	    $vxLgr =($xLgr->textContent); //pegar o valor da tag <xLgr>
	    //NUM 	    
	    $nro = $enderDest->getElementsByTagName("nro")->item(0); 
	    $vnro =($nro->textContent); //pegar o valor da tag <nro>
	    //if($vnro=="S/N" or $vnro == "S/Nº"){
	    //	$vnro="0";
	    //}	 	 
	    //BAIRRO   
	    $xBairro = $enderDest->getElementsByTagName("xBairro")->item(0); 
	    $vxBairro =($xBairro->textContent); //pegar o valor da tag <cPais>	 
	    
	    	if(in_array($rowd['idpessoa'], $arrcodpar)) { 
			    echo "";
			}else{	  
				$p=$p+1;  
			    $l0000=$l0000+1;	    
			    
			    if($vCPF==$vCNPJ){
			    	$vCNPJ="";
			    	$codpart=$vCPF;
			    }else{
			    	$vCPF="";
			    	$codpart=$vCNPJ;
			    }
				//if(empty($codpart)){
					$codpart=$rowd['idpessoa'];
				//}
				$arrcodpar[$p]=$rowd['idpessoa'];

		    	$_SESSION['0150'][$p]=("|0150|".$codpart."|".$vNome."|".$vcPais."|".$vCNPJ."|".$vCPF."|".$vIE."|".$vcMun."||".$vxLgr."|".$vnro."||".$vxBairro."|");		
		    	echo($_SESSION['0150'][$p]."\n");
			}
		
    	}
			
		//PARTICIPANTES POR NOTA FISCAL DE C0MPRA  MANUAL
/*	retirado os manuais por enquanto
		$sqld="select cpfcnpj,razaosocial,cpais,cmunfg,endereco,numero,bairro,idnf,prazo,idempresa,idpessoa
			from vwfornecedornfmanual
			where 
			-- idempresa = ".$idempresa." and
			 prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
		$resd=mysql_query($sqld) or die($sqld." erro ao buscar informações do bloco 0150 para notas MANUAIS".mysql_error());
		while($rowd=mysql_fetch_assoc($resd)){
			
			$vCNPJf=formatarCPF_CNPJ($rowd['cpfcnpj'],false);
			
			if(in_array($vCNPJf, $arrcodpar)) {
				echo "";
			}else{
				$p=$p+1;
				$l0000=$l0000+1;
				$arrcodpar[$p]=$vCNPJf;
				if(strlen($vCNPJf)==11){
					
					$vCNPJ="";
					$vCPF=$vCNPJf;
					$codpart=$vCPF;
				}else{
					$vCPF="";
					$vCNPJ=$vCNPJf;
					$codpart=$vCNPJ;
				}
				$_SESSION['0150'][$p]="|0150|".$codpart."|".$rowd['razaosocial']."|".$rowd['cpais']."|".$vCNPJ."|".$vCPF."||".$rowd['cmunfg']."||".$rowd['endereco']."|".$rowd['numero']."||".$rowd['bairro']."|";
				echo($_SESSION['0150'][$p]."\n");
			}
			
		}
/*			
			
			
    	/*
    	$l0000=$l0000+1;
    	$_SESSION['0190']="|0190|FR|Frasco|";
    	echo($_SESSION['0190']."\n");    	
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][1]="|0200|02|Antigeno MG INATA - 300 testes|||FR|04|30029010||||||";
    	echo($_SESSION['0200'][1]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][2]="|0200|03|Antigeno MS INATA - 300 testes|||FR|04|30029010||||||";
    	echo($_SESSION['0200'][2]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][3]="|0200|04|Antigeno PUL INATA - 300 testes|||FR|04|30029010||||||";
    	echo($_SESSION['0200'][3]."\n");
    	
    	*/
 
    	 
    	 //PARTICIPANTE PELA NOTA CTe
    	 $sqlt="select idempresa as id,xmlret as xml,tiponf,faticms,consumo,imobilizado,comercio,idnf,nnfe,idpessoa
				from nf
				where sped = 'Y'
				and tiponf = 'T'
			 	and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
				and xmlret is not null
			 	and envionfe = 'CONCLUIDA'
			 	and idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."' group by idpessoa";
    	 $rest=mysql_query($sqlt) or die($sqlt." erro ao buscar informações do bloco C001".mysql_error());
    	 while($rowt=mysql_fetch_assoc($rest)){
    	 	
    	 	if($this->veridnf=="Y"){
    	 		echo("NNFe".$rowt['nnfe']."\n");
    	 	}
    	 
    	 	// passar string para UTF-8
    	 	$xml=$rowt['xml'];
			$xml = mb_convert_encoding($xml, 'UTF-8', 'SUA_CODIFICACAO_ATUAL');
    	 	//Carregar o XML em UTF-8
    	 	$doc = DOMDocument::loadXML($xml);
			$doc->encoding = 'UTF-8';
    	 	//inicia lendo as principais tags do xml da nfe
    	 	$cteProc = $doc->getElementsByTagName("cteProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
    	 	$CTe = $cteProc->getElementsByTagName("CTe")->item(0);
    	 	$infCte = $CTe->getElementsByTagName("infCte")->item(0);
    	 	
    	 	$dest = $infCte->getElementsByTagName("emit")->item(0);
    	 	$enderDest = $dest->getElementsByTagName("enderEmit")->item(0);

			$UF = $enderDest->getElementsByTagName("UF")->item(0); 
			$vUF =($UF->textContent); //pegar o valor da tag <UF> 	
    	 	
    	 	//COD_PART
    	 	$CNPJ = $dest->getElementsByTagName("CNPJ")->item(0);
    	 	$vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ>

			//IE
			if(empty($CNPJ) or empty($vCNPJ)){
				$vIE="";
			}else{
				$IE = $dest->getElementsByTagName("IE")->item(0);
				$vIE =strval($IE->textContent); //pegar o valor da tag <IE>
				if($vIE=="ISENTO"){
					$vIE="";
				}else{
					$vIE=ajustarIE($vIE,$vUF);
				}
			}

    	 	if(empty($CNPJ) or empty($vCNPJ)){
    	 		//COD_PART
    	 		$CNPJ = $dest->getElementsByTagName("CPF")->item(0);
    	 		$vCPF =($CNPJ->textContent); //pegar o valor da tag <CPF>
    	 		$vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ>
    	 	}
    	 	
    	 	//xNome
    	 	$xNome = $dest->getElementsByTagName("xNome")->item(0);
    	 	$vNome =($xNome->textContent); //pegar o valor da tag <xNome>
    	 	 
    	 	//COD_PAIS
    	 	$cPais = $enderDest->getElementsByTagName("cPais")->item(0);
    	 	$vcPais =($cPais->textContent); //pegar o valor da tag <cPais>
    	 	 
    	 	if(empty($vcPais) or $vcPais==" "){
    	 		$vcPais=1058;//Brasil
    	 	}
    	 	 
    	 
    	 	
    	 	//COD_MUN
    	 	$cMun = $enderDest->getElementsByTagName("cMun")->item(0);
    	 	$vcMun =($cMun->textContent); //pegar o valor da tag <cMun>
    	 	//END
    	 	$xLgr = $enderDest->getElementsByTagName("xLgr")->item(0);
    	 	$vxLgr =($xLgr->textContent); //pegar o valor da tag <xLgr>
    	 	//NUM
    	 	$nro = $enderDest->getElementsByTagName("nro")->item(0);
    	 	$vnro =($nro->textContent); //pegar o valor da tag <nro>
    	 	//if($vnro=="S/N" or $vnro == "S/Nº"){
    	 	//	$vnro="0";
    	 		//}
    	 		//BAIRRO
    	 	$xBairro = $enderDest->getElementsByTagName("xBairro")->item(0);
    	 		if(!empty($xBairro)){
    	 			$vxBairro =($xBairro->textContent); //pegar o valor da tag <cPais>
    	 		}else{
    	 			$vxBairro="";
    	 		}
    	 		 
    	 		if(in_array($rowt['idpessoa'], $arrcodpar)) {
    	 			echo "";
    	 		}else{
    	 			$p=$p+1;
    	 			$l0000=$l0000+1;
    	 			
    	 			if($vCPF==$vCNPJ){
    	 				$vCNPJ="";
    	 				$codpart=$vCPF;
    	 			}else{
    	 				$vCPF="";
    	 				$codpart=$vCNPJ;
    	 			}
					//if(empty($codpart)){
						$codpart=$rowt['idpessoa'];
					//}
					$arrcodpar[$p]=$rowt['idpessoa'];
					
    	 			$_SESSION['0150'][$p]="|0150|".$codpart."|".$vNome."|".$vcPais."|".$vCNPJ."|".$vCPF."|".$vIE."|".$vcMun."||".$vxLgr."|".$vnro."||".$vxBairro."|";
    	 			echo($_SESSION['0150'][$p]."\n");
    	 		}
    	 }
    	 /*
    	  * DESCREVE AS UNIDADES DE MEDIDA 0190
    	 * */
    	 
    	 /*$l0000=$l0000+1;
    	 $_SESSION['0190'][1]="|0190|UN|Unidade|\n";
    	 echo($_SESSION['0190'][1]."\n"); 
    	 */
		$sqliv='';
    	  if($this->inventario=='Y'){
    	  	 //$sql1="select un,descr	from medida ";
			   $sqliv=" union select upper(p.un) as un,rtrim(ltrim(m.descr)) as descr
			   		from spedh010 s join  prodserv p on(p.idprodserv=s.idprodserv)
					   join unidadevolume m on(m.un=p.un)
				where s.idempresa = ".$idempresa." 
				and s.mes='12'
				and s.ano='".$this->anoinventario."' 
				and s.status='ATIVO' ";
    	  	
    	  }//else{
    	  	// $sql1="select un,descr	from medida where un in ('UN','FR','ML','GR')";
    	  //}
    	 
		  $sql1="select upper(x.un) as un, x.un as descr
					from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')						
					join prodserv p on(x.idprodserv=p.idprodserv)              
					where n.sped = 'Y'
					and n.tiponf ='C'
					and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
					and n.envionfe = 'CONCLUIDA'
					and n.idempresa =  ".$idempresa."
					and not exists(select 1 from  unidadevolume m where (m.un=x.un))
					and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."'
					union
		 			select upper(x.un) as un,m.descr
						from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')
						join unidadevolume m on(m.un=x.un)
						join prodserv p on(x.idprodserv=p.idprodserv)              
						where n.sped = 'Y'
						and n.tiponf ='C'
						and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
						and n.envionfe = 'CONCLUIDA'
						and n.idempresa = ".$idempresa."
						and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."'
					union
					select upper(p.un) as un,m.descr
						from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')					
						join prodserv p on(x.idprodserv=p.idprodserv)      
						join unidadevolume m on(m.un=p.un)        
						where n.sped = 'Y'
						and n.tiponf ='C'
						and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
						and n.envionfe = 'CONCLUIDA'
						and n.idempresa = ".$idempresa."
						and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."'
					union
						select upper(p.un) as un,m.descr
						from lotecons c,lote l,prodserv p, lote w,prodserv pp,unidade u,unidadevolume m
						where(p.venda = 'Y' or  p.fabricado = 'Y')
						and p.idprodserv = l.idprodserv
						and l.idlote = c.idlote
						and m.un=p.un
						and w.idempresa = ".$idempresa."
						and c.qtdd>0
						and (c.qtdd_exp='' or c.qtdd_exp is null)
						and c.idobjeto=w.idlote
						and c.tipoobjeto = 'lote'
						 and pp.venda = 'Y' and pp.fabricado = 'Y'
						and pp.tipo = 'PRODUTO'
						and w.idunidade = u.idunidade
						and w.idempresa = u.idempresa
						and u.idtipounidade in (5,3)
						and w.idprodserv = pp.idprodserv
						and w.fabricacao between  '".$this->dtInicio."' and '".$this->dtFim."'
					union
						select upper(pp.un) as un,m.descr
						from lotecons c,lote l,prodserv p, lote w,prodserv pp,unidade u,unidadevolume m
						where  -- (p.venda = 'Y' or  p.fabricado = 'Y') and
									 p.idprodserv = l.idprodserv
						and l.idlote = c.idlote
						and m.un=pp.un
						and l.idempresa = ".$idempresa."
						and w.idempresa = ".$idempresa."
						and u.idempresa = w.idempresa
						and c.idobjeto=w.idlote
						and c.tipoobjeto = 'lote'
						-- and (c.qtdd_exp ='' or c.qtdd_exp is null)
						and c.qtdd>0
						 and pp.venda = 'Y' and  pp.fabricado = 'Y'
						and pp.tipo = 'PRODUTO'
						and w.idunidade = u.idunidade
						and u.idtipounidade in (5,3)
						and w.idprodserv = pp.idprodserv
						and w.fabricacao between '".$this->dtInicio."' and '".$this->dtFim."'
					union
						select upper(ifnull(m.un,f.unforn)) as un, upper(ifnull( m.descr,f.unforn))  as descr
						from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')
						join prodserv p on(x.idprodserv=p.idprodserv) 
						join prodservforn f  on(p.idprodserv=f.idprodserv and f.status='ATIVO'  and f.valconv > 1 and f.unforn is not null)
						left join unidadevolume m on( m.un=f.unforn)
						where n.sped = 'Y'
						and n.tiponf ='C'
						and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
						and n.envionfe = 'CONCLUIDA'
						and n.idempresa = ".$idempresa."
						and n.prazo between  '".$this->dtInicio."' and '".$this->dtFim."'
						".$sqliv."
						group by un";

    	
    	 $res1=mysql_query($sql1) or die("Erro ao pesquisar unidades sql=".$sql1);
    	 $r0190=1;
		 $_190=0;
    	 while($row1=mysql_fetch_assoc($res1)){

			
			if(!in_array(rmAc($row1['un']), $arrcodUN)) {
				if(!empty($row1['un']) and !empty($row1['descr'])){
					$r0190=$r0190+1;
					$l0000=$l0000+1;				
					$_SESSION['0190'][$r0190]="|0190|".rmAc($row1['un'])."|".rmAc($row1['descr'])."|";
					echo($_SESSION['0190'][$r0190]."\n");
					$arrcodUN[$_190]=rmAc($row1['un']);
					$_190=$_190+1;
				}
			}
    	 }

		 /*
		 	unidades da conversao
		 */
/*
		 $sql1="select upper(m.un) as un,m.descr
				from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')
				join prodserv p on(x.idprodserv=p.idprodserv) 
				join prodservforn f  on(p.idprodserv=f.idprodserv and f.status='ATIVO'  and f.valconv > 1 and f.unforn is not null)
				join medida m on( m.un=f.unforn)
				where n.sped = 'Y'
				and n.tiponf ='C'
				and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
				and n.envionfe = 'CONCLUIDA'
				and n.idempresa = ".$idempresa."
				and n.prazo between  '".$this->dtInicio."' and '".$this->dtFim."' group by un";
		$res1=mysql_query($sql1) or die("Erro ao pesquisar unidades da conversao");
		
		while($row1=mysql_fetch_assoc($res1)){
			$r0190=$r0190+1;
			$l0000=$l0000+1;
			$_SESSION['0190'][$r0190]="|0190|".$row1['un']."|".$row1['descr']."|";
			echo($_SESSION['0190'][$r0190]."\n");
			
		}
*/
    	 /*
    	  * PARA SERVIÇO FOI COLOCADO O CODIGO CONSPD PARA TODOS OS SERVIÇOS
    	 * */
    	// $l0000=$l0000+1;
    	 $r0200=0;
		 $r0205=0;
    	 $arrcodprod=array();
		 $arrcodprodUN=array();
    	 $c=0;
		 $d=0;
    	 //produtos comprados não relacionados
		 $sqlpprod="select x.*
					from nf n join nfitemxml x on(x.idnf=n.idnf and (x.idprodserv is null or x.idprodserv =0) and x.status='Y')				 
					where n.sped = 'Y'
					and n.tiponf ='C'
					and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
					and n.envionfe = 'CONCLUIDA'
					and n.idempresa = ".$idempresa."
					and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
		$resprod=mysql_query($sqlpprod) or die("Erro ao buscar produtos comprados não relacionados");
		$qtdrel=mysqli_num_rows($resprod);
		if($qtdrel>0){
			$r0200=$r0200+1;
			$l0000=$l0000+1;
			$_SESSION['0200'][1]="|0200|CONSPD|Unidade|||UN|99|||00||||";
			echo($_SESSION['0200'][1]."\n");
			$r0205=$r0205+1;
			$l0000=$l0000+1;
			$_SESSION['0205'][1]="|0205|Unidade|".$this->inicio."|".$this->inicio."||";
			echo($_SESSION['0205'][1]."\n");
		}


		 //produtos comprados
		 $sqlpprod="select p.idprodserv,p.codprodserv,trim(p.descr) as descr,upper(
						CASE 
							WHEN p.un IS NOT NULL AND p.un <> '' THEN p.un
							ELSE x.un
						END 
						) as un,upper(x.un) as uncp,x.ncm,replace(dma(LEFT(p.criadoem, 10)),'/','') as criacao,p.venda,p.fabricado
					from nf n join nfitemxml x on(x.idnf=n.idnf and x.idprodserv is not null and x.status='Y')
					join prodserv p on(x.idprodserv=p.idprodserv)              
					where n.sped = 'Y'
					and n.tiponf ='C'
					and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
					and n.envionfe = 'CONCLUIDA'
					and n.idempresa = ".$idempresa."
					and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."' group by p.codprodserv,un,uncp  order by  p.codprodserv,un ";

		 $resprod=mysql_query($sqlpprod) or die("Erro ao buscar produtos comprados");
		 $r0220=0;
	
		 while($rowprod=mysql_fetch_assoc($resprod)){
		
			
			 if($rowprod['venda']=='Y'){
                $tipoativ='04';
            }elseif($rowprod['fabricado']=='Y'){
                $tipoativ='03';
            }else{
                $tipoativ='10';
            }
			 
			 if(in_array(rmAc(trim($rowprod['codprodserv'])), $arrcodprod)) {
				 echo "";

				if(in_array(rmAc(trim($rowprod['codprodserv']))."-".rmAc(trim($rowprod['un'])), $arrcodprodUN)  or empty(rmAc(trim($rowprod['un']))) ) {
					echo "";
				}else{
					$d= $d+1;
					$arrcodprodUN[$d]=rmAc(trim($rowprod['codprodserv']))."-".rmAc(trim($rowprod['un']));
					$r0220=$r0220+1;
					$l0000=$l0000+1;
					$_SESSION['0220'][$r0220]="|0220|".rmAc(trim($rowprod['un']))."|".number_format(1, 2, ',','')."||";
					echo($_SESSION['0220'][$r0220]."\n");
				}
				
				if(in_array(rmAc(trim($rowprod['codprodserv']))."-".rmAc(trim($rowprod['uncp'])), $arrcodprodUN) or empty(rmAc(trim($rowprod['uncp'])))  ) {
					echo "";
				}else{
					$d= $d+1;
					$arrcodprodUN[$d]=rmAc(trim($rowprod['codprodserv']))."-".rmAc(trim($rowprod['uncp']));
					$r0220=$r0220+1;
					$l0000=$l0000+1;
					$_SESSION['0220'][$r0220]="|0220|".rmAc(trim($rowprod['uncp']))."|".number_format(1, 2, ',','')."||";
					echo($_SESSION['0220'][$r0220]."\n");
				}

			 }else{
				 $c=$c+1;
				 $d= $d+1;
				 $r0200=$r0200+1;
				 $l0000=$l0000+1;
				 $arrcodprod[$c]=rmAc(trim($rowprod['codprodserv']));
				 $arrcodprodUN[$d]=rmAc(trim($rowprod['codprodserv']))."-".rmAc(trim($rowprod['un']));
				  
				 $_SESSION['0200'][$r0200]="|0200|".trim(rmAc($rowprod['codprodserv']))."|".trim(rmAc($rowprod['descr']))."|||".trim(rmAc($rowprod['un']))."|".$tipoativ."|".$rowprod['ncm']."||29||||";
				 echo($_SESSION['0200'][$r0200]."\n");

				$r0205=$r0205+1;
				$l0000=$l0000+1;
				$_SESSION['0205'][$r0205]="|0205|".trim(rmAc($rowprod['descr']))."|".$this->inicio."|".$this->inicio."||";
				echo($_SESSION['0205'][$r0205]."\n");


				

				 //conversao
			 	$sl="select upper(ifnull(f.unforn,p.un)) as vun,f.valconv,replace(dma(LEFT(p.criadoem, 10)),'/','') as criacao
				 from prodservforn f join prodserv p on(p.idprodserv=f.idprodserv)
			 		where f.status='ATIVO'  and f.valconv > 1
					and f.idprodserv=".$rowprod['idprodserv']." group by vun";
				$rs=mysql_query($sl) or die("Erro ao buscar conversoes dos produtos comprados");

				while($rwp=mysql_fetch_assoc($rs)){
					if(in_array(trim($rwp['codprodserv'])."-".rmAc(trim($rwp['vun'])), $arrcodprodUN) or empty(rmAc(trim($rwp['vun']))) ) {
						echo "";
					}else{
						$r0220=$r0220+1;
						$l0000=$l0000+1;
						$_SESSION['0220'][$r0220]="|0220|".rmAc(trim($rwp['vun']))."|".number_format($rwp['valconv'], 2, ',','')."||";
						echo($_SESSION['0220'][$r0220]."\n");
						$d= $d+1;
						$arrcodprodUN[$d]=trim($rwp['codprodserv'])."-".rmAc(trim($rwp['vun']));
					}
			
				}

				if($rowprod['un'] != $rowprod['uncp']){
					if(in_array(trim($rowprod['codprodserv'])."-".rmAc(trim($rowprod['uncp'])), $arrcodprodUN) or empty(rmAc(trim($rowprod['uncp']))) ) {
						echo "";
					}else{
						$d= $d+1;
						$arrcodprodUN[$d]=trim($rowprod['codprodserv'])."-".rmAc(trim($rowprod['uncp']));
						$r0220=$r0220+1;
						$l0000=$l0000+1;
						$_SESSION['0220'][$r0220]="|0220|".rmAc(trim($rowprod['uncp']))."|".number_format(1, 2, ',','')."||";
						echo($_SESSION['0220'][$r0220]."\n");
					}	

				}

			 }
			
			
				
		 }


 //buscar ordens de produção com os produtos utilizados
    	 $sqllt="select sum(c.qtdd) as qtd,c.qtdd_exp,upper(pp.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv,
                        pp.codprodserv as codprodservprod,rtrim(ltrim(
							CASE 
								WHEN pp.descrcurta IS NOT NULL AND pp.descrcurta <> '' THEN pp.descrcurta
								ELSE pp.descr
							END 
						)) as descr,
						pp.venda,pp.fabricado,pp.ncm,replace(dma(LEFT(p.criadoem, 10)),'/','') as criacao
			from lotecons c,lote l,prodserv p, lote w,prodserv pp,unidade u
			where p.comprado = 'Y' and
                p.idprodserv = l.idprodserv
			and l.idlote = c.idlote
			and l.idempresa = ".$idempresa."
            and w.idempresa = ".$idempresa."
			and u.idempresa = w.idempresa
			and c.idobjeto=w.idlote
			and c.tipoobjeto = 'lote'
			-- and (c.qtdd_exp ='' or c.qtdd_exp is null)
			and c.qtdd>0
			 and pp.venda = 'Y' and  pp.fabricado = 'Y'
			and pp.tipo = 'PRODUTO'
			 and w.idunidade = u.idunidade
			and u.idtipounidade in (5,3)
			and w.idprodserv = pp.idprodserv
			and w.fabricacao between '".$this->dtInicio."' and '".$this->dtFim."'
			 group by p.codprodserv,pp.codprodserv,un order by codprodservprod,un";
    	 $reslt=mysql_query($sqllt) or die("Erro ao buscar ordens de produção com os produtos utilizados");
			 
      	 $r0210=0;
    	
    	 while($rowlt=mysql_fetch_assoc($reslt)){
            if($rowlt['venda']=='Y'){
                $tipoativ='04';
            }elseif($rowlt['fabricado']=='Y'){
                $tipoativ='03';
            }else{
                $tipoativ='10';
            }
    	 	
            if(in_array(trim($rowlt['codprodservprod']), $arrcodprod)) {
                    echo "";
                     if( $codprodservant!=$rowlt['codprodservprod']){
                         $passa=0;
                     }
            }else{
                    $codprodservant=$rowlt['codprodservprod'];
                    $passa=1;
                    $c=$c+1;
                    $r0200=$r0200+1;
                    $l0000=$l0000+1;
                    $arrcodprod[$c]=trim($rowlt['codprodservprod']); 	 	

                    $_SESSION['0200'][$r0200]="|0200|".trim(rmAc($rowlt['codprodservprod']))."|".trim(rmAc($rowlt['descr']))."|||".trim(rmAc($rowlt['un']))."|".$tipoativ."|".$rowlt['ncm']."||29||||";
                    echo($_SESSION['0200'][$r0200]."\n");

					$r0205=$r0205+1;
					$l0000=$l0000+1;
					$_SESSION['0205'][$r0205]="|0205|".trim(rmAc($rowlt['descr']))."|".$this->inicio."|".$this->inicio."||";
					echo($_SESSION['0205'][$r0205]."\n");
            }
            
			/*
            if($passa==1 and $rowlt['fabricado']=='Y'){

					$rowlt['qtd_exp']=recuperaExpoente(tratanumero($rowlt["qtd"]), $rowlt["qtdd_exp"]);

					if(strpos(strtolower($rowlt['qtd_exp']),"d")){
						$arrvlr=explode("d",$rowlt['qtd_exp']);
						$rowlt['qtd_exp']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
					  
					}elseif(strpos(strtolower($rowlt['qtd_exp']),"e")){
						$arrvlr=explode("e",$rowlt['qtd_exp']);
						$rowlt['qtd_exp']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
					  
					} 

                    $r0210=$r0210+1;
                    $l0000=$l0000+1;
                    $_SESSION['0210'][$r0210]="|0210|".$rowlt['codprodserv']."|".number_format($rowlt['qtd_exp'], 2, ',','')."|0|";
                    echo($_SESSION['0210'][$r0210]."\n");
            }
			*/
    	 }
     
   	 
    	 //produtos na fabrica de outros produtos
       	 $sqlpprod="select upper(p.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv,rtrim(ltrim(
			CASE 
					WHEN p.descrcurta IS NOT NULL AND p.descrcurta <> '' THEN p.descrcurta
					ELSE p.descr
				END 
		 )) as descr,p.fabricado,p.venda,p.ncm,replace(dma(LEFT(p.criadoem, 10)),'/','') as criacao
			from lotecons c,lote l,prodserv p, lote w,prodserv pp,unidade u
			where p.comprado = 'Y'
                        and p.idprodserv = l.idprodserv
			and l.idlote = c.idlote
            and w.idempresa = ".$idempresa."
			and c.qtdd>0
			and (c.qtdd_exp='' or c.qtdd_exp is null)
			and c.idobjeto=w.idlote
			and c.tipoobjeto = 'lote'
			 and pp.venda = 'Y'
			and pp.tipo = 'PRODUTO'
            and w.idunidade = u.idunidade
			and w.idempresa = u.idempresa
			and u.idtipounidade in (5,3)
			and w.idprodserv = pp.idprodserv
			and w.fabricacao between  '".$this->dtInicio."' and '".$this->dtFim."'
			 group by p.codprodserv,un";
       	 $resprod=mysql_query($sqlpprod) or die("Erro ao produtos na fabrica de outros produtos");
       	
       	 while($rowprod=mysql_fetch_assoc($resprod)){
          //  if($rowprod['venda']=='Y' and !empty($rowprod['ncm'])){
		/*	if($rowprod['venda']=='Y'){
                $tipoativ='04';
            }elseif($rowprod['fabricado']=='Y'){
                $tipoativ='03';
            }else{
			*/	
                $tipoativ='10';
         //}

       	 	if(in_array(trim($rowprod['codprodserv']), $arrcodprod)) {
       	 		echo "";
					if(in_array(trim($rowprod['codprodserv'])."-".trim($rowprod['un']), $arrcodprodUN) or empty(rmAc(trim($rowprod['un']))) ) {
						echo "";
					}else{
						$d= $d+1;
						$arrcodprodUN[$d]=trim($rowprod['codprodserv'])."-".trim($rowprod['un']);
						$r0220=$r0220+1;
						$l0000=$l0000+1;
						$_SESSION['0220'][$r0220]="|0220|".rmAc(trim($rowprod['un']))."|".number_format(1, 2, ',','')."||";
						echo($_SESSION['0220'][$r0220]."\n");
					}
       	 	}else{
       	 		$c=$c+1;
       	 		$r0200=$r0200+1;
       	 		$l0000=$l0000+1;
       	 		$arrcodprod[$c]=$rowprod['codprodserv'];
       	 		 
       	 		$_SESSION['0200'][$r0200]="|0200|".trim(rmAc($rowprod['codprodserv']))."|".trim(rmAc($rowprod['descr']))."|||".trim(rmAc($rowprod['un']))."|".$tipoativ."|".$rowprod['ncm']."||29||||";
       	 		echo($_SESSION['0200'][$r0200]."\n");
				
				$r0205=$r0205+1;
				$l0000=$l0000+1;
				$_SESSION['0205'][$r0205]="|0205|".trim(rmAc($rowprod['descr']))."|".$this->inicio."|".$this->inicio."||";
				echo($_SESSION['0205'][$r0205]."\n");
       	 	}
       	 	
       	 }
/*
	//buscar ordens de produção com os produtos utilizados
	$sqllt="select sum(c.qtdd) as qtd,upper(pp.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv,
				pp.codprodserv as codprodservprod,rtrim(ltrim(ifnull(p.descrcurta,p.descr))) as descr,p.fabricado,p.venda,p.ncm
			from lotecons c,lote l,prodserv p, lote w,prodserv pp,unidade u
			where p.comprado='Y'
			and p.idprodserv = l.idprodserv
			and l.idlote = c.idlote
			and l.idempresa = ".$idempresa."
			and w.idempresa = ".$idempresa."
			and u.idempresa = w.idempresa
			and c.idobjeto=w.idlote
			and c.tipoobjeto = 'lote'
			-- and (c.qtdd_exp ='' or c.qtdd_exp is null)
			and c.qtdd>0
			 and pp.venda = 'Y' and  pp.fabricado = 'Y'
			and pp.tipo = 'PRODUTO'
			and w.idunidade = u.idunidade
			and u.idtipounidade in (5,3)
			and w.idprodserv = pp.idprodserv
			and w.fabricacao between '".$this->dtInicio."' and '".$this->dtFim."'
			group by p.codprodserv order by p.codprodserv";
   $reslt=mysql_query($sqllt) or die("Erro ao buscar ordens de produção com os produtos utilizados");
	   
	 $r0210=0;
  
   while($rowlt=mysql_fetch_assoc($reslt)){
		if($rowlt['venda']=='Y'){
			$tipoativ='04';
		}elseif($rowlt['fabricado']=='Y'){
			$tipoativ='03';
		}else{
			$tipoativ='10';
		}
	   
	  if(in_array($rowlt['codprodserv'], $arrcodprod)) {
			  echo "";
			   if( $codprodservant!=$rowlt['codprodserv']){
				   $passa=0;
			   }
	  }else{
			  $codprodservant=$rowlt['codprodserv'];
			  $passa=1;
			  $c=$c+1;
			  $r0200=$r0200+1;
			  $l0000=$l0000+1;
			  $arrcodprod[$c]=$rowlt['codprodserv']; 	 	

			  $_SESSION['0200'][$r0200]="|0200|".$rowlt['codprodserv']."|".$rowlt['descr']."|||".$rowlt['un']."|".$tipoativ."|".$rowlt['ncm']."||29||||";
			  echo($_SESSION['0200'][$r0200]."\n");
	  }

   }

 */    	    	 
    	
    	 
    	 if($this->inventario=='Y'){

    	 	//TODOS OS PRODUTOS DE COMPRADOS PARA INSUMO E TODOS OS DE VENDA FABRICADOS EM ESTOQUE
			$sql1="select upper(p.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv,
			rtrim(ltrim(
				CASE 
					WHEN p.descrcurta IS NOT NULL AND p.descrcurta <> '' THEN p.descrcurta
					ELSE p.descr
				END 
			)) as descr,p.venda,p.ncm,replace(dma(LEFT(p.criadoem, 10)),'/','') as criacao
					from spedh010 s join  prodserv p on(p.idprodserv=s.idprodserv)
			where s.idempresa = ".$idempresa." 
			and s.mes='12'
			and s.ano='".$this->anoinventario."' 
			and s.status='ATIVO' group by s.idprodserv";

					
	    	 
	    	 $res1=mysql_query($sql1) or die("Erro ao pesquisar unidades 0200");
	    	 //$r0200=1;
	    	 while($row1=mysql_fetch_assoc($res1)){
                     
                    if($row1['venda']=='Y'){
                        $tipoativ='04';
                    }else{
                        $tipoativ='10';
                    }
	    	 	
                    if(in_array($row1['codprodserv'], $arrcodprod)) {
                           echo "";
                    }else{
						$c=$c+1;
						$r0200=$r0200+1;
						$l0000=$l0000+1;
						$arrcodprod[$c]=$rowprod['codprodserv'];

						$_SESSION['0200'][$r0200]="|0200|".trim(rmAc($row1['codprodserv']))."|".trim(rmAc($row1['descr']))."|||".trim(rmAc($row1['un']))."|".$tipoativ."|".$row1['ncm']."||29||||";
						echo($_SESSION['0200'][$r0200]."\n");
						   	
						$r0205=$r0205+1;
						$l0000=$l0000+1;
						$_SESSION['0205'][$r0205]="|0205|".trim(rmAc($row1['descr']))."|".$row1['criacao']."|".$row1['criacao']."||";
						echo($_SESSION['0205'][$r0205]."\n");
                    }
		    	 
	    	 }
    	 }
    	$l0000=$l0000+1;
    	$_SESSION['0990']="|0990|".$l0000."|";
    	echo($_SESSION['0990']."\n");
    }
    
    public function bloco_B(){
     	$_SESSION['B001']="|B001|1|\n";
     	echo($_SESSION['B001']);
     	$_SESSION['B990']="|B990|2|\n";	
     	echo($_SESSION['B990']);	
    }
    
    //BLOCO C - Nota Fiscal Eletronica (codigo 55)
    public function bloco_C($idempresa){
    	
    	//$sql="select idempresa as id  from xmlnf where mes = 'fevereiro' and tipo = 'P' and idempresa=".$idempresa;
    	
    	$sql="select idempresa as id,xmlret as xml,tiponf,faticms
				from nf 
				where sped = 'Y'
				and tiponf ='V' 
				and tpnf !='0'
			 	and status in ('CONCLUIDO','ENVIADO','ENVIAR','DEVOLVIDO')
			 	and envionfe = 'CONCLUIDA'
			 	and idempresa = ".$idempresa."
				and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'
		UNION ALL 
		select idempresa as id,xmlret as xml,tiponf,faticms
				from nf 
				where sped = 'Y'
				and tiponf ='C'
			 	and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			 	and envionfe = 'CONCLUIDA'
			 	and idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";    	
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
    	$qtdnf = mysql_num_rows($res);  	
    	if($qtdnf>0){
    		$qtdc001=0;
    	}else{
    		$qtdc001=1;
    	}
    	$_SESSION['C001']="|C001|".$qtdc001."|\n";
      	echo($_SESSION['C001']);    	
    	
    	//$sql="select idempresa as id,xml from xmlnf where mes = 'fevereiro' and tipo = 'P' and idempresa=".$idempresa;  
    	$sql="select idempresa as id,xmlret as xml,tiponf,faticms,consumo,imobilizado,outro,comercio,idnf,nnfe,DATE_FORMAT(prazo,'%d%m%Y') as prazo, 
				case icmscpl when 0.00 then null when icmscpl then icmscpl end as icmscpl
				,CASE
					WHEN faticms = 'Y' THEN 'faticms'
					WHEN consumo = 'Y' THEN 'consumo'
					WHEN imobilizado = 'Y' THEN 'imobilizado'
					WHEN comercio = 'Y' THEN 'comercio'
					ELSE 'outro'
				END as tipoconsumo,idpessoa
				from nf 
				where sped = 'Y'
				and tiponf ='V'
				and tpnf !='0'
			 	and status in ('CONCLUIDO','ENVIADO','ENVIAR','DEVOLVIDO')
			 	and envionfe = 'CONCLUIDA'
			 	and idempresa = ".$idempresa."
				and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'
		UNION ALL 
		select idempresa as id,xmlret as xml,tiponf,faticms,consumo,imobilizado,outro,comercio,idnf,nnfe,DATE_FORMAT(prazo,'%d%m%Y') as prazo, 
				case icmscpl when 0.00 then null when icmscpl then icmscpl end as icmscpl
				,CASE
					WHEN faticms = 'Y' THEN 'faticms'
					WHEN consumo = 'Y' THEN 'consumo'
					WHEN imobilizado = 'Y' THEN 'imobilizado'
					WHEN comercio = 'Y' THEN 'comercio'
					ELSE 'outro'
				END as tipoconsumo,idpessoa
				from nf 
				where sped = 'Y'
				and tiponf ='C'
			 	and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			 	and envionfe = 'CONCLUIDA'
			 	and idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";       	
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
    	$qtdnf = mysql_num_rows($res);
    		$C001=1;
    		$C100 = 0;	
    		$C101 = 0;
    	   	$C190 = 0;
    	   	$C170=0;
    	   	$n=0;
    	   	$this->vlrTotalIcms=0;
    	   	$this->vlrTotalded=0;
    	   	$arrnumnf= array();
      		while ($row=mysql_fetch_assoc($res)){
      			//echo($row['idnf']."\n");
      			$C100=$C100+1;
                $n=$n+1;
                if($row['tiponf']=='C'){
                    $sql0="select * from spedc100 where status in('ATIVO','CORRIGIDO') and  idnf =".$row['idnf'];
                    $res0=d::b()->query($sql0);
                    $qtd0=mysqli_num_rows($res0);
                }

                if($row['tiponf']=='C' and $qtd0>0){
                    $inidnf= $row['idnf']; 

                    $sql0="select * from spedc100 where status in('ATIVO','CORRIGIDO') and  idnf =".$inidnf;
                    $res0=d::b()->query($sql0);
                    $qtd0=mysqli_num_rows($res0);
                    if($qtd0<1){die('Necessario carregar as informações do sped idnf='.$inidnf);}
                    $row0=mysqli_fetch_assoc($res0);

                    $vtpNF=$row0['vtpnf'];
                    $IND_EMIT=$row0['indemit'];
                    $vCNPJ=$row0['vcnpj'];
                    $vmod=$row0['vmod'];
                    $vserie=$row0['vserie'];
                    $vnNF=$row0['vnnf'];
                    $vchNFe=$row0['vchnfe'];
                    $vdEmi=$row0['vdemi'];
                    $vdEntrada=$row0['vdentrada'];
                    $vvNF=number_format($row0['vvnf'], 2, ',','');
                    $vvDesc=number_format($row0['vvdesc'], 2, ',','');
                    $vvProd=number_format($row0['vvprod'], 2, ',','');
                    $vmodFrete=$row0['vmodfrete'];
                    $vvFrete=number_format($row0['vvfrete'], 2, ',','');
                    $vvSeg=number_format($row0['vvseg'], 2, ',','');
                    $vvOutro=number_format($row0['vvoutro'], 2, ',','');
                    $vvBC =number_format($row0['vvbc'], 2, ',','');
                    $vvtICMS=number_format($row0['vvticms'], 2, ',','');
                    $vvIPI=number_format($row0['vvipi'], 2, ',','');
                    $vvPIS=number_format($row0['vvpis'], 2, ',','');
                    $vvCOFINS=number_format($row0['vvcofins'], 2, ',','');
					
					if($row['tiponf']=='C'){
						$vdEntrada=$row['prazo'];
					}

					//if(empty($vCNPJ)){
						$vCNPJ=$row['idpessoa'];
					//}
					

					if($vmod=='65'){$vtpNF=1;};

                    $_SESSION['C100'][$C100]="|C100|".$vtpNF."|".$IND_EMIT."|".$vCNPJ."|".$vmod."|00|".$vserie."|".$vnNF."|".$vchNFe."|".$vdEmi."|".$vdEntrada."|".$vvNF."|1|".$vvDesc."||".$vvProd."|".$vmodFrete."|".$vvFrete."|".$vvSeg."|".$vvOutro."|".$vvBC ."|".$vvtICMS."|||".$vvIPI."|".$vvPIS."|".$vvCOFINS."|||\n";
                    //Duvidas IND_EMIT COD_PART COD_SIT DT_E_S 	IND_PGTO  VL_ABAT_NT VL_ICMS_ST VL_PIS_ST VL_COFINS_ST
                    echo($_SESSION['C100'][$C100]);


                    $sql1="select * from spedc101 where status in('ATIVO') and  idnf =".$inidnf;
                    $res1=d::b()->query($sql1);
                    $qtd1=mysqli_num_rows($res1);
                    if($qtd1>0){
                        $row1=mysqli_fetch_assoc($res1);
                                    
                        $_SESSION['uf'][$row1['vufdest']]=$_SESSION['uf'][$row1['vufdest']]+$row1['vvICMSUFDest'];
                        $_SESSION['uf']['MG']=$_SESSION['uf']['MG']+$row1['vvICMSUFRemet'];
                        
                        $vvFCPUFDest =number_format($row['vvFCPUFDest'], 2, ',','');
                        $vvICMSUFDest =number_format($row['vvICMSUFDest'], 2, ',','');
                        $vvICMSUFRemet =number_format($row['vvICMSUFRemet'], 2, ',','');

                        $C101=$C101+1;
                        $_SESSION['C101'][$C101]="|C101|".$vvFCPUFDest."|".$vvICMSUFDest."|".$vvICMSUFRemet."|\n";		    			
                        echo($_SESSION['C101'][$C101]);   
                        
                    }//if($qtd<1){
				
					if($vmod!='65'){


						$sql7="select p.codprodserv,upper(x.un) as  un,c.* from spedc170 c 
						left join nfitemxml x on(c.vxprod=x.prodservdescr and c.vqcom=x.qtd and x.idnf=c.idnf and x.status='Y')
						left join prodserv p on(p.idprodserv=x.idprodserv)
						where c.status in('ATIVO') and  c.idnf =".$inidnf." group by c.nitem order by c.nitem";
						$res7=d::b()->query($sql7);
						$nitem=0;
						while($row170=mysqli_fetch_assoc($res7)){
							$C170=$C170+1;
							$nitem++;
							if($nitem<10){
								$stritem = "00";
							}elseif($nitem>=10 and $nitem <100){
							$stritem = "0";
							}else{
							$stritem ="";
							}

							//$nitem=$row170['nitem'];
							$vxProd=trim($row170['vxprod']);
							$vqCom=number_format($row170['vqcom'], 2, ',','');
							$vlrtotalitem=number_format($row170['vlrtotalitem'], 2, ',','');
							$vvDesc=number_format($row170['vvdesc'], 2, ',','');
							$CST_ICMS=$row170['csticms'];
							$vCFOP=$row170['vcfop'];
							$vvBCicms=number_format($row170['vvbcicms'], 2, ',','');
							$vpICMS=number_format($row170['vpicms'], 2, ',','');
							$vvICMS=number_format($row170['vvicms'], 2, ',','');
							$vCSTipi=$row170['vcstipi'];
							//$row170['vipi'];
							//$row170['vbcipi'];
							//$row170['vpipi'];
							$vCSTpis=$row170['vcstpis'];
							$vvBCpis=number_format($row170['vvbcpis'], 2, ',','');
							$vpPIS=number_format($row170['vppis'], 2, ',','');
							$vvPISitem=number_format($row170['vvpisitem'], 2, ',','');
							$vCSTcofins=$row170['vcstcofins'];
							$vvBCcofins=number_format($row170['vvbccofins'], 2, ',','');
							$vpCOFINS=number_format($row170['vpcofins'], 2, ',','');
							$vvCOFINSitem=number_format($row170['vvcofinsitem'], 2, ',','');

							if(empty($row170['codprodserv'])){
								$codprodserv='CONSPD';
								$codun='UN';
							}else{
								$codprodserv=$row170['codprodserv'];
								$codun=rmAc($row170['un']);
							}

							//$_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|".$vcProd."|".$vxProd."|".$vqCom."|".$vuTrib."|".$vvUnCom."|".$vvDesc."|0|"/*CST_ICMS*/."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."||\n";
							$_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|".trim(rmAc($codprodserv))."|".rmAc($vxProd)."|".$vqCom."|".$codun."|".$vlrtotalitem."|".$vvDesc."|0|".$CST_ICMS."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."|||\n";
							echo($_SESSION['C170'][$C170]);
						}

						$sql9="select * from spedc190 where status in('ATIVO') and  idnf =".$inidnf;
						$res9=d::b()->query($sql9);
						while($rowi=mysqli_fetch_assoc($res9)){
							$C190=$C190+1;
							//$_SESSION['C190'][$C190]="|C190|".$strST."|".$vCFOP."|".$vpICMS."|".$vlrOper."|".$vvBC."|".$vvtICMS."|0,00|0,00|".$vlrRedBc."|".$vvIPI."||\n";
							$_SESSION['C190'][$C190]="|C190|".$rowi['st']."|".$rowi['cfop']."|".number_format($rowi['aliqicms'], 2, ',','')."|".number_format($rowi['vlopr'], 2, ',','')."|".number_format($rowi['vlbcicms'], 2, ',','')."|".number_format($rowi['vlicms'], 2, ',','')."|0,00|0,00|".number_format($rowi['vlredbc'], 2, ',','')."|".number_format($rowi['vlipi'], 2, ',','')."||\n";
							echo($_SESSION['C190'][$C190]);

							$faticms=traduzid('nf', 'idnf', 'faticms', $inidnf);

							if($faticms=='Y' and($rowi['cfop']=='2101' or $rowi['cfop']=='1102' or $rowi['cfop']=='1101')){
								$this->vlrTotalded = $this->vlrTotalded+$rowi['vlicms'];
								$this->vlrTotalIPIded = $this->vlrTotalIPIded + $rowi['vlipi'];

							}
						}
					}
                 
                }else{
                  
      			// passar string para UTF-8
      			$xml=$row['xml'];
      			
      			//Carregar o XML em UTF-8      			
      			$doc = DOMDocument::loadXML($xml);	

      			//inicia lendo as principais tags do xml da nfe
	      		$nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
				if($nfeProc!=null){
					$NFe = $nfeProc->getElementsByTagName("NFe")->item(0);
				}else{
					$NFe = $doc->getElementsByTagName("NFe")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
				}
	      			      		
	      		$infNFe = $NFe->getElementsByTagName("infNFe")->item(0); 
	      		//BUSCAR A CHAVE DA NFE
	      		$chave= $infNFe->getAttribute("Id");	      		
	      		$vchNFe=substr($chave,3);

	      		if($row['tiponf']=='C'){
	      			//buscando informações do destinatario / emitente
	      			$dest = $infNFe->getElementsByTagName("emit")->item(0);
	      		}else{
	      			//buscando informações do destinatario / Participante
	      			$dest = $infNFe->getElementsByTagName("dest")->item(0);
	      			
	      		}	      
	      			      		
			    //COD_PART 
			    $CNPJ = $dest->getElementsByTagName("CNPJ")->item(0); 			    
			    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
			    
			    if(empty($CNPJ) or empty($vCNPJ)){
			    	 //COD_PART 
				    $CNPJ = $dest->getElementsByTagName("CPF")->item(0); 			    
				    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
			    }
      			
			    
			    $indIEDest = $dest->getElementsByTagName("indIEDest")->item(0);
			    $vindIEDest =($indIEDest->textContent);
			    
	      		//buscando informações basicas
	      		$ide = $infNFe->getElementsByTagName("ide")->item(0); 
	      		$total = $infNFe->getElementsByTagName("total")->item(0);//Totais
	      		$transp = $infNFe->getElementsByTagName("transp")->item(0);//Transp
	      		
	      		//############TRANSPORTE###########
	      		//	IND_FRT modo de transporte
	      		$modFrete = $transp->getElementsByTagName("modFrete")->item(0);//Transp
	      		$vmodFrete =($modFrete->textContent); //IND_FRT     		
	     	      		
	      		//IND_OPER entrada ou saida**********
	      		$tpNF = $ide->getElementsByTagName("tpNF")->item(0);
	      		$vtpNF =($tpNF->textContent); 
	      		//NF de compra o tipo e 0
	      		if($row['tiponf']=='C'){
	      			$vtpNF='0';
	      			$IND_EMIT='1';
	      		}else{
	      			$IND_EMIT='0';
	      		}
	      		
	      		//Serie****************
	      		$serie = $ide->getElementsByTagName("serie")->item(0);
	      		$vserie =($serie->textContent); //pegar o valor da tag <chNFe> 
	      		//COD_MOD *************
	      		$mod = $ide->getElementsByTagName("mod")->item(0);
	      		$vmod =($mod->textContent); //pegar o valor da tag <chNFe> 
	      		//NUM_DOC numero da NF
	      		$nNF = $ide->getElementsByTagName("nNF")->item(0);
	      		$vnNF =($nNF->textContent); //pegar o valor da tag <chNFe> 
	      		//data emissao
	      		$dEmi = $ide->getElementsByTagName("dEmi")->item(0);
	      		$vdEmi =($dEmi->textContent); //pegar o valor da tag <chNFe> 
	      		if(!empty($vdEmi)){
	      			
	      			//2013-01-14
	      			$dia = substr($vdEmi, -2, 2); // retorna "de"
	      			$mes = substr($vdEmi, -5, -3); // retorna "de"
	      			$ano = substr($vdEmi, 0, -6); // retorna "de"
	      			$vdEmi=$dia.$mes.$ano;
	      			
	      			$dataxml = $ano."-".$mes."-".$dia;	      				      			
	      			//função para comparar se a primeira data e maior que a segunda
	      			$retcomparadata=comparadata($this->dtInicio,$dataxml);
	      			
                                if($row['tiponf']=='C'){
                                    $vdEntrada=$row['prazo'];
                                }elseif($retcomparadata=='S'){
                                    $vdEntrada=$this->inicio;
	      			}else{
                                    $vdEntrada=$vdEmi;
	      			}
	      			
	      			
	      			
	      		}else{
	      			//data emissao
	      			$dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
	      			$vdEmi =($dEmi->textContent); //pegar o valor da tag <chNFe>
	      			
	      			//2014-10-08T15:47:00-03:00	      			
	      			$ano = substr($vdEmi,0,-21); // retorna "de"
	      			$mes = substr($vdEmi,5,-18); // retorna "de"
	      			$dia = substr($vdEmi,8,-15); // retorna "de"

	      			$vdEmi=$dia.$mes.$ano;
	      			// colocar a data de entrada no mesmo mês do sped caso ela seja menor que o início do sped
	      			$dataxml = $ano."-".$mes."-".$dia;
	      			//função para comparar se a primeira data e maior que a segunda
	      			$retcomparadata=comparadata($this->dtInicio,$dataxml);
	      			
	      			if($row['tiponf']=='C'){
						$vdEntrada=$row['prazo'];
					}elseif($retcomparadata=='S'){
						$vdEntrada=$this->inicio;
	      			}else{
						$vdEntrada=$vdEmi;
	      			}
	      		}
	      		
	      		//VL_DOC TOTAL DA NF
	      		$ICMSTot = $total->getElementsByTagName("ICMSTot")->item(0);//Totais		

	      		//VL_DESC valor do desconto
	      		if($row['tiponf']=='C'){
				$vDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais	
	      		$vvDesc =($vDesc->textContent); 
	      		$vvDesc=number_format($vvDesc, 2, ',','');
	      		}else{
      			/*$vDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais
      			$vvDesc =($vDesc->textContent);
      			$vvDesc=number_format($vvDesc, 2, ',','');*/
      			$vvDesc =number_format('0,00', 2, ',','');
	      		}
	      		
	      		//VL_MERC valor total da mercadoria
	      		$vProd= $ICMSTot->getElementsByTagName("vProd")->item(0);//Totais	
	      		$nvProd =($vProd->textContent); 
	      		$vvProd =number_format($nvProd, 2, ',','');
	      		//VL_FRT valor total do frete********************
	      		$vFrete= $ICMSTot->getElementsByTagName("vFrete")->item(0);//Totais	
	      		$nvFrete =($vFrete->textContent); 
	      		$vvFrete =number_format($nvFrete, 2, ',','');
	      		// valor da base de calculo VL_BC_ICMS
	      		$vBC= $ICMSTot->getElementsByTagName("vBC")->item(0);//Totais	
	      		$nvBC =($vBC->textContent);
	      		$vvBC =number_format($nvBC, 2, ',','');
	      		
	      		//valor do ICMS VL_ICMS
	      		$vtICMS= $ICMSTot->getElementsByTagName("vICMS")->item(0);//Totais	
	      		$nvtICMS =($vtICMS->textContent);
	      		$vvtICMS =number_format($nvtICMS, 2, ',','');
	      		
	      		//Valor da base de calculo do ICMS substituição tributaria VL_BC_ICMS_ST
	      		$vBCST= $ICMSTot->getElementsByTagName("vBCST")->item(0);//Totais	
	      		$vvBCST =($vBCST->textContent);
	      		$vvBCST =number_format($vvBCST, 2, ',','');

	      		//Valor total do IPI 	VL_IPI
	      		$vIPI= $ICMSTot->getElementsByTagName("vIPI")->item(0);//Totais	
	      		$nvIPI =($vIPI->textContent);
	      		$vvIPI =number_format($nvIPI, 2, ',','');
	      		
	      		//Valor do seguro indicado no documento fiscal VL_SEG
	      		$vSeg= $ICMSTot->getElementsByTagName("vSeg")->item(0);//Totais	
	      		$vvSeg =($vSeg->textContent);	
	      		$vvSeg =number_format($vvSeg, 2, ',','');   

	      		//Valor de outras despesas acessorias fiscal VL_OUT_DA
	      		$vOutro= $ICMSTot->getElementsByTagName("vOutro")->item(0);//Totais	
	      		$vvOutro =($vOutro->textContent);	
	      		$vvOutro =number_format($vvOutro, 2, ',','');   	      		
	      		
	      		// Valor total do PIS VL_PIS
	      		$vPIS= $ICMSTot->getElementsByTagName("vPIS")->item(0);//Totais	
	      		$vvPIS =($vPIS->textContent);
	      		$vvPIS =number_format($vvPIS, 2, ',','');
	      		
	      		// Valor total da COFINS VL_COFINS
	      		$vCOFINS= $ICMSTot->getElementsByTagName("vCOFINS")->item(0);//Totais	
	      		$vvCOFINS =($vCOFINS->textContent);
	      		$vvCOFINS =number_format($vvCOFINS, 2, ',','');
	      		
	      		//TOTAL DA NF *****************	
	      		$vNF= $ICMSTot->getElementsByTagName("vNF")->item(0);//Totais		
	      		$nvNF =($vNF->textContent); 
	      		
	      		
	      		//Buscando a chave da NFE
	      		//ALTERADO PARA BUSCAR NO ATRIBUTO A CHAVE DA NFE 21102014 HERMES
	      		/*$protNFe = $nfeProc->getElementsByTagName("protNFe")->item(0); // pegar a tag <protNFe> detro de <nfeProc>
	      		$infProt = $protNFe->getElementsByTagName("infProt")->item(0); //pegar a tag <infProt> dentro de  <protNFe>

	      		//Chave NFE ****************
	      		$chNFe = $infProt->getElementsByTagName("chNFe")->item(0); //pegar a tag <chNFe> dentro de <infProt>
	      		$vchNFe =($chNFe->textContent); //pegar o valor da tag <chNFe>
	      		*/
	      		
	      		//verifica quantos itens existem no xml da notafiscal
	      		$qtditem  =$infNFe->getElementsByTagName('det')->length;
	      		
	      		

	      		// Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
	      		if(($row['consumo']=="Y" or $row['imobilizado']=='Y' or $row['outro']=='Y' or $row['comercio']=='Y')  and ($row['tiponf'] !='V')){
	      		
	      			$vvOutro =number_format('0,00', 2, ',','');
	      			$vvBC =number_format('0,00', 2, ',','');
	      			$vvtICMS =number_format('0,00', 2, ',','');
	      			$vvIPI=number_format('0,00', 2, ',','');
	      			$vvPIS =number_format('0,00', 2, ',','');
	      			$vvCOFINS =number_format('0,00', 2, ',','');
	      		}
	      		
	      		if(!empty($row['icmscpl'])){
	      			
	       			$vvtICMS =(($nvNF * $row['icmscpl'])/100);
	      			$vvtICMS =number_format($vvtICMS, 2, ',','');
	      			$vvBC=number_format($nvNF, 2, ',','');
	      		}
    			 /*  
                        if($vtpNF==1){//ativado por causa da desoneração
                            // VL_DOC valor total do documento                                 
                            $vlrOper=$nvProd+$nvFrete;
                            $vvNF =number_format($vlrOper, 2, ',',''); //$vnNF   
                            //$vvProd =number_format($vlrOper, 2, ',',''); //$vvProd
	      				      			
	      		}else{// usado para notas de compra
                        */
                            $vvNF =number_format($nvNF, 2, ',','');
                       // }
                            
				if($vserie=='890' or $vserie=='893'){//ser for serie 890 lançar como nota fiscal
					$vmod='01';
					$vchNFe='';
				}
				if($vmod=='65'){$vtpNF=1;};

				//if(empty($vCNPJ)){
					$vCNPJ=$row['idpessoa'];
				//}
	      		
	      		$_SESSION['C100'][$C100]="|C100|".$vtpNF."|".$IND_EMIT."|".$vCNPJ."|".$vmod."|00|".$vserie."|".$vnNF."|".$vchNFe."|".$vdEmi."|".$vdEntrada."|".$vvNF."|1|".$vvDesc."||".$vvProd."|".$vmodFrete."|".$vvFrete."|".$vvSeg."|".$vvOutro."|".$vvBC ."|".$vvtICMS."|||".$vvIPI."|".$vvPIS."|".$vvCOFINS."|||\n";
	      		//Duvidas IND_EMIT COD_PART COD_SIT DT_E_S 	IND_PGTO  VL_ABAT_NT VL_ICMS_ST VL_PIS_ST VL_COFINS_ST
    			echo($_SESSION['C100'][$C100]);
    			
    			
    			if($vindIEDest==9){
    			
    				
	    			$vFCPUFDest= $ICMSTot->getElementsByTagName("vFCPUFDest")->item(0);//Totais
	    			$vvFCPUFDest =($vFCPUFDest->textContent);
	    			$vICMSUFDest= $ICMSTot->getElementsByTagName("vICMSUFDest")->item(0);//Totais
	    			$vvICMSUFDest =($vICMSUFDest->textContent);
	    			$vICMSUFRemet= $ICMSTot->getElementsByTagName("vICMSUFRemet")->item(0);//Totais
	    			$vvICMSUFRemet =($vICMSUFRemet->textContent);
	    			
	    			$enderDest = $dest->getElementsByTagName("enderDest")->item(0);
	    			$UFDest = $enderDest->getElementsByTagName("UF")->item(0);
	    			$vUFDest =($UFDest->textContent);
	    			
	    			
	    			if(!empty($vvICMSUFRemet) and !empty($vvICMSUFDest) and !empty($vUFDest) and $vvICMSUFDest >0){
	    				
	    				
	    				
	    				$_SESSION['uf'][$vUFDest]=$_SESSION['uf'][$vUFDest]+$vvICMSUFDest;
	    				$_SESSION['uf']['MG']=$_SESSION['uf']['MG']+$vvICMSUFRemet;
	    				
	    				$vvFCPUFDest =number_format($vvFCPUFDest, 2, ',','');
	    				$vvICMSUFDest =number_format($vvICMSUFDest, 2, ',','');
	    				$vvICMSUFRemet =number_format($vvICMSUFRemet, 2, ',','');
	    					    				
		    			
		    					    			
		    			$C101=$C101+1;
		    			$_SESSION['C101'][$C101]="|C101|".$vvFCPUFDest."|".$vvICMSUFDest."|".$vvICMSUFRemet."|\n";		    			
		    			echo($_SESSION['C101'][$C101]);
		    			
		    			
		    			    			
	    			}
    			}
    		if($vmod!='65'){
				// itens da NF
	    		for($nitem=0;$nitem<$qtditem;){//comentado o for e para outros tipos de nota que tem o C170	    		
	    			
	    			$det = $infNFe->getElementsByTagName("det")->item($nitem);
	    			$prod = $det->getElementsByTagName("prod")->item(0);
	    			$imposto = $det->getElementsByTagName("imposto")->item(0);	    			
	    			//###Tributacao do item do item ###############################
	    			$ICMS = $imposto->getElementsByTagName("ICMS")->item(0);
	    			
	    			$ICMS40= $ICMS->getElementsByTagName("ICMS40")->item(0);    
	    			$ICMS10= $ICMS->getElementsByTagName("ICMS10")->item(0);
	    			$ICMS20= $ICMS->getElementsByTagName("ICMS20")->item(0); 
	    			$ICMS70= $ICMS->getElementsByTagName("ICMS70")->item(0);
	    			$ICMS00= $ICMS->getElementsByTagName("ICMS00")->item(0);
	    			$ICMS60= $ICMS->getElementsByTagName("ICMS60")->item(0);
                                $ICMS90= $ICMS->getElementsByTagName("ICMS90")->item(0);
                                $ICMSST=$ICMS->getElementsByTagName("ICMSST")->item(0);
	    			$ICMSSN500= $ICMS->getElementsByTagName("ICMSSN500")->item(0);	
	    			$ICMSSN101= $ICMS->getElementsByTagName("ICMSSN101")->item(0);
	    			$ICMSSN102= $ICMS->getElementsByTagName("ICMSSN102")->item(0);
	    			$ICMSSN103= $ICMS->getElementsByTagName("ICMSSN103")->item(0);
	    			
	    			$ICMSSN201= $ICMS->getElementsByTagName("ICMSSN201")->item(0);
	    			$ICMSSN202= $ICMS->getElementsByTagName("ICMSSN202")->item(0);
	    			$ICMSSN203= $ICMS->getElementsByTagName("ICMSSN203")->item(0);
	    			
	    			$ICMSSN300= $ICMS->getElementsByTagName("ICMSSN300")->item(0);
	    			$ICMSSN400= $ICMS->getElementsByTagName("ICMSSN400")->item(0);
	    			$ICMSSN900= $ICMS->getElementsByTagName("ICMSSN900")->item(0);
	    			/*
	    			103
	    			201
	    			202
	    			203
	    			300
	    			400	    			
	    			900
	    			*/
	    			
	    			if($ICMSSN103!=null){
	    				die("erro".$row['idnf']);
	    			}
	    			
	    			if($ICMSSN201!=null){
	    				die("erro".$row['idnf']);
	    			}

	    			if($ICMSSN203!=null){
	    				die("erro".$row['idnf']);
	    			}
	    			if($ICMSSN300!=null){
	    				die("erro".$row['idnf']);
	    			}
	    			if($ICMSSN400!=null){
	    				die("erro".$row['idnf']);
	    			}
    			
	    						
		    		if($ICMS40!=null){
			    		 // echo("HE 40<BR>");
			    		  //Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMS40->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);        
		    			
		    			$vpICMS =number_format("0,00", 2, ',','');//aliq icms
		    			$vvICMS =number_format("0,00", 2, ',','');
		    			$vvBCicms =number_format("0,00", 2, ',','');
				    }
				    
				    if($ICMS20!=null){				   
				     
				     //icms do item VL_BC_ICMS
		    			$vBCicms= $ICMS20->getElementsByTagName("vBC")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 	
		    					
		    			//Aliguota 	ALIQ_ICMS
		    			$pICMS= $ICMS20->getElementsByTagName("pICMS")->item(0);
		    			$vpICMS =($pICMS->textContent);
		    			$vpICMS =number_format($vpICMS, 2, ',',''); 
		    			
		    			// Vlr icms item VL_ICMS
		    			$vICMS=$ICMS20->getElementsByTagName("vICMS")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
		    			
		    			//Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMS20->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);        
				    
				    }
				    if($ICMS70!=null){
				    	 
				    	//icms do item VL_BC_ICMS
				    	$vBCicms= $ICMS70->getElementsByTagName("vBC")->item(0);
				    	$vvBCicms =($vBCicms->textContent);
				    	$vvBCicms =number_format($vvBCicms, 2, ',','');
				    	 
				    	//Aliguota 	ALIQ_ICMS
				    	$pICMS= $ICMS70->getElementsByTagName("pICMS")->item(0);
				    	$vpICMS =($pICMS->textContent);
				    	$vpICMS =number_format($vpICMS, 2, ',','');
				    	 
				    	// Vlr icms item VL_ICMS
				    	$vICMS=$ICMS70->getElementsByTagName("vICMS")->item(0);
				    	$vvICMS =($vICMS->textContent);
				    	$vvICMS =number_format($vvICMS, 2, ',','');
				    	 
				    	//Situação Tributaria referente ao ICMS CST_ICMS
				    	$vCST=$ICMS70->getElementsByTagName("CST")->item(0);
				    	$vvCST =($vCST->textContent);
				    
				    }
				    if($ICMS10!=null){
				    
				    	//icms do item VL_BC_ICMS
				    	$vBCicms= $ICMS10->getElementsByTagName("vBC")->item(0);
				    	$vvBCicms =($vBCicms->textContent);
				    	$vvBCicms =number_format($vvBCicms, 2, ',','');
				    
				       	//Aliguota 	ALIQ_ICMS
				    	$pICMS= $ICMS10->getElementsByTagName("pICMS")->item(0);
				    	$vpICMS =($pICMS->textContent);
				    	$vpICMS =number_format($vpICMS, 2, ',','');
				    
				    	// Vlr icms item VL_ICMS
				    	$vICMS=$ICMS10->getElementsByTagName("vICMS")->item(0);
				    	$vvICMS =($vICMS->textContent);
				    	$vvICMS =number_format($vvICMS, 2, ',','');
				    
		    			//Situação Tributária referente ao ICMS CST_ICMS
		    			$vCST=$ICMS10->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);
		    			// echo("HE 20\n");
				    }
				    
				    if($ICMS00!=null){					     
				     //icms do item VL_BC_ICMS
		    			$vBCicms= $ICMS00->getElementsByTagName("vBC")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 	
		    					
		    			//Aliguota 	ALIQ_ICMS
		    			$pICMS= $ICMS00->getElementsByTagName("pICMS")->item(0);
		    			$vpICMS =($pICMS->textContent);
		    			$vpICMS =number_format($vpICMS, 2, ',',''); 
		    			
		    			// Vlr icms item VL_ICMS
		    			$vICMS=$ICMS00->getElementsByTagName("vICMS")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
		    			
		    			//Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMS00->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);				    
				    }
                                    
                                    if($ICMS90!=null){					     
				     //icms do item VL_BC_ICMS
		    			$vBCicms= $ICMS90->getElementsByTagName("vBC")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 	
		    					
		    			//Aliguota 	ALIQ_ICMS
		    			$pICMS= $ICMS90->getElementsByTagName("pICMS")->item(0);
		    			$vpICMS =($pICMS->textContent);
		    			$vpICMS =number_format($vpICMS, 2, ',',''); 
		    			
		    			// Vlr icms item VL_ICMS
		    			$vICMS=$ICMS90->getElementsByTagName("vICMS")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
		    			
		    			//Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMS90->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);				    
				    }
                                    
                                     if($ICMSST!=null){				     
				     //icms do item VL_BC_ICMS
                                         /*
		    			$vBCicms= $ICMSST->getElementsByTagName("vBCSTRet")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 
                                        */
                                        $vvBCicms =number_format("0,00", 2, ',','');
                                        
                                        //Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMSST->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);  
		    					
		    			// Vlr icms item VL_ICMS
                                        /*
		    			$vICMS=$ICMSST->getElementsByTagName("vICMSSTRet")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
                                        */
                                        
		    			$vvICMS =number_format("0,00", 2, ',','');
		    			
                                    
		    			$vpICMS =number_format("0,00", 2, ',','');//aliq icms			    
				    }	
				    
				    if($ICMS60!=null){					     
				     //icms do item VL_BC_ICMS
		    			$vBCicms= $ICMS60->getElementsByTagName("vBCSTRet")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 	
		    					
		    			// Vlr icms item VL_ICMS
		    			$vICMS=$ICMS60->getElementsByTagName("vICMSSTRet")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
		    			
		    			//Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMS60->getElementsByTagName("CST")->item(0);
		    			$vvCST =($vCST->textContent);        
		    			// echo("HE 20<BR>");
		    			$vpICMS =number_format("0,00", 2, ',','');//aliq icms			    
				    }
				    
				    if($ICMSSN500!=null){				     
				     //icms do item VL_BC_ICMS
		    			$vBCicms= $ICMSSN500->getElementsByTagName("vBCSTRet")->item(0);
		    			$vvBCicms =($vBCicms->textContent);   
		    			$vvBCicms =number_format($vvBCicms, 2, ',',''); 	
		    					
		    			// Vlr icms item VL_ICMS
		    			$vICMS=$ICMSSN500->getElementsByTagName("vICMSSTRet")->item(0);
		    			$vvICMS =($vICMS->textContent);
		    			$vvICMS =number_format($vvICMS, 2, ',',''); 
		    			
		    			//Situação Tributaria referente ao ICMS CST_ICMS
		    			$vCST=$ICMSSN500->getElementsByTagName("CSOSN")->item(0);
		    			$vvCST =($vCST->textContent);     
		    			$vpICMS =number_format("0,00", 2, ',','');//aliq icms			    
				    }	


				    if($ICMSSN101!=null){
				    	 
				    	$vCST=$ICMSSN101->getElementsByTagName("CSOSN")->item(0);
				    	$vvCST =($vCST->textContent);
				    	 
				    	$vpICMS =number_format("0,00", 2, ',','');//aliq icms
				    	$vvICMS =number_format("0,00", 2, ',','');
				    	$vvBCicms =number_format("0,00", 2, ',','');
				    }
				    if($ICMSSN102!=null){
				    	 
				    	$vCST=$ICMSSN102->getElementsByTagName("CSOSN")->item(0);
				    	$vvCST =($vCST->textContent);
				    		
				    	$vpICMS =number_format("0,00", 2, ',','');//aliq icms
				    	$vvICMS =number_format("0,00", 2, ',','');
				    	$vvBCicms =number_format("0,00", 2, ',','');
				    }
				    if($ICMSSN202!=null){
				    	//icms do item VL_BC_ICMS
				    	$vBCicms= $ICMSSN202->getElementsByTagName("vBCST")->item(0);
				    	$vvBCicms =($vBCicms->textContent);
				    	$vvBCicms =number_format($vvBCicms, 2, ',','');
				    
				    	$vRedBC=$nvProd-$vvBCicms;
				    
				    	//Aliguota 	ALIQ_ICMS
				    	$pICMS= $ICMSSN202->getElementsByTagName("pICMSST")->item(0);
				    	$vpICMS =($pICMS->textContent);
				    	$vpICMS =number_format($vpICMS, 2, ',','');
				    
				    	// Vlr icms item VL_ICMS
				    	$vICMS=$ICMSSN202->getElementsByTagName("vICMSST")->item(0);
				    	$vvICMS =($vICMS->textContent);
				    	$vvICMS =number_format($vvICMS, 2, ',','');
				    
				    	//Situação Tributária referente ao ICMS CST_ICMS
				    	$vCST=$ICMSSN202->getElementsByTagName("CSOSN")->item(0);
				    	$vvCST =($vCST->textContent);
				    
				    }
				    
				    if($ICMSSN900!=null){
				    	 
				    	$vCST=$ICMSSN900->getElementsByTagName("CSOSN")->item(0);
				    	$vvCST =($vCST->textContent);
				    	 
				    	$vpICMS =number_format("0,00", 2, ',','');//aliq icms
				    	$vvICMS =number_format("0,00", 2, ',','');
				    	$vvBCicms =number_format("0,00", 2, ',','');
				    }
	    				    			
	    			#### IPI
	    			//cst ipi 	CST_IPI
	    			$IPI = $imposto->getElementsByTagName("IPI")->item(0);
	    			if($IPI!=null){
		    			$IPINT = $IPI->getElementsByTagName("IPINT")->item(0);
		    			$IPITrib = $IPI->getElementsByTagName("IPITrib")->item(0);
		    			if($IPITrib!=null){//E IPITrib
		    				$CSTipi = $IPITrib->getElementsByTagName("CST")->item(0);
		    				$vCSTipi =($CSTipi->textContent);
		    			}else{//e IPINT
		    				$CSTipi = $IPINT->getElementsByTagName("CST")->item(0);
		    				$vCSTipi =($CSTipi->textContent);
		    			}	    			
		    				    			
		    			//	Codigo de enquadramento legal do IPICOD_ENQ
		    			$cEnq = $IPI->getElementsByTagName("cEnq")->item(0);
		    			$vcEnq =($cEnq->textContent);
	    			}
	    			###PIS
	    			// CST_PIS
	    			$PIS = $imposto->getElementsByTagName("PIS")->item(0);
	    			$PISAliq = $PIS->getElementsByTagName("PISAliq")->item(0);
	    			$PISOutr = $PIS->getElementsByTagName("PISOutr")->item(0);
	    			$PISNT = $PIS->getElementsByTagName("PISNT")->item(0);
	    			
	    			//algums xmls nao tem a tag PISaliq
	    			if($PISAliq!=null){
		    			$CSTpis= $PISAliq->getElementsByTagName("CST")->item(0);
		    			$vCSTpis =($CSTpis->textContent);
		    			//VL_BC_PIS
		    			$vBCpis= $PISAliq->getElementsByTagName("vBC")->item(0);
		    			$vvBCpis =($vBCpis->textContent);
		    			$vvBCpis =number_format($vvBCpis, 2, ',','');
		    			//ALIQ_PIS
		    			$pPIS= $PISAliq->getElementsByTagName("pPIS")->item(0);
		    			$vpPIS =($pPIS->textContent);
		    			$vpPIS =number_format($vpPIS, 2, ',','');		    			
		    			//VL_PIS
		    			$vPISitem= $PISAliq->getElementsByTagName("vPIS")->item(0);
		    			$vvPISitem=($vPISitem->textContent);
		    			$vvPISitem =number_format($vvPISitem, 2, ',','');
	    			}elseif($PISOutr!=null){
		    			$CSTpis= $PISOutr->getElementsByTagName("CST")->item(0);
		    			$vCSTpis =($CSTpis->textContent);
		    			//VL_BC_PIS
		    			$vBCpis= $PISOutr->getElementsByTagName("vBC")->item(0);
		    			$vvBCpis =($vBCpis->textContent);
		    			$vvBCpis =number_format($vvBCpis, 2, ',','');
		    			//ALIQ_PIS
		    			$pPIS= $PISOutr->getElementsByTagName("pPIS")->item(0);
		    			$vpPIS =($pPIS->textContent);
		    			$vpPIS =number_format($vpPIS, 2, ',','');		    			
		    			//VL_PIS
		    			$vPISitem= $PISOutr->getElementsByTagName("vPIS")->item(0);
		    			$vvPISitem=($vPISitem->textContent);
		    			$vvPISitem =number_format($vvPISitem, 2, ',','');
	    			}elseif($PISNT!=null){
	    				$CSTpis= $PISNT->getElementsByTagName("CST")->item(0);
	    				$vCSTpis =($CSTpis->textContent);
	    				$vpPIS =number_format('0,00', 2, ',','');
	    				$vvPISitem =number_format('0,00', 2, ',','');
	    				$vvBCpis =number_format('0,00', 2, ',','');
	    			
	    			}		

	    			//Cofins####
	    			//CST_COFINS
	    			$COFINS = $imposto->getElementsByTagName("COFINS")->item(0);
	    			$COFINSAliq = $COFINS->getElementsByTagName("COFINSAliq")->item(0);
	    			$COFINSOutr = $COFINS->getElementsByTagName("COFINSOutr")->item(0);
	    			$COFINSQtde = $COFINS->getElementsByTagName("COFINSQtde")->item(0);
	    			
	    			if($COFINSAliq!=null){
		    			$CSTcofins = $COFINSAliq->getElementsByTagName("CST")->item(0);
		    			$vCSTcofins=($CSTcofins->textContent);		    				    			
		    			//	VL_BC_COFINS
		    			$vBCcofins = $COFINSAliq->getElementsByTagName("vBC")->item(0);
		    			$vvBCcofins=($vBCcofins->textContent);
		    			$vvBCcofins =number_format($vvBCcofins, 2, ',','');		    			
		    			//	ALIQ_COFINS
		    			$pCOFINS = $COFINSAliq->getElementsByTagName("pCOFINS")->item(0);
		    			$vpCOFINS=($pCOFINS->textContent);
		    			$vpCOFINS =number_format($vpCOFINS, 2, ',','');	    			
		    			//VL_COFINS
		    			$vCOFINSitem = $COFINSAliq->getElementsByTagName("vCOFINS")->item(0);
		    			$vvCOFINSitem=($vCOFINSitem->textContent);
		    			$vvCOFINSitem =number_format($vvCOFINSitem, 2, ',','');
	    			}elseif($COFINSOutr!=null){
		    			$CSTcofins = $COFINSOutr->getElementsByTagName("CST")->item(0);
		    			$vCSTcofins=($CSTcofins->textContent);		    				    			
		    			//	VL_BC_COFINS
		    			$vBCcofins = $COFINSOutr->getElementsByTagName("vBC")->item(0);
		    			$vvBCcofins=($vBCcofins->textContent);
		    			$vvBCcofins =number_format($vvBCcofins, 2, ',','');		    			
		    			//	ALIQ_COFINS
		    			$pCOFINS = $COFINSOutr->getElementsByTagName("pCOFINS")->item(0);
		    			$vpCOFINS=($pCOFINS->textContent);
		    			$vpCOFINS =number_format($vpCOFINS, 2, ',','');	    			
		    			//VL_COFINS
		    			$vCOFINSitem = $COFINSOutr->getElementsByTagName("vCOFINS")->item(0);
		    			$vvCOFINSitem=($vCOFINSitem->textContent);
		    			$vvCOFINSitem =number_format($vvCOFINSitem, 2, ',','');
	    			}elseif($COFINSQtde!=null){
	    				$CSTcofins = $COFINSQtde->getElementsByTagName("CST")->item(0);
	    				$vCSTcofins=($CSTcofins->textContent);
	    			}else{
	    				$COFINSNT = $COFINS->getElementsByTagName("COFINSNT")->item(0);
	    				$CSTcofins = $COFINSNT->getElementsByTagName("CST")->item(0);
		    			$vCSTcofins=($CSTcofins->textContent);	  
		    			$vvCOFINSitem =number_format('0,00', 2, ',','');
		    			$vpCOFINS =number_format('0,00', 2, ',','');
		    			$vvBCcofins =number_format('0,00', 2, ',','');
	    			}	
	    			
	    			#############################################################
	    			//Codigo do produto COD_ITEM
	    			$cProd = $prod->getElementsByTagName("cProd")->item(0);
	    			$vcProd =($cProd->textContent);	
	    			//Descrição do produto DESCR_COMPL
	    			$xProd = $prod->getElementsByTagName("xProd")->item(0);
	    			$vxProd =($xProd->textContent);	    
	    			$vxProd =str_replace("|"," ",$vxProd);
	    			//quantidade do item QTD    			
	    			$qCom = $prod->getElementsByTagName("qCom")->item(0);
	    			$vqCom =($qCom->textContent);
	    				    			
	    			//	unidade do item UNID    			
	    			$uTrib = $prod->getElementsByTagName("uTrib")->item(0);
	    			$vuTrib =($uTrib->textContent);	    			
	    			// valor do item VL_ITEM unitario
	    			$vUnCom = $prod->getElementsByTagName("vUnCom")->item(0);
	    			$vvUnCom =($vUnCom->textContent);
				
				// valor do item VL_ITEM
				$vProd = $prod->getElementsByTagName("vProd")->item(0);
	    			$vvProd =($vProd->textContent);
					
	    			//valor do desconto VL_DESC
	    			
	    			$vDesc = $prod->getElementsByTagName("vDesc")->item(0);
	    			$vvDesc =($vDesc->textContent);
	    			$vvDesc =number_format($vvDesc, 2, ',','');	
	    			  			
	    			// CFOP
	    			$CFOP = $prod->getElementsByTagName("CFOP")->item(0);
	    			$vCFOP =($CFOP->textContent);
	    			

					$sqlcfop="select cfopentrada from finalidadeconf where tipoconsumo = '".$row['tipoconsumo']."' and cfopnf='".$vCFOP."' AND status = 'ATIVO'";
					$rescfop = d::b()->query($sqlcfop) or die("A Consulta da conversão de cfop falhou :".mysql_error()."<br>Sql:".$sqlcfop); 
					$qtdcpf=mysqli_num_rows($rescfop);
					if($qtdcpf<1){
						$sqlcfop="select cfopentrada,tipoconsumo from finalidadeconf where cfopnf='".$vCFOP."' AND status = 'ATIVO'";
						$rescfop = d::b()->query($sqlcfop) or die("A Consulta da conversão 2 de cfop falhou :".mysql_error()."<br>Sql:".$sqlcfop); 
					}
					$rowcfop = mysqli_fetch_assoc($rescfop);
					if(!empty($rowcfop['cfopentrada'])){
						$vCFOP=$rowcfop['cfopentrada'];
					}else{
													

							//Converter o CFOP para entrada
							/*
							if(($vCFOP=='5101' or $vCFOP=='5102' or $vCFOP=='5103') and $row['faticms']=='Y'){
								$vCFOP='1101';//1101 Compra para industrialização ou Produção Rural
							}elseif(($vCFOP=='5101' or $vCFOP=='5102' or $vCFOP=='5103') and $row['imobilizado']=='Y'){
								$vCFOP='1551';
							}elseif(($vCFOP=='5101' or $vCFOP=='5102' or $vCFOP=='5103') and $row['consumo']=='Y'){
								$vCFOP='1556';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['faticms']=='Y'){
								$vCFOP='1401';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['imobilizado']=='Y'){
								$vCFOP='1406';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['consumo']=='Y'){
								$vCFOP='1407';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['faticms']=='Y'){
								$vCFOP='2101';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif(($vCFOP=='6401'or $vCFOP=='6402' or $vCFOP=='6403'or $vCFOP=='6404' or $vCFOP=='6405') and $row['faticms']=='Y'){
								$vCFOP='2401';
							}elseif(($vCFOP=='6401'or $vCFOP=='6402' or $vCFOP=='6404' or $vCFOP=='6405') and ($row['consumo']=='Y' or $row['imobilizado']=='Y')){
									$vCFOP='2406';
							}elseif($vCFOP=='6103' and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif($vCFOP=='6103' and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif($vCFOP=='6103'){
								$vCFOP='2101';
							}elseif($vCFOP=='6403' and $row['consumo']=='Y'){
								$vCFOP='2407';
							}elseif($vCFOP=='6403' and $row['imobilizado']=='Y'){
								$vCFOP='2406';
							}elseif($vCFOP=='6949'){
								$vCFOP='2949';
							}elseif($vCFOP=='5949'){
								$vCFOP='1949';
							}elseif($vCFOP=='6915'){
								$vCFOP='2915';
							}elseif($vCFOP=='6917'){
								$vCFOP='2917';
							}elseif($vCFOP=='6916'){
								$vCFOP='2916';
							}elseif($vCFOP=='6923'){
									$vCFOP='2923';
							}elseif($vCFOP=='5656'){
									$vCFOP='1653';
							}else
							*/
							if($vCFOP=='5929' and ( $vvCST==00 or $vvCST==20) and $row['imobilizado']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1551';
							}elseif($vCFOP=='5929'  and $row['imobilizado']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1406';
							}elseif($vCFOP=='5929' and ( $vvCST==00 or $vvCST==20) and $row['faticms']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1101';
							}elseif(($vCFOP=='5929') and $row['faticms']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1401';
							}elseif($vCFOP=='5929' and ( $vvCST==00 or $vvCST==20) and $row['consumo']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1556';
							}elseif($vCFOP=='5929'  and $row['consumo']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1407';
							}
							/*
							elseif($vCFOP=='6910'){
								$vCFOP='2910';
							}elseif($vCFOP=='5910'){
								$vCFOP='1910';
							}elseif($vCFOP=='6908'){
								$vCFOP='2908';
							}elseif($vCFOP=='6909'){
								$vCFOP='2909';
							}elseif($vCFOP=='6201'){
								$vCFOP='2201';
							}elseif($vCFOP=='5920'){
								$vCFOP='1920';
							}elseif($vCFOP=='5922'){
								$vCFOP='1922';
							}elseif($vCFOP=='6922'){
								$vCFOP='2922';
							}elseif($vCFOP=='5116'){
								$vCFOP='1116';
							}elseif($vCFOP=='6116'){
								$vCFOP='2116';
							}elseif($vCFOP=='6106'){
								$vCFOP='2101';
							}elseif($vCFOP=='5122'){
								$vCFOP='1122';
							}elseif($vCFOP=='5916'){
								$vCFOP='1916';
							}elseif($vCFOP=='6119'){
									$vCFOP='2118';
							}elseif($vCFOP=='5117'){
									$vCFOP='1556';
							}elseif($vCFOP=='6556'){
									$vCFOP='2201';
							}elseif($vCFOP=='6551'){
								$vCFOP='2551';
							}elseif($vCFOP=='6117' and $row['faticms']=='Y'){
								$vCFOP='2116';
							}elseif($vCFOP=='6117' and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif($vCFOP=='6117' and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif($vCFOP=='6911'){
									$vCFOP='2911';
							}elseif($vCFOP=='6912'){ 
								$vCFOP='2912';
							}elseif($vCFOP=='6210'){
									$vCFOP='2126';
							}elseif($vCFOP=='5908'){
									$vCFOP='1908';
							}elseif($vCFOP=='6920'){
									$vCFOP='2920';
							}elseif($vCFOP=='5667'){
									$vCFOP='1653';
							}elseif($vCFOP=='6105'){
									$vCFOP='2102';
							}elseif($vCFOP=='6901'){
								$vCFOP='2901';
							}elseif($vCFOP=='5901'){
								$vCFOP='1901';
							}elseif($vCFOP=='5912'){
								$vCFOP='1912';
							}elseif($vCFOP=='6902'){
								$vCFOP='2902';
							}elseif($vCFOP=='6124'){
								$vCFOP='2124';
							}elseif($vCFOP=='6120'){
								$vCFOP='2120';
							}elseif($vCFOP=='6933'){
								$vCFOP='2933';
							}
							*/

						}			
		    			
	    			
	    			//definir o valo ST do ICMS
	    			if($vvCST==10){
	    				$CST_ICMS="010";
	    			}elseif($vvCST==20){
	    				$CST_ICMS="020";
	    			}elseif($vvCST==70){
	    				$CST_ICMS="070";
	    			}elseif($vvCST==40){
	    				$CST_ICMS="040";
	    			}elseif($vvCST==50){
	    				$CST_ICMS="050";
	    			}elseif($vvCST==51){
	    				$CST_ICMS="051";
	    			}elseif($vvCST==41){
	    				$CST_ICMS="041";
	    			}elseif($vvCST==60){
	    				$CST_ICMS="060";
	    			}elseif($vvCST==00){
	    				$CST_ICMS="000";
	    			}elseif(strlen($vvCST)==2){
	    				$CST_ICMS='0'.$vvCST;
                                }else{
                                    $CST_ICMS=$vvCST;
                                }                                
					
	    				    				    			    			
	    			$nitem++;
	    			if($nitem<10){
	    				$stritem = "00";
	    			}elseif($nitem>=10 and $nitem <100){
	    			$stritem = "0";
	    			}else{
	    			$stritem ="";
	    			}
	    			
	    			if($row['tiponf']=='C'){
	    			//DEfini o valor do ST do IPI
	    			 if($vCSTipi=='51'){
	    			 	$vCSTipi='01';
	    			 }
	    			 if($vCSTipi=='52'){
	    			 	$vCSTipi='02';
	    			 }
	    			 if($vCSTipi=='53'){
	    			 	$vCSTipi='03';
	    			 }
	    			 if($vCSTipi=='54'){
	    			 	$vCSTipi='04';
	    			 }
	    			 if($vCSTipi=='55'){
	    			 	$vCSTipi='05';
	    			 }
	    			 if($vCSTipi=='50'){
	    			 	$vCSTipi='00';
	    			 }
	    			 if($vCSTipi=='99'){
	    			 	$vCSTipi='49';
	    			 }


	    			 
				    // Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
				    if($row['consumo']=="Y" or $row['imobilizado']=='Y'  or $row['outros']=='Y'  or $row['comercio']=='Y'){

	    			 	$vvBCicms =number_format('0,00', 2, ',','');
	    			 	$vpICMS =number_format('0,00', 2, ',','');
	    			 	$vvICMS =number_format('0,00', 2, ',','');
	    			 	$vvBCpis=number_format('0,00', 2, ',','');
	    			 	$vpPIS =number_format('0,00', 2, ',','');
	    			 	$vvPISitem =number_format('0,00', 2, ',','');
	    			 	$vvBCcofins =number_format('0,00', 2, ',','');
	    			 	$vpCOFINS=number_format('0,00', 2, ',','');
	    			 	$vvCOFINSitem=number_format('0,00', 2, ',','');
				    }
	    			 
                                    $vlrtotalitem=$vqCom*$vvUnCom;
				    if(!empty($row['icmscpl'])){
	    			 	
	    			 	$vpICMS= number_format($row['icmscpl'], 2, ',','');
	    			 	$vvICMS =(($vlrtotalitem * $row['icmscpl'])/100);
	    			 	$vvICMS =number_format($vvICMS, 2, ',','');
	    			 	$vRedBC=0;
	    			 	$vvBCicms=number_format($vlrtotalitem, 2, ',','');
				    }
	    			 
					IF($vqCom<1){
					    $vvUnCom=$vvProd;
					    $vqCom=1.00;
					}
				     
					$vqCom =number_format($vqCom, 2, ',','');
					$vvUnCom =number_format($vvUnCom, 2, ',','');
                                        $vlrtotalitem=number_format($vlrtotalitem, 2, ',','');
	    			 
		    			$C170=$C170+1;
		    			//duvidas IND_MOV 1  CST_ICMS COD_NAT IND_APUR QUANT_BC_PIS ALIQ_PIS(em reais) QUANT_BC_COFINS ALIQ_COFINS(em reais) COD_CTA
		    			//1  |2       |3       |4          |5  |6   |7      |8      |9      |10      |11  |12     |13        |14       |15     |16           |17     |18        |19      |20     |21     |22       |23      |24    |25     |26       |27      |28          |27      |28          |29      |30    |31        |32          |33         |34       		 |35         |36       |37     |      
		    			//REG|NUM_ITEM|COD_ITEM|DESCR_COMPL|QTD|UNID|VL_ITEM|VL_DESC|IND_MOV|CST_ICMS|CFOP|COD_NAT|VL_BC_ICMS|ALIQ_ICMS|VL_ICMS|VL_BC_ICMS_ST|ALIQ_ST|VL_ICMS_ST|IND_APUR|CST_IPI|COD_ENQ|VL_BC_IPI|ALIQ_IPI|VL_IPI|CST_PIS|VL_BC_PIS|ALIQ_PIS|QUANT_BC_PIS|ALIQ_PIS|QUANT_BC_PIS|ALIQ_PIS|VL_PIS|CST_COFINS|VL_BC_COFINS|ALIQ_COFINS|QUANT_BC_COFINS|ALIQ_COFINS|VL_COFINS|COD_CTA|
		    			//                        |1   | 2                 | 3         |4          |5         | 6         |7           |8          |9|10             |11        ||13           |14         |15         ||||19|20         |21           ||||25          |26          |27        ||29            ||31             |32             |33           |||36               ||
		    			//$_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|".$vcProd."|".$vxProd."|".$vqCom."|".$vuTrib."|".$vvUnCom."|".$vvDesc."|0|"/*CST_ICMS*/."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."||\n";
		    			$_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|CONSPD|".trim(rmAc($vxProd))."|".$vqCom."|UN|".$vlrtotalitem."|".$vvDesc."|0|".$CST_ICMS."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."|||\n";
		    			echo($_SESSION['C170'][$C170]);
	    			}     				    			
	    		}
    		//REGISTRO ANALiTICO DO DOCUMENTO (CoDIGO 01, 1B, 04 E 55)
    		
    		if($row['tiponf']=='V'){
                    $sqli="select cst as st,cfop,aliqicms,round(sum((total)+frete),2) as vl_opr,sum(basecalc) as vl_bc_icms,
                            sum(valicms) as vl_icms,if((sum((total)+frete)-sum(basecalc))<1,'0.00',(sum((total)+frete)-sum(basecalc)))  as vl_red_bc,sum(valipi) as vl_ipi
                        from nfitem where nfe = 'Y' and idnf=".$row['idnf']." group by cst,cfop,aliqicms";
    		}else{
                    $sqli="select  u.cst as st,u.cfop,u.aliqicms,sum((IFNULL(u.valor,0)+IFNULL(u.frete,0)+IFNULL(u.vst,0)+IFNULL(u.valipi,0))-IFNULL(u.des,0)) as vl_opr,sum(IFNULL(u.basecalc,0)) as vl_bc_icms,
                                    sum(u.valicms) as vl_icms,sum(IFNULL(u.redbc,0)) as vl_red_bc,sum(IFNULL(u.valipi,0)) as vl_ipi 
                            from (
                                    select x.cst ,x.cfop
                                    ,if(n.faticms='Y',x.aliqicms,0) as aliqicms
                                    ,IFNULL(x.valor,0) as valor
                                    ,IFNULL(x.frete,0) as frete 
                                    ,IFNULL(x.vst,0) as vst 
                                    ,if(n.faticms='Y',x.valipi,0) as valipi
                                    , IFNULL(x.des,0) as des
                                    ,if(n.faticms='Y',x.basecalc,0) as basecalc
                                    ,if(n.faticms='Y',x.valicms,0) as valicms
                                    ,if(n.faticms='Y',x.redbc,0) as redbc
                                    from nfitemxml x join nf n on(n.idnf=x.idnf)
                                    where x.status = 'Y' and x.idnf=".$row['idnf']."
                                ) as u 
                    group by st,cfop,aliqicms";
    		}    			
    			
    			$resi=mysql_query($sqli);
    			$qtdi=mysql_num_rows($resi);
    			
    			if($qtdi<1){
    				die("Favor marcar nota ".$row['nnfe']." novamente [industria,imobilizado,consumo]!!!");
    			}
    			
    			while($rowi=mysql_fetch_assoc($resi)){
    				
    				//converter o cfop da compra para o cfop de entrada
    				if($row['tiponf']!='V'){
    					$vCFOP=$rowi['cfop'];

						$sqlcfop="select cfopentrada from finalidadeconf where tipoconsumo = '".$row['tipoconsumo']."' and cfopnf='".$vCFOP."' AND status = 'ATIVO'";
						$rescfop = d::b()->query($sqlcfop) or die("A Consulta da conversão de cfop falhou :".mysql_error()."<br>Sql:".$sqlcfop); 
						$qtdcpf=mysqli_num_rows($rescfop);
						if($qtdcpf<1){
							$sqlcfop="select cfopentrada,tipoconsumo from finalidadeconf where cfopnf='".$vCFOP."' AND status = 'ATIVO'";
							$rescfop = d::b()->query($sqlcfop) or die("A Consulta da conversão 2 de cfop falhou :".mysql_error()."<br>Sql:".$sqlcfop); 
						}
						$rowcfop = mysqli_fetch_assoc($rescfop);
						if(!empty($rowcfop['cfopentrada'])){
							$vCFOP=$rowcfop['cfopentrada'];
						}else{

							/*
							if(($vCFOP=='5101' or $vCFOP=='5102') and $row['faticms']=='Y'){
								$vCFOP='1101';//1101 Compra para industrialização ou Produção Rural
							}elseif(($vCFOP=='5101' or $vCFOP=='5102') and $row['imobilizado']=='Y'){
								$vCFOP='1551';
							}elseif(($vCFOP=='5101' or $vCFOP=='5102') and $row['consumo']=='Y'){
								$vCFOP='1556';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['faticms']=='Y'){
								$vCFOP='1401';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['imobilizado']=='Y'){
								$vCFOP='1406';
							}elseif(($vCFOP=='5401'or $vCFOP=='5402' or $vCFOP=='5403'or $vCFOP=='5404' or $vCFOP=='5405') and $row['consumo']=='Y'){
								$vCFOP='1407';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['faticms']=='Y'){
								$vCFOP='2101';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif(($vCFOP=='6101' or $vCFOP=='6102' or $vCFOP=='6107' or $vCFOP=='6108') and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif(($vCFOP=='6401'or $vCFOP=='6402' or $vCFOP=='6403'or $vCFOP=='6404' or $vCFOP=='6405') and $row['faticms']=='Y'){
								$vCFOP='2401';
							}elseif(($vCFOP=='6401'or $vCFOP=='6402' or $vCFOP=='6404' or $vCFOP=='6405') and  ($row['consumo']=='Y' or $row['imobilizado']=='Y')){
								$vCFOP='2406';
							}elseif($vCFOP=='6103' and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif($vCFOP=='6103' and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif($vCFOP=='6103'){
								$vCFOP='2101';
							}elseif($vCFOP=='6403' and $row['consumo']=='Y'){
								$vCFOP='2407';
							}elseif($vCFOP=='6403' and $row['imobilizado']=='Y'){
								$vCFOP='2406';
							}elseif($vCFOP=='6949'){
								$vCFOP='2949';
							}elseif($vCFOP=='5949'){
								$vCFOP='1949';
							}elseif($vCFOP=='6915'){
								$vCFOP='2915';
							}elseif($vCFOP=='6917'){
								$vCFOP='2917';
							}elseif($vCFOP=='6916'){
								$vCFOP='2916';
							}elseif($vCFOP=='6923'){
								$vCFOP='2923';
							}elseif($vCFOP=='5656'){
								$vCFOP='1653';
							}else
							*/
							if($vCFOP=='5929' and ( $rowi['st']==00 or $rowi['st']==20) and $row['imobilizado']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1551';
							}elseif($vCFOP=='5929'  and $row['imobilizado']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1406';
							}elseif($vCFOP=='5929' and ( $rowi['st']==00 or $rowi['st']==20) and $row['faticms']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1101';
							}elseif(($vCFOP=='5929' or $vCFOP=='5910')  and $row['faticms']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1401';
							}elseif($vCFOP=='5929' and ( $rowi['st']==00 or $rowi['st']==20) and $row['consumo']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
								$vCFOP='1556';
							}elseif($vCFOP=='5929'  and $row['consumo']=='Y'){//5929 = vai ser 1406, quando o produto for Substituição Tributaria, CST 060 / 070 / 090 
								$vCFOP='1407';
							}
							/*
							elseif($vCFOP=='6910'){
								$vCFOP='2910';
							}elseif($vCFOP=='5910'){
								$vCFOP='1910';
							}elseif($vCFOP=='6908'){
								$vCFOP='2908';
							}elseif($vCFOP=='6909'){
								$vCFOP='2909';
							}elseif($vCFOP=='6201'){
								$vCFOP='2201';
							}elseif($vCFOP=='5920'){
								$vCFOP='1920';
							}elseif($vCFOP=='5922'){
								$vCFOP='1922';
							}elseif($vCFOP=='6922'){
								$vCFOP='2922';
							}elseif($vCFOP=='5116'){
								$vCFOP='1116';
							}elseif($vCFOP=='6116'){
								$vCFOP='2116';
							}elseif($vCFOP=='6106'){
								$vCFOP='2101';
							}elseif($vCFOP=='5122'){
								$vCFOP='1122';
							}elseif($vCFOP=='5916'){
								$vCFOP='1916';
							}elseif($vCFOP=='6119'){
								$vCFOP='2118';
							}elseif($vCFOP=='5117'){
									$vCFOP='1556';
							}elseif($vCFOP=='6556'){
									$vCFOP='2201';
							}elseif($vCFOP=='6551'){
								$vCFOP='2551';
							}elseif($vCFOP=='6911'){
									$vCFOP='2911';
							}elseif($vCFOP=='6117' and $row['faticms']=='Y'){
								$vCFOP='2116';
							}elseif($vCFOP=='6117' and $row['imobilizado']=='Y'){
								$vCFOP='2551';
							}elseif($vCFOP=='6117' and $row['consumo']=='Y'){
								$vCFOP='2556';
							}elseif($vCFOP=='6912'){ 
								$vCFOP='2912';
							}elseif($vCFOP=='6210'){
									$vCFOP='2126';
							}elseif($vCFOP=='5908'){
									$vCFOP='1908';
							}elseif($vCFOP=='6920'){
									$vCFOP='2920';
							}elseif($vCFOP=='5667'){
									$vCFOP='1653';
							}elseif($vCFOP=='6105'){
									$vCFOP='2102';
							}elseif($vCFOP=='6901'){
								$vCFOP='2901';
							}elseif($vCFOP=='5901'){
								$vCFOP='1901';
							}elseif($vCFOP=='5912'){
								$vCFOP='1912';
							}elseif($vCFOP=='6902'){
								$vCFOP='2902';
							}elseif($vCFOP=='6124'){
								$vCFOP='2124';
							}elseif($vCFOP=='6120'){
								$vCFOP='2120';
							}elseif($vCFOP=='6933'){
								$vCFOP='2933';
							}
							*/
						}	
		  
		    			$rowi['cfop']=$vCFOP;
		    			// Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
		    			 if($row['consumo']=="Y" or $row['imobilizado']=='Y'  or $row['comercio']=='Y'){
			    			$rowi['aliqicms']="0.00";
			    			$rowi['vl_bc_icms']="0.00";
			    			$rowi['vl_icms']="0.00";
			    			$rowi['vl_red_bc']="0.00";
			    			$rowi['vl_ipi']="0.00";
		    			}
    				}
    				
    				//Corrigir st para 3 digitos
    				if($rowi['st']==10){
    					$rowi['st']="010";
    				}elseif($rowi['st']==20){
    					$rowi['st']="020";
    				}elseif($rowi['st']==70){
    					$rowi['st']="070";
    				}elseif($rowi['st']==40){
    					$rowi['st']="040";
    				}elseif($rowi['st']==50){
    					$rowi['st']="050";
    				}elseif($rowi['st']==51){
    					$rowi['st']="051";
    				}elseif($rowi['st']==41){
    					$rowi['st']="041";
    				}elseif($rowi['st']==60){
    					$rowi['st']="060";
    				}elseif($rowi['st']==00){
    					$rowi['st']="000";
    				}elseif($rowi['st']==90){
    					$rowi['st']="090";
    				}
    				
	    			$C190=$C190+1;
		    		//|C190|||7,00||470,98|3,74||||0,00||
		    		//CST_ICMS CFOP VL_OPR VL_BC_ICMS_ST VL_ICMS_ST VL_RED_BC
		    		//Valor da reducao e o valor total + valor do frete - base de calculo = 706,47
		    		$vlrRedBc=$nvProd+$nvFrete-$nvBC;
		    		$vlrRedBc =number_format($vlrRedBc, 2, ',','');		    		
		    		//round(, 2);
		    		
		    		if(!empty($row['icmscpl'])){
		    			
		    			$rowi['aliqicms']=$row['icmscpl'];
		    			$rowi['vl_bc_icms']=$rowi['vl_opr'];
		    			$rowi['vl_icms']=(($rowi['vl_opr'] * $row['icmscpl'])/100);
		    		
		    		}
		
		    		//$_SESSION['C190'][$C190]="|C190|".$strST."|".$vCFOP."|".$vpICMS."|".$vlrOper."|".$vvBC."|".$vvtICMS."|0,00|0,00|".$vlrRedBc."|".$vvIPI."||\n";
		    		$_SESSION['C190'][$C190]="|C190|".$rowi['st']."|".$rowi['cfop']."|".number_format($rowi['aliqicms'], 2, ',','')."|".number_format($rowi['vl_opr'], 2, ',','')."|".number_format($rowi['vl_bc_icms'], 2, ',','')."|".number_format($rowi['vl_icms'], 2, ',','')."|0,00|0,00|".number_format($rowi['vl_red_bc'], 2, ',','')."|".number_format($rowi['vl_ipi'], 2, ',','')."||\n";
		    		echo($_SESSION['C190'][$C190]);
		    		
		    		if($row['tiponf']=='C' and $row['faticms']=='Y' and($rowi['cfop']=='2101' or $rowi['cfop']=='1102' or $rowi['cfop']=='1101')){
		    			$this->vlrTotalded = $this->vlrTotalded+$rowi['vl_icms'];
		    			$this->vlrTotalIPIded = $this->vlrTotalIPIded + $rowi['vl_ipi'];
		    		
		    		}elseif($row['tiponf']=='V'){
		    			$this->vlrTotalIcms = $this->vlrTotalIcms+$rowi['vl_icms'];
		    			$this->vlrTotalIPI = $this->vlrTotalIPI + $rowi['vl_ipi'];
		    		}
    			} 
			}//	if($vmod!='65'){   		
    		
    		/*if($row['tiponf']=='C' and $row['faticms']=='Y' and(2101)){
    			$this->vlrTotalded = $this->vlrTotalded+$nvtICMS;
    			$this->vlrTotalIPIded = $this->vlrTotalIPIded + $nvIPI;
    			    			
    		}elseif($row['tiponf']=='V'){
    			$this->vlrTotalIcms = $this->vlrTotalIcms+$nvtICMS;
    			$this->vlrTotalIPI = $this->vlrTotalIPI + $nvIPI;
            }*/ 
            }   		
		} 
		

		//notas canceladas
		$sql0="select idnf,nnfe,substring(idnfe,4) as idnfe 
				from nf 
				where sped = 'Y'
					and tiponf ='V'
					and tpnf !='0'
					and status in ('CANCELADOO')
					and envionfe = 'CANCELADAA'
					and idempresa = ".$idempresa."
					and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'";

		$res0=d::b()->query($sql0);
	
		while($row0=mysqli_fetch_assoc($res0)){
			$C100=$C100+1;
			
			$vnNF=$row0['nnfe'];
			$vchNFe=$row0['idnfe'];		

			$_SESSION['C100'][$C100]="|C100|2|0|||00|55|".$vnNF."|".$vchNFe."||||1|||||||||||||||||\n";
			//Duvidas IND_EMIT COD_PART COD_SIT DT_E_S 	IND_PGTO  VL_ABAT_NT VL_ICMS_ST VL_PIS_ST VL_COFINS_ST
			echo($_SESSION['C100'][$C100]);
		}
    	
    	//NOTA FISCAL DE C0MPRA  MANUAL
	/*
    	$sqld="select idempresa,cpfcnpj,nnfe,emissao,total,dtemissao,idnf
				from vwnfmanual
				where idempresa = ".$idempresa."
				and dtemissao between '".$this->dtInicio."' and '".$this->dtFim."'";
    	$resd=mysql_query($sqld) or die($sqld." erro ao buscar informações do bloco C100 para notas MANUAIS".mysql_error());
    	while($rowd=mysql_fetch_assoc($resd)){
    			
    		$vCNPJf=formatarCPF_CNPJ($rowd['cpfcnpj'],false);
    		$C100=$C100+1;
    		
    		//|C100|0|1|21448477000104|01|00||3917||05082015|05082015|245|0|||245|2|||||||||||||
    		$_SESSION['C100'][$C100]="|C100|0|1|".$vCNPJf."|01|00||".$rowd['nnfe']."||".$rowd['emissao']."|".$rowd['emissao']."|".number_format($rowd['total'], 2, ',','')."|0|||".number_format($rowd['total'], 2, ',','')."|2|||||||||||||\n";
    		echo($_SESSION['C100'][$C100]);
    		
    		$sqlit="select qtd,total,cfop from nfitem where idnf=".$rowd['idnf'];
    		$resit=mysql_query($sqlit) or die("Erro ao buscar itens da nf manual sql=".$sqlit);
    		$nitem=1;
    		while($rowit=mysql_fetch_assoc($resit)){
    			$vlritem=$rowit['total']/$rowit['qtd'];
    			$cfop=$rowit['cfop'];
    			$C170=$C170+1;
	    		//|C170|1|777||1|UN|245||0|000|1407|||||||||||||||||||||||||||
	    		$_SESSION['C170'][$C170]="|C170|".$nitem."|777||".number_format($rowit['qtd'], 2, ',','')."|UN|".number_format($vlritem, 2, ',','')."||0|000|".$rowit['cfop']."|||||||||||||||||||||||||||\n";
	    		echo($_SESSION['C170'][$C170]);
	    		$nitem=$nitem+1;
    		}
    		$C190=$C190+1;
    		//|C190|000|1407||245|0|0|0|0|0|0||    	
    		$_SESSION['C190'][$C190]="|C190|000|".$cfop."||".number_format($rowd['total'], 2, ',','')."|0|0|0,00|0,00|0|0||\n";
    		echo($_SESSION['C190'][$C190]);
    	
    			
    	}
    	*/
    	

    	//energia ENERGIAA so e listado porque 
		//Este registro deve ser apresentado, nas operações de saída, pelos contribuintes do segmento de energia elétrica 
    	$sqle="select p.cpfcnpj,replace(n.nnfe,'/','') as nnfe,replace(dma(n.dtemissao),'/','') as dtemissao,i.qtd,i.basecalc,i.aliqicms,i.valicms,n.pis,n.cofins,i.total
			from nf n,nfitem i,pessoa p
			where p.idpessoa =n.idpessoa
			and n.idnf = i.idnf
			and n.tiponf = 'E' 
			and n.sped = 'Y'
			and n.conssecionaria = 'ENERGIAA' 
			and n.idempresa = ".$idempresa."
			and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
    	$rese=mysql_query($sqle) or die("Erro ao buscar contas de energia sql=".$sqle);
    	$C500=0;
    	$qtde=mysql_num_rows($rese);
    	if($qtde>0){
	    	$rowe=mysql_fetch_assoc($rese);  	
	    	$C500=1;
	    	//energia 
	    	//$_SESSION['C500'][$C500] = "|C500|0|1|06981180000116|06|00|||04|".$rowe['nnfe']."|".$rowe['dtemissao']."|".$rowe['dtemissao']."|".number_format($rowe['total'], 2, ',','')."||".number_format($rowe['qtd'], 2, ',','')."||||".number_format($rowe['basecalc'], 2, ',','')."|".number_format($rowe['valicms'], 2, ',','')."||||".number_format($rowe['pis'], 2, ',','')."|".number_format($rowe['cofins'], 2, ',','')."|||\n";
	    	$_SESSION['C500'][$C500] = "|C500|0|1|06981180000116|06|00|||04|".$rowe['nnfe']."|".$rowe['dtemissao']."|".$rowe['dtemissao']."|".number_format($rowe['total'], 2, ',','')."||".number_format($rowe['qtd'], 2, ',','')."||||0,00|0,00||||0,00|0,00|||||||||\n";
	    	echo($_SESSION['C500'][$C500]);
    	   
	    	$C590=1;
	    	$_SESSION['C590'][$C590] = "|C590|000|1252|".number_format($rowe['aliqicms'], 2, ',','')."|".number_format($rowe['total'], 2, ',','')."|0,00|0,00|||||\n";
	    	echo($_SESSION['C590'][$C590]);
    	} 	
    	//FIM DO BLOCO C
    	//$C990=$C001+$C100+$C170+$C190+1;
    	$C990=$C001+$C100+$C101+$C170+$C190+$C500+$C590+1;
    	$_SESSION['C990']="|C990|".$C990."|\n";
    	echo($_SESSION['C990']);  
     }
    //DOCUMENTOS FISCAIS II - SERVICOS  e TRANSPORTE(ICMS)
	public function bloco_D($idempresa){
		

		$sql="select idempresa as id,xmlret as xml,tiponf,faticms
				from nf
				where sped = 'Y'
				and tiponf = 'T'
			 	and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			 	and envionfe = 'CONCLUIDA'
				and xmlret is not null
			 	and idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
		$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
		$qtdnf = mysql_num_rows($res);
		if($qtdnf>0){
			$qtdd001=0;
		}else{
			$qtdd001=1;
		}
		
		$D001=1;
		$_SESSION['D001']="|D001|".$qtdd001."|\n";
     	echo($_SESSION['D001']);
     	
     	//$sql="select idempresa as id,xml from xmlnf where mes = 'fevereiro' and tipo = 'P' and idempresa=".$idempresa;
     	$sql="select idempresa as id,xmlret as xml,tiponf,faticms,consumo,imobilizado,comercio,idnf,idpessoa
				from nf
				where sped = 'Y'
				and tiponf = 'T'
			 	and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			 	and envionfe = 'CONCLUIDA'
				and xmlret is not null
			 	and idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
     	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
     	
     	$D100=0;
     	$D190=0;
     	while($row=mysql_fetch_assoc($res)){

			$sql0="select * from spedd100 where status in('ATIVO','CORRIGIDO') and  idnf =".$row['idnf'];
			$res0=d::b()->query($sql0);
			$qtd0=mysqli_num_rows($res0);
			if($qtd0>0){
				$row0=mysqli_fetch_assoc($res0);
				$vCNPJ=$row0['vcnpj'];
				$vnCT=$row0['vnct'];
				$vdEmi=$row0['vdemi'];
				$vdRecbto=$row0['vdrecbto'];
				$vchCTe=$row0['vchcte'];
				$vvTPrest=number_format($row0['vvtprest'], 2, ',','');
				$vvBCicms=number_format($row0['vvbcicms'], 2, ',','');
				$vvICMS=number_format($row0['vvicms'], 2, ',','');
				$VL_RED_BC=number_format($row0['vlredbc'], 2, ',','');
				$vcMunIni=$row0['vcmunini'];
				$vcMunFim=$row0['vcmunfim'];
				//01122020
				$diar = substr($vdRecbto, 0, 2); // retorna "de"
				$mesr = substr($vdRecbto, 2, 2); // retorna "de"
				$anor = substr($vdRecbto, 4, 4); // retorna "de"
							
				// colocar a data de entrada no mesmo mês do sped caso ela seja menor que o início do sped
				$dataxml = $anor."-".$mesr."-".$diar;
				//função para comparar se a primeira data e maior que a segunda
				$retcomparadata=comparadata($this->dtInicio,$dataxml);							
				if($retcomparadata=='S'){					
					$vdRecbto =  $this->fim;
				}else{
					$retcomparadata=comparadata($this->dtFim,$dataxml);		
					if($retcomparadata=='N'){					
						$vdRecbto =  $this->fim;
					}
				}
				
				$cteserie = substr($vchCTe, 22, 3); 
                if($cteserie < 100){
                   $cteserie = substr($cteserie, 1,2);
                }
							     		     		
				$D100=$D100+1;
				//|D100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|SUB|NUM_DOC|CHV_CTE|DT_DOC|DT_A_P|TP_CT-e|CHV_CTE_REF|VL_DOC|VL_DESC|IND_FRT|VL_SERV|VL_BC_ICMS|VL_ICMS|VL_NT|COD_INF|COD_CTA|COD_MUN_ORIG|COD_MUN_DEST|
				$_SESSION['D100'][$D100]="|D100|0|1|".$row['idpessoa']."|57|00|".$cteserie."||".$vnCT."|".$vchCTe."|".$vdEmi."|".$vdRecbto."|0||".$vvTPrest."|0,00|9|".$vvTPrest."|".$vvBCicms."|".$vvICMS."|".$VL_RED_BC."||0511|".$vcMunIni."|".$vcMunFim."|\n";
				echo($_SESSION['D100'][$D100]);

				$sql09="select * from spedd190 where status in('ATIVO','CORRIGIDO') and  idnf =".$row['idnf'];
				$res09=d::b()->query($sql09);
				$row09=mysqli_fetch_assoc($res09);

				$CST_ICMS=$row09['csticms'];
				$vCFOP=$row09['vcfop'];
				$vpICMS=number_format($row09['vpicms'], 2, ',','');
				$vvTPrest=number_format($row09['vvtprest'], 2, ',','');
				$vvBCicms=number_format($row09['vvbcicms'], 2, ',','');
				$vvICMS=number_format($row09['vvicms'], 2, ',','');
				$VL_RED_BC=number_format($row09['vlredbc'], 2, ',','');
			
				$D190=$D190+1;
				//|D190|CST_ICMS|CFOP|ALIQ_ICMS|VL_OPR|VL_BC_ICMS|VL_ICMS|VL_RED_BC|COD_OBS|
				$_SESSION['D190'][$D190]="|D190|".$CST_ICMS."|".$vCFOP."|".$vpICMS."|".$vvTPrest."|".$vvBCicms."|".$vvICMS."|".$VL_RED_BC."||\n";
				echo($_SESSION['D190'][$D190]);
				
			   $this->vlrTotalded = $this->vlrTotalded+$vvICMS;

			}else{
     	
				// passar string para UTF-8
				$xml=$row['xml'];
				
				//Carregar o XML em UTF-8
				$doc = DOMDocument::loadXML($xml);
				//inicia lendo as principais tags do xml da nfe
				$cteProc = $doc->getElementsByTagName("cteProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
				$CTe = $cteProc->getElementsByTagName("CTe")->item(0);
				$protCTe = $cteProc->getElementsByTagName("protCTe")->item(0);
				
				$infProt = $protCTe->getElementsByTagName("infProt")->item(0);
				$chCTe = $infProt->getElementsByTagName("chCTe")->item(0);
				$vchCTe =($chCTe->textContent);
				
				$infCte = $CTe->getElementsByTagName("infCte")->item(0);
				
				$dest = $infCte->getElementsByTagName("emit")->item(0);
				$enderDest = $dest->getElementsByTagName("enderEmit")->item(0);
				
				$ide = $infCte->getElementsByTagName("ide")->item(0);
				
				$nCT = $ide->getElementsByTagName("nCT")->item(0);
				$vnCT =($nCT->textContent);
			
			$cMunIni= $ide->getElementsByTagName("cMunIni")->item(0);
				$vcMunIni =($cMunIni->textContent);
			
			$cMunFim= $ide->getElementsByTagName("cMunFim")->item(0);
				$vcMunFim =($cMunFim->textContent);
				
				$UFIni = $ide->getElementsByTagName("UFIni")->item(0);
				$vUFIni =($UFIni->textContent);  

				$UFFim = $ide->getElementsByTagName("UFFim")->item(0);
				$vUFFim =($UFFim->textContent);
				
				
				
				$vPrest = $infCte->getElementsByTagName("vPrest")->item(0);
				$vTPrest = $vPrest->getElementsByTagName("vTPrest")->item(0);
				$vvTPrest=($vTPrest->textContent);
				
				$imp = $infCte->getElementsByTagName("imp")->item(0);
				$ICMS = $imp->getElementsByTagName("ICMS")->item(0);
				
				$ICMS40= $ICMS->getElementsByTagName("ICMS40")->item(0);
				$ICMSSN= $ICMS->getElementsByTagName("ICMSSN")->item(0);
				$ICMS45= $ICMS->getElementsByTagName("ICMS45")->item(0);
				$ICMS10= $ICMS->getElementsByTagName("ICMS10")->item(0);
				$ICMS20= $ICMS->getElementsByTagName("ICMS20")->item(0);
				$ICMS70= $ICMS->getElementsByTagName("ICMS70")->item(0);
				$ICMS00= $ICMS->getElementsByTagName("ICMS00")->item(0);
				$ICMS60= $ICMS->getElementsByTagName("ICMS60")->item(0);
				$ICMSSN500= $ICMS->getElementsByTagName("ICMSSN500")->item(0);
				$ICMSSN101= $ICMS->getElementsByTagName("ICMSSN101")->item(0);
				$ICMSSN102= $ICMS->getElementsByTagName("ICMSSN102")->item(0);
				$ICMSSN103= $ICMS->getElementsByTagName("ICMSSN103")->item(0);
				
				$ICMSSN201= $ICMS->getElementsByTagName("ICMSSN201")->item(0);
				$ICMSSN202= $ICMS->getElementsByTagName("ICMSSN202")->item(0);
				$ICMSSN203= $ICMS->getElementsByTagName("ICMSSN203")->item(0);
				
				$ICMSSN300= $ICMS->getElementsByTagName("ICMSSN300")->item(0);
				$ICMSSN400= $ICMS->getElementsByTagName("ICMSSN400")->item(0);
				$ICMSSN900= $ICMS->getElementsByTagName("ICMSSN900")->item(0);
				
				
				if($ICMSSN103!=null){
					die("erro CONFIGURAR ICMSSN103".$row['idnf']);
				}
				
				if($ICMSSN201!=null){
					die("erro CONFIGURAR ICMSSN201".$row['idnf']);
				}
				
				if($ICMSSN203!=null){
					die("erro CONFIGURAR ICMSSN203".$row['idnf']);
				}
				if($ICMSSN300!=null){
					die("erro CONFIGURAR ICMSSN300".$row['idnf']);
				}
				if($ICMSSN400!=null){
					die("erro CONFIGURAR ICMSSN400".$row['idnf']);
				}
				
				if($ICMSSN!=null){
					// echo("HE 40\n");
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMSSN->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				
				}elseif($ICMS40!=null){
					// echo("HE 40\n");
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS40->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				
				}elseif($ICMS45!=null){
					// echo("HE 40\n");
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS45->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				
				}elseif($ICMS20!=null){
				
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMS20->getElementsByTagName("vBC")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
				
					$vRedBC=$vvProd-$vvBCicms;
				
					//Aliguota 	ALIQ_ICMS
					$pICMS= $ICMS20->getElementsByTagName("pICMS")->item(0);
					$vpICMS =($pICMS->textContent);
					$vpICMS =number_format($vpICMS, 2, ',','');
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMS20->getElementsByTagName("vICMS")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS20->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					// echo("HE 20\n");
				}elseif($ICMS70!=null){
				
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMS70->getElementsByTagName("vBC")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
				
					$vRedBC=$vvProd-$vvBCicms;
				
					//Aliguota 	ALIQ_ICMS
					$pICMS= $ICMS70->getElementsByTagName("pICMS")->item(0);
					$vpICMS =($pICMS->textContent);
					$vpICMS =number_format($vpICMS, 2, ',','');
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMS70->getElementsByTagName("vICMS")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS70->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					// echo("HE 20\n");
				}elseif($ICMS10!=null){				   
				
				//icms do item VL_BC_ICMS
					$vBCicms= $ICMS10->getElementsByTagName("vBC")->item(0);
					$vvBCicms =($vBCicms->textContent);   
					$vvBCicms =number_format($vvBCicms, 2, ',',''); 

					$vRedBC=$vvProd-$vvBCicms;
							
					//Aliguota 	ALIQ_ICMS
					$pICMS= $ICMS10->getElementsByTagName("pICMS")->item(0);
					$vpICMS =($pICMS->textContent);
					$vpICMS =number_format($vpICMS, 2, ',',''); 
					
					// Vlr icms item VL_ICMS
					$vICMS=$ICMS10->getElementsByTagName("vICMS")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',',''); 
					
					//Situação Tributária referente ao ICMS CST_ICMS
					//$vCST=$ICMS10->getElementsByTagName("CST")->item(0);
					//$vvCST =($vCST->textContent);
					$vvCST ='00';
					// echo("HE 20\n");					    
				}elseif($ICMS00!=null){
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMS00->getElementsByTagName("vBC")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
				
					$vRedBC=0;
				
					//Aliguota 	ALIQ_ICMS
					$pICMS= $ICMS00->getElementsByTagName("pICMS")->item(0);
					$vpICMS =($pICMS->textContent);
					$vpICMS =number_format($vpICMS, 2, ',','');
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMS00->getElementsByTagName("vICMS")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS00->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					
				}elseif($ICMS60!=null){
					
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMS60->getElementsByTagName("vBCSTRet")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
					$vRedBC=$vvProd-$vvBCicms;
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMS60->getElementsByTagName("vICMSSTRet")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributaria referente ao ICMS CST_ICMS
					$vCST=$ICMS60->getElementsByTagName("CST")->item(0);
					$vvCST =($vCST->textContent);
					// echo("HE 20<BR>");
					//zerar pos este ST nao tem aliq
					$vpICMS =0;
				
				}elseif($ICMSSN500!=null){
				
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMSSN500->getElementsByTagName("vBCSTRet")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMSSN500->getElementsByTagName("vICMSSTRet")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributaria referente ao ICMS CST_ICMS
					$vCST=$ICMSSN500->getElementsByTagName("CSOSN")->item(0);
					$vvCST =($vCST->textContent);
					// echo("HE 20<BR>");
					$vpICMS =0;
					$vRedBC==0;
						
				}elseif($ICMSSN101!=null){
					
					$vCST=$ICMSSN101->getElementsByTagName("CSOSN")->item(0);
					$vvCST =($vCST->textContent);
					
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				}elseif($ICMSSN102!=null){
					
					$vCST=$ICMSSN102->getElementsByTagName("CSOSN")->item(0);
					$vvCST =($vCST->textContent);
						
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				}elseif($ICMSSN202!=null){
					//icms do item VL_BC_ICMS
					$vBCicms= $ICMSSN202->getElementsByTagName("vBCST")->item(0);
					$vvBCicms =($vBCicms->textContent);
					$vvBCicms =number_format($vvBCicms, 2, ',','');
				
					$vRedBC=$vvProd-$vvBCicms;
				
					//Aliguota 	ALIQ_ICMS
					$pICMS= $ICMSSN202->getElementsByTagName("pICMSST")->item(0);
					$vpICMS =($pICMS->textContent);
					$vpICMS =number_format($vpICMS, 2, ',','');
				
					// Vlr icms item VL_ICMS
					$vICMS=$ICMSSN202->getElementsByTagName("vICMSST")->item(0);
					$vvICMS =($vICMS->textContent);
					$vvICMS =number_format($vvICMS, 2, ',','');
				
					//Situação Tributária referente ao ICMS CST_ICMS
					$vCST=$ICMS00->getElementsByTagName("CSOSN")->item(0);
					$vvCST =($vCST->textContent);
				
				}elseif($ICMSSN900!=null){
					
					$vCST=$ICMSSN900->getElementsByTagName("CSOSN")->item(0);
					$vvCST =($vCST->textContent);
					
					$vpICMS =0;
					$vvBCicms =0;
					$vRedBC=0;
					$vvICMS=0;
				}
				
				
				//COD_PART
				$CNPJ = $dest->getElementsByTagName("CNPJ")->item(0);
				$vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ>
				if(empty($CNPJ) or empty($vCNPJ)){
					//COD_PART
					$CNPJ = $dest->getElementsByTagName("CPF")->item(0);
					$vCPF =($CNPJ->textContent); //pegar o valor da tag <CPF>
					$vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ>
				}
				
				$dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
				$vdEmi =($dEmi->textContent); //pegar o valor da tag <chNFe>
				//2013-01-14
				$dia = substr($vdEmi, 8, 2); // retorna "de"
				$mes = substr($vdEmi, 5, 2); // retorna "de"
				$ano = substr($vdEmi, 0, 4); // retorna "de"
				$vdEmi=$dia.$mes.$ano;
				
				$dhRecbto = $protCTe->getElementsByTagName("dhRecbto")->item(0);
				$vdRecbto =($dhRecbto->textContent); //pegar o valor da tag <chNFe>
				//2013-01-14
				$diar = substr($vdRecbto, 8, 2); // retorna "de"
				$mesr = substr($vdRecbto, 5, 2); // retorna "de"
				$anor = substr($vdRecbto, 0, 4); // retorna "de"
				$vdRecbto=$diar.$mesr.$anor;
				
				// colocar a data de entrada no mesmo mês do sped caso ela seja menor que o início do sped
				$dataxml = $anor."-".$mesr."-".$diar;
				//função para comparar se a primeira data e maior que a segunda
				$retcomparadata=comparadata($this->dtInicio,$dataxml);
				
				if($retcomparadata=='S'){
					$vdRecbto=$this->fim;
				}
					if($vvCST<100){
					$CST_ICMS="0".$vvCST;
				}else{
					$CST_ICMS=$vvCST;
				}  		
				
	/*    		if($vvCST==20){
					$CST_ICMS="020";
				}elseif($vvCST==70){
					$CST_ICMS="070";
				}elseif($vvCST==40){
					$CST_ICMS="040";
				}elseif($vvCST==60){
					$CST_ICMS="060";
				}elseif($vvCST==51){
					$CST_ICMS="051";
				}elseif($vvCST==00){
					$CST_ICMS="000";
				}else{
					$CST_ICMS=$vvCST;
				}
				
	*/
				
				if($vUFIni=="MG" and $vUFFim=="MG"){
					$vCFOP='1352';

					//if($vUFFim=="MG"){
						$VL_RED_BC =number_format('0,00', 2, ',','');
						$vvBCicms =number_format('0,00', 2, ',','');
						$vvICMS =number_format('0,00', 2, ',','');
						$vpICMS =number_format('0,00', 2, ',','');
					/*}else{
						$VL_RED_BC=$vvTPrest-$vvBC;
						$VL_RED_BC =number_format($VL_RED_BC, 2, ',','');
					}*/
					
				}else{
					$vCFOP='2352';   
					//$VL_RED_BC =number_format($VL_RED_BC, 2, ',','');
					//$vvBCicms =number_format($vvBCicms, 2, ',','');
					//$vvICMS =number_format($vvICMS, 2, ',','');
					//$vpICMS =number_format($vpICMS, 2, ',','');
					$VL_RED_BC=$vvTPrest-$vvBC;
					$VL_RED_BC =number_format($VL_RED_BC, 2, ',','');
				}
				
				if(empty($vvICMS)){// ser for vazio vai ficar 0.00
					$vvBCicms =number_format('0,00', 2, ',','');
					$vvICMS =number_format('0,00', 2, ',','');
					$vpICMS =number_format('0,00', 2, ',','');
				}
			
				
				
				$vvTPrest =number_format($vvTPrest, 2, ',','');			
								
				$cteserie = substr($vchCTe, 22, 3); 
                if($cteserie < 100){
                   $cteserie = substr($cteserie, 1,2);
                }
							
				$D100=$D100+1;
				//|D100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|SUB|NUM_DOC|CHV_CTE|DT_DOC|DT_A_P|TP_CT-e|CHV_CTE_REF|VL_DOC|VL_DESC|IND_FRT|VL_SERV|VL_BC_ICMS|VL_ICMS|VL_NT|COD_INF|COD_CTA|COD_MUN_ORIG|COD_MUN_DEST|
				$_SESSION['D100'][$D100]="|D100|0|1|".$row['idpessoa']."|57|00|".$cteserie."||".$vnCT."|".$vchCTe."|".$vdEmi."|".$vdRecbto."|0||".$vvTPrest."|0,00|9|".$vvTPrest."|".$vvBCicms."|".$vvICMS."|".$VL_RED_BC."||0511|".$vcMunIni."|".$vcMunFim."|\n";
				echo($_SESSION['D100'][$D100]);
			
				$D190=$D190+1;
				//|D190|CST_ICMS|CFOP|ALIQ_ICMS|VL_OPR|VL_BC_ICMS|VL_ICMS|VL_RED_BC|COD_OBS|
				$_SESSION['D190'][$D190]="|D190|".$CST_ICMS."|".$vCFOP."|".$vpICMS."|".$vvTPrest."|".$vvBCicms."|".$vvICMS."|".$VL_RED_BC."||\n";
				echo($_SESSION['D190'][$D190]);
				
				$this->vlrTotalded = $this->vlrTotalded+$vvICMS;
			}
     	}
     	
     	$D990=$D190+$D100+$D001+1;
     	$_SESSION['D990']="|D990|".$D990."|\n";
     	echo($_SESSION['D990']);		
     }
    //APURAcaO DO ICMS E DO IPI
	public function bloco_E($idempresa){
		
		$vlrSomaIcms =number_format($this->vlrTotalIcms, 2, ',','');
		$vlrSomaded =number_format($this->vlrTotalded, 2, ',','');
		
		$VL_ICMS_RECOLHER=$this->vlrTotalIcms - $this->vlrTotalded;
		
		$VL_ICMS_RECOLHER=number_format($VL_ICMS_RECOLHER, 2, ',','');		
		
     	$_SESSION['E001']="|E001|0|\n";
     	echo($_SESSION['E001']);
     	//APURAÇÃO ICMS
     	$_SESSION['E100']="|E100|".$this->inicio."|".$this->fim."|\n";
     	echo($_SESSION['E100']);
     	//$_SESSION['E110']="|E110|".$vlrSomaIcms."|0,00|0,00|0,00|".$vlrSomaded."|0,00|0,00|0,00|0,00|".$vlrSomaIcms."|".$vlrSomaded."|".$VL_ICMS_RECOLHER."|0,00|0,00|\n";
     	$_SESSION['E110']="|E110|".$vlrSomaIcms."|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|\n";
     	echo($_SESSION['E110']);     	
     	//$_SESSION['E116']="|E116|000|".$VL_ICMS_RECOLHER."|".$this->fim."|1214|||||".$this->mesAno."|\n";   
     	$_SESSION['E116']="|E116|000|0,00|".$this->fim."|1214|||||".$this->mesAno."|\n";
     	echo($_SESSION['E116']);
     	
     	
     	$E316=0;
     	$linhasE=0;
     	foreach ($_SESSION['uf'] as $key => $value) {
     		
     		//echo $key." = ".$value ."\n";
     		
     		$value =number_format($value, 2, ',','');
     		$E316=$E316+1;
     		
     		$_SESSION['E300'][$E316]="|E300|".$key."|".$this->inicio."|".$this->fim."|\n";
     		$_SESSION['E310'][$E316]="|E310|1|0|".$value."|0|0|0|".$value."|0|".$value."|0|0|0|0|0|0|0|0|0|0|0|0|\n";
     		$_SESSION['E316'][$E316]="|E316|000|".$value."|".$this->fim."|100080|||||".$this->mesAno."|\n";
     		echo($_SESSION['E300'][$E316]);
     		echo($_SESSION['E310'][$E316]);
     		echo($_SESSION['E316'][$E316]);
     		$linhasE=$linhasE+3;
     	}
        
     	$linhasET=$linhasE+5;
     	
     	
     	/*
     	$_SESSION['E300'][$E316]="|E300|MG|01062016|30062016|\n";
     	$_SESSION['E310'][$E316]="|E310|1|0|103,5|0|0|0|0|0|103,5|0|103,5|0|0|\n";
     	$_SESSION['E316'][$E316]="|E316|000|103,5|30062016|7187|||||062016|\n";
     	*/
        
        /*
        |E500|0|01102019|31102019|
        |E510|2101|99|2877,91|2877,91|287,79|
        |E510|2101|50|618,3|618,3|61,83|
        |E520|0|0|349,62|0|0|349,62|0|
         */
        
        $sqli="select x.cfop,x.ipint as cstipi ,sum(x.valor) as valor,sum(x.bcipi) basecalc,sum(x.valipi) vipi
				from nf n join nfitemxml x on(x.idnf=n.idnf and x.status='Y' and x.valipi>0 )
				where n.sped = 'Y'
				and n.tiponf ='C'
				and n.faticms='Y'
			 	and n.status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			 	and n.envionfe = 'CONCLUIDA'
                and n.idempresa = ".$idempresa."
				and n.prazo between '".$this->dtInicio."' and '".$this->dtFim."' group by x.cfop";
        
        $resi=mysql_query($sqli) or die($sqli." erro ao buscar informações do bloco E500".mysql_error());
        $qtdipi = mysql_num_rows($resi);
        if($qtdipi>0){
        $_SESSION['E500']="|E500|0|".$this->inicio."|".$this->fim."|\n";
     	echo($_SESSION['E500']);
        $linhasET=$linhasET+1;
        $E510=0;
        $VALIPI=0;
        while($rowi=mysql_fetch_assoc($resi)){
            $_SESSION['E510'][$E510]="|E510|".$rowi['cfop']."|".$rowi['cstipi']."|".number_format($rowi['valor'], 2, ',','')."|".number_format($rowi['basecalc'], 2, ',','')."|".number_format($rowi['vipi'], 2, ',','')."|\n";
            echo($_SESSION['E510'][$E510]);
            $linhasET=$linhasET+1;
            $E510=$E510+1;
            $VALIPI=$VALIPI+$rowi['vipi'];
        }
        $_SESSION['E520']="|E520|0|0|".number_format($VALIPI, 2, ',','')."|0|0|".number_format($VALIPI, 2, ',','')."|0|\n";       
        echo($_SESSION['E520']);
        $linhasET=$linhasET+1;
        /*
     	//APURAÇÃO IPI
     	
     	$vlrIpiDebito =number_format($this->vlrTotalIPI, 2, ',','');
     	$vlrIpiCredito =number_format($this->vlrTotalIPIded, 2, ',','');
     	$VL_IPI_RECOLHER= $this->vlrTotalIPIded-$this->vlrTotalIPI;
     	$VL_IPI_RECOLHER=number_format($VL_IPI_RECOLHER, 2, ',','');
     	$_SESSION['E520']="|E520|0,00|".$vlrIpiDebito."|".$vlrIpiCredito."|0,00|0,00|0,00|0,00|\n";
     	echo($_SESSION['E520']);
     	*/
        }
     	
     	
     	
     	$_SESSION['E990']="|E990|".$linhasET."|\n";	
     	echo($_SESSION['E990']);	
     }
    //CONTROLE DO CReDITO DE ICMS DO ATIVO PERMANENTE - CIAP - modelos 
	public function bloco_G(){
     	$_SESSION['G001']="|G001|1|\n";
     	echo($_SESSION['G001']);
     	$_SESSION['G990']="|G990|2|\n";	
     	echo($_SESSION['G990']);	
     }
    //INVENTaRIO FiSICO
	public function bloco_H($idempresa){
		//die('inventario='.$this->inventario);
		// se for informar o inventario de itens
		if($this->inventario=='Y'){
			$H=1;
			$_SESSION['H001']="|H001|0|\n"; 			
			echo($_SESSION['H001']);
		/*	
			$sqlv="select sum(vlr) as vlrf from(						
					   select ROUND((p.vlrcompra*(sum(f.qtd)/p.uncom)),2) as vlr,p.idprodserv 
					   from prodserv p,lote l,lotefracao f,unidade u
					   where l.idprodserv = p.idprodserv
											   and l.status in ('APROVADO','QUARENTENA')
						and p.un is not null
					   and p.tipo = 'PRODUTO'
					   and p.idempresa = ".cb::idempresa()."
					   and p.insumo = 'Y' 
					   and p.comprado = 'Y' 
					   and p.vlrcompra != '0.00'
											   and u.idtipounidade = 3
											   and f.idunidade = u.idunidade
											   and f.status='DISPONIVEL'
											   AND f.idlote =l.idlote
					   and f.qtd >0
					   and p.uncom != '0.00'
						group by p.idprodserv
					   union
					   select ROUND(((sum(f.qtd))*if(p.vlrvenda >0,p.vlrvenda, v.valor)),2) as vlr,p.idprodserv
					   from prodserv p,lote l,prodvalor v,lotefracao f,unidade u
						where v.idaliqicms = 1 and v.status = 'ATIVO'						
					   and v.idprodserv = p.idprodserv
					   and l.idprodserv = p.idprodserv
											   and l.status in ('APROVADO','QUARENTENA')
					   and p.un is not null
					   and p.tipo = 'PRODUTO'
					   and p.idempresa = ".cb::idempresa()."
											   and u.idtipounidade in(3,5)
											   and f.idunidade = u.idunidade
											   and f.status='DISPONIVEL'
											   AND f.idlote =l.idlote
					   and f.qtd >0
					   and (p.venda = 'Y' or p.fabricado = 'Y')
						group by p.idprodserv
				) as a";
				*/
		   $sqlv="select sum(vlritemir) as vlrf from spedh010 where idempresa = ".$idempresa." and mes='12' and ano='".$this->anoinventario."' and status='ATIVO'";
		   die( $sqlv);
			$resv=mysql_query($sqlv) or die("Erro ao buscar valor total do estoque sql:".$sqlv);
			$rowv=mysql_fetch_assoc($resv);
			
			$novoaano=intval($this->ano);
			$anoant=$novoaano-1;
			$vlrf=number_format($rowv['vlrf'], 2, ',','');
			$H=$H+1;
			$_SESSION['H005']="|H005|3112".$anoant."|".$vlrf."|01|\n"; 			
			echo($_SESSION['H005']);
			/*
			$sqlp="select  p.idprodserv,ROUND((sum(f.qtd/p.uncom)),2) as qtdf,ROUND(((sum(f.qtd)/p.uncom)*p.vlrcompra),2) as vlritem,rtrim(ltrim(p.codprodserv)) as codprodserv,p.vlrcompra,p.descr,p.un
					from prodserv p,lote l,lotefracao f,unidade u
					where l.status ='APROVADO'
				   and l.idprodserv = p.idprodserv
					and p.un is not null
					and p.tipo = 'PRODUTO'
				   and p.idempresa = ".cb::idempresa()."
									   and l.idunidade in (8)
				   and p.insumo = 'Y' 
				   and p.comprado = 'Y'
				   and p.vlrcompra != '0.00'
									   and u.idtipounidade = 3
									   and f.idunidade = u.idunidade
									   and f.status='DISPONIVEL'
									   and f.idlote =l.idlote
									   and f.qtd >0
				   and p.uncom != '0.00'
					group by p.idprodserv
				   UNION
				   select p.idprodserv,ROUND(sum(f.qtd),2) as qtdf,
									   ROUND(sum((f.qtd)* if(p.vlrvenda >0,p.vlrvenda, v.valor)),2) as vlritem,
									   rtrim(ltrim(p.codprodserv)) as codprodserv,
									   if(p.vlrvenda >0,p.vlrvenda, v.valor) as vlrcompra,
									   p.descr,
									   p.un
					from prodserv p,lote l,prodvalor v,lotefracao f,unidade u
					where v.idaliqicms = 1 and v.status = 'ATIVO'
				   and v.idprodserv = p.idprodserv
				   and l.status ='APROVADO'
				   and l.idprodserv = p.idprodserv
									   and u.idtipounidade in(3,5)
									   and f.idunidade = u.idunidade
									   and f.status='DISPONIVEL'
									   AND f.idlote =l.idlote
									   and f.qtd >0
				   and p.un is not null
					and p.idempresa = ".cb::idempresa()."
				   and (p.venda = 'Y' or p.fabricado = 'Y')
				   and p.tipo = 'PRODUTO'
				   group by p.idprodserv";
				   */
		   $sqlp="select s.idprodserv,rtrim(ltrim(p.codprodserv)) as codprodserv,upper(p.un) as un,sum(s.qtd) as qtdf, round((sum(s.vlrcompra)/count(*)),2) as  vlrcompra
				, round((sum(s.vlritem)/count(*)),2) as vlritem,
				rtrim(ltrim(
					CASE 
						WHEN p.descrcurta IS NOT NULL AND p.descrcurta <> '' THEN p.descrcurta
						ELSE p.descr
					END 
				)) as descr  
				,round((sum(s.vlritemir)/count(*)),2) as vlritemir
			from spedh010 s join  prodserv p on(p.idprodserv=s.idprodserv)
			where s.idempresa = ".$idempresa." 
			and s.ano='".$this->anoinventario."'
			and s.mes='12'
			and s.status='ATIVO' group by s.idprodserv";


			$resp=mysql_query($sqlp)or die("Erro ao buscar produtos sql:".$sqlp);
			//echo($sqlp);
			$H10=0;
			while($rowp=mysql_fetch_assoc($resp)){
				$H10=$H10+1;
				//"|REG|COD_ITEM|UNID|QTD|VL_UNIT|VL_ITEM|IND_PROD|COD_PART|TXT_COMPL|COD_CTA|
				$_SESSION['H010'][$H10]="|H010|".trim(rmAc($rowp['codprodserv']))."|".trim(rmAc($rowp['un']))."|".number_format($rowp['qtdf'], 2, ',','')."|".number_format($rowp['vlrcompra'], 2, ',','')."|".number_format($rowp['vlritem'], 2, ',','')."|0||".trim(rmAc($rowp['descr']))."|0511|".number_format($rowp['vlritemir'], 2, ',','')."|\n";
				echo($_SESSION['H010'][$H10]); 				
			} 			
					
			$H=$H+1+$H10;
			$_SESSION['H990']="|H990|".$H."|\n";
			echo($_SESSION['H990']);
		}else{//neste caso não e informado o inventario de itens
			$_SESSION['H001']="|H001|0|\n";
			echo($_SESSION['H001']); 	
			$_SESSION['H005']="|H005|".$this->fim."|0|01|\n";
			echo($_SESSION['H005']);
			$_SESSION['H990']="|H990|3|\n";
			echo($_SESSION['H990']);
		}     		
	}
     //ORDENS DE PRODUCAO E CONSUMO DE PRODUTOS (ligação com 0200 e 0210 em relacao a composição dos produtos) hermesp 05-02-2016
     public function bloco_K($idempresa){
     	$sqlk="select w.idlote,upper(p.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv,rtrim(ltrim(
				CASE 
					WHEN p.descrcurta IS NOT NULL AND p.descrcurta <> '' THEN p.descrcurta
					ELSE p.descr
				END 
			)) as descr,replace(dma(w.fabricacao),'/','') as fabricacao,round(w.qtdprod) as qtdprod,w.qtdprod_exp,p.fabricado,p.venda
			from lote w,prodserv p,unidade u 
			where  p.venda = 'Y'
			and p.tipo = 'PRODUTO'
			and w.qtdprod > 0
			and u.idempresa = w.idempresa
			and u.idunidade =w.idunidade
			and u.idtipounidade in (5,3)
			and w.idempresa = ".$idempresa."
			and w.idprodserv = p.idprodserv
			and w.fabricacao between  '".$this->dtInicio."' and '".$this->dtFim."'
                        and exists (select 1 from lotecons c,lote l,prodserv p
					    where p.idprodserv = l.idprodserv
					    and l.idlote = c.idlote
					    and c.idobjeto=w.idlote
                                            -- and c.idempresa = ".$idempresa."
                                            and p.idprodserv != w.idprodserv
					    -- and (c.qtdd_exp ='' or c.qtdd_exp is null)
					    and c.qtdd >0                                            
					    and c.tipoobjeto = 'lote'
						and c.idtransacao is null
						and p.comprado='Y'
						)";
     	$resk=mysql_query($sqlk) or die("Erro ao iniciar blocoK sql=".$sqlk);
     	$qtdk=mysql_num_rows($resk);
     	
     	if($qtdk>0){
     		$K='0';
     	}else{
     		$K='1';
     	}
     	
     	$K990=1;
     	$_SESSION['K001']="|K001|".$K."|";
     	echo($_SESSION['K001']."\n");

		 $K990++;
     	$_SESSION['K010']="|K010|0|";
     	echo($_SESSION['K010']."\n");
		
     	if($qtdk>0){
     	$K990=$K990+1;
     	$_SESSION['K100'][1]="|K100|".$this->inicio."|".$this->fim."|";
     	echo($_SESSION['K100'][1]."\n");
     		$K230=0;
     		$K235=0;
     		while($rowk=mysql_fetch_assoc($resk)){

				$rowk['qtdprod']=recuperaExpoente(tratanumero($rowk["qtdprod"]), $rowk["qtdprod_exp"]);

				if(strpos(strtolower($rowk['qtdprod']),"d")){
					$arrvlr=explode("d",$rowk['qtdprod']);
					$rowk['qtdprod']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
				  
				}elseif(strpos(strtolower($rowk['qtdprod']),"e")){
					$arrvlr=explode("e",$rowk['qtdprod']);
					$rowk['qtdprod']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
				  
				} 
     			
     			$K990=$K990+1;
     			$K230=$K230+1;
     			$_SESSION['K230'][$K230]="|K230|".$this->inicio."|".$this->fim."|".$rowk['idlote']."|".trim(rmAc($rowk['codprodserv']))."|".number_format($rowk['qtdprod'], 2, ',','')."|";
     			echo($_SESSION['K230'][$K230]."\n");
                    if($rowk['fabricado']=='Y'){
			$sqlk1="select round(sum(c.qtdd)) as qtd,c.qtdd_exp,upper(p.un) as un,rtrim(ltrim(p.codprodserv)) as codprodserv
					    from lotecons c,lote l,prodserv p
					    where p.idprodserv = l.idprodserv
					    and l.idlote = c.idlote
                        -- and l.idempresa = ".$idempresa."
					    and c.idobjeto=".$rowk['idlote']."
					   -- and (c.qtdd_exp ='' or c.qtdd_exp is null)
					    and c.qtdd >0
						and p.comprado='Y'
					    and c.tipoobjeto = 'lote'
						and c.idtransacao is null
					    group by p.codprodserv";
                        
                        
                        
                        
                        
                        
     			$resk1=mysql_query($sqlk1) or die('Erro ao buscar produtos utilizado na  OP sql='.$sqlk1);
     			while($rowk1=mysql_fetch_assoc($resk1)){
			    if($rowk1['qtd']>0 and $rowk1['qtd']!=""){

					$rowk1['qtd_exp']=recuperaExpoente(tratanumero($rowk1["qtd"]), $rowk1["qtdd_exp"]);

					if(strpos(strtolower($rowk1['qtd_exp']),"d")){
						$arrvlr=explode("d",$rowk1['qtd_exp']);
						$rowk1['qtd_exp']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
					  
					}elseif(strpos(strtolower($rowk1['qtd_exp']),"e")){
						$arrvlr=explode("e",$rowk1['qtd_exp']);
						$rowk1['qtd_exp']=tratadouble($arrvlr[0]);//Multipicacao direta da diluicao
					  
					} 
     			
	     			$K990=$K990+1;
	     			$K235=$K235+1;
	     			$_SESSION['K235'][$K235]="|K235|".$rowk['fabricacao']."|".trim(rmAc($rowk1['codprodserv']))."|".number_format($rowk1['qtd_exp'], 2, ',','')."||";
	     			echo($_SESSION['K235'][$K235]."\n");
			    }
     			}
                    }
     		}
     	}
     	
     	$K990=$K990+1;
     	$_SESSION['K990']="|K990|".$K990."|\n";
     	echo($_SESSION['K990']);
     }
    //OUTRAS INFORMAcoES
	public function bloco_1(){
     	$_SESSION['1001']="|1001|0|\n";
     	echo($_SESSION['1001']);
     	$_SESSION['1010']="|1010|N|N|N|N|N|N|N|N|N|N|N|N|N|\n";
     	echo($_SESSION['1010']);     	
     	$_SESSION['1990']="|1990|3|\n";	
     	echo($_SESSION['1990']);	
     }
     
     //Bloco 9 e Encerramento do arquivo digital
     public function bloco_9(){
    	   	echo("|9001|0|\n"); 
    	   	if(count($_SESSION['0000'])>0){ 
    	   		echo("|9900|0000|".count($_SESSION['0000'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0000']);
    	   	}
     		if(count($_SESSION['0001'])>0){ 
    	   		echo("|9900|0001|".count($_SESSION['0001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0001']);
    	   	}
                if(count($_SESSION['0002'])>0){ 
    	   		echo("|9900|0002|".count($_SESSION['0002'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0002']);
    	   	}
     		/*if(count($_SESSION['0007'])>0){ 
    	   		echo("|9900|0007|".count($_SESSION['0007'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0007']);
    	   	}*/
    	   	if(count($_SESSION['0005'])>0){	 
    	   		echo("|9900|0005|".count($_SESSION['0005'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0005']);
    	   	} 
    	   	if(count($_SESSION['0100'])>0){
    	   		echo("|9900|0100|".count($_SESSION['0100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0100']);
    	   	}
         	if(count($_SESSION['0150'])>0){
    	   		echo("|9900|0150|".count($_SESSION['0150'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0150']);
    	   	}    	   
     		if(count($_SESSION['0190'])>0){
    	   		echo("|9900|0190|".count($_SESSION['0190'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0190']);
    	   	}    	   	
     		if(count($_SESSION['0200'])>0){
    	   		echo("|9900|0200|".count($_SESSION['0200'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0200']);
    	   	}
			if(count($_SESSION['0205'])>0){
				echo("|9900|0205|".count($_SESSION['0205'])."|\n"); 
				$l9900=$l9900 +1;
				$linha=$linha+count($_SESSION['0205']);
			}
		   	if(count($_SESSION['0210'])>0){
    	   		echo("|9900|0210|".count($_SESSION['0210'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0210']);
    	   	}			
			if(count($_SESSION['0220'])>0){
				echo("|9900|0220|".count($_SESSION['0220'])."|\n");
				$l9900=$l9900 +1;
				$linha=$linha+count($_SESSION['0220']);
			}			
    	   	if(count($_SESSION['0990'])){
    	   		echo("|9900|0990|".count($_SESSION['0990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0990']);    	   	
    	   	}
                if(count($_SESSION['B001'])>0){
    	   		echo("|9900|B001|".count($_SESSION['B001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['B001']); 
    	   	}
     		if(count($_SESSION['B990'])>0){
    	   		echo("|9900|B990|".count($_SESSION['B990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['B990']); 
    	   	}
    	   	if(count($_SESSION['C001'])>0){
    	   		echo("|9900|C001|".count($_SESSION['C001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C001']);    	   	
    	   	}
    	   	if(count($_SESSION['C100'])>0){
    	   		echo("|9900|C100|".count($_SESSION['C100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C100']);
    	   	}
    	   	if(count($_SESSION['C101'])>0){
    	   		echo("|9900|C101|".count($_SESSION['C101'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C101']);
    	   	}
    	   	if(count($_SESSION['C170'])>0){ 
    	   		echo("|9900|C170|".count($_SESSION['C170'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C170']);
    	   	}
    	   	if(count($_SESSION['C190'])){	 
    	   		echo("|9900|C190|".count($_SESSION['C190'])."|\n"); 	
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C190']);
    	   	}
         	if(count($_SESSION['C500'])){	 
    	   		echo("|9900|C500|".count($_SESSION['C500'])."|\n"); 	
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C500']);
    	   	}
         	if(count($_SESSION['C590'])){	 
    	   		echo("|9900|C590|".count($_SESSION['C590'])."|\n"); 	
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C590']);
    	   	}    	   	
    	   	if(count($_SESSION['C990'])>0){
    	   		echo("|9900|C990|".count($_SESSION['C990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C990']); 
    	   	}  
    	   	if(count($_SESSION['D001'])>0){
    	   		echo("|9900|D001|".count($_SESSION['D001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['D001']); 
    	   	} 
    	   	if(count($_SESSION['D100'])>0){
    	   		echo("|9900|D100|".count($_SESSION['D100'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['D100']);
    	   	}
    	   	if(count($_SESSION['D190'])>0){
    	   		echo("|9900|D190|".count($_SESSION['D190'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['D190']);
    	   	}
    	   	if(count($_SESSION['D990'])>0){
    	   		echo("|9900|D990|".count($_SESSION['D990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['D990']); 
    	   	} 
    	    if(count($_SESSION['E001'])>0){
    	   		echo("|9900|E001|".count($_SESSION['E001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E001']); 
    	   	} 
    		if(count($_SESSION['E100'])>0){
    	   		echo("|9900|E100|".count($_SESSION['E100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E100']); 
    	   	}
         	if(count($_SESSION['E110'])>0){
    	   		echo("|9900|E110|".count($_SESSION['E110'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E110']); 
    	   	}    	   
    	   	if(count($_SESSION['E116'])>0){
    	   		echo("|9900|E116|".count($_SESSION['E116'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E116']); 
    	   	}    	
    	   	if(count($_SESSION['E300'])>0){
    	   		echo("|9900|E300|".count($_SESSION['E300'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E300']);
    	   	}
    	   	if(count($_SESSION['E310'])>0){
    	   		echo("|9900|E310|".count($_SESSION['E310'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E310']);
    	   	}
    	   	if(count($_SESSION['E316'])>0){
    	   		echo("|9900|E316|".count($_SESSION['E316'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E316']);
    	   	}
    	   	if(count($_SESSION['E500'])>0){
    	   		echo("|9900|E500|".count($_SESSION['E500'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E500']); 
    	   	}  
                if(count($_SESSION['E510'])>0){
    	   		echo("|9900|E510|".count($_SESSION['E510'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E510']); 
    	   	}  
    	   	if(count($_SESSION['E520'])>0){
    	   		echo("|9900|E520|".count($_SESSION['E520'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E520']); 
    	   	} 
     		if(count($_SESSION['E990'])>0){
    	   		echo("|9900|E990|".count($_SESSION['E990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['E990']); 
    	   	}
     		if(count($_SESSION['G001'])>0){
    	   		echo("|9900|G001|".count($_SESSION['G001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['G001']); 
    	   	}
     		if(count($_SESSION['G990'])>0){
    	   		echo("|9900|G990|".count($_SESSION['G990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['G990']); 
    	   	}
     		if(count($_SESSION['H001'])>0){
    	   		echo("|9900|H001|".count($_SESSION['H001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['H001']); 
    	   	}
    	   	if(count($_SESSION['H005'])>0){
    	   		echo("|9900|H005|".count($_SESSION['H005'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['H005']);
    	   	}
    	   	if(count($_SESSION['H010'])>0){
    	   		echo("|9900|H010|".count($_SESSION['H010'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['H010']);
    	   	}
     		if(count($_SESSION['H990'])>0){
    	   		echo("|9900|H990|".count($_SESSION['H990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['H990']); 
    	   	}
    	   	if(count($_SESSION['K001'])>0){
    	   		echo("|9900|K001|".count($_SESSION['K001'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['K001']);
    	   	}
			if(count($_SESSION['K010'])>0){
				echo("|9900|K010|".count($_SESSION['K010'])."|\n");
				$l9900=$l9900 +1;
				$linha=$linha+count($_SESSION['K010']);
			}
    	   	if(count($_SESSION['K100'])>0){
    	   		echo("|9900|K100|".count($_SESSION['K100'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['K100']);
    	   	}
    	   	if(count($_SESSION['K230'])>0){
    	   		echo("|9900|K230|".count($_SESSION['K230'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['K230']);
    	   	}
    	   	if(count($_SESSION['K235'])>0){
    	   		echo("|9900|K235|".count($_SESSION['K235'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['K235']);
    	   	}
    	   	if(count($_SESSION['K990'])>0){
    	   		echo("|9900|K990|".count($_SESSION['K990'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['K990']);
    	   	}
          	if(count($_SESSION['1001'])>0){
    	   		echo("|9900|1001|".count($_SESSION['1001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['1001']); 
    	   	}
     		if(count($_SESSION['1010'])>0){
    	   		echo("|9900|1010|".count($_SESSION['1010'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['1010']); 
    	   	}
            if(count($_SESSION['1990'])>0){
    	   		echo("|9900|1990|".count($_SESSION['1990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['1990']); 
    	   	}

    	   	echo("|9900|9001|1|\n");$l9900=$l9900 +4;
			echo("|9900|9900|".$l9900."|\n");
			echo("|9900|9990|1|\n");
			echo("|9900|9999|1|\n");
			$l9900=$l9900+3;
			echo("|9990|".$l9900."|\n");
			$linha=$linha+$l9900;
			echo("|9999|".$linha."|");     	   	
    }
        
} 

$sped = new sped();
$sped->inicio=$inicio;
$sped->fim=$fim;
$sped->mesAno=$mesano;
$sped->dtInicio=$dtInicio;
$sped->dtFim=$dtFim;
$sped->mes=$mes;
$sped->ano=$ano; 
$sped->anoinventario=$anoinventario;
$sped->inventario=$inventario;
$sped->veridnf=$veridnf;
try {
	$sped->bloco_0($idempresa); //Abertura do Arquivo Digital e Identificação da entidade Dados do Contabilista
} catch (Exception $e) {
    die('Erro Bloco O '.$e->getMessage()."\n");
}
try {
	$sped->bloco_B($idempresa);
} catch (Exception $e) {
    die('Erro Bloco B '.$e->getMessage()."\n");
}
try {
$sped->bloco_C($idempresa); // Abertura do Bloco C Nota Fiscal Eletronica (codigo 55) 
} catch (Exception $e) {
    die('Erro Bloco C '.$e->getMessage()."\n");
}
try {
$sped->bloco_D($idempresa);//DOCUMENTOS FISCAIS II - SERVIcOS (ICMS)
} catch (Exception $e) {
    die('Erro Bloco D '.$e->getMessage()."\n");
}
try {
$sped->bloco_E($idempresa);//APURAcaO DO ICMS E DO IPI
} catch (Exception $e) {
    die('Erro Bloco E '.$e->getMessage()."\n");
}
try {
$sped->bloco_G();//CONTROLE DO CReDITO DE ICMS DO ATIVO PERMANENTE - CIAP - modelos
} catch (Exception $e) {
    die('Erro Bloco G '.$e->getMessage()."\n");
} 
try {
$sped->bloco_H($idempresa);//INVENTaRIO FiSICO
} catch (Exception $e) {
    die('Erro Bloco H '.$e->getMessage()."\n");
}
try {
$sped->bloco_K($idempresa);//CONTROLE DA PRODUÇÃO E DO ESTOQUE
} catch (Exception $e) {
    die('Erro Bloco K '.$e->getMessage()."\n");
}
try {
$sped->bloco_1();//OUTRAS INFORMAcoES
} catch (Exception $e) {
    die('Erro Bloco 1 '.$e->getMessage()."\n");
}
try {
$sped->bloco_9(); //Bloco 9 e Encerramento do arquivo digital
} catch (Exception $e) {
    die('Erro Bloco 9 '.$e->getMessage()."\n");
}