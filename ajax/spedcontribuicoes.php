<?php
/*HERMES 08032013
 * GERAR SPED PIS/COFINS - SPED CONTRIBUIÇÕES
 * PRODUTOS E SERVIÇOS
 */
//ini_set("display_errors","1");
//error_reporting(E_ALL);
require_once("../inc/php/functions.php");
$mes=$_GET["mes"];
$ano=$_GET["ano"];
$idempresa=$_GET["_idempresa"];
$veridnf=$_GET["veridnf"];
//$dtInicio=$_GET["dtInicio"];
//$dtFim=$_GET["dtFim"];


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
	$_header="SPED_".$mes."_".$ano;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $_header);	
	//gera o csv
	header("Content-type: text/txt; charset=UTF-8");
	header("Content-Disposition: attachment; filename=".$infilename.".txt");
	header("Pragma: no-cache");
	header("Expires: 0");
######### FIM GERAR TXT ##########################################################	


//Limpar Sessões anteriores
unset($_SESSION['0000']); 
unset($_SESSION['0001']); 
unset($_SESSION['0140']); 
unset($_SESSION['0100']); 
unset($_SESSION['0150']); 
unset($_SESSION['0190']);
unset($_SESSION['0200']); 
unset($_SESSION['0500']);
unset($_SESSION['0990']); 
unset($_SESSION['A001']);
unset($_SESSION['A010']);
unset($_SESSION['A100']);
unset($_SESSION['A170']);
unset($_SESSION['A990']);
unset($_SESSION['C001']);
unset($_SESSION['C010']); 
unset($_SESSION['C100']); 
unset($_SESSION['C170']); 
unset($_SESSION['C990']); 
unset($_SESSION['D001']); 
unset($_SESSION['D990']); 
unset($_SESSION['F001']); 
unset($_SESSION['F600']); 
unset($_SESSION['F990']); 
unset($_SESSION['M001']); 
unset($_SESSION['M200']);
unset($_SESSION['M205']);
unset($_SESSION['M210']);
unset($_SESSION['M400']);
unset($_SESSION['M410']);
unset($_SESSION['M600']);
unset($_SESSION['M605']);
unset($_SESSION['M610']);
unset($_SESSION['M800']);
unset($_SESSION['M810']);
unset($_SESSION['M990']); 
unset($_SESSION['1001']); 
unset($_SESSION['1990']); 

//Formatar datas de inicio e fim
if(!empty($mes) and !empty($ano)){
    /*
	$dia = substr($dtInicio, -2, 2); // retorna "de"
	$mes = substr($dtInicio, -5, -3); // retorna "de"
	$ano = substr($dtInicio, 0, -6); // retorna "de"
     
     */
	
	/*
	$dia = substr($dtFim, -2, 2); // retorna "de"
	$mes = substr($dtFim, -5, -3); // retorna "de"
	$ano = substr($dtFim, 0, -6); // retorna "de"         
     */
        $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
        $inicio="01".$mes.$ano;
	    $fim=$ultimo_dia.$mes.$ano;
        
        $dtInicio=$ano."-".$mes.'-01';
        $dtFim=$ano."-".$mes.'-'.$ultimo_dia;
        
        //die($dtFim);
}else{
 die("É necessario informar o inicio o e fim do envio");
}
//Montar a variavel mesano de faturamento do ICMS
if(!empty($mes) and !empty($ano)){
$mesano=$mes.$ano;
}else{
 die("Informar o mês de vencimento do ICMS");
}


$renda=traduzid('empresa','idempresa','renda',$idempresa);

if($renda!='P' and $renda!='R'){die("Falha ao identificar na empresa o tipo de renda");}

/* http://www.fazenda.gov.br/confaz/confaz/atos/atos_cotepe/2008/ac009_08.htm
BLOCO 0 Abertura, Identificação e Referências
BLOCO C Documentos Fiscais I  - Mercadorias (ICMS/IPI)
BLOCO D Documentos Fiscais II - Serviços (ICMS)
BLOCO E Apuração do ICMS e do IPI
BLOCO G Controle do Crédito de ICMS do Ativo Permanente - CIAP - modelos "C" e "D'
BLOCO H Inventário Físico
BLOCO 1 Outras Informações
BLOCO 9 Controle e Encerramento do Arquivo Digital
*/
class sped{  
	public $inicio;
	public $fim;
	public $dtInicio;
	public $dtFim;
	public $nf;
	public $cnpj;
	public $mesAno;
	public $codPart;
	public $vlrTotalIcms;
	public $vlrRetpis;
	public $vlrTotal06;
	public $vlrRetcofins;
	public $vlrnftotal;
	public $renda;
    public $vlrbcpiscofins;
	public $veridnf;
		  
    //// BLOCO 0 - Abertura do Arquivo Digital e Identificação da entidade
    public function bloco_0($idempresa){
    	$sql="select idempresa as id,concat ('|0000|006|0|0||".$this->inicio."|".$this->fim."|',ifnull(e.razaosocial,''),'|',
		ifnull(e.cnpj,''),'|',ifnull(e.uf,''),'|',ifnull(e.cmun,''),'||00|1|') as inf, e.cnpj as cnpj
		from empresa e where idempresa = ".$idempresa;
    	//|0000|002|0|0||01012011|31012011|MOGIANA VEICULOS LTDA|59849299000104|SP|3549409||00|1|
    	//|0000|007|0|0||01012013|31012013|LAUDO LABORATORIO|23259427000104|MG|3170206||00|1|
    	
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0000".mysql_error());    	
    	while ($row=mysql_fetch_assoc($res)){    	
			$this->cnpj = $row['cnpj'];
      		$_SESSION['0000']=$row['inf']."\n";
      		$l0000=$l0000+1;
    	} 
    	 /*DADOS DA EMPRESA EMITENTE DO SPED*/     
    	echo($_SESSION['0000']);
    	
    	$l0000=$l0000+1;
    	$_SESSION['0001']="|0001|0|\n";
    	echo($_SESSION['0001']);
    	
    	//$l0000=$l0000+1;
    	//$_SESSION['0007']="|0007|||\n";
    	//echo($_SESSION['0007']);
    	   	
    	$l0000=$l0000+1;
    	$_SESSION['0100']="|0100|Hugney Ferreira de Miranda|71333061668|50607-MG|18119289000128|38400683|Rua Ivaldo Alves do Nascimento|966||Aparecida|3432919100|3432919100|leandro@aserco.com.br|3170206|";
    	 /*DADOS DO CONTADDOR*/
    	echo($_SESSION['0100']."\n");
    	
    	$l0000=$l0000+1;
		if($this->renda == 'R' ){
			$_SESSION['0110']="|0110|1|1|||";
		}else{
			$_SESSION['0110']="|0110|2|2|||";
		}
    	
    	echo($_SESSION['0110']."\n");
    	    	
    	//Dados Complementares da entidade
    	$sql="select idempresa as id,
		concat ('|0140|',ifnull(e.cnpj,''),'|',nomefantasia,'|',ifnull(e.cnpj,''),'|',ifnull(e.uf,''),'|',ifnull(inscestadual,''),'|',ifnull(e.cmun,''),'|',ifnull(InscricaoMunicipalPrestador,''),'||') as inf
    	from empresa e where idempresa = ".$idempresa;
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0140".mysql_error());    	
    	while ($row=mysql_fetch_assoc($res)){       		
    		$_SESSION['0140']=$row['inf']."\n";
    		$l0000=$l0000+1;
    	}    	
    	echo($_SESSION['0140']);

		if($this->renda=='R'){
			$sqldp="UNION ALL
			select n.idempresa as id,n.tiponf,n.xmlret as xml,'P' as tipo
			from nf n
			where sped = 'Y'
			and tiponf ='C'
			and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			and exists(select 1  from spedc100 s where s.idnf=n.idnf and s.vvpis>0)
			and envionfe = 'CONCLUIDA'
			and n.idempresa = ".$idempresa."
			and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
		}else{
			$sqldp="";
		}
    	
    	//$sqld="select idempresa as id,xmlret	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null and idempresa=".$idempresa;
    	//$sqld="select idempresa as id,xml,tipo from xmlnf where mes='abril' and idempresa=".$idempresa;
  	$sqld="select n.idempresa as id,n.tiponf,n.xmlret as xml,'P' as tipo
                from nf n  join natop o on(o.idnatop=n.idnatop and o.natop  like('%VENDA%') and o.natop not like('%DEVOLU%'))
				where n.sped = 'Y'
				and n.status = 'CONCLUIDO'
				and n.envionfe = 'CONCLUIDA'
				and n.idempresa = ".$idempresa."
				and n.tiponf ='V'
				and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'
			UNION ALL
				select n.idempresa as id,'V' as tiponf,l.xml,'S' as tipo   
			    from notafiscal n,nfslote l			       
			    where l.status = 'SUCESSO'
				and l.nnfe= n.nnfe
				and n.idempresa = ".$idempresa."
				and n.emissao between ('".$this->dtInicio."') and ('".$this->dtFim."')
				and n.status  in ('FATURADO','CONCLUIDO')".$sqldp;
    	
  /*    	$sqld="select n.idempresa as id,l.xml,'S' as tipo
			    from notafiscal n,nfslote l
			    where l.status = 'SUCESSO'
				and l.nnfe= n.nnfe
				and n.idempresa = ".$idempresa."
				and n.emissao between ('".$this->dtInicio."') and ('".$this->dtFim."')
				and n.status = 'FATURADO'";
    	*/
    	
