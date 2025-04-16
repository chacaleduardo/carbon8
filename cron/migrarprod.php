<?

require_once("../inc/php/functions.php");
// estes ids ja devem estar duplicados
$strids='38036,38263,38047,38046,37970,38039,37890,38045,38044,37925,38330,38336,37939,37892,38027,37972,37973,37987,37880,37975,37979,37980,37981,38385,38381,38338,38392,38378,38386,
38377,38024,38339,37883,38383,38077,38286,37932,38028,38030,38079,37945,37940,37947,37949,38053,38252,38331,38332,38254,38280,38281,38136,38137,38344,38037,38346,
38326,38025,38408,38382,37959,38258,38038,38021,38026,38407,38409,38080,38081,39475,38347,38154,38349,37924,37961,38040,37965,37993,38316,38259,38329,38022,38007,38126,37938,
38645,37891,38033,38041,38048,38035,38029,38008,38013,38156,38042,38014,38247,38017,38018,38131,37923,37984,38321,38322,38323,38324,38132,38139,38315,38270,38091';

//$strids='38081';

$aridprodserv = explode(",", $strids);



foreach($aridprodserv as $idprodserv){ 

    $idprodduplicado= getidduplicado($idprodserv);
    // se o pai não estiver vazio duplicar 
    if(empty($idprodduplicado)){
        duplicar($idprodserv);
    }
    verificapai($idprodserv);//38985
}

function verificapai($idprodserv){

    

    // buscar as formulas relacionada e o idprodservformulains do prodserv a ser substituido ou não
    $sqlpai="select f.idprodserv as idprodservpai,i.idprodservformulains from prodservformulains i join prodservformula f on(f.idprodservformula =i.idprodservformula and f.status!='INATIVO') where i.idprodserv= ".$idprodserv." and i.status='ATIVO'";//38255	76351
    $respai= d::b()->query($sqlpai);
    echo($sqlpai."\n");
    while($rpai=mysqli_fetch_assoc($respai)){
        //varifica se o pai e biofabrica
        $sqlbf="select * from prodservformulaitem where idprodservformula in(9665,10244) and idprodserv=".$rpai['idprodservpai'];
        $resbf= d::b()->query($sqlbf);
        $eBF=mysqli_num_rows($resbf);
    
         //varifica se o pai esta a ligado a outro produto não biofabrica
        $sqlNbf="select * from prodservformulaitem where idprodservformula in(9176,10239) and idprodserv=".$rpai['idprodservpai'];
        $resNbf= d::b()->query($sqlNbf);
        $NBF=mysqli_num_rows($resNbf);
    
        if($eBF > 0 and $NBF > 0){//duplicar 
            echo("Vai duplicar uma formula"."\n");
            verificaouduplica($rpai['idprodservpai']);            
        }elseif($eBF > 0 and $NBF == 0){
            $idprodduplicado= getidduplicado($idprodserv);
            // se o pai não estiver vazio duplicar 
            if(!empty($idprodduplicado)){
                atualizainsumoform($rpai['idprodservformulains'],$idprodduplicado);
            }
        }
            verificapai($rpai['idprodservpai']);
      
    }

  

}

function  atualizainsumoform($idprodservformulains,$idprodduplicado){

    $sqlup="update prodservformulains set idprodserv=".$idprodduplicado.",alteradopor='794169',alteradoem=now() where idprodservformulains =".$idprodservformulains;
    d::b()->query($sqlup);
    echo($sqlup."\n");
}

// pega o produto duplicado
function getidduplicado($idprodserv){
    $sql1="select * from prodserv where idprodservorigem=".$idprodserv;
    $res1= d::b()->query($sql1);
    $row1 =mysql_fetch_assoc($res1);
    return $row1['idprodserv'];
}

