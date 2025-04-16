<?
require_once("../inc/php/validaacesso.php");
$mes=$_GET["mes"];
$ano=$_GET["ano"];
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
function bloco_A(){
        global $dtInicio,$dtFim;
        //$sql="select idempresa as id	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null and idempresa=".$idempresa;
    	//$sql="select idempresa as id from xmlnf where mes='abril' and tipo = 'S' and  idempresa=".$idempresa;
    	$sql="select n.idempresa as id
				from   notafiscal n,nfslote l
				where l.status = 'SUCESSO'
				and l.nnfe= n.nnfe
				and n.idempresa = ".cb::idempresa()."
				and n.emissao between ('".$dtInicio." 00:00:00') and ('".$dtFim." 23:59:00')
				and n.status  in ('FATURADO','CONCLUIDO')";
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco A001".mysql_error());
    	$qtdnf = mysql_num_rows($res);  	
    	$qtda=1;
        $array = array();
      	if($qtdnf>0){
	    	
	    	//$sql="select idempresa as id,xml	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null  and idempresa=".$idempresa;
	    	//$sql="select idempresa as id,xml from xmlnf where mes='abril' and tipo = 'S' and idempresa=".$idempresa;
    		$sql="select n.idempresa as id,l.xml,n.nnfe
			from notafiscal n,nfslote l
			where l.status = 'SUCESSO'
			and l.nnfe= n.nnfe
			and n.idempresa = ".cb::idempresa()."
			and n.emissao between ('".$dtInicio."  00:00:00') and ('".$dtFim." 23:59:00')
			and n.status in ('FATURADO','CONCLUIDO')";
	    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco A100".mysql_error());
	    	$qtdnf = mysql_num_rows($res);
	    		$A100=0;
	    		$A170=0;
                $vlrnftotal = 0;
                $TotalRETCofins = 0;
                $TotalRETPis = 0;
                $n = 0;
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
					//$TotalRETCofins = $TotalRETCofins+$VL_PIS_RET;
				    $VL_PIS_RET =number_format($VL_PIS_RET, 2, ',','');
				    
				    $ValorCOFINS = $RPS->getElementsByTagName("ValorCOFINS")->item(0); 
				    $VL_COFINS_RET=($ValorCOFINS->textContent); //pegar o valor da tag <ValorCOFINS>  VL_COFINS
					//$TotalRETCofins = $TotalRETCofins+$VL_COFINS_RET;
				    $VL_COFINS_RET =number_format($VL_COFINS_RET, 2, ',','');
					
					$ValorPIS = $RPS->getElementsByTagName("ValorPIS")->item(0); 
					$VL_PIS_NF=($ValorPIS->textContent); //pegar o valor da tag <ValorPIS>  VL_PIS
				   


				   	$VL_PIS=0;
					   
			   		//$VL_COFINS=$VL_BC*0.03;
			   		//$VL_COFINS =number_format($VL_COFINS, 2, ',','');
				   	$VL_COFINS=0;
					$VL_BC=0;
			   		//$VL_BC=number_format($VL_BC, 2, ',','');

			   		$vlrnftotal = $vlrnftotal+ $VL_DOC;
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
						}else{
							$VL_BC_I=0;
						}
				    	$Descr = $Item->getElementsByTagName("DiscriminacaoServico")->item(0); 		    	
						$DESCR = ($Descr->textContent); //pegar o valor da tag <TipoRecolhimento>  NUM_DOC



					   	$VL_BC=$VL_BC+$VL_BC_I;
				   		$VL_PIS_I=$VL_BC_I*0.0065;
						$VL_PIS=$VL_PIS+($VL_PIS_I);
						$TotalRETPis = $TotalRETPis+$VL_PIS;
				   		$VL_PIS_I =number_format($VL_PIS_I, 2, ',','');
				   		
				   		$VL_COFINS_I=$VL_BC_I*0.03;
						$VL_COFINS=	$VL_COFINS+($VL_COFINS_I);
						$TotalRETCofins = $TotalRETCofins+$VL_COFINS;
				   		$VL_COFINS_I =number_format($VL_COFINS_I, 2, ',','');

						
				   		$VL_BC_I=number_format($VL_BC_I, 2, ',','');
				   		$VL_ITEM=number_format($VL_ITEM, 2, ',','');
				   		
			    	$qtda=$qtda+1;
			    	$nitems++;
					//$array['A170'][$A170]="|A170|".$nitems."|777||".$VL_ITEM."||||01|".$VL_BC_I."|0,65|".$VL_PIS_I."|01|".$VL_BC_I."|3,00|".$VL_COFINS_I."|2606||\n";
					$array['ITENS'][$A170]=(
					    "<td>".$row['nnfe']."</td>"
					   ."<td>".$DESCR."</td>"
					   ."<td align='right'>".$VL_BC_I."</td>"
					   ."<td align='right'>".$VL_PIS_I."</td>"
					   ."<td align='right'>".$VL_COFINS_I."</td>");
					//	$array['A170'][$A170]="|A170|".$nitems."|777||".$VL_ITEM."||||01|".$VL_ITEM."|0,65|".$VL_PIS."|01|".$VL_ITEM."|3,00|".$VL_COFINS."|||\n";

			      
			    	}
					//$VL_PIS=number_format($VL_PIS, 2, ',','');
					//$VL_COFINS=number_format($VL_COFINS, 2, ',','');
					//$VL_BC=number_format($VL_BC, 2, ',','');
					$array['NOTA'][$A100]=(
							"<tr style='background-color:#eee; font-size:13.5px'>"
								."<td colspan='2'><b>TOTAL NFE N° ".$row['nnfe']."</b></td>"
								."<td align='right'><b>".($VL_DOC)."</b></td>"
								."<td align='right'><b>".($VL_PIS_RET)."</b></td>"
								."<td align='right'><b>".($VL_COFINS_RET)."</b></td>"
							."<tr>");
					 /*
					  * A100 CORRESPONDE A NFS
					  * */
					foreach($arra170 as $x => $val) {
						echo("<tr class='res'>");
						echo($array['ITENS'][$val]);
						echo("</tr>");
					}
					?>
					
						<?echo($array['NOTA'][$A100]);?>
					<?
	      		}  	
      	}
		  echo('
		  		<tr>
		  			<td colspan="4">&nbsp;</td>
				</tr>
		 		 <tr class="res">
		  			<td colspan="2" class="inv"></td>
		  			<td align="right" class="tot" style="font-size: 13.5px;"><b>'.number_format($vlrnftotal, 2, ',','.').'</b></td>
		  			<td align="right" class="tot" style="font-size: 13.5px;"><b>'.number_format($TotalRETPis, 2, ',','.').'</b></td>
		  			<td align="right" class="tot" style="font-size: 13.5px;"><b>'.number_format($TotalRETCofins, 2, ',','.').'</b></td>
		 		</tr>');
    }
	function bloco_C(){

		global $dtInicio,$dtFim;

    	//$sql="select idempresa as id	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null and idempresa=".$idempresa;
    	//$sql="select idempresa as id from xmlnf where mes='abril' and tipo = 'P' and  idempresa=".$idempresa;
		$sql="select n.idnf
				from nf n  join natop o on(o.idnatop=n.idnatop and o.natop  like('%VENDA%') and o.natop not like('%DEVOLU%'))
				where n.sped = 'Y'
				and n.status = 'CONCLUIDO'
				and n.envionfe = 'CONCLUIDA'
				and n.tiponf ='V'
                                and n.idempresa = ".cb::idempresa()."
				and dtemissao between '".$dtInicio." 00:00:00' and '".$dtFim." 23:59:00'";
    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
    	$qtdnf = mysql_num_rows($res);  	
    	if($qtdnf>0){
    		$qtdc001=0;
    	}else{
    		$qtdc001=1;
    	}
    	$C001=1;
    	//$_SESSION['C001']="|C001|".$qtdc001."|\n";
      	//echo($_SESSION['C001']);
		$array = array();
      	if($qtdnf>0){
      	
	      	$C010=1;
	    	//$_SESSION['C010']="|C010|23259427000104|2|\n";
	      	//echo($_SESSION['C010']);
	  	    	
	    	//$sql="select idempresa as id,xml	from nf e where dtemissao between '".$this->dtInicio."' and '".$this->dtFim."' and xml is not null  and idempresa=".$idempresa;
	    	//$sql="select idempresa as id,xml from xmlnf where mes='abril' and tipo = 'P' and idempresa=".$idempresa;
	    	$sql="select n.idnf,n.idempresa as id,n.xmlret as xml,n.nnfe
				from nf n join natop o on(o.idnatop=n.idnatop and o.natop  like('%VENDA%') and o.natop not like('%DEVOLU%'))
				where n.sped = 'Y'
				and n.status = 'CONCLUIDO'
				and n.envionfe = 'CONCLUIDA'
				and n.tiponf ='V'
                                and n.idempresa = ".cb::idempresa()."
				and n.dtemissao between '".$dtInicio." 00:00:00' and '".$dtFim." 23:59:00'
				order by n.nnfe";
	    	$res=mysql_query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
	    	$qtdnf = mysql_num_rows($res);
	    		
	    		$C100 = 0;	
	    	   	$C190 = 0;
	    	   	$C170=0;
	    	   	$n=0;
	    	   	$vlrTotalIcms=0;
	      		while ($row=mysql_fetch_assoc($res)){
				//$arrnumnf[$C100]=$C100;
			   // echo($row['idnf']."\n");
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
				    $dest = $infNFe->getElementsByTagName("dest")->item(0); 
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
		      		$tpNF = $ide->getElementsByTagName("tpNF")->item(0);
		      		$vtpNF =($tpNF->textContent); 
		      		
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
                              
	    			
	    			//$nvpis=$vlrOper*0.0065;                                
					$nvpis=$vbcpisconfins*0.0065;
	    			$nvpis =number_format($nvpis, 2, ',',''); //subistituido $vvPIS por $nvpis
	    			
	    			//$nvcofins=$vlrOper*0.03;
					$nvcofins=$vbcpisconfins*0.03;
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
					$arrc170=array();
					$ValorNF = 0;
					$ValorPISNF = 0;
					$ValorCOFINSNF = 0;
			    	for($nitem=0;$nitem<$qtditem;){//comentado o for e para outros tipos de nota que tem o C170
						$C170=$C170+1;
						$arrc170[$C170]=$C170;
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
						$ValorNF = $ValorNF + $vvProd;
		    			//$vvProd =number_format($vvProd, 2, ',','');
		    			
		    			$vFrete = $prod->getElementsByTagName("vFrete")->item(0);
		    			$vvFrete =($vFrete->textContent);
		    			//$vvFrete =number_format($vvFrete, 2, ',','');
		    			
		    			// CFOP
		    			$CFOP = $prod->getElementsByTagName("CFOP")->item(0);
		    			$vCFOP =($CFOP->textContent);
		    			
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
							$ValorPISNF = $ValorPISNF+$vvPISitem;
		    				//$vvPISitem =number_format($vvPISitem, 2, ',','');
		    				
		    			
							$valopis=(($valorproditen)*$vpPIS)/100;//08092015 adicionado este valor para o pis ignorado o $vvPISitem
							
							$vvPISitem =number_format($valopis, 2, ',','.');
		    				
		    				$vpPIS =number_format($vpPIS, 2, ',','');
		    				
		    			}else{
		    				$PISNT = $PIS->getElementsByTagName("PISNT")->item(0);
		    				$vCSTpis =($PISNT->textContent);
		    			}
		    			//Cofins####
		    			//CST_COFINS
		    			$COFINS = $imposto->getElementsByTagName("COFINS")->item(0);
		    			$COFINSAliq = $COFINS->getElementsByTagName("COFINSAliq")->item(0);
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

							$ValorCOFINSNF = $ValorCOFINSNF+$valorcofins;
                                               
		    				$vvCOFINSitem =number_format($valorcofins, 2, ',','.');
		    				
		    				$vpCOFINS =number_format($vpCOFINS, 2, ',','');
		    				
		    			}else{
		    				$COFINSNT = $COFINS->getElementsByTagName("COFINSNT")->item(0);
		    				$CSTcofins = $COFINSNT->getElementsByTagName("CST")->item(0);
		    				$vCSTcofins=($CSTcofins->textContent);
		    				 
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
						$vlrTotal06= $vlrTotal06+$valorproditen;
						$nvpis='0,00';
						$nvcofins='0,00';
						$vvCOFINSitem='0,00';
						$vpCOFINS='0,00';
						$vvPISitem='0,00';
						$vpPIS='0,00';
					}else{
						$vlrnftotal = $vlrnftotal+$vlrOper; 
						$vlrbcpiscofins=$vlrbcpiscofins+$valorproditen;
					}    
					$vprodFDesc=number_format($vprodFDesc, 2, ',','');
					$vlrOper =number_format($vlrOper, 2, ',','');
					$vvProd =number_format($vvProd, 2, ',','.');
					$vvFrete =number_format($vvFrete, 2, ',','');
					

					$array['ITENS'][$C170]=(
						'<tr class="res">
							<td>
								'.$row['nnfe'].'
							</td>
							<td>
								'.$vxProd.'
							</td>
							<td align="right">
								'.$vvProd.'
							</td>
							<td align="right">
								'.$vvPISitem.'
							</td>
							<td align="right">
								'.$vvCOFINSitem.'
							</td>
						</tr');			
				}
				foreach($arrc170 as $x => $val) {
					echo("<tr class='res'>");
					echo($array['ITENS'][$val]);
					echo("</tr>");
				}
				$valornftotal = $valornftotal+$ValorNF;
				$valorpistotal = $valorpistotal+$ValorPISNF;
				$valorcofinstotal = $valorcofinstotal+$ValorCOFINSNF;


				$ValorNF = number_format($ValorNF,2,',','.');
				$ValorPISNF= number_format($ValorPISNF,2,',','.');
				$ValorCOFINSNF= number_format($ValorCOFINSNF,2,',','.');
				$array['NOTA'][$C100]=(
					"<tr style='background-color:#eee; font-size: 13.5px;'>"
						."<td colspan='2'><b>TOTAL NF N° ".$vnNF."</b></td>"
						."<td align='right'><b>".($ValorNF)."</b></td>"
						."<td align='right'><b>".($ValorPISNF)."</b></td>"
						."<td align='right'><b>".($ValorCOFINSNF)."</b></td>"
					."<tr>");

				echo($array['NOTA'][$C100]);
      	}
	}
    	//FIM DO BLOCO C
		  echo('
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
					<tr class="res">
						<td colspan="2" class="inv"></td>
		  			<td class="tot" style="font-size: 13.5px;">
					  '.number_format($valornftotal,2,",",".").'
					</td>
		  			<td class="tot" style="font-size: 13.5px;">
					  '.number_format($valorpistotal,2,",",".").'
					</td>
					<td class="tot" style="font-size: 13.5px;">
		  			  '.number_format($valorcofinstotal,2,",",".").'
					</td>
				</tr>
		  ');
     }