		$resd=mysql_query($sqld) or die($sqld." erro ao buscar informações do bloco 0150".mysql_error());
    	$p=0;
    	$arrcodpar=array();
    	 while($rowd=mysql_fetch_assoc($resd)){
	    		
    	 	if($rowd['tipo']=="P"){
		    	// passar string para UTF-8
		      	$xml=$rowd['xml'];
		      	//Carregar o XML em UTF-8      			
		      	$doc = DOMDocument::loadXML($xml);	
		      	//inicia lendo as principais tags do xml da nfe
			    $nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
			    $NFe = $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
			    $infNFe = $NFe->getElementsByTagName("infNFe")->item(0);      			
		        //buscando informações do destinatario / Participante
				if($rowd['tiponf']=='C'){
					$dest = $infNFe->getElementsByTagName("emit")->item(0); 
				}else{
					$dest = $infNFe->getElementsByTagName("dest")->item(0);
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
		
			    //xNome
			    $xNome = $dest->getElementsByTagName("xNome")->item(0); 
			    $vNome =($xNome->textContent); //pegar o valor da tag <xNome> 			    	   
				
			    if($rowd['tiponf']=='C'){
					$enderDest = $dest->getElementsByTagName("enderEmit")->item(0);
				}else{
					$enderDest = $dest->getElementsByTagName("enderDest")->item(0);
				}

				//COD_PAIS
				$cPais = $enderDest->getElementsByTagName("cPais")->item(0); 
			    $vcPais =($cPais->textContent); //pegar o valor da tag <cPais> 
			    
			    //IE
			    $IE = $dest->getElementsByTagName("IE")->item(0);
			    $vIE =($IE->textContent); //pegar o valor da tag <IE> 
			    if($vIE=="ISENTO"){
			    	$vIE="";
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
					    	
    	 	}elseif($rowd['tipo']=='S'){
    	 		// passar string para UTF-8
		      	$xml=$rowd['xml'];
		      	//Carregar o XML em UTF-8      			
		      	$doc = DOMDocument::loadXML($xml);	
		      	
		      	$ReqEnvioLoteRPS = $doc->getElementsByTagName("ReqEnvioLoteRPS")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
			    $Lote = $ReqEnvioLoteRPS->getElementsByTagName("Lote")->item(0); 	      		
			    $RPS = $Lote->getElementsByTagName("RPS")->item(0); 
			    
			    $CNPJ = $RPS->getElementsByTagName("CPFCNPJTomador")->item(0); 
			    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CPFCNPJTomador> 
		    	$qtdval=strlen($vCNPJ);
		    	if($qtdval=="11"){
		    	$vCPF=$vCNPJ;
		    	}		    	
			    //xNome
			    $xNome = $RPS->getElementsByTagName("RazaoSocialTomador")->item(0); 
			    $vNome =($xNome->textContent); //pegar o valor da tag <xNome> 
			    
			   $vcPais ="1058"; //pegar o valor da tag <cPais> BRASIL
			    
			    //IE
			    $vIE="";
			    
			      //xNome
			    $codcidade = $RPS->getElementsByTagName("MunicipioPrestacao")->item(0); 
			    $vcodcidade =($codcidade->textContent); //pegar o valor da tag <xNome> 
			    
			    if(!empty($vcodcidade)){
			     $sqlc="SELECT * FROM nfscidadesiaf where codcidade =".$vcodcidade;
			     $resc=mysql_query($sqlc);
			     $rowc=mysql_fetch_assoc($resc);
			     $vcMun = $rowc['cmunfg'];
			    }
			   
			    //END
			    $xLgr = $RPS->getElementsByTagName("LogradouroTomador")->item(0); 
			    $vxLgr =($xLgr->textContent); //pegar o valor da tag <xLgr>
			    //NUM 	    
			    $nro = $RPS->getElementsByTagName("NumeroEnderecoTomador")->item(0); 
			    $vnro =($nro->textContent); //pegar o valor da tag <nro>
			    //if($vnro=="S/N" or $vnro == "S/Nº"){
			    //	$vnro="0";
			    //}	 
			    //BAIRRO   
			    //$xBairro = $enderDest->getElementsByTagName("BairroTomador")->item(0); 
			    //$vxBairro =($xBairro->textContent); //pegar o valor da tag <cPais>     	 		
    	 	}	    	 	
    	 	 /*
    	 	  * SO DEVE ADICIONAR O CODIGO DO PARTICIPANTE UMA VEZ NA LISTA GERADA 0150
    	 	  * */
    	 	if(in_array($vCNPJ, $arrcodpar)) { 
			    echo "";
			}else{	  
				$p=$p+1;  
			    $l0000=$l0000+1;	    
			    $arrcodpar[$p]=$vCNPJ;
			    if($vCPF==$vCNPJ){
			    	$vCNPJ="";
			    	$codpart=$vCPF;
			    }else{
			    	$vCPF="";
			    	$codpart=$vCNPJ;
			    }
		    	$_SESSION['0150'][$p]=("|0150|".$codpart."|".$vNome."|".$vcPais."|".$vCNPJ."|".$vCPF."|".$vIE."|".$vcMun."||".$vxLgr."|".$vnro."||".$vxBairro."|");		
				 /*
				  * 0150 DECLARA OS PARTICIPANTES
				  * */
		    	echo($_SESSION['0150'][$p]."\n");
			}
    	}
    	 /*
    	  * DESCREVE AS UNIDADES DE MEDIDA 0190
    	  * */
    	$l0000=$l0000+1;
    	$_SESSION['0190'][1]="|0190|FR|Frasco|";
    	echo($_SESSION['0190'][1]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][2]="|0190|AN|Analise|";
    	echo($_SESSION['0190'][2]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][3]="|0190|CX|Caixa|";
    	echo($_SESSION['0190'][3]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][4]="|0190|UN|Unidade|";
    	echo($_SESSION['0190'][4]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][5]="|0190|ML|Mili litros|";
    	echo($_SESSION['0190'][5]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][6]="|0190|TB|Tubo|";
    	echo($_SESSION['0190'][6]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][7]="|0190|PC|Peca|";
    	echo($_SESSION['0190'][7]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][8]="|0190|EA|EA|";
    	echo($_SESSION['0190'][8]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0190'][9]="|0190|PCT|Pacote|";
    	echo($_SESSION['0190'][9]."\n");
    	
       	
         /*
          * DESCREVE OS ITEMS NO 0200
          * */
        
        /*
    	 * PARA SERVIÇO FOI COLOCADO O CODIGO 777 PARA TODOS OS SERVIÇOS
    	* */
    	$l0000=$l0000+1;
    	$_SESSION['0200'][1]="|0200|777|SERVIÇOS LABORATORIAIS|||AN|09|||00|||";
    	echo($_SESSION['0200'][1]."\n");
        if($this->renda=='R'){
            $_SESSION['0200'][2]="|0200|888|Insumos para serviço|||UN|09|||00|||";
            $r0200=2;
            $l0000=$l0000+1;
            echo($_SESSION['0200'][2]."\n");
        }else{
            $r0200=1;
        }
        
        
         $arrcodprod=array();
         //produtos vendidos
       	 $sqlpprod="select p.idprodserv,rtrim(ltrim(ifnull(p.descrcurta,p.descr))) as descr,p.codprodserv,upper(p.un) as un
                from nf n,nfitem i,prodserv p, natop o
                where p.idprodserv = i.idprodserv
                and n.idnf = i.idnf
                and n.sped = 'Y'
                and n.status = 'CONCLUIDO'
                and n.envionfe = 'CONCLUIDA'
                and n.tiponf ='V'
                and n.idempresa = ".$idempresa."
                and o.idnatop=n.idnatop 
                and o.natop  like('%VENDA%')
                and o.natop not like('%DEVOLU%')
                and n.dtemissao between '".$this->inicio."'  and '".$this->dtFim."' group by p.idprodserv;";
       	 $resprod=mysql_query($sqlpprod) or die("Erro ao pesquisar unidades");
       	
       	 while($rowprod=mysql_fetch_assoc($resprod)){
       	 	
       	 	if(in_array($rowprod['codprodserv'], $arrcodprod)) {
       	 		echo "";
       	 	}else{
       	 		$c=$c+1;
       	 		$r0200=$r0200+1;
       	 		$l0000=$l0000+1;
       	 		$arrcodprod[$c]=$rowprod['codprodserv'];
       	 		 
       	 		$_SESSION['0200'][$r0200]="|0200|".$rowprod['codprodserv']."|".$rowprod['descr']."|||".$rowprod['un']."|04||||||";
                        echo($_SESSION['0200'][$r0200]."\n");
       	 	}
       	 	
       	 }
        /*
    	$l0000=$l0000+1;
    	$_SESSION['0200'][1]="|0200|02|Antígeno MG INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][1]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][2]="|0200|03|Antígeno MS INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][2]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][3]="|0200|04|Antígeno PUL INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][3]."\n");

    	
    	/*
    	$l0000=$l0000+1;
    	$_SESSION['0200'][5]="|0200|05|PUL LENTO INATA - 50 testes|||FR|04||||||";
    	echo($_SESSION['0200'][5]."\n");
   
    	$l0000=$l0000+1;
    	$_SESSION['0200'][6]="|0200|00020025|CALDO FREY - MEIO DE ISOL. P/ MYCOPLASMA (1 ML)|||UN|04||||||";
    	echo($_SESSION['0200'][6]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][7]="|0200|92|MEIO DE TRANSPORTE|||UN|04||||||";
    	echo($_SESSION['0200'][7]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][8]="|0200|00025025|CALDO FREY (25 tubos c/ 2,5 ml cada)|||UN|04||||||";
    	echo($_SESSION['0200'][8]."\n");
    	    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][9]="|0200|005|Material para coleta de amostra(s)|||CX|07||||||";
    	echo($_SESSION['0200'][9]."\n");
   
    	$l0000=$l0000+1;
    	$_SESSION['0200'][10]="|0200|79|Antígeno HI MG INATA - 25 testes|||FR|04||||||";
    	echo($_SESSION['0200'][10]."\n");

    	$l0000=$l0000+1;
    	$_SESSION['0200'][11]="|0200|78|Antígeno HI MS INATA - 25 testes|||FR|04||||||";
    	echo($_SESSION['0200'][11]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][12]="|0200|006|Botijao de Nitrogenio|||UN|07||||||";
    	echo($_SESSION['0200'][12]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][13]="|0200|CMT-060|Caldo Meio de Transporte MAPA|||TB|07||||||";
    	echo($_SESSION['0200'][13]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][14]="|0200|10|Antissoro SAR MG (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][14]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][15]="|0200|11|Antissoro SAR MS (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][15]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][16]="|0200|09|Antissoro SAR PUL (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][16]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][17]="|0200|13|ANTÍGENOS E REAGENTES|||FR|04||||||";
    	echo($_SESSION['0200'][17]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][18]="|0200|08|Antissoro de HI MG (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][18]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][19]="|0200|12|Antissoro de HI MS (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][19]."\n");
    	
 //#######   	///////
    	$l0000=$l0000+1;
    	$_SESSION['0200'][20]="|0200|IATG|Antígeno MG INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][20]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][21]="|0200|IATP|Antígeno PUL INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][21]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][22]="|0200|MCA|Material para coleta de amostras|||CX|07||||||";
    	echo($_SESSION['0200'][22]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][23]="|0200|IATS|Antígeno MS INATA - 300 testes|||FR|04||||||";
    	echo($_SESSION['0200'][23]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][24]="|0200|IHIS|Antissoro de HI MG (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][24]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][25]="|0200|IHIG|Antissoro de HI MS (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][25]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][26]="|0200|CFRY|CALDO FREY (25 tubos c/ 2,5 ml cada)|||UN|04||||||";
    	echo($_SESSION['0200'][26]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][27]="|0200|IAG|Antissoro SAR MG (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][27]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][28]="|0200|IANEG|Antissoro negativo p/ SAR |||FR|04||||||";
    	echo($_SESSION['0200'][28]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][29]="|0200|IAS|Antissoro SAR MS (CONTROLE INATA)|||FR|04||||||";
    	echo($_SESSION['0200'][29]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][30]="|0200|IALP|Antígeno PUL Lento - 50 testes|||FR|04||||||";
    	echo($_SESSION['0200'][30]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][31]="|0200|IANLP|Antissoro SAL PUL|||FR|04||||||";
    	echo($_SESSION['0200'][31]."\n");
    	
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][32]="|0200|IAP|Antissoro SAL PUL|||FR|04||||||";
    	echo($_SESSION['0200'][32]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][33]="|0200|IAHIS|Antissoro HI MS|||FR|04||||||";
    	echo($_SESSION['0200'][33]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][34]="|0200|IAHIG|Antissoro HI MG|||FR|04||||||";
    	echo($_SESSION['0200'][34]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][35]="|0200|CMT|CALDO MEIO DE TRANSPORTE|||ML|04||||||";
    	echo($_SESSION['0200'][35]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][36]="|0200|IHIE|ANTIGENO HIEDS - 1 TESTE|||FR|04||||||";
    	echo($_SESSION['0200'][36]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][37]="|0200|BOTNIT|Botijao de Nitrogenio|||PC|07||||||";
    	echo($_SESSION['0200'][37]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][38]="|0200|IKER|KIT ELISA PARA REUVIRUS|||CX|04||||||";
    	echo($_SESSION['0200'][38]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][39]="|0200|IKEB|KIT ELISA PARA BRONQUITE|||CX|04||||||";
    	echo($_SESSION['0200'][39]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][40]="|0200|IKEE|KIT ELISA PARA ENCEFALOMIELITE|||CX|04||||||";
    	echo($_SESSION['0200'][40]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][41]="|0200|A11002|TQ CRIOGÊNICO CPL CHART SC11|||EA|07||||||";
    	echo($_SESSION['0200'][41]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][42]="|0200|SRMG|SORO REFERÊNCIA MYCOPLASMA GALLISEPTICUM (MG) - 1 ML|||FR|04||||||";
    	echo($_SESSION['0200'][42]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][43]="|0200|SRMS|SORO REFERÊNCIA MYCOPLASMA SYNOVIAE (MS) - 1 ML|||FR|04||||||";
    	echo($_SESSION['0200'][43]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][44]="|0200|3057323|FR 500ML RED SORO DOS AD/BD NAT|||ML|04||||||";
    	echo($_SESSION['0200'][44]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][45]="|0200|SPCAV|ANTISSORO BRUTO DE ADENOVÍRUS GRUPO 1 SOROTIPO 1|||ML|04||||||";
    	echo($_SESSION['0200'][45]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][46]="|0200|BDA|SORO BDA CONTRA VÍRUS DE GUMBORO|||FR|04||||||";
    	echo($_SESSION['0200'][46]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][47]="|0200|CBOT|Canister para Botijao|||PC|07||||||";
    	echo($_SESSION['0200'][47]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][48]="|0200|1020400462|PLACA DE PETRI(150X15mm)ESTERIL TRATADO P/RAD. IONIZANTE-PACOTE C/10|||PCT|07||||||";
    	echo($_SESSION['0200'][48]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][49]="|0200|99-09260|IDEXX IBD 5/SOLID 99-09260 - R|||CX|04||||||";
    	echo($_SESSION['0200'][49]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][50]="|0200|99-09262|IDEXX IBV 5/SOLID 99-09262 - R|||CX|04||||||";
    	echo($_SESSION['0200'][50]."\n");
    	
    	$l0000=$l0000+1;
    	$_SESSION['0200'][51]="|0200|A11001|TQ CRIOGÊNICO CPL CHART XC47|||EA|07||||||";
    	echo($_SESSION['0200'][51]."\n");
    	
      	/*
    	$l0000=$l0000+1;
    	$_SESSION['0200'][5]="|0200|5U1 -0000000756|TR ELETRICA|||AN|09|||00|1401|0,00|";
    	echo($_SESSION['0200'][5]."\n");
        */
        $l0000=$l0000+1;
    	$_SESSION['0500']="|0500|01012018|09|A|1|2606|Vendas a Prazo|||\n";
	echo($_SESSION['0500']);
    	$l0000=$l0000+1;
    	$_SESSION['0990']="|0990|".$l0000."|\n";
    	echo($_SESSION['0990']);
    }    

     /*
      * LISTA AS NOTAS FISCAIS DE SERVIÇO
      * */
    public function bloco_A($idempresa){
   	    	//$sql="select idempresa as id	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null and idempresa=".$idempresa;
    	//$sql="select idempresa as id from xmlnf where mes='abril' and tipo = 'S' and  idempresa=".$idempresa;
    	$sql="select n.idempresa as id
				from   notafiscal n,nfslote l
				where l.status = 'SUCESSO'
				and l.nnfe= n.nnfe
				and n.idempresa = ".$idempresa."
				and n.emissao between ('".$this->dtInicio."') and ('".$this->dtFim."')
				and n.status  in ('FATURADO','CONCLUIDO')";
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco A001".mysql_error());
    	$qtdnf = mysql_num_rows($res);  	
    	if($qtdnf>0){
    		$qtda001=0;
    	}else{
    		$qtda001=1;
    	}
    	$qtda=1;
    	$_SESSION['A001']="|A001|".$qtda001."|\n";
      	echo($_SESSION['A001']);
      	
      	if($qtdnf>0){
	    	$qtda=$qtda+1;
	    	$_SESSION['A010']="|A010|".$this->cnpj."|\n";
	    	 /*
	    	  * INICIA O BLOCO A
	    	  * */
	    	echo($_SESSION['A010']);	
	    	
	    	//$sql="select idempresa as id,xml	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null  and idempresa=".$idempresa;
	    	//$sql="select idempresa as id,xml from xmlnf where mes='abril' and tipo = 'S' and idempresa=".$idempresa;
    		$sql="select n.idempresa as id,l.xml
			from notafiscal n,nfslote l
			where l.status = 'SUCESSO'
			and l.nnfe= n.nnfe
			and n.idempresa = ".$idempresa."
			and n.emissao between ('".$this->dtInicio."') and ('".$this->dtFim."')
			and n.status in ('FATURADO','CONCLUIDO')";
	    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco A100".mysql_error());
	    	$qtdnf = mysql_num_rows($res);
	    		$A100=0;
	    		$A170=0;
	    	   	while ($row=mysql_fetch_assoc($res)){
	      			$A100=$A100+1;
	      			$n=$n+1;
	      			// passar string para UTF-8
	      			$xml=$row['xml'];
	      			//Carregar o XML em UTF-8      			
	      			$doc = DOMDocument::loadXML($xml);	
	 				$ReqEnvioLoteRPS = $doc->getElementsByTagName("ReqEnvioLoteRPS")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
	
	 				$Lote = $ReqEnvioLoteRPS->getElementsByTagName("Lote")->item(0); 	      		
				    $RPS = $Lote->getElementsByTagName("RPS")->item(0); 
				    $Cabecalho = $ReqEnvioLoteRPS->getElementsByTagName("Cabecalho")->item(0);
				    				    
				    $CNPJ = $RPS->getElementsByTagName("CPFCNPJTomador")->item(0); 
				    $COD_PART =($CNPJ->textContent); //pegar o valor da tag <CPFCNPJTomador>  COD_PART	
				    
				    $TipoRecolhimento = $RPS->getElementsByTagName("TipoRecolhimento")->item(0); 
				    $SER=($TipoRecolhimento->textContent); //pegar o valor da tag <TipoRecolhimento>  SER	
	
				    $NumeroRPS = $RPS->getElementsByTagName("NumeroRPS")->item(0); 
				    $NUM_DOC=($NumeroRPS->textContent); //pegar o valor da tag <TipoRecolhimento>  NUM_DOC
				    $NUM_DOC=$NUM_DOC+18;//diferença sped laudo
				    
				    $DataEmissaoRPS = $RPS->getElementsByTagName("DataEmissaoRPS")->item(0); 
				    $DT_DOC=($DataEmissaoRPS->textContent); //pegar o valor da tag <DataEmissaoRPS>  DT_DOC
				    $DT_DOC =strtotime($DT_DOC);
					$vDT_DOC = date ("dmY", $DT_DOC);
					
					$ValorTotalServicos = $Cabecalho->getElementsByTagName("ValorTotalServicos")->item(0); 
				    $VL_DOC=($ValorTotalServicos->textContent); //pegar o valor da tag <ValorTotalServicos>  VL_DOC
				    
				    $ValorPIS = $RPS->getElementsByTagName("ValorPIS")->item(0); 
				    $VL_PIS_RET=($ValorPIS->textContent); //pegar o valor da tag <ValorPIS>  VL_PIS
				    $VL_PIS_RET =number_format($VL_PIS_RET, 2, ',','');
				    
				    $ValorCOFINS = $RPS->getElementsByTagName("ValorCOFINS")->item(0); 
				    $VL_COFINS_RET=($ValorCOFINS->textContent); //pegar o valor da tag <ValorCOFINS>  VL_COFINS
				    $VL_COFINS_RET =number_format($VL_COFINS_RET, 2, ',','');
					
					$ValorPIS = $RPS->getElementsByTagName("ValorPIS")->item(0); 
					$VL_PIS_NF=($ValorPIS->textContent); //pegar o valor da tag <ValorPIS>  VL_PIS
				   /* 					
				    if($VL_PIS>0){
				    	$VL_BC =$VL_DOC;			    	
				    }else{
				    	//$VL_BC="0,00";
				    	$VL_BC =$VL_DOC;	
				    }
				 	*/			    
				 	//$VL_PIS=$VL_BC*0.0065;
			   		//$VL_PIS =number_format($VL_PIS, 2, ',','');
				   	$VL_PIS=0;
					   
			   		//$VL_COFINS=$VL_BC*0.03;
			   		//$VL_COFINS =number_format($VL_COFINS, 2, ',','');
				   	$VL_COFINS=0;
					$VL_BC=0;
			   		//$VL_BC=number_format($VL_BC, 2, ',','');

			   		$this->vlrnftotal = $this->vlrnftotal+ $VL_DOC;
				    $VL_DOC=number_format($VL_DOC, 2, ',','');
				  				        	
			    	$qtda=$qtda+1;

			    	
			    	$Itens = $RPS->getElementsByTagName("Itens")->item(0); 
			    	
			    	$qtditems  =$Itens->getElementsByTagName('Item')->length;
					$arra170=array();
			    	for($nitems=0;$nitems<$qtditems;){//comentado o for e para outros tipos de nota que tem o C170
		    			$A170=$A170+1;
						$arra170[$A170]=$A170;
		    			
		    			$Item = $Itens->getElementsByTagName("Item")->item($nitems);
		    			
		    			$ValorTotal = $Item->getElementsByTagName("ValorTotal")->item(0); 
				    	$VL_ITEM=($ValorTotal->textContent); //pegar o valor da tag <TipoRecolhimento>  NUM_DOC
						if($VL_PIS_NF>0){
							$VL_BC_I =$VL_ITEM;	
				    	$VL_BC_I =$VL_ITEM;			    	
							$VL_BC_I =$VL_ITEM;	
				    	$VL_BC_I =$VL_ITEM;			    	
							$VL_BC_I =$VL_ITEM;	
				    	$VL_BC_I =$VL_ITEM;			    	
							$VL_BC_I =$VL_ITEM;	
				    	$VL_BC_I =$VL_ITEM;			    	
							$VL_BC_I =$VL_ITEM;	
						}else{
							$VL_BC_I=0;
						}
				    	if($this->renda!='R'){
                            $ppis='0,65';                            
                            $pvpis='0.0065';

                            $pconfins='3,00';
                            $pvconfins='0.03';

                        }else{
                            $ppis='1,65';
                            $pvpis='0.0165';

                            $pconfins='7,60';                            
                            $pvconfins='0.076';
                        }	    	
				   		
					   	$VL_BC=$VL_BC+$VL_BC_I;
				   		//$VL_PIS_I=$VL_BC_I*0.0065;
                        $VL_PIS_I=$VL_BC_I*$pvpis;
						$VL_PIS=$VL_PIS+round($VL_PIS_I,2);
				   		$VL_PIS_I =number_format($VL_PIS_I, 2, ',','');
				   		
				   		//$VL_COFINS_I=$VL_BC_I*0.03;
                        $VL_COFINS_I=$VL_BC_I*$pvconfins;   
						$VL_COFINS=	$VL_COFINS+round($VL_COFINS_I,2);
				   		$VL_COFINS_I =number_format($VL_COFINS_I, 2, ',','');
				   		$VL_BC_I=number_format($VL_BC_I, 2, ',','');
				   		$VL_ITEM=number_format($VL_ITEM, 2, ',','');
				   		
			    	$qtda=$qtda+1;
			    	$nitems++;
					$_SESSION['A170'][$A170]="|A170|".$nitems."|777||".$VL_ITEM."||||01|".$VL_BC_I."|".$ppis."|".$VL_PIS_I."|01|".$VL_BC_I."|".$pconfins."|".$VL_COFINS_I."|2606||\n";
					//	$_SESSION['A170'][$A170]="|A170|".$nitems."|777||".$VL_ITEM."||||01|".$VL_ITEM."|0,65|".$VL_PIS."|01|".$VL_ITEM."|3,00|".$VL_COFINS."|||\n";
					/*
					* ITEMS DA NFS
					* */	
				  /* */	
					/* */	
				  /* */	
					/* */	
				   	
					/* */	
				  /* */	
					/* */	
			      
			    	}
					$VL_PIS=number_format($VL_PIS, 2, ',','');
					$VL_COFINS=number_format($VL_COFINS, 2, ',','');
					$VL_BC=number_format($VL_BC, 2, ',','');
					$_SESSION['A100'][$A100]=("|A100|1|0|".$COD_PART."|00|".$SER."||".$NUM_DOC."||".$vDT_DOC."|".$vDT_DOC."|".$VL_DOC."|1|0,00|".$VL_BC."|".$VL_PIS."|".$VL_BC."|".$VL_COFINS."|".$VL_PIS_RET."|".$VL_COFINS_RET."||\n");
			    	//$_SESSION['A100'][$A100]="|A100|1|0|".$COD_PART."|00|".$SER."||".$NUM_DOC."||".$vDT_DOC."|".$vDT_DOC."|".$VL_DOC."|1|0,00|".$VL_DOC."|".$VL_PIS."|".$VL_DOC."|".$VL_COFINS."||||\n";
					 /*
					  * A100 CORRESPONDE A NFS
					  * */
			    	echo($_SESSION['A100'][$A100]);
				
					foreach($arra170 as $x => $val) {
						echo($_SESSION['A170'][$val]);
					}
					
	      		}  	
      	}
    	$qtda=$qtda+1;
    	$_SESSION['A990']="|A990|".$qtda."|\n";
    	//FIM DO BLOCO A
    	echo($_SESSION['A990']);
  
    }
    
    //BLOCO C - Nota Fiscal Eletrônica (código 55)
     /*
      * NFs de produto
      * */
    public function bloco_C($idempresa){
      	
    	//$sql="select idempresa as id	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null and idempresa=".$idempresa;
    	//$sql="select idempresa as id from xmlnf where mes='abril' and tipo = 'P' and  idempresa=".$idempresa;
		
		if($this->renda=='R'){
			$sqldp="UNION ALL
			select n.idnf
			from nf n
			where sped = 'Y'
			and tiponf ='C'
			and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
			and exists(select 1  from spedc100 s where s.idnf=n.idnf and s.vvpis>0)
			and envionfe = 'CONCLUIDA'
			and n.idempresa = ".$idempresa."
			and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
		}else{
			$sqldp="";
		}

		$sql="select n.idnf
				from nf n  join natop o on(o.idnatop=n.idnatop and o.natop  like('%VENDA%') and o.natop not like('%DEVOLU%'))
				where n.sped = 'Y'
				and n.status = 'CONCLUIDO'
				and n.envionfe = 'CONCLUIDA'
				and n.tiponf ='V'
				and n.idempresa = ".$idempresa."
				and dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'".$sqldp;
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
    	$qtdnf = mysql_num_rows($res);  	
    	if($qtdnf>0){
    		$qtdc001=0;
    	}else{
    		$qtdc001=1;
    	}
    	$C001=1;
    	$_SESSION['C001']="|C001|".$qtdc001."|\n";
      	echo($_SESSION['C001']);
      	if($qtdnf>0){
      	
	      	$C010=1;
	    	$_SESSION['C010']="|C010|23259427000104|2|\n";
	      	echo($_SESSION['C010']);

			if($this->renda=='R'){
				$sqldp="UNION ALL
				select n.idnf,n.tiponf,n.idempresa as id,n.xmlret as xml,faticms,consumo,imobilizado
				,CASE
					WHEN faticms = 'Y' THEN 'faticms'
					WHEN consumo = 'Y' THEN 'consumo'
					WHEN imobilizado = 'Y' THEN 'imobilizado'
					WHEN comercio = 'Y' THEN 'comercio'
					ELSE 'outro'
				END as tipoconsumo
				from nf n
				where sped = 'Y'
				and tiponf ='C'
				and status in ('DIVERGENCIA','RECEBIDO','CONCLUIDO')
				and exists(select 1  from spedc100 s where s.idnf=n.idnf and s.vvpis>0)
				and envionfe = 'CONCLUIDA'
				and n.idempresa = ".$idempresa."
				and prazo between '".$this->dtInicio."' and '".$this->dtFim."'";
			}else{
				$sqldp="";
			}	
	  	    	
	    	//$sql="select idempresa as id,xml	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null  and idempresa=".$idempresa;
	    	//$sql="select idempresa as id,xml from xmlnf where mes='abril' and tipo = 'P' and idempresa=".$idempresa;
	    	$sql="select n.idnf,n.tiponf,n.idempresa as id,n.xmlret as xml,faticms,consumo,imobilizado
				,CASE
					WHEN faticms = 'Y' THEN 'faticms'
					WHEN consumo = 'Y' THEN 'consumo'
					WHEN imobilizado = 'Y' THEN 'imobilizado'
					WHEN comercio = 'Y' THEN 'comercio'
					ELSE 'outro'
				END as tipoconsumo
				from nf n  join natop o on(o.idnatop=n.idnatop and o.natop  like('%VENDA%') and o.natop not like('%DEVOLU%'))
				where n.sped = 'Y'
				and n.status = 'CONCLUIDO'
				and n.envionfe = 'CONCLUIDA'
				and n.tiponf ='V'
				and n.idempresa = ".$idempresa."
				and n.dtemissao between '".$this->dtInicio." 00:00:00' and '".$this->dtFim." 23:59:00'".$sqldp;
	    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
	    	$qtdnf = mysql_num_rows($res);
	    		
	    		$C100 = 0;	
	    	   	$C190 = 0;
	    	   	$C170=0;
	    	   	$n=0;
	    	   	$this->vlrTotalIcms=0;
	    	   	$arrnumnf= array();
	      		while ($row=mysql_fetch_assoc($res)){
			   // echo($row['idnf']."\n");
					if($this->veridnf=="Y"){
						echo("idnf =".$row['idnf']."\n");
					}
	      			$C100=$C100+1;
	      			$n=$n+1;
                                
                                $vbcpisconfins=0.00;
                                $nvProd=0.00;
                                $nvFrete=0.00;
                                $nvICMSDeson=0.00;
                                $vprodFDesc=0.00;
                                
	      			// passar string para UTF-8
	      			$xml=$row['xml'];
	      			//Carregar o XML em UTF-8      			
	      			$doc = DOMDocument::loadXML($xml);	
	
	      			//inicia lendo as principais tags do xml da nfe
		      		$nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
		      		$NFe = $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
		      		$infNFe = $NFe->getElementsByTagName("infNFe")->item(0);  
	
		      		//buscando informações do destinatario / Participante
					if($row['tiponf']=='C'){
                        $dest = $infNFe->getElementsByTagName("emit")->item(0); 
                    }else{
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
	      			
		      		//buscando informações basicas
		      		$ide = $infNFe->getElementsByTagName("ide")->item(0); 
		      		$total = $infNFe->getElementsByTagName("total")->item(0);//Totais
		      		$transp = $infNFe->getElementsByTagName("transp")->item(0);//Transp
		      		
		      		//############TRANSPORTE###########
		      		//	IND_FRT modo de transporte
		      		$modFrete = $transp->getElementsByTagName("modFrete")->item(0);//Transp
		      		$vmodFrete =($modFrete->textContent); //IND_FRT     		
		     	      		
		      		//IND_OPER entrada ou saida**********
		      		//$tpNF = $ide->getElementsByTagName("tpNF")->item(0);
		      		//$vtpNF =($tpNF->textContent); 
                    if($row['tiponf']=='C'){
                        $vtpNF='0';//entrada
                    }else{
                        $vtpNF='1';//saida
                    }
		      		
		      		//Serie****************
		      		$serie = $ide->getElementsByTagName("serie")->item(0);
		      		$vserie =($serie->textContent); //pegar o valor da tag <serie> 
		      		//COD_MOD *************
		      		$mod = $ide->getElementsByTagName("mod")->item(0);
		      		$vmod =($mod->textContent); //pegar o valor da tag <mod> 
		      		//NUM_DOC numero da NF
		      		$nNF = $ide->getElementsByTagName("nNF")->item(0);
		      		$vnNF =($nNF->textContent); //pegar o valor da tag <nNF> 
		      		//data emissao
		      		$dEmi = $ide->getElementsByTagName("dEmi")->item(0);
		      		if($dEmi!=null){
		      				      		
		      		$vdEmi =($dEmi->textContent); //pegar o valor da tag <dEmi> 
		      		
		      		//2013-01-14
		      		$dia = substr($vdEmi, -2, 2); // retorna "14"
		      		$mes = substr($vdEmi, -5, -3); // retorna "01"
		      		$ano = substr($vdEmi, 0, -6); // retorna "2013"
		      		$vdEmi=$dia.$mes.$ano;
		      		}else{
		      			$dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
		      			$vdEmi =($dEmi->textContent); //pegar o valor da tag <dEmi>
		      			
		      			//2015-03-25T10:26:00-03:00
		      			$dia = substr($vdEmi, 8, 2); // retorna "25"
		      			$mes = substr($vdEmi, 5, 2); // retorna "03"
		      			$ano = substr($vdEmi, 0, 4); // retorna "2015"
		      			$vdEmi=$dia.$mes.$ano;		      			
		      		}
		      		
		      		//VL_DOC TOTAL DA NF
		      		$ICMSTot = $total->getElementsByTagName("ICMSTot")->item(0);//Totais		
	
		      		//VL_DESC valor do desconto
		      		/*
					$vDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais	
		      		$vvDesc =($vDesc->textContent); 
		      		$vvDesc=number_format($vvDesc, 2, ',','');*/
		      		
		      		//VL_MERC valor total da mercadoria
		      		$vProd= $ICMSTot->getElementsByTagName("vProd")->item(0);//Totais	
		      		$nvProd =($vProd->textContent); 
		      		
					$vFrete= $ICMSTot->getElementsByTagName("vFrete")->item(0);//Totais	
					$nvFrete =($vFrete->textContent); 
				  	$vc100Prod = $nvProd+$nvFrete;
					$vc100Prod =number_format($vc100Prod, 2, ',','');
							  
					$vvICMSDeson= $ICMSTot->getElementsByTagName("vICMSDeson")->item(0);//Totais	
					$nvICMSDeson =($vvICMSDeson->textContent);  

		      		// valor da base de calculo VL_BC_ICMS
		      		$vBC= $ICMSTot->getElementsByTagName("vBC")->item(0);//Totais	
		      		$nvBC =($vBC->textContent);
		      		$vvBC =number_format($nvBC, 2, ',','');
		      		
		      		//valor do ICMS VL_ICMS
		      		$vtICMS= $ICMSTot->getElementsByTagName("vICMS")->item(0);//Totais	
		      		$nvtICMS =($vtICMS->textContent);
		      		$vvtICMS =number_format($nvtICMS, 2, ',','');
		      		
		      		//Valor da base de cálculo do ICMS substituição tributária VL_BC_ICMS_ST
		      		$vBCST= $ICMSTot->getElementsByTagName("vBCST")->item(0);//Totais	
		      		$vvBCST =($vBCST->textContent);
		      		$vvBCST =number_format($vvBCST, 2, ',','');
	
		      		//Valor total do IPI 	VL_IPI
		      		$vIPI= $ICMSTot->getElementsByTagName("vIPI")->item(0);//Totais	
		      		$vvIPI =($vIPI->textContent);
		      		$vvIPI =number_format($vvIPI, 2, ',','');
		      		
		      		//Valor do seguro indicado no documento fiscal VL_SEG
		      		$vSeg= $ICMSTot->getElementsByTagName("vSeg")->item(0);//Totais	
		      		$vvSeg =($vSeg->textContent);	
		      		$vvSeg =number_format($vvSeg, 2, ',','');   
	
		      		//Valor de outras despesas acessórias fiscal VL_OUT_DA
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
					$vprodDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais		
		      		$vprodFDesc =($vprodDesc->textContent); 
				
		      		$vNF= $ICMSTot->getElementsByTagName("vNF")->item(0);//Totais		
		      		$nvNF =($vNF->textContent); 

		      		
		      		//Buscando a chave da NFE
		      		$protNFe = $nfeProc->getElementsByTagName("protNFe")->item(0); // pegar a tag <protNFe> detro de <nfeProc>
		      		$infProt = $protNFe->getElementsByTagName("infProt")->item(0); //pegar a tag <infProt> dentro de  <protNFe>
	
		      		//Chave NFE ****************
		      		$chNFe = $infProt->getElementsByTagName("chNFe")->item(0); //pegar a tag <chNFe> dentro de <infProt>
		      		$vchNFe =($chNFe->textContent); //pegar o valor da tag <chNFe>
		      		
		      		//verifica quantos itens existem no xml da notafiscal
		      		$qtditem  =$infNFe->getElementsByTagName('det')->length;
		      		
		      		/*08072015 retirado
		      		// VL_DOC valor total do documento
		      		$vlrOper=$nvProd+$nvFrete;
		      		$this->vlrnftotal = $this->vlrnftotal+$vlrOper;
		      		$vlrOper =number_format($vlrOper, 2, ',',''); //$vvNF
		      		*/
		      		//08072015 alterado para 
		      		// VL_DOC valor total do documento
					$vlrOper=$nvNF;
		      		//$this->vlrnftotal = $this->vlrnftotal+$vlrOper; 
                                
					$vbcpisconfins=$nvProd+$nvFrete-$nvICMSDeson-$vprodFDesc;

					if($this->renda!='R'){
					                            
						$pvpis='0.0065';
						$pvconfins='0.03';
			
					}else{
					
						$pvpis='0.0165';    
						$pvconfins='0.076';
					}	

	    			
	    			//$nvpis=$vlrOper*0.0065;                                
                    $nvpis=$vbcpisconfins*$pvpis;
	    			$nvpis =number_format($nvpis, 2, ',',''); //subistituido $vvPIS por $nvpis
	    			
	    			//$nvcofins=$vlrOper*0.03;
                    $nvcofins=$vbcpisconfins*$pvconfins;
	    			$nvcofins =number_format($nvcofins, 2, ',',''); //subistituido $vvCOFINS por $nvcofins
	    			
	    			
	    			//$vlrOper =number_format($vlrOper, 2, ',',''); //$vvNF
	    			$vvFrete =number_format($nvFrete, 2, ',','');
	    			$vvNF =number_format($nvNF, 2, ',','');
	    			
	    				    			 			
		      		//$_SESSION['C100'][$C100]=utf8_decode("|C100|".$vtpNF."|0|".$vCNPJ."|".$vmod."|00|".$vserie."|".$vnNF."|".$vchNFe."|".$vdEmi."||".$vlrOper."|1|".$vvDesc."||".$vvProd."|".$vmodFrete."|".$vvFrete."|".$vvSeg."|".$vvOutro."|".$vvBC ."|".$vvtICMS."|||".$vvIPI."|".$nvpis."|".$nvcofins."|||\n");
		      		 /*
		      		  * O C100 E A NOTA FISCAL 
		      		  * */
		      		//echo($_SESSION['C100'][$C100]);
					// itens da NF
		    		for($nitem=0;$nitem<$qtditem;){//comentado o for e para outros tipos de nota que tem o C170
		    		
		    			$C170=$C170+1;
		    			$vvICMSDeson=0.00;
                                        $vvDesc=0.00;
                                        $vvFrete=0.00;
		    			$det = $infNFe->getElementsByTagName("det")->item($nitem);
		    			$prod = $det->getElementsByTagName("prod")->item(0);
		    			$imposto = $det->getElementsByTagName("imposto")->item(0);
		    			
		    			//valor do desconto VL_DESC
		    			$vDesc = $prod->getElementsByTagName("vDesc")->item(0);
		    			$vvDesc =($vDesc->textContent);
		    			
		    			//###Tributacao do item do item ###############################
		    			$ICMS = $imposto->getElementsByTagName("ICMS")->item(0);
		    			
		    			$ICMS40= $ICMS->getElementsByTagName("ICMS40")->item(0);    
		    			$ICMS20= $ICMS->getElementsByTagName("ICMS20")->item(0); 
		    			$ICMS00= $ICMS->getElementsByTagName("ICMS00")->item(0);
		    					    						
			    		if($ICMS40!=null){
				    		 // echo("HE 40\n");
				    		  //Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS40->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);        
			    					    			 
					    }elseif($ICMS20!=null){				   
					     
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
			    			
			    			// Vlr desconto por desoneração item
			    			$vICMSDeson=$ICMS20->getElementsByTagName("vICMSDeson")->item(0);
			    			$vvICMSDeson =($vICMSDeson->textContent);
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS20->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);        
			    			// echo("HE 20\n");					    
					    }elseif($ICMS00!=null){						     
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
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS00->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);        
			    			// echo("HE 20\n");					    
					    }		    				    			
		    			#### IPI
		    			//cst ipi 	CST_IPI
		    			$IPI = $imposto->getElementsByTagName("IPI")->item(0);
		    			$IPINT = $IPI->getElementsByTagName("IPINT")->item(0);
		    			if($IPINT!=null){
			    			$CSTipi = $IPINT->getElementsByTagName("CST")->item(0);
			    			$vCSTipi =($CSTipi->textContent);
		    			}else{
		    				$IPINT = $IPI->getElementsByTagName("IPITrib")->item(0);
		    				$CSTipi = $IPINT->getElementsByTagName("CST")->item(0);
		    				$vCSTipi =($CSTipi->textContent);
		    			}
		    				    			
		    			//	Código de enquadramento legal do IPICOD_ENQ
		    			$cEnq = $IPI->getElementsByTagName("cEnq")->item(0);
		    			$vcEnq =($cEnq->textContent);
		    		
		    			
		    			#############################################################
		    			//Codigo do produto COD_ITEM
		    			$cProd = $prod->getElementsByTagName("cProd")->item(0);
		    			$vcProd =($cProd->textContent);
		
		    			//Descrição do produto DESCR_COMPL
		    			$xProd = $prod->getElementsByTagName("xProd")->item(0);
		    			$vxProd =($xProd->textContent);
		    			
		    			//quantidade do item QTD    			
		    			$qCom = $prod->getElementsByTagName("qCom")->item(0);
		    			$vqCom =($qCom->textContent);
		    			$vqCom =number_format($vqCom, 2, ',','');
		    			
		    			//	unidade do item UNID    			
		    			$uTrib = $prod->getElementsByTagName("uTrib")->item(0);
		    			$vuTrib =($uTrib->textContent);
		    			
		    			// valor do item VL_ITEM
		    			$vUnCom = $prod->getElementsByTagName("vUnCom")->item(0);
		    			$vvUnCom =($vUnCom->textContent);
		    			$vvUnCom =number_format($vvUnCom, 2, ',','');
		    			
		    			$vProd = $prod->getElementsByTagName("vProd")->item(0);
		    			$vvProd =($vProd->textContent);
		    			//$vvProd =number_format($vvProd, 2, ',','');
		    			
		    			$vFrete = $prod->getElementsByTagName("vFrete")->item(0);
		    			$vvFrete =($vFrete->textContent);
		    			//$vvFrete =number_format($vvFrete, 2, ',','');
		    			
		    			// CFOP
		    			$CFOP = $prod->getElementsByTagName("CFOP")->item(0);
		    			$vCFOP =($CFOP->textContent);

						if($row['tiponf']=='C'){	
							
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
							
								if(($vCFOP=='5101' or $vCFOP=='5102'  or $vCFOP=='5103') and $row['faticms']=='Y'){
									$vCFOP='1101';//1101 Compra para industrialização ou Produção Rural
								}elseif(($vCFOP=='5101' or $vCFOP=='5102'  or $vCFOP=='5103') and $row['imobilizado']=='Y'){
									$vCFOP='1551';
								}elseif(($vCFOP=='5101' or $vCFOP=='5102'  or $vCFOP=='5103') and $row['consumo']=='Y'){
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
								}elseif($vCFOP=='5929' and ( $vvCST==00 or $vvCST==20) and $row['imobilizado']=='Y'){	//5929 = vai ser 1551, quando o produto for tributado, CST: 000 / 020	    				
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
								}elseif($vCFOP=='6910'){
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
								}		
							}

							
						}

		    			//CONSIDERAR A DESONERACAO
		    			//$valorproditen=$vvProd+$vvFrete-$vvDesc;
		    			$valorproditen=$vvProd+$vvFrete-$vvICMSDeson-$vvDesc;// base calculo do pis e cofins
		    			###PIS
		    			// CST_PIS
		    			$PIS = $imposto->getElementsByTagName("PIS")->item(0);
		    			$PISAliq = $PIS->getElementsByTagName("PISAliq")->item(0);
		    			//algums xmls não tem a tag PISaliq
		    			if($PISAliq!=null){
							
		    				$CSTpis= $PISAliq->getElementsByTagName("CST")->item(0);
		    				$vCSTpis =($CSTpis->textContent);
		    				//VL_BC_PIS
                                              
							$vBCpis= $PISAliq->getElementsByTagName("vBC")->item(0);
							$vvBCpis =($vBCpis->textContent);
                                                                                             
                                              
		    				//ALIQ_PIS
		    				$pPIS= $PISAliq->getElementsByTagName("pPIS")->item(0);
		    				$vpPIS =($pPIS->textContent);
		    					    				
		    				//VL_PIS
		    				$vPISitem= $PISAliq->getElementsByTagName("vPIS")->item(0);
		    				$vvPISitem=($vPISitem->textContent);
		    				//$vvPISitem =number_format($vvPISitem, 2, ',','');
		    				
		    			
							$valopis=(($valorproditen)*$vpPIS)/100;//08092015 adicionado este valor para o pis ignorado o $vvPISitem
							
							$vvPISitem =number_format($valopis, 2, ',','');
		    				
		    				$vpPIS =number_format($vpPIS, 2, ',','');
		    				
		    			}else{
		    				$PISNT = $PIS->getElementsByTagName("PISNT")->item(0);
		    				$vCSTpis =($PISNT->textContent);
		    			}
						if($row['tiponf']=='C'){
							$vCSTpis=50;
						}
		    			//Cofins####
		    			//CST_COFINS
		    			$COFINS = $imposto->getElementsByTagName("COFINS")->item(0);
		    			$COFINSAliq = $COFINS->getElementsByTagName("COFINSAliq")->item(0);
						$COFINSOutr = $COFINS->getElementsByTagName("COFINSOutr")->item(0);
		    			if($COFINSAliq!=null){
		    				$CSTcofins = $COFINSAliq->getElementsByTagName("CST")->item(0);
		    				$vCSTcofins=($CSTcofins->textContent);
		    			
		    				//	VL_BC_COFINS
                                               
                                                $vBCcofins = $COFINSAliq->getElementsByTagName("vBC")->item(0);
                                                $vvBCcofins=($vBCcofins->textContent);
                                                                                         
		    			
		    				//	ALIQ_COFINS
		    				$pCOFINS = $COFINSAliq->getElementsByTagName("pCOFINS")->item(0);
		    				$vpCOFINS=($pCOFINS->textContent);
		    						    				 
		    				//VL_COFINS
		    				//$vCOFINSitem = $COFINSAliq->getElementsByTagName("vCOFINS")->item(0);
		    				//$vvCOFINSitem=($vCOFINSitem->textContent);
		    				//$vvCOFINSitem =number_format($vvCOFINSitem, 2, ',','');
		    				
		    				
                                               
                            $valorcofins=(($valorproditen)*$vpCOFINS)/100;//08092015 adicionado este valor para o cofins ignorado o $vvCOFINSitem
                                               
		    				$vvCOFINSitem =number_format($valorcofins, 2, ',','');
		    				
		    				$vpCOFINS =number_format($vpCOFINS, 2, ',','');
		    				
		    			}elseif($COFINSOutr!=null){

							

							$CSTcofins = $COFINSOutr->getElementsByTagName("CST")->item(0);
							$vCSTcofins=($CSTcofins->textContent);		    				    			
							//	VL_BC_COFINS
							$vBCcofins = $COFINSOutr->getElementsByTagName("vBC")->item(0);
							$vvBCcofins=($vBCcofins->textContent);
							    			
							//	ALIQ_COFINS
							$pCOFINS = $COFINSOutr->getElementsByTagName("pCOFINS")->item(0);
							$vpCOFINS=($pCOFINS->textContent);
						   			
							$valorcofins=(($valorproditen)*$vpCOFINS)/100;//08092015 adicionado este valor para o cofins ignorado o $vvCOFINSitem
                                               
		    				$vvCOFINSitem =number_format($valorcofins, 2, ',','');
		    				
		    				$vpCOFINS =number_format($vpCOFINS, 2, ',','');

					
						}else{
		    				$COFINSNT = $COFINS->getElementsByTagName("COFINSNT")->item(0);
		    				$CSTcofins = $COFINSNT->getElementsByTagName("CST")->item(0);
		    				$vCSTcofins=($CSTcofins->textContent);
		    				 
		    			}

						if($row['tiponf']=='C'){
							$vCSTcofins=50;
						}
                                                     
		    			 				    			    			
		    			$nitem++;
		    			if($nitem<10){
		    				$stritem = "00";
		    			}elseif($nitem>=10 and $nitem <100){
		    			$stritem = "0";
		    			}else{
		    			$stritem ="";
		    			}
		    			
					$vlrtotalitem = $vvFrete + ($vvProd-$vvDesc); 
		    			//$vlrtotalitem = $vvFrete + ($vvProd-$vvICMSDeson);
					
                                        $vlrtotalitem =number_format($vlrtotalitem, 2, ',','');

                                        $vvDesc =number_format('0,00' , 2, ',','');
                                       // $vvBCpis =number_format($vvBCicms, 2, ',','');
                                        $vvBCpis =number_format($valorproditen, 2, ',','');
                                        $vvBCcofins =number_format($valorproditen, 2, ',','');
                                                
                                                
                                        //$vlrOper=$nvNF;
                                        
                                                
					if($vCSTcofins=='06'){
                                            $this->vlrTotal06= $this->vlrTotal06+$valorproditen;
                                            $nvpis='0,00';
                                            $nvcofins='0,00';
                                            $vvCOFINSitem='0,00';
                                            $vpCOFINS='0,00';
                                            $vvPISitem='0,00';
                                            $vpPIS='0,00';
                                        }else{
                                            $this->vlrnftotal = $this->vlrnftotal+$vlrOper; 
                                            $this->vlrbcpiscofins=$this->vlrbcpiscofins+$valorproditen;
                                        }    
					  $vprodFDesc=number_format($vprodFDesc, 2, ',','');
                                          $vlrOper =number_format($vlrOper, 2, ',','');
                                          $vvProd =number_format($vvProd, 2, ',','');
                                          $vvFrete =number_format($vvFrete, 2, ',','');
					 
                                        if($vchNFe!=$vchNFeant){
                                            $vchNFeant=$vchNFe;

											if($row['tiponf']=='C'){
												$tpemitente='1';
											}else{
												$tpemitente='0';
											}
                                            /*
                                             * C100 NF
                                             */
                                            $_SESSION['C100'][$C100]=("|C100|".$vtpNF."|".$tpemitente."|".$vCNPJ."|".$vmod."|00|".$vserie."|".$vnNF."|".$vchNFe."|".$vdEmi."||".$vlrOper."|1|".$vprodFDesc."||".$vc100Prod."|".$vmodFrete."|".$vvFrete."|".$vvSeg."|".$vvOutro."|".$vvBC ."|".$vvtICMS."|||".$vvIPI."|".$nvpis."|".$nvcofins."|||\n");
                                            /*
                                            * O C100 E A NOTA FISCAL 
                                            * */
                                             echo($_SESSION['C100'][$C100]);
                                        }
                        
                        if($row['tiponf']=='C'){
                            $vcProd='888';
                        }
		    			 /*
		    			  * C170 SÃO OS ITEM DA NF
		    			  * */				
		    			$_SESSION['C170'][$C170]=("|C170|".$stritem.$nitem."|".$vcProd."|".$vxProd."|".$vqCom."|".$vuTrib."|".$vlrtotalitem."|".$vvDesc."|0|"/*CST_ICMS*/."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."|||".$vvPISitem."|".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."|2606|\n");
		    			//                        | 1  | 2  				|3         |   4       |  5        |   6      |  7         |   8      |9|  10           | 11       |12| 13        |    14     | 15        ||||19|20	        | 21          ||||  25		  | 26         | 27       |||30            | 31            | 32            |   33        ||| 36              |37|           
		    			
		    			echo($_SESSION['C170'][$C170]);    			
		    		}
	    		//REGISTRO ANALÍTICO DO DOCUMENTO (CÓDIGO 01, 1B, 04 E 55)
	    		$C190=$C190+1;
	    		if($vvCST==20){
	    			$strST="020";
	    		}elseif($vvCST==70){
	    			$strST="070";
	    		}elseif($vvCST==40){
	    			$strST="040";
	    		}else{
	    			$strST="000";
	    		}
	    		//|C190|||7,00||470,98|3,74||||0,00||
	    		//CST_ICMS CFOP VL_OPR VL_BC_ICMS_ST VL_ICMS_ST VL_RED_BC
	    		//Valor da reducao e o valor total + valor do frete - base de calculo = 706,47
	    		$vlrRedBc=$nvProd+$nvFrete-$nvBC;
	    		$vlrRedBc =number_format($vlrRedBc, 2, ',','');
	
	    		//$_SESSION['C190'][$C190]="|C190|".$strST."|".$vCFOP."|".$vpICMS."|".$vlrOper."|".$vvBC."|".$vvtICMS."|0,00|0,00|".$vlrRedBc."|".$vvIPI."||\n";
	    		//echo($_SESSION['C190'][$C190]);
	    		$this->vlrTotalIcms = $this->vlrTotalIcms+$nvtICMS;
	    	} 
      	}
    	//FIM DO BLOCO C
    	//$C990=$C001+$C100+$C170+$C190+1;
    	$C990=$C010+$C001+$C100+$C170+1;
    	$_SESSION['C990']="|C990|".$C990."|\n";
    	echo($_SESSION['C990']);  
     }
    //DOCUMENTOS FISCAIS II - SERVIÇOS (ICMS)
	public function bloco_D(){
     	$_SESSION['D001']="|D001|1|\n";
     	echo($_SESSION['D001']);
     	$_SESSION['D990']="|D990|2|\n";
     	echo($_SESSION['D990']);		
     }
 
    //CONTROLE DO CRÉDITO DE ICMS DO ATIVO PERMANENTE - CIAP - modelos "C" e "D" 
     /*
      * Este faz a soma total das nfs e quantidade ja paga
      * */
	public function bloco_F($idempresa){
     	$_SESSION['F001']="|F001|0|\n";
     	echo($_SESSION['F001']);
        $sql="select e.cnpj from empresa e where idempresa = ".$idempresa;
    	
    	
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco 0000".mysql_error());    	
    	$row=mysql_fetch_assoc($res); 
     	$_SESSION['F010']="|F010|".$row['cnpj']."|\n";		    	
		echo($_SESSION['F010']);
     	
     	//$sql="select idempresa as id,xml	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null  and idempresa=".$idempresa;
    	//$sql="select idempresa as id,xml from xmlnf where mes='abril' and tipo = 'S' and idempresa=".$idempresa;
    	$sql="select n.idempresa as id,l.xml
				from notafiscal n,nfslote l
				where l.status = 'SUCESSO'
				and l.nnfe= n.nnfe
				and n.idempresa = ".$idempresa."
				and n.emissao between ('".$this->dtInicio."') and ('".$this->dtFim."')
				and n.status in ('FATURADO','CONCLUIDO')";
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco A100".mysql_error());
    	$qtdnf = mysql_num_rows($res);
    		$SVBC=0;
    		$SVPIS=0;
    		$SVCOFINS=0;
    		
    	   	while ($row=mysql_fetch_assoc($res)){
      			$n=$n+1;
      			// passar string para UTF-8
      			$xml=$row['xml'];
      			//Carregar o XML em UTF-8      			
      			$doc = DOMDocument::loadXML($xml);	
 				$ReqEnvioLoteRPS = $doc->getElementsByTagName("ReqEnvioLoteRPS")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>

 				$Lote = $ReqEnvioLoteRPS->getElementsByTagName("Lote")->item(0); 	      		
			    $RPS = $Lote->getElementsByTagName("RPS")->item(0); 
			    $Cabecalho = $ReqEnvioLoteRPS->getElementsByTagName("Cabecalho")->item(0);
					    
			    $CNPJ = $RPS->getElementsByTagName("CPFCNPJTomador")->item(0); 
			    $COD_PART =($CNPJ->textContent); //pegar o valor da tag <CPFCNPJTomador>  COD_PART	
			    
			    $TipoRecolhimento = $RPS->getElementsByTagName("TipoRecolhimento")->item(0); 
			    $SER=($TipoRecolhimento->textContent); //pegar o valor da tag <TipoRecolhimento>  SER	

			    $NumeroRPS = $RPS->getElementsByTagName("NumeroRPS")->item(0); 
			    $NUM_DOC=($NumeroRPS->textContent); //pegar o valor da tag <TipoRecolhimento>  NUM_DOC
			    
			    $DataEmissaoRPS = $RPS->getElementsByTagName("DataEmissaoRPS")->item(0); 
			    $DT_DOC=($DataEmissaoRPS->textContent); //pegar o valor da tag <DataEmissaoRPS>  DT_DOC
			    $DT_DOC =strtotime($DT_DOC);
				$vDT_DOC = date ("dmY", $DT_DOC);
				
				$ValorTotalServicos = $Cabecalho->getElementsByTagName("ValorTotalServicos")->item(0); 
			    $VL_DOC=($ValorTotalServicos->textContent); //pegar o valor da tag <ValorTotalServicos>  VL_DOC
			    
			    $CPFCNPJRemetente = $Cabecalho->getElementsByTagName("CPFCNPJRemetente")->item(0); 
			    $CPFCNPJ=($CPFCNPJRemetente->textContent); //pegar o valor da tag <ValorTotalServicos>  VL_DOC
			    
			    $ValorPIS = $RPS->getElementsByTagName("ValorPIS")->item(0); 
			    $VL_PIS=($ValorPIS->textContent); //pegar o valor da tag <ValorPIS>  VL_PIS
			    
			    $ValorCOFINS = $RPS->getElementsByTagName("ValorCOFINS")->item(0); 
			    $VL_COFINS=($ValorCOFINS->textContent); //pegar o valor da tag <ValorCOFINS>  VL_COFINS
			    				
			    if($VL_PIS>0){
			    	$SVBC = $SVBC + $VL_DOC;
			    	$SVPIS = $SVPIS + $VL_PIS;
			    	$SVCOFINS = $SVCOFINS+$VL_COFINS;
			    				    	
			    }
      		}  	
     	
      	$TOTAL_RET = $SVPIS + $SVCOFINS;
      	$this->vlrRetpis=$SVPIS;
		$this->vlrRetcofins=$SVCOFINS;	
		$SVBC =number_format($SVBC, 2, ',','');
		$SVPIS=number_format($SVPIS, 2, ',','');
		$SVCOFINS=number_format($SVCOFINS, 2, ',','');
		$TOTAL_RET=number_format($TOTAL_RET, 2, ',','');	
     	
		$_SESSION['F600']="|F600|01|".$vDT_DOC."|".$SVBC."|".$TOTAL_RET."|||".$CPFCNPJ."|".$SVPIS."|".$SVCOFINS."|0|\n";		    	
		echo($_SESSION['F600']);
		    	
     	$_SESSION['F990']="|F990|4|\n";	
     	echo($_SESSION['F990']);	
     }
    //INVENTÁRIO FÍSICO
    /*
     * Este bloco soma o total dos valores das NFs e os valores de pis e cofins ja pagos e o que falta para pagar
     * */
 	public function bloco_M(){

		if($this->renda!='R'){
			$ppis='0,65';                            
			$pvpis='0.0065';

			$pconfins='3,00';
			$pvconfins='0.03';

		}else{
			$ppis='1,65';
			$pvpis='0.0165';

			$pconfins='7,60';                            
			$pvconfins='0.076';
		}	

     	$_SESSION['M001']="|M001|0|\n";
     	echo($_SESSION['M001']);
     	$TOTAL_NF=$this->vlrnftotal;
        $bcpiscofins=$this->vlrbcpiscofins;
          	
     	$VL_PIS=$bcpiscofins*$pvpis; //0.0065    	     	
     	$DIF_PIS=$this->vlrRetpis-$VL_PIS;
     	if($DIF_PIS<0){		
			$DIF_PIS= abs($DIF_PIS);
		}
     	$VL_COFINS=$bcpiscofins*$pvconfin;//0.03     	     	
     	$DIF_COFINS=   $this->vlrRetcofins-$VL_COFINS;
		
		 if($DIF_COFINS<0){
			$DIF_COFINS= abs($DIF_COFINS);
		}
     	     	
     	$TOTAL_NF=number_format($TOTAL_NF, 2, ',','');
     	$this->vlrRetpis=number_format($this->vlrRetpis, 2, ',','');
     	$DIF_PIS=number_format($DIF_PIS, 2, ',','');
     	$VL_PIS=number_format($VL_PIS, 2, ',','');
		 
        
        $TOTAL_VBPC=number_format($bcpiscofins, 2, ',','');
        
        $this->vlrTotal06=number_format($this->vlrTotal06, 2, ',','');
     	
     	$_SESSION['M200']="|M200|0|0|0|0|0|0|0|".$VL_PIS."|".$this->vlrRetpis."|0|".$DIF_PIS."|".$DIF_PIS."|\n";
     	echo($_SESSION['M200']);   

     	$_SESSION['M205']="|M205|12|810902|".$DIF_PIS."|\n";
     	echo($_SESSION['M205']);
     	
     	$_SESSION['M210']="|M210|51|".$TOTAL_NF."|".$TOTAL_VBPC."|0|0|".$TOTAL_VBPC."|".$ppis."|0||".$VL_PIS."|0|0|||".$VL_PIS."|\n";
     	echo($_SESSION['M210']);
        
        $_SESSION['M400']="|M400|06|".$this->vlrTotal06."|2606||\n";
     	echo($_SESSION['M400']);
        
        $_SESSION['M410']="|M410|107|".$this->vlrTotal06."|2606||\n";
     	echo($_SESSION['M410']);
     	 
     	$VL_COFINS=number_format($VL_COFINS, 2, ',','');
     	$DIF_COFINS=number_format($DIF_COFINS, 2, ',','');
     	$this->vlrRetcofins=number_format($this->vlrRetcofins, 2, ',','');
     	
     	$_SESSION['M600']="|M600|0|0|0|0|0|0|0|".$VL_COFINS."|".$this->vlrRetcofins."|0|".$DIF_COFINS."|".$DIF_COFINS."|\n";
     	echo($_SESSION['M600']);
     	
     	$_SESSION['M605']="|M605|12|217201|".$DIF_COFINS."|\n";
     	echo($_SESSION['M605']);
     	
     	$_SESSION['M610']="|M610|51|".$TOTAL_NF."|".$TOTAL_VBPC."|0|0|".$TOTAL_VBPC."|".$pconfins."|0||".$VL_COFINS."|0|0|||".$VL_COFINS."|\n";
  
     	echo($_SESSION['M610']);  
        
        $_SESSION['M800']="|M800|06|".$this->vlrTotal06."|2606||\n";
     	echo($_SESSION['M800']);
        
        $_SESSION['M810']="|M810|107|".$this->vlrTotal06."|2606||\n";
     	echo($_SESSION['M810']);        
     	
     	$_SESSION['M990']="|M990|12|\n";	
     	echo($_SESSION['M990']);	
     }
    //OUTRAS INFORMAÇÕES   
	public function bloco_1(){
     	$_SESSION['1001']="|1001|1|\n";
     	echo($_SESSION['1001']);
     	//$_SESSION['1010']="|1010|N|N|N|N|N|N|N|N|N|\n";
     	//echo($_SESSION['1010']);     	
     	$_SESSION['1990']="|1990|2|\n";	
     	echo($_SESSION['1990']);	
     }
     
     //Bloco 9 e Encerramento do arquivo digital
      /*
       * Este bloco mostra todos os registros e quantas vezes ele foi citado
       * */
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
     		/*if(count($_SESSION['0007'])>0){ 
    	   		echo("|9900|0007|".count($_SESSION['0007'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0007']);
    	   	}*/
    	   	if(count($_SESSION['0100'])>0){
    	   		echo("|9900|0100|".count($_SESSION['0100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0100']);
    	   	}
     		if(count($_SESSION['0110'])>0){
    	   		echo("|9900|0110|".count($_SESSION['0110'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0110']);
    	   	}
    	   	if(count($_SESSION['0140'])>0){	 
    	   		echo("|9900|0140|".count($_SESSION['0140'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0140']);
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
		if(count($_SESSION['0500'])>0){
    	   		echo("|9900|0500|".count($_SESSION['0500'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0500']);
    	   	}
    	   	if(count($_SESSION['0990'])){
    	   		echo("|9900|0990|".count($_SESSION['0990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['0990']);
    	   	
    	   	}
      		if(count($_SESSION['A001'])){
    	   		echo("|9900|A001|".count($_SESSION['A001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['A001']);
    	   	
    	   	}
     		if(count($_SESSION['A010'])){
    	   		echo("|9900|A010|".count($_SESSION['A010'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['A010']);    	   	
    	   	}
     		if(count($_SESSION['A100'])){
    	   		echo("|9900|A100|".count($_SESSION['A100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['A100']);    	   	
    	   	}
          	if(count($_SESSION['A170'])){
    	   		echo("|9900|A170|".count($_SESSION['A170'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['A170']);    	   	
    	   	}    	   	
           	if(count($_SESSION['A990'])){
    	   		echo("|9900|A990|".count($_SESSION['A990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['A990']);
    	   	
    	   	}     		
    	   	if(count($_SESSION['C001'])>0){
    	   		echo("|9900|C001|".count($_SESSION['C001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C001']);    	   	
    	   	}
    		if(count($_SESSION['C010'])>0){
    	   		echo("|9900|C010|".count($_SESSION['C010'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C010']);    	   	
    	   	}
    	   	if(count($_SESSION['C100'])>0){
    	   		echo("|9900|C100|".count($_SESSION['C100'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C100']);
    	   	}
    	    if(count($_SESSION['C170'])>0){ 
    	   		echo("|9900|C170|".count($_SESSION['C170'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C170']);
    	   	}
    	   /*	if(count($_SESSION['C190'])){	 
    	   		echo("|9900|C190|".count($_SESSION['C190'])."|\n"); 	
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['C190']);
    	   	}*/
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
    	   	if(count($_SESSION['D990'])>0){
    	   		echo("|9900|D990|".count($_SESSION['D990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['D990']); 
    	   	} 
    	 	if(count($_SESSION['F001'])>0){
    	   		echo("|9900|F001|".count($_SESSION['F001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['F001']); 
    	   	}
     		if(count($_SESSION['F010'])>0){
    	   		echo("|9900|F010|".count($_SESSION['F010'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['F010']); 
    	   	}
          	if(count($_SESSION['F600'])>0){
    	   		echo("|9900|F600|".count($_SESSION['F600'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['F600']); 
    	   	}
     		if(count($_SESSION['F990'])>0){
    	   		echo("|9900|F990|".count($_SESSION['F990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['F990']); 
    	   	}
     		if(count($_SESSION['M001'])>0){
    	   		echo("|9900|M001|".count($_SESSION['M001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M001']); 
    	   	}
    		if(count($_SESSION['M200'])>0){
    	   		echo("|9900|M200|".count($_SESSION['M200'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M200']); 
    	   	}
    	   	if(count($_SESSION['M205'])>0){
    	   		echo("|9900|M205|".count($_SESSION['M205'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M205']);
    	   	}
    		if(count($_SESSION['M210'])>0){
    	   		echo("|9900|M210|".count($_SESSION['M210'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M210']); 
    	   	}
                if(count($_SESSION['M400'])>0){
    	   		echo("|9900|M400|".count($_SESSION['M400'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M400']); 
    	   	}
                if(count($_SESSION['M410'])>0){
    	   		echo("|9900|M410|".count($_SESSION['M410'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M410']); 
    	   	}
         	if(count($_SESSION['M600'])>0){
    	   		echo("|9900|M600|".count($_SESSION['M600'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M600']); 
    	   	}
    	   	if(count($_SESSION['M605'])>0){
    	   		echo("|9900|M605|".count($_SESSION['M605'])."|\n");
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M605']);
    	   	}
    		if(count($_SESSION['M610'])>0){
    	   		echo("|9900|M610|".count($_SESSION['M610'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M610']); 
    	   	}
                if(count($_SESSION['M800'])>0){
    	   		echo("|9900|M800|".count($_SESSION['M800'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M800']); 
    	   	}
                if(count($_SESSION['M810'])>0){
    	   		echo("|9900|M810|".count($_SESSION['M810'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M810']); 
    	   	}
     		if(count($_SESSION['M990'])>0){
    	   		echo("|9900|M990|".count($_SESSION['M990'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['M990']); 
    	   	}
          	if(count($_SESSION['1001'])>0){
    	   		echo("|9900|1001|".count($_SESSION['1001'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['1001']); 
    	   	}
     		/*if(count($_SESSION['1010'])>0){
    	   		echo("|9900|1010|".count($_SESSION['1010'])."|\n"); 
    	   		$l9900=$l9900 +1;
    	   		$linha=$linha+count($_SESSION['1010']); 
    	   	}*/
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
$sped->vlrTotalIcms=0;
$sped->vlrTotal06=0;
$sped->vlrRetpis=0;
$sped->vlrRetcofins=0;
$sped->vlrnftotal=0;
$sped->renda=$renda;
$sped->veridnf=$veridnf;
$sped->bloco_0($idempresa); //Abertura do Arquivo Digital e Identificação da entidade Dados do Contabilista
$sped->bloco_A($idempresa); // Abertura do Bloco A Nota Fiscal Eletrônica (servico) 
$sped->bloco_C($idempresa); // Abertura do Bloco C Nota Fiscal Eletrônica (código 55) 
$sped->bloco_D();//DOCUMENTOS FISCAIS II - SERVIÇOS (ICMS)
$sped->bloco_F($idempresa);//APURAÇÃO DO ICMS E DO IPI
$sped->bloco_M();//CONTROLE DO CRÉDITO DE ICMS DO ATIVO PERMANENTE - CIAP - modelos "C" e "D" 
$sped->bloco_1();//OUTRAS INFORMAÇÕES
$sped->bloco_9(); //Bloco 9 e Encerramento do arquivo digital
