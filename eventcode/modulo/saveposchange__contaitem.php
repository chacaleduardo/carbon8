<?

$_idtipoprodserv = $_SESSION["arrpostbuffer"]["1"]["i"]["contaitemtipoprodserv"]["idtipoprodserv"];
$_idcontaitem = $_SESSION["arrpostbuffer"]["1"]["i"]["contaitemtipoprodserv"]["idcontaitem"];

if(!empty($_idtipoprodserv) and !empty($_idcontaitem)){


    $sql="update prodserv p  
        join prodservcontaitem pc on(pc.idprodserv=p.idprodserv and pc.status='ATIVO')
        Join contaitemtipoprodserv ct on(ct.idtipoprodserv=p.idtipoprodserv)
        set pc.idcontaitem = ct.idcontaitem
        where ct.idcontaitem != pc.idcontaitem
        and p.idtipoprodserv=".$_idtipoprodserv ;
        
    $resh=d::b()->query($sql) or die("Comportamento não esperado ao atualizar o cadastro de produto : " . mysqli_error(d::b()) . "<p>SQL:".$sql);


    $sql="update nf n 
            join nfitem i on(i.idnf=n.idnf and i.nfe='Y')
            join contaitemtipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv and p.idtipoprodserv=".$_idtipoprodserv.")
            set i.idcontaitem=p.idcontaitem
            where n.dtemissao > '2021-12-31 23:59:00'    
            and i.idcontaitem!=p.idcontaitem";

    $resh=d::b()->query($sql) or die("Comportamento não esperado ao atualizar o itens por tipo : " . mysqli_error(d::b()) . "<p>SQL:".$sql);
}


?>