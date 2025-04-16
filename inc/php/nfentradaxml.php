<?
$idnfentradaxml=($_GET['idnfentradaxml']);


$sql="SELECT c.idmatriz,x.* from nfentradaxml x 
        left join matrizconf c on (c.idempresa=x.idempresa and c.matrizfilial='Y') 
        where idnfentradaxml=".$idnfentradaxml;
$res=d::b()->query($sql) or die("Erro ao buscar XML sql=".$sql);
$qtdr=mysqli_num_rows($res);
$row=mysqli_fetch_assoc($res);

if($qtdr<1){
    mostraerro("FALHA","Não encontrado XML para leitura");
    die;
}
if($_acao=='u'){    
        
        $sqlu="update nfentradaxml set idnf=".$_1_u_nf_idnf." where idnfentradaxml=".$idnfentradaxml;
        $res=d::b()->query($sqlu) or die("Erro ao atualizar  nfentradaxml sql=".$sqlu);

        $sqlu="update nf n join nfentradaxml x on(x.idnf=n.idnf) set n.xmlret=x.xml,envionfe = 'CONCLUIDA' where n.idnf=".$_1_u_nf_idnf." and x.idnfentradaxml=".$idnfentradaxml;
        $res=d::b()->query($sqlu) or die("Erro ao atualizar  nota sql=".$sqlu);


}else{

    
    //ser for NFE
    if($row['tipo']=='NFE'){

        // passar string para UTF-8
        $xml=$row['xml'];

        //Carregar o XML em UTF-8      			
        $doc = DOMDocument::loadXML($xml);	

        //inicia lendo as principais tags do xml da nfe
        $nfeProc = $doc->getElementsByTagName("nfeProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>	
        $NFe = $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
        $infNFe = $NFe->getElementsByTagName("infNFe")->item(0); 
        $ide = $infNFe->getElementsByTagName("ide")->item(0); 
        //BUSCAR A CHAVE DA NFE
        $chave= $infNFe->getAttribute("Id");	      		
        $vchNFe=substr($chave,3);
    
        $dest = $infNFe->getElementsByTagName("emit")->item(0);     
                            
        //COD_PART 
        $CNPJ = $dest->getElementsByTagName("CNPJ")->item(0); 		
        $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 

        if(empty($CNPJ) or empty($vCNPJ)){
            //COD_PART 
            $CNPJ = $dest->getElementsByTagName("CPF")->item(0); 			    
            $vCNPJ =($CNPJ->textContent); //pegar o valor da tag <CNPJ> 
        }

        $nNFe = $ide->getElementsByTagName("nNF")->item(0);
        $vnNFe =($nNFe->textContent);

        //data emissao
        $dEmi = $ide->getElementsByTagName("dhEmi")->item(0);
        $vdEmi =($dEmi->textContent); //pegar o valor da tag <chNFe> 

        //$timestamp = strtotime($vdEmi);
        //$newDate = date("Y-m-d H:i:s", $timestamp );

        $dhEmilocal = new \DateTime($vdEmi);
        $newDate = $dhEmilocal->format('d/m/Y H:i:s');
    

        if(empty($row['idmatriz'])){
            $_1_u_nf_idempresa=$row['idempresa'];
        }else{
            $_1_u_nf_idempresa=$row['idmatriz'];
            $_1_u_nf_idempresafat=$row['idempresa'];
        }               


        $_1_u_nf_tiponf='C';
        $_1_u_nf_nnfe=$vnNFe;
        $_1_u_nf_idnfe=$vchNFe;
        $_1_u_nf_dtemissao= $newDate;

        if($vCNPJ){
            $sqlp="select * from pessoa where cpfcnpj ='".$vCNPJ."' and status ='ATIVO' LIMIT 1";
            $resp=d::b()->query($sqlp) or die("Erro ao buscar XML sql=".$sqlp);
            $rowp=mysqli_fetch_assoc($resp);
            $_1_u_nf_idpessoa= $rowp['idpessoa'];
        }
        
    }else{// e um CTE

        $xml=($row['xml']);

        //Carregar o XML em UTF-8
        $doc = DOMDocument::loadXML($xml);
        //inicia lendo as principais tags do xml da nfe
        $cteProc = $doc->getElementsByTagName("cteProc")->item(0); // pegar a primeira ocorrencia da tag <nfeProc>
        $CTe = $cteProc->getElementsByTagName("CTe")->item(0); 
        
        $infCte = $CTe->getElementsByTagName("infCte")->item(0);
        
    
        
        $ide = $infCte->getElementsByTagName("ide")->item(0);
        
        $nCT = $ide->getElementsByTagName("nCT")->item(0);
        $vnCT =($nCT->textContent);

        if(empty($row['idmatriz'])){
            $_1_u_nf_idempresa=$row['idempresa'];
        }else{
            $_1_u_nf_idempresa=$row['idmatriz'];
            $_1_u_nf_idempresafat=$row['idempresa'];
        }               


        $_1_u_nf_tiponf='T';
        $_1_u_nf_status='PREVISAO';
        $_1_u_nf_nnfe=$vnCT;
        $_1_u_nf_idnfe=$row['chave'];

        ?>
  <input name="_1_<?= $_acao ?>_nf_xmlret" type="hidden" value='<?=str_replace('&', '&amp;',$row['xml'])?>'>
  <input name="_1_<?= $_acao ?>_nf_envionfe" type="hidden" value="CONCLUIDA">
<?
        $_1_u_nf_dtemissao= dmahms($row['dtemissao']);

        if($row['cpfcnpj']){
            $sqlp="select * from pessoa where cpfcnpj ='".$row['cpfcnpj']."' and status ='ATIVO' LIMIT 1";
            $resp=d::b()->query($sqlp) or die("Erro ao buscar XML sql=".$sqlp);
            $rowp=mysqli_fetch_assoc($resp);
            $_1_u_nf_idpessoa= $rowp['idpessoa'];
        }


}
}



?>