?>
<html>
	<head>
		<title>
			Relatório <?=$mes.'/'.$ano?>
		</title>
	</head>
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
	<style type="text/css">
	.header{
		font-size: 12px !important;
	}
	.tbrepheader .header{
		font-size: 13px !important;
	}
	.res{
		font-size: 13px !important;
	}
	.tot{
		text-align: right !important;
	}
	table { page-break-inside:auto }
		tr    { page-break-inside:avoid; page-break-after:auto }
		thead { display:table-header-group }
		tfoot { display:table-footer-group }
	</style>
	<body>
		<?
			$sqlfig="select logosis from empresa where idempresa =".cb::idempresa();
			$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
			$figrel=mysqli_fetch_assoc($resfig);
		
			//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
			//$figurarelatorio = "../inc/img/repheader.png";
			$figurarelatorio = $figrel["logosis"];
		?>
		<table class="tbrepheader">
			<tr>
				<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
				<td></td>
				<td class="header">Relatório <?=$mes.'/'.$ano?></td>
			</tr>
		</table>
			
				<?
				switch ($_GET['tipo']) {
					case 'servicos':
					?>
			<table style="width: 100%;" class="normal">
				<tr class="header">
					<td class="header" >N° NFE</td>
					<td class="header" >DESCR. SERVIÇO</td>
					<td class="header" >VALOR</td>
					<td class="header" >PIS RETIDO</td>
					<td class="header" >COFINS RETIDO</td>
				</tr>
					<?
						bloco_A();
					?>
			</table>
					<?
						break;
					case 'produtos':
					?>
				<table style="width: 100%;" class="normal">
					<tr class="header">
						<td class="header" >N° NF</td>
						<td class="header" >DESCR. PRODUTO</td>
						<td class="header" >VALOR</td>
						<td class="header" >PIS RETIDO</td>
						<td class="header" >COFINS RETIDO</td>
					</tr>
					<?
						bloco_C();
					?>
				</table>
					<?
						break;
					default:
						?>
						<tr>
							<td colspan="5" align='CENTER'>
							Nenhum tipo de relatório foi escolhido!
							</td>
						</tr>
						<?
						break;
				}?>
	</body>
</html>