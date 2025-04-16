<?
//print_r($_SESSION['arrpostbuffer']); die;
$idnfitem=$_SESSION['arrpostbuffer']['com']['u']['nfitem']['idnfitem'];
if(!empty($idnfitem)){

    $idnf=traduzid("nfitem","idnfitem","idnf",$idnfitem);

    if(!empty($idnf)){

        $sql="select i.idnfitem 
                from nfitem i join prodserv p on(p.idprodserv=i.idprodserv and p.comissionado ='Y')
                where i.nfe = 'Y'
                and i.idnfitem !=".$idnfitem."
                and i.idnf =".$idnf; 
        
          
        $res = d::b()->query($sql) or die("saveprechange_nfitemcomissao: Falha ao recuperar nfitemcomissao:\n".mysqli_error(d::b())."\n".$sql);
       
        while($r = mysqli_fetch_assoc($res)){

            $sqld="delete from nfitemcomissao where idnfitem=".$r['idnfitem'];    
            $resd = d::b()->query($sqld) or die("saveprechange_nfitemcomissao: Falha ao excluir nfitemcomissao:\n".mysqli_error(d::b())."\n".$sqld);

            $sqli="select idnfitem,idpessoa,idobjeto,tipoobjeto,pcomissao from nfitemcomissao where idnfitem=".$idnfitem; 
            $resi= d::b()->query($sqli) or die("saveprechange_nfitemcomissao: Falha ao recuperar nfitemcomissao:\n".mysqli_error(d::b())."\n".$sqli);
           
            while($ri = mysqli_fetch_assoc($resi)){
               
            
                $insnfconfpagar = new Insert();
                $insnfconfpagar->setTable("nfitemcomissao");
                $insnfconfpagar->idnfitem=$r['idnfitem'];   
                $insnfconfpagar->idpessoa=$ri['idpessoa'];               
                $insnfconfpagar->pcomissao=$ri['pcomissao'];  
                $idnfconfpagar=$insnfconfpagar->save();
            }

        }

    }

}

$idnfitem=$_SESSION['arrpostbuffer']['com']['u']['notafiscalitens']['idnotafiscalitens'];
if(!empty($idnfitem)){

    $idnf=traduzid("notafiscalitens","idnotafiscalitens","idnotafiscal",$idnfitem);

    if(!empty($idnf)){

        $sql="select i.idnotafiscalitens
                from notafiscalitens i 
                where i.idnotafiscalitens !=".$idnfitem."
                and i.idnotafiscal =".$idnf; 
        
          
        $res = d::b()->query($sql) or die("saveprechange_nfitemcomissao: Falha ao recuperar nfitemscomissao:\n".mysqli_error(d::b())."\n".$sql);
       
        while($r = mysqli_fetch_assoc($res)){

            $sqld="delete from notafiscalitenscomissao where idnotafiscalitens=".$r['idnotafiscalitens'];    
            $resd = d::b()->query($sqld) or die("saveprechange_nfitemcomissao: Falha ao excluir nfitemscomissao:\n".mysqli_error(d::b())."\n".$sqld);

            $sqli="select idnotafiscalitens,idpessoa,idobjeto,tipoobjeto,pcomissao from notafiscalitenscomissao where idnotafiscalitens=".$idnfitem; 
            $resi= d::b()->query($sqli) or die("saveprechange_nfitemcomissao: Falha ao recuperar nfitemcomissao:\n".mysqli_error(d::b())."\n".$sqli);
           
            while($ri = mysqli_fetch_assoc($resi)){
               
            
                $insnfconfpagar = new Insert();
                $insnfconfpagar->setTable("notafiscalitenscomissao");
                $insnfconfpagar->idnotafiscalitens=$r['idnotafiscalitens'];   
                $insnfconfpagar->idpessoa=$ri['idpessoa'];               
                $insnfconfpagar->pcomissao=$ri['pcomissao'];  
                $idnfconfpagar=$insnfconfpagar->save();
            }

        }

    }

}


//select * from nfitemcomissao c where c.idnfitem = 399627
?>