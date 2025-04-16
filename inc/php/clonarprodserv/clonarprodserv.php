<?
set_time_limit(0);

require_once("../../php/functions.php");

$idprodserv = $_GET['idprodserv'];
$idempresaAMigrar = $_GET['idempresa'];
$idevento = $_GET['idevento'];
echo '<pre>';
if(!empty($idprodserv) && !empty($idempresaAMigrar)){
    //Busca os Produtos a serem duplicados
    $sqlProdserv = "SELECT idprodserv, idtipoprodserv, codprodserv, tipo, especial, descrtipo, biobox, insumo, material, venda, geraagente, comissionado, comissao, comissaogest,
                          fabricado, processado, visualizacliente, comprado, finalidade, validade, validadeforn, alertavenc, descr, descrgenerica, descrcurta, conferencia,
                          conferenciares, local, ncm, cest, ipi, ncfop, reducaobc, idmedida, un, unconv, unvolume, valconv, uncom, uncptransf, status, volumeprod, qtdpadrao,
                          qtdpadrao_exp, potencia, vlrvenda, vlrcompra, margem, conteudo, 
                          (SELECT u.idunidade as idunidadeest FROM unidade u WHERE u.idtipounidade = 3 and u.idcentrocusto = 67 AND u.status = 'ATIVO' AND idempresa = $idempresaAMigrar LIMIT 1) as idunidadeest, unest,
                          (SELECT u2.idunidade as idunidadealerta FROM unidade u2 WHERE u2.idtipounidade = 19 and u2.idcentrocusto = 64 AND u2.status = 'ATIVO' AND idempresa = $idempresaAMigrar LIMIT 1) as idunidadealerta,
                          consometransf, qtdest, qtdest_exp,
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
                                      alteradopor, alteradoem)
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
                                    '".$rowobj['consumodiaslote']."', '".$rowobj['unbkp']."', '".$rowobj['imobilizado']."', $idtagtipo, '".$rowobj['nfe']."', 'evento_$idevento', now(), 'evento_$idevento', now())";
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
        }
        echo "// ----------------------------------------------------------------------------------------------------------------------------";
    }

    // FIM - Clona Prodserv
    // ----------------------------------------------------------------------------------------------------------------------------
    echo '</pre>';
}else{
    header("HTTP/1.1 500 Parâmetros inválidos");
	die("Parâmetros Inválidos");
}


?>