<?
//aplicar o custo
$dataini=$_POST['dataini'];
$datafim=$_POST['datafim'];
$idunidade=$_POST['idunidade'];
$tiporateio=$_POST['tiporateio'];
$valorrateio = $_POST['valorrateio'];
$valorrateioun = $_POST['valorrateioun'];
$stidrateioitemdest=$_POST['stidrateioitemdest'];

if(!empty($dataini) and !empty($datafim) and !empty($tiporateio) and !empty($valorrateio) and !empty($valorrateioun) and !empty($stidrateioitemdest)){

    $insrateiocusto = new Insert();
    $insrateiocusto->setTable("rateiocusto");
    $insrateiocusto->idempresa=cb::idempresa();
    $insrateiocusto->datainicio=$dataini;
    $insrateiocusto->datafim=$datafim;
    $insrateiocusto->stidrateioitemdest=$stidrateioitemdest;
    $insrateiocusto->tiporateio=$tiporateio;
    $insrateiocusto->valorun=$valorrateioun;
    $insrateiocusto->valor=$valorrateio;
    $idrateiocusto=$insrateiocusto->save();

    header("IDRATEIOCUSTO: $idrateiocusto");

    $arrpb=$_SESSION["arrpostbuffer"];
    unset($_SESSION["arrpostbuffer"]);
    reset($arrpb); 
    // print_r($arrpb); die();
    $linhanova = 99999;      
    foreach($arrpb as $linha => $arrlinha) {
        foreach($arrlinha as $acao => $arracao) {
            foreach($arracao as $tab => $arrtab) {
                $linhanova++;
                $ilotecusto= new Insert();
                $ilotecusto->setTable("lotecusto");
                $ilotecusto->idempresa=cb::idempresa();
                $ilotecusto->idrateiocusto=$idrateiocusto;
                $ilotecusto->idlote=$arrtab['idlote'];
                $ilotecusto->idobjeto=$idunidade;
                $ilotecusto->tipoobjeto='unidade';
                $ilotecusto->origem='rateiocusto';
                $ilotecusto->tipo='CI';
                $ilotecusto->valor=$arrtab['vlrlotecusto'];
                $idlotecusto=$ilotecusto->save();

                $_SESSION["arrpostbuffer"][$linhanova."xy"]['u']["lote"]["idlote"] =$arrtab['idlote'];
                $_SESSION["arrpostbuffer"][$linhanova."xy"]['u']["lote"]["vlrlote"] =$arrtab['vlrlote'];
                $_SESSION["arrpostbuffer"][$linhanova."xy"]['u']["lote"]["vlrlotetotal"] =$arrtab['vlrlotetotal'];


            }
        }
    } 

    $arr= explode(",", $stidrateioitemdest);

    foreach ($arr as $key => $value) {   
    // echo "{$key} => {$value} ";
        $_SESSION["arrpostbuffer"][$key."x"]['u']["rateioitemdest"]["idrateioitemdest"] =$value;
        $_SESSION["arrpostbuffer"][$key."x"]['u']["rateioitemdest"]["idrateiocusto"] =$idrateiocusto;
        $_SESSION["arrpostbuffer"][$key."x"]['u']["rateioitemdest"]["custeado"] ='Y';
    }
}


//limpar custo
$idrateiocusto=$_SESSION['arrpostbuffer']['x1']['u']['rateiocusto']['idrateiocusto'];
if(!empty($idrateiocusto)){
    $sql="select c.idlotecusto,
                l.idlote,
                round((ifnull(l.vlrlotetotal,0)-c.valor),4) as vlrlotetotal, 
                round((ifnull(l.vlrlote,0) -(c.valor/l.qtdprod)),4) as vlrlote 
            from lotecusto c 
                join lote l on(l.idlote = c.idlote)
            where c.idrateiocusto=".$idrateiocusto;
    $res=  d::b()->query($sql) or die("Falha ao buscar lotes custo presave: <p>SQL: $sql");  
    $li=0;
    while($row=mysqli_fetch_assoc($res)){
        $li++;
        $_SESSION["arrpostbuffer"][$li."del"]['d']["lotecusto"]["idlotecusto"] =$row['idlotecusto'];

        $_SESSION["arrpostbuffer"][$li."xy"]['u']["lote"]["idlote"] =$row['idlote'];
        $_SESSION["arrpostbuffer"][$li."xy"]['u']["lote"]["vlrlote"] =$row['vlrlote'];
        $_SESSION["arrpostbuffer"][$li."xy"]['u']["lote"]["vlrlotetotal"] =$row['vlrlotetotal'];
    }

    $sql="select * from rateioitemdest  where idrateiocusto=".$idrateiocusto;
    $res=  d::b()->query($sql) or die("Falha ao buscar rateio item destino custo presave: <p>SQL: $sql");  
 
    while($row=mysqli_fetch_assoc($res)){
        $li++;
        $_SESSION["arrpostbuffer"][$li."up"]['u']["rateioitemdest"]["idrateioitemdest"] =$row['idrateioitemdest'];
        $_SESSION["arrpostbuffer"][$li."up"]['u']["rateioitemdest"]["idrateiocusto"] = null;
        $_SESSION["arrpostbuffer"][$li."up"]['u']["rateioitemdest"]["custeado"] = 'N';
    }
    

}

    montatabdef();

?>