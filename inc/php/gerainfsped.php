<?php
ini_set("display_errors","1");
error_reporting(E_ALL);

include_once("functions.php");
require_once(_CARBON_ROOT."/form/controllers/nfentrada_controller.php");

$idnf=$_GET["idnf"];

$simplesnascional='N';


		$sqlu="update nfitemxml set status='N' where idnf=".$idnf;
		$resu=d::b()->query($sqlu) or die("erro ao atualizar tabela nfitemxml sql".$sqlu);
                
		$sqlu="delete from nfitem where idprodserv is null and nfe='N' and idnf =".$idnf;
		$resu=d::b()->query($sqlu) or die("erro ao atualizar tabela nfitem sql".$sqlu);
                
		//verificar se ja foram buscados os item do xml para o BD		
		$sqlx="select * from nfitemxml where status = 'Y' and idnf=".$idnf;
		$resx=d::b()->query($sqlx) or die("erro ao buscar itens do xml no banco de dados sql=".$sqlx);
		$qtdx=mysqli_num_rows($resx);
		
		if($qtdx==0){
			
	    		$sql="select idempresa as id,xmlret as xml,faticms,imobilizado,consumo,outro,comercio, idpessoa,
					case icmscpl when 0.00 then null when icmscpl then icmscpl end as icmscpl,idfinalidadeprodserv
					,CASE
						WHEN faticms = 'Y' THEN 'faticms'
						WHEN consumo = 'Y' THEN 'consumo'
						WHEN imobilizado = 'Y' THEN 'imobilizado'
						WHEN comercio = 'Y' THEN 'comercio'
						ELSE 'outro'
					END as tipoconsumo
					from nf 
					where idnf = ".$idnf;
		    	$res=d::b()->query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());
		    	$qtdnf = mysqli_num_rows($res);
		    	$row=mysqli_fetch_assoc($res);	    		
	
	      			// passar string para UTF-8
	      			$xml=($row['xml']);
	      			//Carregar o XML em UTF-8      			
	      			$doc = DOMDocument::loadXML($xml);	
	     			
	      			//inicia lendo as principais tags do xml da nfe
		      		$nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
		      		$NFe = $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
					  $infNFe = $NFe->getElementsByTagName("infNFe")->item(0);  
					  
					//buscando informações do destinatario / emitente
					$emit= $infNFe->getElementsByTagName("emit")->item(0);
					$eCNPJ = $emit->getElementsByTagName("CNPJ")->item(0); 			    
					$veCNPJ =($eCNPJ->textContent); //pegar o valor da tag <CNPJ>	
					if(empty($eCNPJ) or empty($veCNPJ)){
						//COD_PART 
					   $eCNPJ = $emit->getElementsByTagName("CPF")->item(0); 			    
					   $veCNPJ =($eCNPJ->textContent); //pegar o valor da tag <CNPJ> 
				   }
						 

		      		//buscando informações do destinatario / Participante
				    $dest = $infNFe->getElementsByTagName("dest")->item(0); 
				    //COD_PART 
				    $CNPJ = $dest->getElementsByTagName("CNPJ")->item(0); 			    
				    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ>				   
				    
					$indIEDest = $dest->getElementsByTagName("indIEDest")->item(0); 
					$vindIEDest =($indIEDest->textContent);
                                    
				    if(empty($CNPJ) or empty($vCNPJ)){
				    	 //COD_PART 
					    $CNPJ = $dest->getElementsByTagName("CPF")->item(0); 			    
					    $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
				    }
	      			
		      		//buscando informações basicas
		      		$ide = $infNFe->getElementsByTagName("ide")->item(0); 
		      		$total = $infNFe->getElementsByTagName("total")->item(0);//Totais
                                
					$infAdic = $infNFe->getElementsByTagName("infAdic")->item(0);
					$infCpl = $infNFe->getElementsByTagName("infCpl")->item(0);
					$vinfCpl =($infCpl->textContent);
                                
                                
		      		$transp = $infNFe->getElementsByTagName("transp")->item(0);//Transp
		      		                          
					$NFref= $ide->getElementsByTagName("NFref")->item(0);	    					    		    			
					if($NFref!=null){
						$nrefNFe = $NFref->getElementsByTagName("refNFe")->item(0);
						$refNFe=($nrefNFe->textContent); //pegar o valor da tag <chNFe> 
					}

					$serie = $ide->getElementsByTagName("serie")->item(0);
					$vserie =($serie->textContent);

					$nNFe = $ide->getElementsByTagName("nNF")->item(0);
					$vnNFe =($nNFe->textContent);
                                
		      		//data emissao
		      		$dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
		      		$vdEmi =($dEmi->textContent); //pegar o valor da tag <chNFe> 

					//$timestamp = strtotime($vdEmi);
					//$newDate = date("Y-m-d H:i:s", $timestamp );

					$dhEmilocal = new \DateTime($vdEmi);
					$newDate = $dhEmilocal->format('Y-m-d H:i:s');


		      		//2013-01-14
					$dia = substr($vdEmi, -2, 2); // retorna "de"
					$mes = substr($vdEmi, -5, -3); // retorna "de"
					$ano = substr($vdEmi, 0, -6); // retorna "de"
					$vdEmi=$dia.$mes.$ano;
		      		
		      		//VL_DOC TOTAL DA NF
		      		$ICMSTot = $total->getElementsByTagName("ICMSTot")->item(0);//Totais		
			      		
		      		//VL_MERC valor total da mercadoria
		      		$vProd= $ICMSTot->getElementsByTagName("vProd")->item(0);//Totais	
		      		$nvProd =($vProd->textContent); 
					$valProd =number_format($nvProd, 2, '.','');

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
		      		
		      			      		
		      		// Valor total da COFINS VL_COFINS
		      		$vCOFINS= $ICMSTot->getElementsByTagName("vCOFINS")->item(0);//Totais	
		      		$vvCOFINS =($vCOFINS->textContent);
		      		$vvCOFINS =number_format($vvCOFINS, 2, ',','');
		      		
		      		//TOTAL DA NF *****************	
		      		$vNF= $ICMSTot->getElementsByTagName("vNF")->item(0);//Totais		
		      		$nvNF =($vNF->textContent); 
					$vvNF =number_format($nvNF, 2, '.','');
		      		
		      		//Buscando a chave da NFE
		      	
		      		$protNFe = $nfeProc->getElementsByTagName("protNFe")->item(0); // pegar a tag <protNFe> detro de <nfeProc>
		      		
		      		$infProt = $protNFe->getElementsByTagName("infProt")->item(0); //pegar a tag <infProt> dentro de  <protNFe>
	
		      		//Chave NFE ****************
		      		$chNFe = $infProt->getElementsByTagName("chNFe")->item(0); //pegar a tag <chNFe> dentro de <infProt>
		      		$vchNFe =($chNFe->textContent); //pegar o valor da tag <chNFe>
		      		
		      			      		
		      		//verifica quantos itens existem no xml da notafiscal
		      		$qtditem  =$infNFe->getElementsByTagName('det')->length;
		      		// VL_DOC valor total do documento
		      				      		
	   				    				    			
		      		 /*
		      		  * O C100 E A NOTA FISCAL 
		      		  * */
					// itens da NF
		    		for($nitem=0;$nitem<$qtditem;){//comentado o for e para outros tipos de nota que tem o C170
		    		
		    			$C170=$C170+1;
						$vvST='';
		    			$det = $infNFe->getElementsByTagName("det")->item($nitem);
		    			$prod = $det->getElementsByTagName("prod")->item(0);
		    			
		    			#############################################################
		    			//Codigo do produto COD_ITEM
		    			//$cProd = $prod->getElementsByTagName("cProd")->item(0);
		    			//$vcProd =($cProd->textContent);
		    			
		    			//Descrição do produto DESCR_COMPL
		    			$xProd = $prod->getElementsByTagName("xProd")->item(0);
		    			$vxProd =($xProd->textContent);
						$vxProd=str_replace("\""," ",$vxProd);
						$vxProd =str_replace("|"," ",$vxProd);
                                        
						$xncm = $prod->getElementsByTagName("NCM")->item(0);
		    			$ncm =($xncm->textContent);
                                        
						$cProd = $prod->getElementsByTagName("cProd")->item(0);
		    			$vcProd =($cProd->textContent);
						$vcProd=str_replace("\""," ",$vcProd);
						
						$cest = $prod->getElementsByTagName("CEST")->item(0);
						if($cest!=null){
							$vcest =($cest->textContent);
						}
                                    	 
		    			//quantidade do item QTD
		    			$qCom = $prod->getElementsByTagName("qCom")->item(0);
		    			$vqCom =($qCom->textContent);
		    			$vqCom =number_format($vqCom, 2, '.','');
		    			 
		    			//	unidade do item UNID
		    			$uTrib = $prod->getElementsByTagName("uTrib")->item(0);
		    			$vuTrib =($uTrib->textContent);		    			
		    			 
		    			// valor do item VL_ITEM
		    			$vUnCom = $prod->getElementsByTagName("vUnCom")->item(0);
		    			$vvUnCom =($vUnCom->textContent);
		    			$vvUnCom =number_format($vvUnCom, 2, '.','');
		    			 
		    			$vProd = $prod->getElementsByTagName("vProd")->item(0);
		    			$vvProd =($vProd->textContent);
		    			$vvProd =number_format($vvProd, 2, '.','');
		    			 
		    			$vFrete = $prod->getElementsByTagName("vFrete")->item(0);
		    			$vvFrete =($vFrete->textContent);
		    			$vvFrete =number_format($vvFrete, 2, '.','');
		    			
		    			//valor do desconto VL_DESC
		    			$vDesc = $prod->getElementsByTagName("vDesc")->item(0);
		    			$vvDesc =($vDesc->textContent);
		    			$vvDesc =number_format($vvDesc, 2, '.','');
                                        
						$viOutro = $prod->getElementsByTagName("vOutro")->item(0);
		    			$viOutrot =($viOutro->textContent);	
						$viOutrot =number_format($viOutrot, 2, '.','');
		    			 
		    			//CFOP ##############
		    			$CFOP = $prod->getElementsByTagName("CFOP")->item(0);
		    			$vCFOP =($CFOP->textContent);		    			
		    			
		    			$imposto = $det->getElementsByTagName("imposto")->item(0);
		    			
		    			//###Tributacao do item do item ###############################
						
		    			$ICMS = $imposto->getElementsByTagName("ICMS")->item(0);
		    			
		    			$ICMS40= $ICMS->getElementsByTagName("ICMS40")->item(0); 
		    			$ICMS70= $ICMS->getElementsByTagName("ICMS70")->item(0);
		    			
		    			$ICMS10= $ICMS->getElementsByTagName("ICMS10")->item(0);
		    			$ICMS20= $ICMS->getElementsByTagName("ICMS20")->item(0);
						$ICMS30= $ICMS->getElementsByTagName("ICMS20")->item(0); 
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
		    			$origem='0';
		    					    		    			
		    			if($ICMSSN103!=null){
		    				die("erro CONFIGURAR ICMSSN103".$row['idnf']);
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
	    					    						
			    		if($ICMS40!=null){
				    		 // echo("HE 40\n");
				    		  //Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS40->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent); 

							$orig= $ICMS40->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}


			    			$vmodBC =0;                                                 
                                                
			    			$vpICMS =0;   
			    			$vvBCicms =0;   
			    			$vRedBC=0; 
			    			$vvICMS=0;
			    					    			 
					    }elseif($ICMS70!=null){				   
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS70->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 

			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS70->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}

                                                
							$modBC=$ICMS70->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS70->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS70->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			// Vlr substituicao tributaria
			    			$vICMSST=$ICMS70->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS70->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent); 
                                                
							$pRedBC= $ICMS70->getElementsByTagName("pRedBC")->item(0);
							$vpRedBC =($pRedBC->textContent);   
			    			$vpRedBC =number_format($vpRedBC, 2, '.',''); 
			    			// echo("HE 20\n");					    
					    }elseif($ICMS20!=null){				   
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS20->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 

			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS20->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}

			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS20->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
                                                
							$modBC=$ICMS20->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS20->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			// Vlr substituicao tributaria
			    			$vICMSST=$ICMS20->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS20->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);  
                                                
							$pRedBC= $ICMS20->getElementsByTagName("pRedBC")->item(0);
			    			$vpRedBC =($pRedBC->textContent);   
			    			$vpRedBC =number_format($vpRedBC, 2, '.',''); 
			    			// echo("HE 20\n");					    
					    }elseif($ICMS30!=null){				   
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS30->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 

			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS30->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}

			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS30->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
                                                
							$modBC=$ICMS30->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS30->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			// Vlr substituicao tributaria
			    			$vICMSST=$ICMS30->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS30->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);  
                                                
							$pRedBC= $ICMS30->getElementsByTagName("pRedBC")->item(0);
			    			$vpRedBC =($pRedBC->textContent);   
			    			$vpRedBC =number_format($vpRedBC, 2, '.',''); 
			    			// echo("HE 20\n");					    
					    }elseif($ICMS90!=null){				   
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS90->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 

			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS90->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS90->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
                                                
							$modBC=$ICMS90->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS90->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    						    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS90->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);  
                                                
							$vpRedBC =0.00;   
			    			$vpRedBC =number_format($vpRedBC, 2, '.',''); 
			    			// echo("HE 20\n");					    
						}elseif($ICMSST!=null){
								
							//icms do item VL_BC_ICMS
                            $vvBCicms =number_format("0,00", 2, ',','');
							
							// Vlr icms item VL_ICMS
                            $vvICMS =number_format("0,00", 2, ',','');
                                                     
							$vpICMS =0;
							$vRedBC=0;

							$orig= $ICMSST->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
			    					
							//Situação Tributária referente ao ICMS CST_ICMS
							$vCST=$ICMSST->getElementsByTagName("CST")->item(0);
							$vvCST =($vCST->textContent);

							$pRedBC= $ICMSST->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
							$vpRedBC =($pRedBC->textContent);   
							$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
						}elseif($ICMS10!=null){				   
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS10->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 

			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS10->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS10->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
                                                
							$modBC=$ICMS10->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS10->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			$vICMSST=$ICMS10->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS10->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);
                                                
							$pRedBC= $ICMS10->getElementsByTagName("pRedBC")->item(0);
			    			$vpRedBC =($pRedBC->textContent);   
			    			$vpRedBC =number_format($vpRedBC, 2, '.',''); 
			    		    
			    			// echo("HE 20\n");					    
					    }elseif($ICMS00!=null){						     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS00->getElementsByTagName("vBC")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 	
			    			
			    			$vRedBC=0; 
							$vpRedBC=0.00;

							$orig= $ICMS00->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
                                                
							$modBC=$ICMS00->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMS00->getElementsByTagName("pICMS")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS00->getElementsByTagName("vICMS")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			$vICMSST=$ICMS00->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMS00->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);   			    			     
			    							    
					    }elseif($ICMS60!=null){				   
				     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMS60->getElementsByTagName("vBCSTRet")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 
			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMS60->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
			    					
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMS60->getElementsByTagName("vICMSSTRet")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			$modBC=$ICMS60->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    			
			    			//Situação Tributaria referente ao ICMS CST_ICMS
			    			$vCST=$ICMS60->getElementsByTagName("CST")->item(0);
			    			$vvCST =($vCST->textContent);        
			    			// echo("HE 20<BR>");
				    		//zerar pos este ST nao tem aliq
			    			$vpICMS =0;
							$vpRedBC=0.00;
			    			
					    }elseif($ICMSSN500!=null){
							
							$simplesnascional='Y';		
							
							$orig= $ICMSSN500->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
					     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMSSN500->getElementsByTagName("vBCSTRet")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.','');                                                 
                                                
							$modBC=$ICMSSN500->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    					
			    			// Vlr icms item VL_ICMS
			    			$vICMS=$ICMSSN500->getElementsByTagName("vICMSSTRet")->item(0);
			    			$vvICMS =($vICMS->textContent);
			    			$vvICMS =number_format($vvICMS, 2, '.',''); 
			    			
			    			//Situação Tributaria referente ao ICMS CST_ICMS
			    			$vCST=$ICMSSN500->getElementsByTagName("CSOSN")->item(0);
			    			$vvCST =($vCST->textContent);        
			    			// echo("HE 20<BR>");
			    			$vpICMS =0;
			    			$vRedBC==0;
                                                
							$pRedBC= $ICMSSN500->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
								$vpRedBC =($pRedBC->textContent);   
			    				$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
					    
					    }elseif($ICMSSN101!=null){
							$simplesnascional='Y';		
					    	 
					    	$vCST=$ICMSSN101->getElementsByTagName("CSOSN")->item(0);
					    	$vvCST =($vCST->textContent);
                                                
							$modBC=$ICMSSN101->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
					    		
					    	$vpICMS =0;
					    	$vvBCicms =0;
					    	$vRedBC=0;
					    	$vvICMS=0;

							$orig= $ICMSSN101->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
                                                
							$pRedBC= $ICMSSN101->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
								$vpRedBC =($pRedBC->textContent);   
								$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
					    }elseif($ICMSSN102!=null){
							$simplesnascional='Y';		
					    		
					    	$vCST=$ICMSSN102->getElementsByTagName("CSOSN")->item(0);
					    	$vvCST =($vCST->textContent);
                                                
							$modBC=$ICMSSN102->getElementsByTagName("modBC")->item(0);

							if($modBC!=null){
								$vmodBC =($modBC->textContent);
							}else{
								$vmodBC =0;
							}
			    			
					    
			    			$vpICMS =0;   
			    			$vvBCicms =0;   
			    			$vRedBC=0; 
			    			$vvICMS=0;

							$orig= $ICMSSN102->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
                                                
							$pRedBC= $ICMSSN102->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
								$vpRedBC =($pRedBC->textContent);   
								$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
					    }elseif($ICMSSN202!=null){	
							$simplesnascional='Y';							     
					     //icms do item VL_BC_ICMS
			    			$vBCicms= $ICMSSN202->getElementsByTagName("vBCST")->item(0);
			    			$vvBCicms =($vBCicms->textContent);   
			    			$vvBCicms =number_format($vvBCicms, 2, '.',''); 	
			    			
			    			$vRedBC=$vvProd-$vvBCicms;

							$orig= $ICMSSN202->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
                                                
							$modBC=$ICMSSN202->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);
			    					
			    			//Aliguota 	ALIQ_ICMS
			    			$pICMS= $ICMSSN202->getElementsByTagName("pICMSST")->item(0);
			    			$vpICMS =($pICMS->textContent);
			    			$vpICMS =number_format($vpICMS, 2, '.',''); 
			    			
			    			// Vlr icms item VL_ICMS
			    			$vICMSST=$ICMSSN202->getElementsByTagName("vICMSST")->item(0);
			    			$vvST =($vICMSST->textContent);
			    			$vvST =number_format($vvST, 2, '.','');
			    			
			    			//Situação Tributária referente ao ICMS CST_ICMS
			    			$vCST=$ICMSSN202->getElementsByTagName("CSOSN")->item(0);
			    			$vvCST =($vCST->textContent);   
                                                
							$pRedBC= $ICMSSN202->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
								$vpRedBC =($pRedBC->textContent);   
								$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
			    							    
					    }elseif($ICMSSN900!=null){
							$simplesnascional='Y';		
					    	 
					    	$vCST=$ICMSSN900->getElementsByTagName("CSOSN")->item(0);
					    	$vvCST =($vCST->textContent);
                                                
							$modBC=$ICMSSN900->getElementsByTagName("modBC")->item(0);
			    			$vmodBC =($modBC->textContent);

							$orig= $ICMSSN900->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
						
					    		
			    			$vpICMS =0;   
			    			$vvBCicms =0;   
			    			$vRedBC=0; 
			    			$vvICMS=0;
                                                
							$pRedBC= $ICMSSN900->getElementsByTagName("pRedBC")->item(0);
							if($pRedBC!=null){
								$vpRedBC =($pRedBC->textContent);   
								$vpRedBC =number_format($vpRedBC, 2, '.',''); 
							}else{
								$vpRedBC=0; 
							}
					    }elseif($ICMSSN201!=null){
							$simplesnascional='Y';		
							//icms do item VL_BC_ICMS
							$vBCicms= $ICMSSN201->getElementsByTagName("vBCST")->item(0);
							$vvBCicms =($vBCicms->textContent);
							$vvBCicms =number_format($vvBCicms, 2, '.','');

							$orig= $ICMSSN201->getElementsByTagName("orig")->item(0);
							if($orig!=null){
								$origem =($orig->textContent);
							}
						
						
						
							$vRedBC=$nvProd-$vvBCicms;
						
							//Aliguota 	ALIQ_ICMS
							$pICMS= $ICMSSN201->getElementsByTagName("pICMSST")->item(0);
							$vpICMS =($pICMS->textContent);
							$vpICMS =number_format($vpICMS, 2, '.','');       
						
							// Vlr icms item VL_ICMS
							$vICMS=$ICMSSN201->getElementsByTagName("vICMSST")->item(0);
							$vvICMS =($vICMS->textContent);
							$vvICMS =number_format($vvICMS, 2, '.','');
						
							//Situação Tributária referente ao ICMS CST_ICMS
							$vCST=$ICMSSN201->getElementsByTagName("CSOSN")->item(0);
							$vvCST =($vCST->textContent);
						
						}
					       				    			
		    			#### IPI
		    			//cst ipi 	CST_IPI
		    			$IPI = $imposto->getElementsByTagName("IPI")->item(0);
		    			if($IPI!=null){
			    			$IPITrib = $IPI->getElementsByTagName("IPITrib")->item(0);
							$IPINT = $IPI->getElementsByTagName("IPINT")->item(0);
                                                
			    			if($IPITrib!=null){
                                                    
								$vvCSTIPI = $IPITrib->getElementsByTagName("CST")->item(0);
								$vvBCIPI = $IPITrib->getElementsByTagName("vBC")->item(0);
								$vvpIPI = $IPITrib->getElementsByTagName("pIPI")->item(0);
			    				$vvIPI = $IPITrib->getElementsByTagName("vIPI")->item(0);
			    				if($vvIPI!=null){
			    					$vCSTIPI =($vvCSTIPI->textContent);
									$vBCIPI =($vvBCIPI->textContent);
									$vpIPI =($vvpIPI->textContent);
									$vIPI =($vvIPI->textContent);
			    				}else{
			    					$vIPI=0;
									$vCSTIPI =0;
									$vBCIPI =0;
									$vpIPI =0;
			    				}
			    			}elseif($IPINT!=null){
									$vvCSTIPI = $IPINT->getElementsByTagName("CST")->item(0);
									$vCSTIPI =($vvCSTIPI->textContent);
									$vIPI=0;
									$vBCIPI =0;
									$vpIPI =0;
							}else{
			    				$vIPI=0;
								$vCSTIPI =0;
								$vBCIPI =0;
								$vpIPI =0;
			    			}
			    			//	Código de enquadramento legal do IPICOD_ENQ
			    			//$cEnq = $IPI->getElementsByTagName("cEnq")->item(0);
			    			//$vcEnq =($cEnq->textContent);
		    			}else{
		    				$vIPI=0;
							$vCSTIPI =0;
							$vBCIPI =0;
							$vpIPI =0;
		    			}	    			
		    			
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
							
							// material aplicado
							if($row['idfinalidadeprodserv']==19){
								if(intval($vCFOP) < 6000 ){
									$vCFOP='1128';
								}else{
									$vCFOP='2128';
								}
							
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
							}
							/*
							if(($vCFOP=='5101' or $vCFOP=='5102' or $vCFOP=='5103') and $row['faticms']=='Y'){
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
							}elseif($vCFOP=='6949' ){
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
							}
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
							}elseif($vCFOP=='6151'){
								$vCFOP='2151';
							}elseif($vCFOP=='6656'){
								$vCFOP='2653';
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
		    			
		
		    			###PIS
		    			// CST_PIS
		    			$PIS = $imposto->getElementsByTagName("PIS")->item(0);
		    			$PISAliq = $PIS->getElementsByTagName("PISAliq")->item(0);
		    			$PISOutr = $PIS->getElementsByTagName("PISOutr")->item(0);
		    			$PISNT = $PIS->getElementsByTagName("PISNT")->item(0);
		    			//algums xmls não tem a tag PISaliq
		    			if($PISAliq!=null){
			    			$CSTpis= $PISAliq->getElementsByTagName("CST")->item(0);
			    			$vCSTpis =($CSTpis->textContent);
			    			//VL_BC_PIS
			    			$vBCpis= $PISAliq->getElementsByTagName("vBC")->item(0);
			    			$vvBCpis =($vBCpis->textContent);
			    			$vvBCpis =number_format($vvBCpis, 2, '.','');
			    			//ALIQ_PIS
			    			$pPIS= $PISAliq->getElementsByTagName("pPIS")->item(0);
			    			$vpPIS =($pPIS->textContent);
			    			$vpPIS =number_format($vpPIS, 2, '.','');

		    			}elseif($PISOutr!=null){
			    			$CSTpis= $PISOutr->getElementsByTagName("CST")->item(0);
			    			$vCSTpis =($CSTpis->textContent);
			    			//VL_BC_PIS
			    			$vBCpis= $PISOutr->getElementsByTagName("vBC")->item(0);
			    			$vvBCpis =($vBCpis->textContent);
			    			$vvBCpis =number_format($vvBCpis, 2, '.','');
			    			//ALIQ_PIS
			    			$pPIS= $PISOutr->getElementsByTagName("pPIS")->item(0);
                                                if($pPIS!=null){
                                                    $vpPIS =($pPIS->textContent);
                                                    $vpPIS =number_format($vpPIS, 2, '.','');
                                                }else{
                                                    $vpPIS =number_format(0.00, 2, '.','');
                                                }
			    			
			    			//VL_PIS
			    			//$vPISitem= $PISAliq->getElementsByTagName("vPIS")->item(0);
			    			//$vvPISitem=($vPISitem->textContent);
			    			//$vvPISitem =number_format($vvPISitem, 2, '.','');
		    			}else{
		    				$PISNT = $PIS->getElementsByTagName("PISNT")->item(0);
                                                $CSTpis = $PISNT->getElementsByTagName("CST")->item(0);
		    				$vCSTpis =($CSTpis->textContent);
                                                $vpPIS=0;
                                                $vvBCpis =0;
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
			    			$vvBCcofins =number_format($vvBCcofins, 2, '.','');
			    			
			    			//	ALIQ_COFINS
			    			$pCOFINS = $COFINSAliq->getElementsByTagName("pCOFINS")->item(0);
			    			$vpCOFINS=($pCOFINS->textContent);
			    			$vpCOFINS =number_format($vpCOFINS, 2, '.','');
		    			
			    			//VL_COFINS
			    			$vCOFINSitem = $COFINSAliq->getElementsByTagName("vCOFINS")->item(0);
			    			$vvCOFINSitem=($vCOFINSitem->textContent);
			    			$vvCOFINSitem =number_format($vvCOFINSitem, 2, '.','');
		    			}elseif($COFINSOutr!=null){
			    			$CSTcofins = $COFINSOutr->getElementsByTagName("CST")->item(0);
			    			$vCSTcofins=($CSTcofins->textContent);		    				    			
			    			//	VL_BC_COFINS
			    			$vBCcofins = $COFINSOutr->getElementsByTagName("vBC")->item(0);
			    			$vvBCcofins=($vBCcofins->textContent);
			    			$vvBCcofins =number_format($vvBCcofins, 2, '.','');		    			
			    			//	ALIQ_COFINS
			    			$pCOFINS = $COFINSOutr->getElementsByTagName("pCOFINS")->item(0);
			    			$vpCOFINS=($pCOFINS->textContent);
			    			$vpCOFINS =number_format($vpCOFINS, 2, '.','');	    			
			    			//VL_COFINS
			    			$vCOFINSitem = $COFINSOutr->getElementsByTagName("vCOFINS")->item(0);
			    			$vvCOFINSitem=($vCOFINSitem->textContent);
			    			$vvCOFINSitem =number_format($vvCOFINSitem, 2, '.','');
		    			}elseif($COFINSQtde!=null){
		    				$CSTcofins = $COFINSQtde->getElementsByTagName("CST")->item(0);
		    				$vCSTcofins=($CSTcofins->textContent);
                                                $vvCOFINSitem =0;
                                                $vpCOFINS =0;   
                                                $vvBCcofins =0;
		    			}else{
		    				$COFINSNT = $COFINS->getElementsByTagName("COFINSNT")->item(0);
		    				$CSTcofins = $COFINSNT->getElementsByTagName("CST")->item(0);
			    			$vCSTcofins=($CSTcofins->textContent);	  
			    			$vvCOFINSitem =0;
			    			$vpCOFINS =0;
			    			$vvBCcofins =0;
		    			}		
		    				    			
		    				    				    			    			
		    			$nitem++;
		    			if($nitem<10){
		    				$stritem = "00";
		    			}elseif($nitem>=10 and $nitem <100){
		    			$stritem = "0";
		    			}else{
		    			$stritem ="";
		    			}
						$vlrtotalitem =$vvProd;//$vvFrete + $vvProd; 
						$vlrtotalitem =number_format($vlrtotalitem, 2, '.','');
						
						// Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
						if($row['consumo']=="Y" or $row['imobilizado']=='Y'  or $row['comercio']=='Y'){						
							$vRedBC =0;
							$vvBCicms =0;
							$vvICMS =0;							
						}
						
						if($vRedBC<0){
							$vRedBC=0;
						}

						if(!empty($row['icmscpl'])){
							$vpICMS= $row['icmscpl'];
							$vvICMS =(($vlrtotalitem * $row['icmscpl'])/100);
							$vRedBC=0;
							$origem='0';
							$vvBCicms=$vlrtotalitem;
						}
						
						if(empty($vvST)){
							$vvST=0;
						}
                                                
						$vlritem=($vlrtotalitem/$vqCom);
						
						if(empty($vpRedBC)){$vpRedBC=0;}

						IF($vqCom<1){							
							$vqCom=1.00;
						}
						 
						 $vqCom =number_format($vqCom, 2, '.','');
		    			 /*
		    			  * OS ITEM DA NF
                                          *    
		    			  * */	
						$prodserv = NfEntradaController::buscarIdProdservPorIdpessoaIdCodForn($vcProd, $row['idpessoa'],$row['id']);
						$idproserv = empty($prodserv) ? '0' : $prodserv['idprodserv'];
		    			// insere os items no banco
		    			$sqlin=" insert into nfitemxml(idempresa,idnf,prodservdescr,qtd,un,valor,des,cst,cfop,aliqicms,valicms,basecalc,redbc,valipi,ipint,bcipi,aliqipi,frete,vst,outro,
                                            indiedest,confinscst,piscst,modbc,aliqcofins,aliqpis,cprod,cest,aliqbasecal,ncm,bccofins,bcpis,cofins,origem, idprodserv)
		    			values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idnf.", '".addslashes($vxProd)."','".$vqCom."','".$vuTrib."','".$vlrtotalitem."','".$vvDesc."'
		    			,'".$vvCST."','".$vCFOP."','".$vpICMS."','".$vvICMS."','".$vvBCicms."','".$vRedBC."','".$vIPI."','".$vCSTIPI."','".$vBCIPI."','".$vpIPI."','".$vvFrete."','".$vvST."','".$viOutrot."',
                                            '".$vindIEDest."','".$vCSTcofins."','".$vCSTpis."','".$vmodBC."','".$vpCOFINS."','".$vpPIS."','".$vcProd."','".$vcest."','".$vpRedBC."','".$ncm."','".$vvBCcofins."','".$vvBCpis."',
                                                '".$vvCOFINSitem."','".$origem."' ,'".$idproserv."')";
		    			
		    			//echo($sqlin);
		    			$resin=d::b()->query($sqlin) or die("Erro ao inserir itens sql=".$sqlin);                                   
		    						
		    		}

		
		if($simplesnascional=='Y'){
			$sqlx1="update nf n 
						join pessoa p on (p.idpessoa = n.idpessoa)
					set p.regimetrib=1
					where n.idnf=".$idnf;
			$resx1=d::b()->query($sqlx1) or die("erro ao atualizar fornecedor simples nascional sql=".$sqlx1);
		}else{
			$sqlx1="update nf n 
						join pessoa p on (p.idpessoa = n.idpessoa)
					set p.regimetrib=3
					where p.regimetrib is null
					and n.idnf=".$idnf;
			$resx1=d::b()->query($sqlx1) or die("erro ao atualizar fornecedor simples nascional sql=".$sqlx1);
		}
		
		$dataPrazo = explode(" ", $newDate);
		//, prazo = '".$dataPrazo[0]."'
    	$sqlx="update nf set  dtemissao = '".$newDate."', remcnpj='".$veCNPJ."',nnfe='".$vnNFe."',serie='".$vserie."',idnfe='".$vchNFe."',nfref='".$refNFe."',vprod='".$valProd."',vnf='".$vvNF."',infcpl='".$vinfcpl."' where idnf=".$idnf; 
		$resx=d::b()->query($sqlx) or die("erro ao atualizar a chave da NF sql=".$sqlx);
		$qtdx=mysqli_num_rows($resx);
		    ///echo('Carregado com sucesso...');
		}else{
			echo("Já carregado informações para o SPEED");
		}	
	
?>