function verificaouduplica($idprodservpai){
   $idprodduplicado= getidduplicado($idprodservpai);

   // se o pai não estiver vazio duplicar 
    if(empty($idprodduplicado)){
        $idprodduplicado  =  duplicar($idprodservpai);
        verificapai($idprodservpai);
    }//else{
        //atualizainsumos($idprodservpai);
    //}

    /*
    rodar no final

    select i.idprodserv,pi.idprodserv 
                        from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv and p.idprodservorigem is not null )
                        join prodservformulains i on(i.idproservformula =f.idprodservformula)
                        join prodserv pi on(pi.idprodservorigem=i.idprodserv)
                        where f.criadopor='794169'
    */


}
/*
function atualizainsumos($idprodservpai){
    $sqlu=" update  
                         prodservformula f
                        join prodservformulains i on(i.idproservformula =f.idprodservformula)
                        join prodserv pi on(pi.idprodservorigem=i.idprodserv)
                        set i.idprodserv= pi.idprodserv,i.alteradopor='794169',i.alteradoem=now()
                        where f.idprodser=".$idprodservpai;
        d::b()->query($sqlu);
}
*/
function  duplicar($idprodserv){

    $idempresaAMigrar=15;
    $idevento = 794169;
    echo '<pre>';
    if(!empty($idprodserv)){
        //Busca os Produtos a serem duplicados
        $sqlProdserv = "SELECT idprodserv, idtipoprodserv, concat(codprodserv,'-BF') as  codprodserv, tipo, especial, descrtipo, biobox, insumo, material, venda, geraagente, comissionado, comissao, comissaogest,
                              fabricado, processado, visualizacliente, comprado, finalidade, validade, validadeforn, alertavenc, descr, descrgenerica, descrcurta, conferencia,
                              conferenciares, local, ncm, cest, ipi, ncfop, reducaobc, idmedida, un, unconv, unvolume, valconv, uncom, uncptransf, status, volumeprod, qtdpadrao,
                              qtdpadrao_exp, potencia, vlrvenda, vlrcompra, margem, conteudo, idunidadeest, unest, idunidadealerta, consometransf, qtdest, qtdest_exp,
                              estideal, estmax, armazanagem, formafarm, certanalise, infprod, origem, obs, pis, iss, cofins, modbc, cst, ipint,
                              piscst, confinscst, tipoespecial, tipoformalizacao, tiporelatorio, geralegenda, logoinmetro, titulotextopadrao, textopadrao, textoinclusaores, 
                              textointerpretacao, prazoexec, idtipoteste, tipogmt, tipobact, geragraf, geracalc, assinatura, tipocertanalise, idportaria, relatoriopositivo,
                              oficial, notoficial, sif, geraincubacao, modopart, jarvore, jarvorehash, ordenacao, alertaem, justificativa, modelo, modo, comparativodelotes,
                              jsonconfig, permiteformatacao, prioridadecompra, taguiavel, alertarotulo, alertarotuloy, alertarotulon, licenca, temporeposicao, estoqueseguranca,
                              pedido, tempoconsrateio, consumodias, consumodiasgraf, consumodiaslote, mediadiaria,
                              unbkp, imobilizado, idtagtipo, nfe
                         FROM prodserv p
                        WHERE idprodserv in ($idprodserv);";
        $resProdserv = d::b()->query($sqlProdserv);
        if(mysqli_error(d::b()))
            print_r("Erro ao Buscar prodserv: ".mysqli_error(d::b())."\n"); 
    
        print_r($sqlProdserv);
        echo '<br />';
        while($rowobj = mysqli_fetch_assoc($resProdserv)){
            //Insere Prodserv
            $jsonconfig = empty($rowobj['jsonconfig']) ? '{}' : $rowobj['jsonconfig'];
            $jarvore = empty($rowobj['jarvore']) ? '{}' : $rowobj['jarvore'];
            $comissao = empty($rowobj['comissao']) ? '0.00' : $rowobj['comissao'];
            $comissaogest = empty($rowobj['comissaogest']) ? '0.00' : $rowobj['comissaogest'];
            $valconv = empty($rowobj['valconv']) ? '0.00' : $rowobj['valconv']; 
            $alertavenc = empty($rowobj['alertavenc']) ? '0.00' : $rowobj['alertavenc']; 
            $margem = empty($rowobj['margem']) ? '0.00' : $rowobj['margem']; 
            $idmedida = empty($rowobj['idmedida']) ? '0.00' : $rowobj['idmedida']; 
            $idtipoteste = empty($rowobj['idtipoteste']) ? 0 : $rowobj['idtipoteste']; 
            $idportaria = empty($rowobj['idportaria']) ? 0 : $rowobj['idportaria']; 
            $prazoexec = empty($rowobj['prazoexec']) ? 0 : $rowobj['prazoexec']; 
            $ordenacao = empty($rowobj['ordenacao']) ? 0 : $rowobj['ordenacao']; 
            $idtagtipo = empty($rowobj['idtagtipo']) ? 0 : $rowobj['idtagtipo']; 
           
            $sql = "INSERT INTO prodserv (idempresa, idtipoprodserv, codprodserv, tipo, especial, descrtipo, biobox, insumo, material, venda, geraagente, comissionado, comissao, 
                                          comissaogest, fabricado, processado, visualizacliente, comprado, finalidade, validade, validadeforn, alertavenc, descr, descrgenerica, 
                                          descrcurta, conferencia, conferenciares, local, ncm, cest, ipi, ncfop, reducaobc, idmedida, un, unconv, unvolume, valconv, uncom, 
                                          uncptransf, status, volumeprod, qtdpadrao, qtdpadrao_exp, potencia, vlrvenda, vlrcompra, margem, conteudo, idunidadeest, unest, 
                                          idunidadealerta, consometransf, qtdest, qtdest_exp, estmin, estmin_exp, estminautomatico, estideal, estmax, armazanagem, formafarm, 
                                          certanalise, infprod, origem, obs, qtd, pis, iss, cofins, modbc, cst, ipint, piscst, confinscst, tipoespecial, tipoformalizacao, 
                                          tiporelatorio, geralegenda, logoinmetro, titulotextopadrao, textopadrao, textoinclusaores, textointerpretacao, prazoexec, 
                                          idtipoteste, tipogmt, tipobact, geragraf, geracalc, assinatura, tipocertanalise, idportaria, relatoriopositivo, oficial, 
                                          notoficial, sif, geraincubacao, modopart, jarvore, jarvorehash, ordenacao, alertaem, justificativa, modelo, modo, comparativodelotes,
                                          jsonconfig, permiteformatacao, prioridadecompra, taguiavel, alertarotulo, alertarotuloy, alertarotulon, licenca, temporeposicao, 
                                          estoqueseguranca, pedido, pedidoautomatico, tempocompra, pedido_automatico, tempoconsrateio, destoque, consumodias, consumodiasgraf, 
                                          consumodiaslote, unbkp, imobilizado, idtagtipo, nfe, criadopor, criadoem, 
                                          alteradopor, alteradoem,idprodservorigem)
                                   VALUES ($idempresaAMigrar, ".$rowobj['idtipoprodserv'].", '".$rowobj['codprodserv']."', '".$rowobj['tipo']."', '".$rowobj['especial']."', '".$rowobj['descrtipo']."', '".$rowobj['biobox']."', '".$rowobj['insumo']."', '".$rowobj['material']."', '".$rowobj['venda']."', '".$rowobj['geraagente']."', '".$rowobj['comissionado']."', '".$comissao."', 
                                        '".$comissaogest."', '".$rowobj['fabricado']."', '".$rowobj['processado']."', '".$rowobj['visualizacliente']."', '".$rowobj['comprado']."', '".$rowobj['finalidade']."', '".$rowobj['validade']."', '".$rowobj['validadeforn']."', '$alertavenc', '".$rowobj['descr']."', '".$rowobj['descrgenerica']."', 
                                        '".$rowobj['descrcurta']."', '".$rowobj['conferencia']."', '".$rowobj['conferenciares']."', '".$rowobj['local']."', '".$rowobj['ncm']."', '".$rowobj['cest']."', '".$rowobj['ipi']."', '".$rowobj['ncfop']."', '".$rowobj['reducaobc']."', '$idmedida', '".$rowobj['un']."', '".$rowobj['unconv']."', '".$rowobj['unvolume']."', '$valconv', '".$rowobj['uncom']."', 
                                        '".$rowobj['uncptransf']."', '".$rowobj['status']."', '".$rowobj['volumeprod']."', '".$rowobj['qtdpadrao']."', '".$rowobj['qtdpadrao_exp']."', '".$rowobj['potencia']."', '".$rowobj['vlrvenda']."', '".$rowobj['vlrcompra']."', '$margem', '".$rowobj['conteudo']."', '".$rowobj['idunidadeest']."', '".$rowobj['unest']."', 
                                        '".$rowobj['idunidadealerta']."', '".$rowobj['consometransf']."', '".$rowobj['qtdest']."', '".$rowobj['qtdest_exp']."', 0, '', 0, '".$rowobj['estideal']."', '".$rowobj['estmax']."', '".$rowobj['armazanagem']."', '".$rowobj['formafarm']."', 
                                        '".$rowobj['certanalise']."', '".$rowobj['infprod']."', '".$rowobj['origem']."', '".$rowobj['obs']."', 0, '".$rowobj['pis']."', '".$rowobj['iss']."', '".$rowobj['cofins']."', '".$rowobj['modbc']."', '".$rowobj['cst']."', '".$rowobj['ipint']."', '".$rowobj['piscst']."', '".$rowobj['confinscst']."', '".$rowobj['tipoespecial']."', '".$rowobj['tipoformalizacao']."', 
                                        '".$rowobj['tiporelatorio']."', '".$rowobj['geralegenda']."', '".$rowobj['logoinmetro']."', '".$rowobj['titulotextopadrao']."', '".$rowobj['textopadrao']."', '".$rowobj['textoinclusaores']."', '".$rowobj['textointerpretacao']."', $prazoexec, 
                                        $idtipoteste, '".$rowobj['tipogmt']."', '".$rowobj['tipobact']."', '".$rowobj['geragraf']."', '".$rowobj['geracalc']."', '".$rowobj['assinatura']."', '".$rowobj['tipocertanalise']."', $idportaria, '".$rowobj['relatoriopositivo']."', '".$rowobj['oficial']."', 
                                        '".$rowobj['notoficial']."', '".$rowobj['sif']."', '".$rowobj['geraincubacao']."', '".$rowobj['modopart']."', '$jarvore', '".$rowobj['jarvorehash']."', $ordenacao, '".$rowobj['alertaem']."', '".$rowobj['justificativa']."', '".$rowobj['modelo']."', '".$rowobj['modo']."', '".$rowobj['comparativodelotes']."',
                                        '$jsonconfig', '".$rowobj['permiteformatacao']."', '".$rowobj['prioridadecompra']."', '".$rowobj['taguiavel']."', '".$rowobj['alertarotulo']."', '".$rowobj['alertarotuloy']."', '".$rowobj['alertarotulon']."', '".$rowobj['licenca']."', '".$rowobj['temporeposicao']."', 
                                        '".$rowobj['estoqueseguranca']."', '".$rowobj['pedido']."', 0, 0, 0, '".$rowobj['tempoconsrateio']."', 0, '".$rowobj['consumodias']."', '".$rowobj['consumodiasgraf']."', 
                                        '".$rowobj['consumodiaslote']."', '".$rowobj['unbkp']."', '".$rowobj['imobilizado']."', $idtagtipo, '".$rowobj['nfe']."', 'evento_$idevento', now(), 'evento_$idevento', now(),".$idprodserv.")";
            d::b()->query($sql);
            if(mysqli_error(d::b()))
                print_r("Erro ao Criar Prodserv: Erro: ".mysqli_error(d::b())."\n");
        
            print_r($sql);
            echo '<br>';
            // Recupera o Último ID inserido
            $idNewProdserv = mysqli_insert_id(d::b());
    
            if(!empty($idNewProdserv)){
                //Insere os Fornecedores relacionados
                $sqlProdservForn = "SELECT * FROM prodservforn WHERE idprodserv = ".$rowobj['idprodserv'];
                $resProdservForn = d::b()->query($sqlProdservForn);
                if(mysqli_error(d::b()))
                    print_r("Erro ao Buscar prodservforn: ".mysqli_error(d::b())."\n");   
                
                print_r($sqlProdservForn);
                echo '<br>';
            
                while($rowProdservForn = mysqli_fetch_assoc($resProdservForn)){
                    $qtd = empty($rowProdservForn['qtd']) ? '0.00' : $rowProdservForn['qtd']; 
                    $validadoem = empty($rowProdservForn['validadoem']) ? '000-00-00' : $rowProdservForn['validadoem']; 
                    $idprodservformula = empty($rowProdservForn['idprodservformula']) ? 0 : $rowProdservForn['idprodservformula']; 
                    $valor = empty($rowProdservForn['valor']) ? '0.00' : $rowProdservForn['valor']; 
                    $valconv = empty($rowProdservForn['valconv']) ? '0.00' : $rowProdservForn['valconv']; 
                    $reducao = empty($rowProdservForn['reducao']) ? '0.00' : $rowProdservForn['reducao']; 
                    $idprodservori = empty($rowProdservForn['idprodservori']) ? 0 : $rowProdservForn['idprodservori']; 
    
                    $q1 = "INSERT INTO prodservforn (idempresa, idprodservformula, idprodserv, idpessoa, obs, codforn, unforn, status, validadopor, validadoem, qtd, valido, valor, converteest, valconv, reducao, obsbkp, idprodservori, multiempresa, criadopor, criadoem, alteradopor, alteradoem) 
                                VALUES ($idempresaAMigrar, $idprodservformula, '$idNewProdserv', ".$rowProdservForn['idpessoa'].", '".$rowProdservForn['obs']."', '".$rowProdservForn['codforn']."',
                                        '".$rowProdservForn['unforn']."', '".$rowProdservForn['status']."', '".$rowProdservForn['validadopor']."', '$validadoem',  '$qtd',  '".$rowProdservForn['valido']."',  
                                        '$valor', '".$rowProdservForn['converteest']."',  '$valconv',  '$reducao',  '".$rowProdservForn['obsbkp']."',  $idprodservori,  '".$rowProdservForn['multiempresa']."', 
                                        'evento_$idevento', now(), 'evento_$idevento', now())";
                    d::b()->query($q1);
                    if(mysqli_error(d::b()))
                        print_r("Erro ao Criar prodservforn: Erro: ".mysqli_error(d::b())."\n");
    
                    print_r($q1);
                    echo '<br>';
                    $idnew = mysqli_insert_id(d::b());
                }
    
                //Insere os Conta Item relacionados
                $sqlProdservContaItem = "SELECT * FROM prodservcontaitem WHERE idprodserv = ".$rowobj['idprodserv'];
                $resProdservContaItem = d::b()->query($sqlProdservContaItem);
                if(mysqli_error(d::b()))
                    print_r("Erro ao Buscar prodservforn: ".mysqli_error(d::b())."\n");   
    
                print_r($sqlProdservContaItem);
                echo '<br>';
                
                while($rowProdservContaItem = mysqli_fetch_assoc($resProdservContaItem)){
                    $q2 = "INSERT INTO prodservcontaitem (idempresa, idprodserv, idcontaitem, status, criadopor, criadoem, alteradopor, alteradoem) 
                                VALUES ($idempresaAMigrar, '$idNewProdserv', ".$rowProdservContaItem['idcontaitem'].", '".$rowProdservContaItem['status']."', 'evento_$idevento', now(), 'evento_$idevento', now())";
                    d::b()->query($q2);
                    if(mysqli_error(d::b()))
                        print_r("Erro ao Criar prodservcontaitem: Erro: ".mysqli_error(d::b())."\n");
    
                    print_r($q2);
                    echo '<br>';
                    $idnew = mysqli_insert_id(d::b());
                }


                $sqlf="select idprodservformula from prodservformula where status!= 'INATIVO' and idprodserv =".$idprodserv;
                $resf=d::b()->query($sqlf);
                while($rowf=mysqli_fetch_assoc($resf)){
                     $sqli="insert prodservformula 
                    (idempresa,idprodserv,idfluxostatus,rotulo,dose,cor,qtdpadraof,qtdpadraof_exp,volumeformula,volumeformula_exp,un,idplantel,especie,vlrvenda,
                    vlrcusto,comissao,idunidadeest,idunidadealerta,status,ordem,estmin,estmin_exp,estminautomatico,pedido,
                    pedidoautomatico,temporeposicao,estoqueseguranca,tempocompra,pedido_automatico,tempoconsrateio,destoque,
                    consumodias,consumodiasgraf,consumodiaslote,mediadiaria,sugestaocompra2,ultimoorcamento,qtdest,qtdest_exp,
                    atualizaarvore,versao,editar,justificativa,criadopor,criadoem,alteradopor,alteradoem)
                    (
                    select 
                    idempresa,".$idNewProdserv.",idfluxostatus,rotulo,dose,cor,qtdpadraof,qtdpadraof_exp,volumeformula,volumeformula_exp,un,idplantel,especie,vlrvenda,
                    vlrcusto,comissao,idunidadeest,idunidadealerta,status,ordem,estmin,estmin_exp,estminautomatico,pedido,
                    pedidoautomatico,temporeposicao,estoqueseguranca,tempocompra,pedido_automatico,tempoconsrateio,destoque,
                    consumodias,consumodiasgraf,consumodiaslote,mediadiaria,sugestaocompra2,ultimoorcamento,qtdest,qtdest_exp,
                    atualizaarvore,versao,editar,justificativa,'evento_".$idevento."',now(),'evento_".$idevento."',now()
                    from prodservformula where  idprodservformula = ".$rowf['idprodservformula'].")";
                    $resf=d::b()->query($sqli);
                    $idnewProservformula = mysqli_insert_id(d::b());


                    $sqlin="insert prodservformulains (idempresa,idprodservformula,idprodserv,qtdi,listares,ord,status,criadopor,criadoem,alteradopor,alteradoem)
                        (
                        select i.idempresa,".$idnewProservformula.",ifnull(p.idprodserv,i.idprodserv),i.qtdi,i.listares,i.ord,i.status,'evento_794169',now(),'evento_794169',now()
                        from prodservformulains i left join prodserv p on(p.idprodservorigem = i.idprodserv)
                        where i.idprodservformula = ".$rowf['idprodservformula']." and i.status='ATIVO')";
                    d::b()->query($sqlin);
                }


                return $idNewProdserv;
            }
            echo "// ----------------------------------------------------------------------------------------------------------------------------";
        }
    
        // FIM - Clona Prodserv
        // ----------------------------------------------------------------------------------------------------------------------------
        echo '</pre>';
    }












    //insert prodserv
    return  $idprodserv;
}

?>