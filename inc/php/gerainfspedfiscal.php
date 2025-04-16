<?php
ini_set("display_errors","1");
error_reporting(E_ALL);

include_once("functions.php");

$idnf=$_GET["idnf"];

$sqlsc100="select * from spedc100 where idnf = ".$idnf." and status = 'CORRIGIDO'";
$resc100=d::b()->query($sqlsc100) or die($sqlsc100." erro ao buscar informações do bloco C100".mysql_error());
$qtdc100=mysqli_num_rows($resc100);
if($qtdc100>0){
    echo "Sucesso, não atualizado sped por estar corrigido.";

}else{


//$sql="select idempresa as id,xml from xmlnf where mes = 'fevereiro' and tipo = 'P' and idempresa=".$idempresa;  
$sql="select idempresa as id,xmlret as xml,tiponf,faticms,consumo,imobilizado,outro,comercio,idnf,nnfe,DATE_FORMAT(prazo,'%d%m%Y') as prazo, 
case icmscpl when 0.00 then null when icmscpl then icmscpl end as icmscpl,idfinalidadeprodserv
,CASE
    WHEN faticms = 'Y' THEN 'faticms'
    WHEN consumo = 'Y' THEN 'consumo'
    WHEN imobilizado = 'Y' THEN 'imobilizado'
    WHEN comercio = 'Y' THEN 'comercio'
    ELSE 'outro'
END as tipoconsumo
from nf 
where  tiponf in ('C', 'O') 
 and envionfe = 'CONCLUIDA'
and idnf=".$idnf;   

//echo($sql);
$res= d::b()->query($sql) or die($sql." erro ao buscar informações do bloco C001".mysql_error());

$qtdnf = mysqli_num_rows($res);
$C001=1;
$C100 = 0;	
$C101 = 0;
$C190 = 0;
$C170=0;
$n=0;
//$this->vlrTotalIcms=0;
//$this->vlrTotalded=0;
$arrnumnf= array();
while ($row=mysqli_fetch_assoc($res)){
  //echo($row['idnf']."\n");
  $C100=$C100+1;
  $n=$n+1;
  // passar string para UTF-8
  $xml=$row['xml'];
  
  //Carregar o XML em UTF-8      			
  $doc = DOMDocument::loadXML($xml);	

  //inicia lendo as principais tags do xml da nfe
  $nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
  $NFe = $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
  $infNFe = $NFe->getElementsByTagName("infNFe")->item(0); 
  //BUSCAR A CHAVE DA NFE
  $chave= $infNFe->getAttribute("Id");	      		
  $vchNFe=substr($chave,3);

  if($row['tiponf']=='C' || $row['tiponf']=='O'){
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
  if($row['tiponf']=='C' || $row['tiponf']=='O'){
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
      //$retcomparadata=comparadata($this->dtInicio,$dataxml);
      
                if($row['tiponf']=='C' || $row['tiponf']=='O'){
                    $vdEntrada=$row['prazo'];
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
     // $retcomparadata=comparadata($this->dtInicio,$dataxml);
      
      if($row['tiponf']=='C' || $row['tiponf']=='O'){
            $vdEntrada=$row['prazo'];                
      }else{
            $vdEntrada=$vdEmi;
      }
  }
  
  //VL_DOC TOTAL DA NF
  $ICMSTot = $total->getElementsByTagName("ICMSTot")->item(0);//Totais		

  //VL_DESC valor do desconto
  if($row['tiponf']=='C' || $row['tiponf']=='O'){
$vDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais	
  $vvDesc =($vDesc->textContent); 
  $vvDesc=number_format($vvDesc, 2, '.','');
  }else{
  /*$vDesc= $ICMSTot->getElementsByTagName("vDesc")->item(0);//Totais
  $vvDesc =($vDesc->textContent);
  $vvDesc=number_format($vvDesc, 2, ',','');*/
  $vvDesc =number_format('0,00', 2, '.','');
  }
  
  //VL_MERC valor total da mercadoria
  $vProd= $ICMSTot->getElementsByTagName("vProd")->item(0);//Totais	
  $nvProd =($vProd->textContent); 
  $vvProd =number_format($nvProd, 2, '.','');
  //VL_FRT valor total do frete********************
  $vFrete= $ICMSTot->getElementsByTagName("vFrete")->item(0);//Totais	
  $nvFrete =($vFrete->textContent); 
  $vvFrete =number_format($nvFrete, 2, '.','');
  // valor da base de calculo VL_BC_ICMS
  $vBC= $ICMSTot->getElementsByTagName("vBC")->item(0);//Totais	
  $nvBC =($vBC->textContent);
  $vvBC =number_format($nvBC, 2, '.','');
  
  //valor do ICMS VL_ICMS
  $vtICMS= $ICMSTot->getElementsByTagName("vICMS")->item(0);//Totais	
  $nvtICMS =($vtICMS->textContent);
  $vvtICMS =number_format($nvtICMS, 2, '.','');
  
  //Valor da base de calculo do ICMS substituição tributaria VL_BC_ICMS_ST
  $vBCST= $ICMSTot->getElementsByTagName("vBCST")->item(0);//Totais	
  $vvBCST =($vBCST->textContent);
  $vvBCST =number_format($vvBCST, 2, '.','');

  //Valor total do IPI 	VL_IPI
  $vIPI= $ICMSTot->getElementsByTagName("vIPI")->item(0);//Totais	
  $nvIPI =($vIPI->textContent);
  $vvIPI =number_format($nvIPI, 2, '.','');
  
  //Valor do seguro indicado no documento fiscal VL_SEG
  $vSeg= $ICMSTot->getElementsByTagName("vSeg")->item(0);//Totais	
  $vvSeg =($vSeg->textContent);	
  $vvSeg =number_format($vvSeg, 2, '.','');   

  //Valor de outras despesas acessorias fiscal VL_OUT_DA
  $vOutro= $ICMSTot->getElementsByTagName("vOutro")->item(0);//Totais	
  $vvOutro =($vOutro->textContent);	
  $vvOutro =number_format($vvOutro, 2, '.','');   	      		
  
  // Valor total do PIS VL_PIS
  $vPIS= $ICMSTot->getElementsByTagName("vPIS")->item(0);//Totais	
  $vvPIS =($vPIS->textContent);
  $vvPIS =number_format($vvPIS, 2, '.','');
  
  // Valor total da COFINS VL_COFINS
  $vCOFINS= $ICMSTot->getElementsByTagName("vCOFINS")->item(0);//Totais	
  $vvCOFINS =($vCOFINS->textContent);
  $vvCOFINS =number_format($vvCOFINS, 2, '.','');
  
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
  if(($row['consumo']=="Y" or $row['imobilizado']=='Y'  or $row['comercio']=='Y' ) and ($row['tiponf'] !='V')){
  
      $vvOutro =number_format('0,00', 2, '.','');
      $vvBC =number_format('0,00', 2, '.','');
      $vvtICMS =number_format('0,00', 2, '.','');
      $vvIPI=number_format('0,00', 2, '.','');
      $vvPIS =number_format('0,00', 2, '.','');
      $vvCOFINS =number_format('0,00', 2, '.','');
  }
  
  if(!empty($row['icmscpl'])){
      
       $vvtICMS =(($nvNF * $row['icmscpl'])/100);
      $vvtICMS =number_format($vvtICMS, 2, '.','');
      $vvBC=number_format($nvNF, 2, '.','');
  }
 /*  
        if($vtpNF==1){//ativado por causa da desoneração
            // VL_DOC valor total do documento                                 
            $vlrOper=$nvProd+$nvFrete;
            $vvNF =number_format($vlrOper, 2, '.',''); //$vnNF   
            //$vvProd =number_format($vlrOper, 2, '.',''); //$vvProd
                            
  }else{// usado para notas de compra
        */
            $vvNF =number_format($nvNF, 2, '.','');
       // }
            
        if($vserie=='890' or $vserie=='893'){//ser for serie 890 lançar como nota fiscal
            $vmod='01';
            $vchNFe='';
        }
  
//  $_SESSION['C100'][$C100]="|C100|".$vtpNF."|".$IND_EMIT."|".$vCNPJ."|".$vmod."|00|".$vserie."|".$vnNF."|".$vchNFe."|".$vdEmi."|".$vdEntrada."|".$vvNF."|1|".$vvDesc."||".$vvProd."|".$vmodFrete."|".$vvFrete."|".$vvSeg."|".$vvOutro."|".$vvBC ."|".$vvtICMS."|||".$vvIPI."|".$vvPIS."|".$vvCOFINS."|||\n";
  //Duvidas IND_EMIT COD_PART COD_SIT DT_E_S 	IND_PGTO  VL_ABAT_NT VL_ICMS_ST VL_PIS_ST VL_COFINS_ST
//echo($_SESSION['C100'][$C100]);

$sqld="update spedc100 set status='INATIVO' where idnf=".$idnf;
$resd=d::b()->query($sqld);

$sqld1="update spedc170 set status='INATIVO' where idnf=".$idnf;
$resd1=d::b()->query($sqld1);

$sqld2="update spedc190 set status='INATIVO' where idnf=".$idnf;
$resd2=d::b()->query($sqld2);

$vvDesc =number_format($vvDesc, 2, '.','');
$vvProd =number_format($vvProd, 2, '.','');
$vvFrete =number_format($vvFrete, 2, '.','');
$vvSeg =number_format($vvSeg, 2, '.','');
$vvOutro =number_format($vvOutro, 2, '.','');
$vvBC =number_format($vvBC, 2, '.','');
$vvtICMS =number_format($vvtICMS, 2, '.','');
$vvIPI =number_format($vvIPI, 2, '.','');
$vvPIS =number_format($vvPIS, 2, '.','');
$vvCOFINS =number_format($vvCOFINS, 2, '.','');

$sqli="INSERT INTO spedc100
(
idempresa,idnf,vtpnf,indemit,vcnpj,vmod,vserie,vnnf,vchnfe,vdemi,vdentrada,vvnf,vvdesc,vvprod,vmodfrete,vvfrete,vvseg,vvoutro,vvbc,
vvticms,vvipi,vvpis,vvcofins,criadopor,criadoem,alteradopor,alteradoem
)
VALUES
(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idnf.",'".$vtpNF."','".$IND_EMIT."','".$vCNPJ."','".$vmod."','".$vserie."','".$vnNF."','".$vchNFe."','".$vdEmi."','".$vdEntrada."','".$vvNF."','".$vvDesc."','".$vvProd."','".$vmodFrete."','".$vvFrete."','".$vvSeg."','".$vvOutro."','".$vvBC ."','".$vvtICMS."','".$vvIPI."','".$vvPIS."','".$vvCOFINS."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";

$resin=d::b()->query($sqli) or die("Erro ao inserir sped C100 sql=".$sqli); 

$sqld1="update spedc101 set status='INATIVO' where idnf=".$idnf;
$resd1=d::b()->query($sqld1);

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
        
        $vvFCPUFDest =number_format($vvFCPUFDest, 2, '.','');
        $vvICMSUFDest =number_format($vvICMSUFDest, 2, '.','');
        $vvICMSUFRemet =number_format($vvICMSUFRemet, 2, '.','');
                                
        
                                
        $C101=$C101+1;
  //      $_SESSION['C101'][$C101]="|C101|".$vvFCPUFDest."|".$vvICMSUFDest."|".$vvICMSUFRemet."|\n";		    			
     //   echo($_SESSION['C101'][$C101]);
        
        $sq101=" INSERT INTO spedc101
        (
        idempresa, idnf,vufdest,vvfcpufdest,vvicmsufdest,vvicmsufremet,
        criadopor,criadoem,alteradopor,alteradoem)
        VALUES
        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idnf.",'".$vUFDest."','".number_format($vvFCPUFDest, 2, '.','')."','".number_format($vvICMSUFDest, 2, '.','')."','".number_format($vvICMSUFRemet, 2, '.','') ."',
        '".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
        $re101=d::b()->query($sq101) or die("Erro ao inserir sped C101 sql=".$sq101); 
                        
    }
}

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
        die("erro ICMSSN103 não configurado".$row['idnf']);
    }
    
    if($ICMSSN203!=null){
        die("erro ICMSSN203 não configurado".$row['idnf']);
    }
    if($ICMSSN300!=null){
        die("erro ICMSSN300 não configurado".$row['idnf']);
    }
    if($ICMSSN400!=null){
        die("erro ICMSSN400 não configurado".$row['idnf']);
    }

                
    if($ICMS40!=null){
         // echo("HE 40<BR>");
          //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMS40->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);        
        
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms
        $vvICMS =number_format("0,00", 2, '.','');
        $vvBCicms =number_format("0,00", 2, '.','');
    }
    
    if($ICMS20!=null){				   
     
     //icms do item VL_BC_ICMS
        $vBCicms= $ICMS20->getElementsByTagName("vBC")->item(0);
        $vvBCicms =($vBCicms->textContent);   
        $vvBCicms =number_format($vvBCicms, 2, '.',''); 	
                
        //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMS20->getElementsByTagName("pICMS")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.',''); 
        
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS20->getElementsByTagName("vICMS")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.',''); 
        
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMS20->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);        
    
    }
    if($ICMS70!=null){
         
        //icms do item VL_BC_ICMS
        $vBCicms= $ICMS70->getElementsByTagName("vBC")->item(0);
        $vvBCicms =($vBCicms->textContent);
        $vvBCicms =number_format($vvBCicms, 2, '.','');
         
        //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMS70->getElementsByTagName("pICMS")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.','');
         
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS70->getElementsByTagName("vICMS")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.','');
         
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMS70->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);
    
    }
    if($ICMS10!=null){
    
        //icms do item VL_BC_ICMS
        $vBCicms= $ICMS10->getElementsByTagName("vBC")->item(0);
        $vvBCicms =($vBCicms->textContent);
        $vvBCicms =number_format($vvBCicms, 2, '.','');
    
           //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMS10->getElementsByTagName("pICMS")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.','');
    
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS10->getElementsByTagName("vICMS")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.','');
    
        //Situação Tributária referente ao ICMS CST_ICMS
        $vCST=$ICMS10->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);
        // echo("HE 20\n");
    }
    
    if($ICMS00!=null){					     
     //icms do item VL_BC_ICMS
        $vBCicms= $ICMS00->getElementsByTagName("vBC")->item(0);
        $vvBCicms =($vBCicms->textContent);   
        $vvBCicms =number_format($vvBCicms, 2, '.',''); 	
                
        //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMS00->getElementsByTagName("pICMS")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.',''); 
        
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS00->getElementsByTagName("vICMS")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.',''); 
        
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMS00->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);				    
    }
                    
    if($ICMS90!=null){					     
     //icms do item VL_BC_ICMS
        $vBCicms= $ICMS90->getElementsByTagName("vBC")->item(0);
        $vvBCicms =($vBCicms->textContent);   
        $vvBCicms =number_format($vvBCicms, 2, '.',''); 	
                
        //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMS90->getElementsByTagName("pICMS")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.',''); 
        
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS90->getElementsByTagName("vICMS")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.',''); 
        
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMS90->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);				    
    }
                    
    if($ICMSST!=null){				     
     //icms do item VL_BC_ICMS
        $vvBCicms =number_format("0,00", 2, '.','');
                        
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMSST->getElementsByTagName("CST")->item(0);
        $vvCST =($vCST->textContent);  
                
        // Vlr icms item VL_ICMS
        $vvICMS =number_format("0,00", 2, '.','');
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms			    
    }	
    
    if($ICMS60!=null){					     
     //icms do item VL_BC_ICMS
        $vBCicms= $ICMS60->getElementsByTagName("vBCSTRet")->item(0);
        $vvBCicms =($vBCicms->textContent);   
        $vvBCicms =number_format($vvBCicms, 2, '.',''); 	
                
        // Vlr icms item VL_ICMS
        $vICMS=$ICMS60->getElementsByTagName("vICMSSTRet")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.',''); 
        
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
        $vvBCicms =number_format($vvBCicms, 2, '.',''); 	
                
        // Vlr icms item VL_ICMS
        $vICMS=$ICMSSN500->getElementsByTagName("vICMSSTRet")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.',''); 
        
        //Situação Tributaria referente ao ICMS CST_ICMS
        $vCST=$ICMSSN500->getElementsByTagName("CSOSN")->item(0);
        $vvCST =($vCST->textContent);     
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms			    
    }	


    if($ICMSSN101!=null){
         
        $vCST=$ICMSSN101->getElementsByTagName("CSOSN")->item(0);
        $vvCST =($vCST->textContent);
         
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms
        $vvICMS =number_format("0,00", 2, '.','');
        $vvBCicms =number_format("0,00", 2, '.','');
    }
    if($ICMSSN102!=null){
         
        $vCST=$ICMSSN102->getElementsByTagName("CSOSN")->item(0);
        $vvCST =($vCST->textContent);
            
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms
        $vvICMS =number_format("0,00", 2, '.','');
        $vvBCicms =number_format("0,00", 2, '.','');
    }
    if($ICMSSN202!=null){
        //icms do item VL_BC_ICMS
        $vBCicms= $ICMSSN202->getElementsByTagName("vBCST")->item(0);
        $vvBCicms =($vBCicms->textContent);
        $vvBCicms =number_format($vvBCicms, 2, '.','');
    
        $vRedBC=$nvProd-$vvBCicms;
    
        //Aliguota 	ALIQ_ICMS
        $pICMS= $ICMSSN202->getElementsByTagName("pICMSST")->item(0);
        $vpICMS =($pICMS->textContent);
        $vpICMS =number_format($vpICMS, 2, '.','');       
    
        // Vlr icms item VL_ICMS
        $vICMS=$ICMSSN202->getElementsByTagName("vICMSST")->item(0);
        $vvICMS =($vICMS->textContent);
        $vvICMS =number_format($vvICMS, 2, '.','');
    
        //Situação Tributária referente ao ICMS CST_ICMS
        $vCST=$ICMSSN202->getElementsByTagName("CSOSN")->item(0);
        $vvCST =($vCST->textContent);
    
    }
    if($ICMSSN201!=null){
        //icms do item VL_BC_ICMS
        $vBCicms= $ICMSSN201->getElementsByTagName("vBCST")->item(0);
        $vvBCicms =($vBCicms->textContent);
        $vvBCicms =number_format($vvBCicms, 2, '.','');
    
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
    if($ICMSSN900!=null){
         
        $vCST=$ICMSSN900->getElementsByTagName("CSOSN")->item(0);
        $vvCST =($vCST->textContent);
         
        $vpICMS =number_format("0,00", 2, ',','');//aliq icms
        $vvICMS =number_format("0,00", 2, '.','');
        $vvBCicms =number_format("0,00", 2, '.','');
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
           
            $vvBCIPI = $IPITrib->getElementsByTagName("vBC")->item(0);
            $vvpIPI = $IPITrib->getElementsByTagName("pIPI")->item(0);
            $vvIPI = $IPITrib->getElementsByTagName("vIPI")->item(0);
            if($vvIPI!=null){              
                $vBCIPI =($vvBCIPI->textContent);
                $vpIPI =($vvpIPI->textContent);
                $vIPI =($vvIPI->textContent);
            }else{
                $vIPI=0;               
                $vBCIPI =0;
                $vpIPI =0;
            }

        }elseif($IPINT!=null){
            $CSTipi = $IPINT->getElementsByTagName("CST")->item(0);
            $vCSTipi =($CSTipi->textContent);
            $vIPI=0;
            $vBCIPI =0;
            $vpIPI =0;
        }else{
            $vIPI=0;
            $vCSTipi =0;
            $vBCIPI =0;
            $vpIPI =0;
        }	    			
                            
        //	Codigo de enquadramento legal do IPICOD_ENQ
        $cEnq = $IPI->getElementsByTagName("cEnq")->item(0);
        $vcEnq =($cEnq->textContent);
    }else{
        $vIPI=0;
        $vCSTIPI =0;
        $vBCIPI =0;
        $vpIPI =0;
        $vcEnq=0;
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
        $vvBCpis =number_format($vvBCpis, 2, '.','');
        //ALIQ_PIS
        $pPIS= $PISAliq->getElementsByTagName("pPIS")->item(0);
        $vpPIS =($pPIS->textContent);
        $vpPIS =number_format($vpPIS, 2, '.','');		    			
        //VL_PIS
        $vPISitem= $PISAliq->getElementsByTagName("vPIS")->item(0);
        $vvPISitem=($vPISitem->textContent);
        $vvPISitem =number_format($vvPISitem, 2, '.','');
    }elseif($PISOutr!=null){
        $CSTpis= $PISOutr->getElementsByTagName("CST")->item(0);
        $vCSTpis =($CSTpis->textContent);
        //VL_BC_PIS
        $vBCpis= $PISOutr->getElementsByTagName("vBC")->item(0);
        $vvBCpis =($vBCpis->textContent);
        $vvBCpis =number_format($vvBCpis, 2, '.','');
        //ALIQ_PIS
        $pPIS= $PISOutr->getElementsByTagName("pPIS")->item(0);
        $vpPIS =($pPIS->textContent);
        $vpPIS =number_format($vpPIS, 2, '.','');		    			
        //VL_PIS
        $vPISitem= $PISOutr->getElementsByTagName("vPIS")->item(0);
        $vvPISitem=($vPISitem->textContent);
        $vvPISitem =number_format($vvPISitem, 2, '.','');
    }elseif($PISNT!=null){
        $CSTpis= $PISNT->getElementsByTagName("CST")->item(0);
        $vCSTpis =($CSTpis->textContent);
        $vpPIS =number_format('0,00', 2, '.','');
        $vvPISitem =number_format('0,00', 2, '.','');
        $vvBCpis =number_format('0,00', 2, '.','');
    
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
        $vvCOFINSitem =number_format('0,00', 2, '.','');
        $vpCOFINS =number_format('0,00', 2, '.','');
        $vvBCcofins =number_format('0,00', 2, '.','');
    }else{
        $COFINSNT = $COFINS->getElementsByTagName("COFINSNT")->item(0);
        $CSTcofins = $COFINSNT->getElementsByTagName("CST")->item(0);
        $vCSTcofins=($CSTcofins->textContent);	  
        $vvCOFINSitem =number_format('0,00', 2, '.','');
        $vpCOFINS =number_format('0,00', 2, '.','');
        $vvBCcofins =number_format('0,00', 2, '.','');
     
    }	
    
    #############################################################
    //Codigo do produto COD_ITEM
    $cProd = $prod->getElementsByTagName("cProd")->item(0);
    $vcProd =($cProd->textContent);	

    $xNCM = $prod->getElementsByTagName("NCM")->item(0);
    $vNCM =($xNCM->textContent);	   

    //Descrição do produto DESCR_COMPL
    $xProd = $prod->getElementsByTagName("xProd")->item(0);
    $vxProd =($xProd->textContent);	    
    $vxProd=str_replace("\""," ",$vxProd);
	$vxProd =str_replace("|"," ",$vxProd);
    $vxProd =str_replace("'","",$vxProd);
    
    //quantidade do item QTD    			
    $qCom = $prod->getElementsByTagName("qCom")->item(0);
    $vqCom =($qCom->textContent);

    $uCom = $prod->getElementsByTagName("uCom")->item(0);
    $vuCom =($uCom->textContent);

    $uTrib = $prod->getElementsByTagName("uTrib")->item(0);
    $vuTrib =($uTrib->textContent);

    $cEAN = $prod->getElementsByTagName("cEAN")->item(0);

    if($cEAN!=null){
        $vcEAN =($cEAN->textContent);
    }else{
        $vcEAN=' ';
    }    

    $CEST = $prod->getElementsByTagName("CEST")->item(0);
    if($CEST!=null){
        $vcest =($CEST->textContent);
    }else{
        $vcest=' ';
    }
                        
    			
    // valor do item VL_ITEM unitario
    $vUnCom = $prod->getElementsByTagName("vUnCom")->item(0);
    $vvUnCom =($vUnCom->textContent);

    // valor do item VL_ITEM
    $vProd = $prod->getElementsByTagName("vProd")->item(0);
    $vvProd =($vProd->textContent);
    
    //valor do desconto VL_DESC
    
    $vDesc = $prod->getElementsByTagName("vDesc")->item(0);
    $vvDesc =($vDesc->textContent);
    $vvDesc =number_format($vvDesc, 2, '.','');	
                  
    // CFOP
    $CFOP = $prod->getElementsByTagName("CFOP")->item(0);
    $vCFOP =($CFOP->textContent);
    
    //Converter o CFOP para entrada
    $vCFOP=getCfop($vCFOP,$vvCST,$row);
       
    
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
 
    //DEfini o valor do ST do IPI
     if($vCSTipi=='51'){
        $vCSTipi='01';
     }elseif($vCSTipi=='52'){
        $vCSTipi='02';
     }elseif($vCSTipi=='53'){
        $vCSTipi='03';
     }elseif($vCSTipi=='54'){
        $vCSTipi='04';
     }elseif($vCSTipi=='55'){
        $vCSTipi='05';
     }elseif($vCSTipi=='50'){
        $vCSTipi='00';
     }elseif($vCSTipi=='99'){
        $vCSTipi='49';
     }else{
        $vCSTipi='00';
     }


     
    // Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
    if($row['consumo']=="Y" or $row['imobilizado']=='Y'  or $row['comercio']=='Y'){

         $vvBCicms =number_format('0,00', 2, '.','');
         $vpICMS =number_format('0,00', 2, '.','');
         $vvICMS =number_format('0,00', 2, '.','');
         $vvBCpis=number_format('0,00', 2, '.','');
         $vpPIS =number_format('0,00', 2, '.','');
         $vvPISitem =number_format('0,00', 2, '.','');
         $vvBCcofins =number_format('0,00', 2, '.','');
         $vpCOFINS=number_format('0,00', 2, '.','');
         $vvCOFINSitem=number_format('0,00', 2, '.','');
    }
     
                    $vlrtotalitem=$vqCom*$vvUnCom;
    if(!empty($row['icmscpl'])){
         
         $vpICMS= number_format($row['icmscpl'], 2, '.','');
         $vvICMS =(($vlrtotalitem * $row['icmscpl'])/100);
         $vvICMS =number_format($vvICMS, 2, '.','');
         $vRedBC=0;
         $vvBCicms=number_format($vlrtotalitem, 2, '.','');
    }
     
    IF($vqCom<1){
        $vvUnCom=$vvProd;
        $vqCom=1.00;
    }
     
    $vqCom =number_format($vqCom, 2, '.','');
    $vvUnCom =number_format($vvUnCom, 2, '.','');
    $vlrtotalitem=number_format($vlrtotalitem, 2, '.','');

                        
    $vqTrib =number_format($vqTrib, 2, '.','');
                  

     
        $C170=$C170+1;
        //duvidas IND_MOV 1  CST_ICMS COD_NAT IND_APUR QUANT_BC_PIS ALIQ_PIS(em reais) QUANT_BC_COFINS ALIQ_COFINS(em reais) COD_CTA
        //1  |2       |3       |4          |5  |6   |7      |8      |9      |10      |11  |12     |13        |14       |15     |16           |17     |18        |19      |20     |21     |22       |23      |24    |25     |26       |27      |28          |27      |28          |29      |30    |31        |32          |33         |34       		 |35         |36       |37     |      
        //REG|NUM_ITEM|COD_ITEM|DESCR_COMPL|QTD|UNID|VL_ITEM|VL_DESC|IND_MOV|CST_ICMS|CFOP|COD_NAT|VL_BC_ICMS|ALIQ_ICMS|VL_ICMS|VL_BC_ICMS_ST|ALIQ_ST|VL_ICMS_ST|IND_APUR|CST_IPI|COD_ENQ|VL_BC_IPI|ALIQ_IPI|VL_IPI|CST_PIS|VL_BC_PIS|ALIQ_PIS|QUANT_BC_PIS|ALIQ_PIS|QUANT_BC_PIS|ALIQ_PIS|VL_PIS|CST_COFINS|VL_BC_COFINS|ALIQ_COFINS|QUANT_BC_COFINS|ALIQ_COFINS|VL_COFINS|COD_CTA|
        //                        |1   | 2                 | 3         |4          |5         | 6         |7           |8          |9|10             |11        ||13           |14         |15         ||||19|20         |21           ||||25          |26          |27        ||29            ||31             |32             |33           |||36               ||
        //$_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|".$vcProd."|".$vxProd."|".$vqCom."|".$vuTrib."|".$vvUnCom."|".$vvDesc."|0|"/*CST_ICMS*/."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."||\n";
   //     $_SESSION['C170'][$C170]="|C170|".$stritem.$nitem."|777|".$vxProd."|".$vqCom."|UN|".$vlrtotalitem."|".$vvDesc."|0|".$CST_ICMS."|".$vCFOP."||".$vvBCicms."|".$vpICMS."|".$vvICMS."||||0|".$vCSTipi."|"/*$vcEnq*/."||||".$vCSTpis."|".$vvBCpis."|".$vpPIS."||".$vvPISitem."||".$vCSTcofins."|".$vvBCcofins."|".$vpCOFINS."|||".$vvCOFINSitem."|||\n";
    //    echo($_SESSION['C170'][$C170]);
        $sq170="INSERT INTO spedc170
        (idempresa,idnf,nitem,vxprod,ncm,vqcom,uCom,uTrib,qTrib,cean,cest,vlrtotalitem,vvdesc,csticms,vcfop,
        vvbcicms,vpicms,vvicms,vcstipi,vipi,vpipi,vbcipi,vcstpis,vvbcpis,vppis,vvpisitem,vcstcofins,vvbccofins,vpcofins,vvcofinsitem,
        criadopor,criadoem,alteradopor,alteradoem)
        VALUES
        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idnf.",'".$stritem.$nitem."','".$vxProd."','".$vNCM."','".$vqCom."','".$vuCom."','".$vuTrib."','".$vqTrib."','".$vcEAN."','".$vcest."','".$vlrtotalitem."','".$vvDesc."','".$CST_ICMS."','".$vCFOP."','".$vvBCicms."','".$vpICMS."','".$vvICMS."','".$vCSTipi."','".$vIPI."','".  $vpIPI."','".$vBCIPI."','".$vCSTpis."','".$vvBCpis."','".$vpPIS."','".$vvPISitem."','".$vCSTcofins."','".$vvBCcofins."','".$vpCOFINS."','".$vvCOFINSitem."',
        '".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
      
    
        $r170=d::b()->query($sq170) or die("Erro ao inserir sped C170 sql=".$sq170); 

         				    			
    }//LOOP ITEM

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
            where x.status = 'Y' and x.idnf=".$idnf."
        ) as u 
        group by st,cfop,aliqicms";

        $resi=d::b()->query($sqli);
        $qtdi=mysqli_num_rows($resi);
        
        if($qtdi<1){
            die("Favor marcar nota ".$row['nnfe']." novamente [industria,imobilizado,consumo,comercio]!!!");
        }
        
        while($rowi=mysqli_fetch_assoc($resi)){

            
            //Converter o CFOP para entrada
           // $vCFOP=getCfop($rowi['cfop'],$rowi['st'],$row);
            
           // $rowi['cfop']=$vCFOP;
            // Não é permitido aproveitamento de crédito para material de USO e CONSUMO!
            if($row['consumo']=="Y" or $row['imobilizado']=='Y' or $row['comercio']=='Y'){
                $rowi['aliqicms']="0.00";
                $rowi['vl_bc_icms']="0.00";
                $rowi['vl_icms']="0.00";
                $rowi['vl_red_bc']="0.00";
                $rowi['vl_ipi']="0.00";
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
            $vlrRedBc =number_format($vlrRedBc, 2, '.','');		    		
            //round(, 2);
            
            if(!empty($row['icmscpl'])){
                
                $rowi['aliqicms']=$row['icmscpl'];
                $rowi['vl_bc_icms']=$rowi['vl_opr'];
                $rowi['vl_icms']=(($rowi['vl_opr'] * $row['icmscpl'])/100);
            
            }

          
            $sq190="INSERT INTO spedc190
                            (idempresa,idnf,st,cfop,aliqicms,vlopr,vlbcicms,vlicms,vlredbc,vlipi,
                            criadopor,criadoem,alteradopor,alteradoem)
                            VALUES
                            (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idnf.",'".$rowi['st']."','".$rowi['cfop']."','".number_format($rowi['aliqicms'], 2, '.','')."','".number_format($rowi['vl_opr'], 2, '.','')."','".number_format($rowi['vl_bc_icms'], 2, '.','')."','".number_format($rowi['vl_icms'], 2, '.','')."','".number_format($rowi['vl_red_bc'], 2, '.','')."','".number_format($rowi['vl_ipi'], 2, '.','')."',
                            '".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";

            $r190=d::b()->query($sq190) or die("Erro ao inserir sped C190 sql=".$sq190); 

            //$_SESSION['C190'][$C190]="|C190|".$strST."|".$vCFOP."|".$vpICMS."|".$vlrOper."|".$vvBC."|".$vvtICMS."|0,00|0,00|".$vlrRedBc."|".$vvIPI."||\n";
        //    $_SESSION['C190'][$C190]="|C190|".$rowi['st']."|".$rowi['cfop']."|".number_format($rowi['aliqicms'], 2, ',','')."|".number_format($rowi['vl_opr'], 2, ',','')."|".number_format($rowi['vl_bc_icms'], 2, ',','')."|".number_format($rowi['vl_icms'], 2, ',','')."|0,00|0,00|".number_format($rowi['vl_red_bc'], 2, ',','')."|".number_format($rowi['vl_ipi'], 2, ',','')."||\n";
          //  echo($_SESSION['C190'][$C190]);
           
        }          


}//SELECT DA NF

$idnfe =traduzid("nf","idnf","idnfe",$idnf);

    if(!empty($idnfe)){ 

			$sql="select * from nfentradaxml x  where tipo='NFE' and chave ='".$idnfe."' and idnf is not null and idnf!=".$idnf;
			$res =  d::b()->query($sql) or die("Falha ao buscar se XML NFE já foi vinculado : " . mysql_error() . "<p>SQL:". $sql);  
			$qtdb = mysqli_num_rows($res);
			if($qtdb > 0){
				$row=mysqli_fetch_assoc($res);
				die("DUPLICADO - Este NFE já foi cadastro no sistema antes., Idnf:".$row['idnf']);
			}


			$sql="select * from nfentradaxml x  where tipo='NFE' and chave ='".$idnfe."' and idnf is null";
			$res =  d::b()->query($sql) or die("Falha ao buscar XML NFE ja baixado: " . mysql_error() . "<p>SQL:". $sql);  
			$qtdb = mysqli_num_rows($res);

			if($qtdb > 0){
				$row=mysqli_fetch_assoc($res);

				$sqlU="update nfentradaxml set idnf='".$idnf."'where idnfentradaxml= ".$row['idnfentradaxml'];
				$resU =  d::b()->query($sqlU) or die("Falha ao vincular XML do NFE: " . mysql_error() . "<p>SQL:". $sqlU);  
				
				
			}
			
    }

echo('Carregado com sucesso...');
}

function getCfop($vCFOP,$vvCST,$row){

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
        }elseif(($vCFOP=='5101' or $vCFOP=='5102'  or $vCFOP=='5103') and $row['faticms']=='Y'){
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

    }

    return  $vCFOP;
}// getCfop($vCFOP,$vvCST,$row){
?>