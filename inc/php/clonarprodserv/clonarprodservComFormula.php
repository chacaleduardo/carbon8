<?
set_time_limit(0);

require_once("../../php/functions.php");
require_once(__DIR__ . "/../../../form/controllers/formulaprocesso_controller.php");

$idprodserv = $_GET['idprodserv'];
$idempresaAMigrar = $_GET['idempresa'];
$idevento = $_GET['idevento'];
echo '<pre>';

function buscarInsumo($inidprodservformula, $idProdservFormulaClonada, $percentagem, $lvl = 0, $linha = 0, $nivel = 0, $lvl_old = 0)
{
    global $valoritem, $idempresaAMigrar, $idevento;

    if ($lvl == 0) {
        $valoritem = 0;
    }

    $buscarInsumoSQL = "SELECT * 
                        FROM (SELECT i.idprodservformulains,
                            i.qtdi,
                            i.qtdi_exp,
                            i.idprodserv,
                            i.qtdpd,
                            i.qtdpd_exp,
                            i.chkvolume,
                            i.listares,
                            i.ord,
                            p.fabricado,
                            p.descr,
                            CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                            p.un,
                            fi.idprodservformula,
                            IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
                        FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                        JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                        JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
                        WHERE f.idprodservformula = '$inidprodservformula' 
                        UNION SELECT i.idprodservformulains,
                            i.qtdi,
                            i.qtdi_exp,
                            i.idprodserv,
                            i.qtdpd,
                            i.qtdpd_exp,
                            i.chkvolume,
                            i.listares,
                            i.ord,
                            p.fabricado,
                            p.descr,
                            CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                            p.un,
                            fi.idprodservformula,
                            IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
                        FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                        JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
                        JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
                        WHERE f.idprodservformula = '$inidprodservformula'
                        AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
                        UNION SELECT i.idprodservformulains,
                            i.qtdi,
                            i.qtdi_exp,
                            i.idprodserv,
                            i.qtdpd,
                            i.qtdpd_exp,
                            i.chkvolume,
                            i.listares,
                            i.ord,
                            p.fabricado,
                            p.descr,
                            '' AS rotulo,
                            p.un,
                            NULL,
                            IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc
                        FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                        JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                        WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
                        GROUP BY idprodservformulains
                        ORDER BY fabricado";

    $res = d::b()->query($buscarInsumoSQL);

    echo '<br>';
    if (mysqli_error(d::b()))
        print_r("Erro ao buscar prodservformulains: Erro: " . mysqli_error(d::b()) . "\n");

    echo '<br>';
    print_r($buscarInsumoSQL);

    while ($row = mysqli_fetch_assoc($res)) {
        $linha = $linha + 1;

        /**
         * Para cada insumos, verificar se já existe prodserv(usar codprodserv), se não criar
         */
        $idProdservClonada = clonarProdserv($row['idprodserv']);

        $formulas = FormulaProcessoController::listarProdservFormulaPlantel($row['idprodserv']);
        // $idFormulaClonada = false;

        if (count($formulas)) {
            foreach ($formulas as $formula) {
                if ($formula['status'] == 'ATIVO') {
                    $idFormulaClonada = clonarFormula($formula, $idProdservClonada, $idempresaAMigrar, $idevento);

                    // Atualizando vinculo com plantel
                    atualizarPlantelFormula($formula['idprodservformula'], $idFormulaClonada, $formula['idempresa']);

                    buscarInsumo($formula['idprodservformula'], $idFormulaClonada, $percentagem);
                }
            }
        }

        if ($idProdservClonada) {
            /**
             * Criando insumo
             */
            $prodservFormulaInsSQL = "INSERT INTO prodservformulains (idempresa ,idprodservformula ,idprodserv ,qtdi ,qtdi_exp ,qtdpd ,qtdpd_exp ,chkvolume ,listares ,ord ,status ,criadopor ,criadoem ,alteradopor ,alteradoem)
                                    SELECT 
                                        $idempresaAMigrar,
                                        $idProdservFormulaClonada,
                                        $idProdservClonada,
                                        " . ($row['qtdi'] ? $row['qtdi'] : 'null') . ",
                                        '" . ($row['qtdi_exp'] ? $row['qtdi_exp'] : null) . "',
                                        " . ($row['qtdpd'] ? $row['qtdpd'] : 'null') . ",
                                        '" . ($row['qtdpd_exp'] ? $row['qtdpd_exp'] : null) . "',
                                        '" . ($row['chkvolume'] ? $row['chkvolume'] : null) . "',
                                        '" . ($row['listares'] ? $row['listares'] : null) . "',
                                        " . ($row['ord'] ? $row['ord'] : 'null') . ",
                                        '" . ($row['status'] ? $row['status'] : 'ATIVO') . "',
                                        'evento_$idevento',
                                        now(),
                                        'evento_$idevento',
                                        now()
                                    WHERE NOT EXISTS (
                                        select 1
                                        from prodservformulains
                                        where idprodservformula = $idProdservFormulaClonada
                                        and idprodserv = $idProdservClonada
                                        and idempresa = $idempresaAMigrar
                                    )";

            d::b()->query($prodservFormulaInsSQL);
            if (mysqli_error(d::b()))
                print_r("Erro ao Criar prodservformulains: Erro: " . mysqli_error(d::b()) . "\n");

            echo '<br>';
            print_r($prodservFormulaInsSQL);

            /**
             * Repetir processo para os filhos(insumos).
             */
            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) buscarInsumo($row['idprodservformula'], $idProdservClonada, $row['perc']);
        }
    } //while($row=mysqli_fetch_assoc($res)){
}

function clonarProdserv($idProdservOrigem, $travarSeExistir = false)
{
    global $idempresaAMigrar, $idevento;

    /**
     * Busca os Produtos a serem duplicados e suas unidades de estoque e alerta
     */
    $sqlProdserv = "SELECT idprodserv, idtipoprodserv, codprodserv, tipo, especial, descrtipo, biobox, insumo, material, venda, geraagente, comissionado, comissao, comissaogest,
                        fabricado, processado, visualizacliente, comprado, finalidade, validade, validadeforn, alertavenc, descr, descrgenerica, descrcurta, conferencia,
                        conferenciares, local, ncm, cest, ipi, ncfop, reducaobc, idmedida, un, unconv, unvolume, valconv, uncom, uncptransf, status, volumeprod, qtdpadrao,
                        qtdpadrao_exp, potencia, vlrvenda, vlrcompra, margem, conteudo, idest.idunidadeest, unest, idale.idunidadealerta, consometransf, qtdest, qtdest_exp,
                        estideal, estmax, armazanagem, formafarm, certanalise, infprod, origem, obs, pis, iss, cofins, modbc, cst, ipint,
                        piscst, confinscst, tipoespecial, tipoformalizacao, tiporelatorio, geralegenda, logoinmetro, titulotextopadrao, textopadrao, textoinclusaores, 
                        textointerpretacao, prazoexec, idtipoteste, tipogmt, tipobact, geragraf, geracalc, assinatura, tipocertanalise, idportaria, relatoriopositivo,
                        oficial, notoficial, sif, geraincubacao, modopart, jarvore, jarvorehash, ordenacao, alertaem, justificativa, modelo, modo, comparativodelotes,
                        jsonconfig, permiteformatacao, prioridadecompra, taguiavel, alertarotulo, alertarotuloy, alertarotulon, licenca, temporeposicao, estoqueseguranca,
                        pedido, tempoconsrateio, consumodias, consumodiasgraf, consumodiaslote, mediadiaria,
                        unbkp, imobilizado, idtagtipo, nfe
                    FROM prodserv p
                    JOIN (SELECT u.idunidade as idunidadeest, u.idempresa FROM unidade u WHERE u.idtipounidade = 3 and u.idcentrocusto = 67 AND u.status = 'ATIVO') as idest ON idest.idempresa = $idempresaAMigrar
                    JOIN (SELECT u2.idunidade as idunidadealerta, u2.idempresa FROM unidade u2 WHERE u2.idtipounidade = 19 and u2.idcentrocusto = 64 AND u2.status = 'ATIVO') as idale ON idale.idempresa = $idempresaAMigrar
                    WHERE idprodserv in ($idProdservOrigem)
                    GROUP BY idprodserv;";
    $resProdservOrigem = d::b()->query($sqlProdserv);
    print_r($sqlProdserv);
    if (mysqli_error(d::b()))
        die("Erro ao Buscar prodserv: " . mysqli_error(d::b()) . "\n");

    if (!$resProdservOrigem->num_rows) {
        echo '<br />';
        print_r('Unidades da prodserv não configuradas na empresa destino!');
        return false;
    }

    echo '<br />';

    while ($rowobj = mysqli_fetch_assoc($resProdservOrigem)) {
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

        /**
         * Verificar se prodserv já existe na empresa destino
         */
        $verificaProdservSQL = "SELECT idprodserv
                                from prodserv
                                where codprodserv  = '{$rowobj['codprodserv']}'
                                and idempresa = $idempresaAMigrar;";

        $prodservEmpresaDestino = d::b()->query($verificaProdservSQL);
        $resArray = mysqli_fetch_array($prodservEmpresaDestino);

        if (count($resArray)) {
            print_r("Prodserv {$rowobj['codprodserv']} já existe na empresa $idempresaAMigrar.");

            if ($travarSeExistir) {
                return false;
            }

            return $resArray['idprodserv'];
        } else {
            //Insere Prodserv
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
                        VALUES ($idempresaAMigrar, " . ($rowobj['idtipoprodserv'] ? $rowobj['idtipoprodserv'] : 'null') . ", '" . $rowobj['codprodserv'] . "', '" . $rowobj['tipo'] . "', '" . $rowobj['especial'] . "', '" . $rowobj['descrtipo'] . "', '" . $rowobj['biobox'] . "', '" . $rowobj['insumo'] . "', '" . $rowobj['material'] . "', '" . $rowobj['venda'] . "', '" . $rowobj['geraagente'] . "', '" . $rowobj['comissionado'] . "', '" . $comissao . "', 
                            '" . $comissaogest . "', '" . $rowobj['fabricado'] . "', '" . $rowobj['processado'] . "', '" . $rowobj['visualizacliente'] . "', '" . $rowobj['comprado'] . "', '" . $rowobj['finalidade'] . "', '" . $rowobj['validade'] . "', '" . $rowobj['validadeforn'] . "', '$alertavenc', '" . $rowobj['descr'] . "', '" . $rowobj['descrgenerica'] . "', 
                            '" . $rowobj['descrcurta'] . "', '" . $rowobj['conferencia'] . "', '" . $rowobj['conferenciares'] . "', '" . $rowobj['local'] . "', '" . $rowobj['ncm'] . "', '" . $rowobj['cest'] . "', '" . $rowobj['ipi'] . "', '" . $rowobj['ncfop'] . "', '" . $rowobj['reducaobc'] . "', '$idmedida', '" . $rowobj['un'] . "', '" . $rowobj['unconv'] . "', '" . $rowobj['unvolume'] . "', '$valconv', '" . $rowobj['uncom'] . "', 
                            '" . $rowobj['uncptransf'] . "', '" . $rowobj['status'] . "', '" . $rowobj['volumeprod'] . "', '" . $rowobj['qtdpadrao'] . "', '" . $rowobj['qtdpadrao_exp'] . "', '" . $rowobj['potencia'] . "', '" . $rowobj['vlrvenda'] . "', '" . $rowobj['vlrcompra'] . "', '$margem', '" . $rowobj['conteudo'] . "', '" . $rowobj['idunidadeest'] . "', '" . $rowobj['unest'] . "', 
                            '" . $rowobj['idunidadealerta'] . "', '" . $rowobj['consometransf'] . "', '" . $rowobj['qtdest'] . "', '" . $rowobj['qtdest_exp'] . "', 0, '', 0, '" . $rowobj['estideal'] . "', '" . $rowobj['estmax'] . "', '" . $rowobj['armazanagem'] . "', '" . $rowobj['formafarm'] . "', 
                            '" . $rowobj['certanalise'] . "', '" . $rowobj['infprod'] . "', '" . $rowobj['origem'] . "', '" . $rowobj['obs'] . "', 0, '" . $rowobj['pis'] . "', '" . $rowobj['iss'] . "', '" . $rowobj['cofins'] . "', '" . $rowobj['modbc'] . "', '" . $rowobj['cst'] . "', '" . $rowobj['ipint'] . "', '" . $rowobj['piscst'] . "', '" . $rowobj['confinscst'] . "', '" . $rowobj['tipoespecial'] . "', '" . $rowobj['tipoformalizacao'] . "', 
                            '" . $rowobj['tiporelatorio'] . "', '" . $rowobj['geralegenda'] . "', '" . $rowobj['logoinmetro'] . "', '" . $rowobj['titulotextopadrao'] . "', '" . $rowobj['textopadrao'] . "', '" . $rowobj['textoinclusaores'] . "', '" . $rowobj['textointerpretacao'] . "', $prazoexec, 
                            $idtipoteste, '" . $rowobj['tipogmt'] . "', '" . $rowobj['tipobact'] . "', '" . $rowobj['geragraf'] . "', '" . $rowobj['geracalc'] . "', '" . $rowobj['assinatura'] . "', '" . $rowobj['tipocertanalise'] . "', $idportaria, '" . $rowobj['relatoriopositivo'] . "', '" . $rowobj['oficial'] . "', 
                            '" . $rowobj['notoficial'] . "', '" . $rowobj['sif'] . "', '" . $rowobj['geraincubacao'] . "', '" . $rowobj['modopart'] . "', '$jarvore', '" . $rowobj['jarvorehash'] . "', $ordenacao, '" . $rowobj['alertaem'] . "', '" . $rowobj['justificativa'] . "', '" . $rowobj['modelo'] . "', '" . $rowobj['modo'] . "', '" . $rowobj['comparativodelotes'] . "',
                            '$jsonconfig', '" . $rowobj['permiteformatacao'] . "', '" . $rowobj['prioridadecompra'] . "', '" . $rowobj['taguiavel'] . "', '" . $rowobj['alertarotulo'] . "', '" . $rowobj['alertarotuloy'] . "', '" . $rowobj['alertarotulon'] . "', '" . $rowobj['licenca'] . "', '" . $rowobj['temporeposicao'] . "', 
                            '" . $rowobj['estoqueseguranca'] . "', '" . $rowobj['pedido'] . "', 0, 0, 0, '" . $rowobj['tempoconsrateio'] . "', 0, '" . $rowobj['consumodias'] . "', '" . $rowobj['consumodiasgraf'] . "', 
                            '" . $rowobj['consumodiaslote'] . "', '" . $rowobj['unbkp'] . "', '" . $rowobj['imobilizado'] . "', $idtagtipo, '" . $rowobj['nfe'] . "', 'evento_$idevento', now(), 'evento_$idevento', now())";
            d::b()->query($sql);
            print_r($sql);
            if (mysqli_error(d::b()))
                die("Erro ao Criar Prodserv: Erro: " . mysqli_error(d::b()) . "\n");

            echo '<br>';
            // Recupera o Último ID inserido
            $idNewProdserv = mysqli_insert_id(d::b());

            if (!empty($idNewProdserv)) {
                /**
                 * Busca e Insere os Fornecedores relacionados a prodserv a ser clonada
                 */
                $sqlProdservForn = "SELECT * FROM prodservforn WHERE idprodserv = " . $rowobj['idprodserv'];
                $resProdservForn = d::b()->query($sqlProdservForn);
                if (mysqli_error(d::b()))
                    print_r("Erro ao Buscar prodservforn: " . mysqli_error(d::b()) . "\n");

                print_r($sqlProdservForn);
                echo '<br>';

                while ($rowProdservForn = mysqli_fetch_assoc($resProdservForn)) {
                    $qtd = empty($rowProdservForn['qtd']) ? '0.00' : $rowProdservForn['qtd'];
                    $validadoem = empty($rowProdservForn['validadoem']) ? '000-00-00' : $rowProdservForn['validadoem'];
                    $idprodservformula = empty($rowProdservForn['idprodservformula']) ? 0 : $rowProdservForn['idprodservformula'];
                    $valor = empty($rowProdservForn['valor']) ? '0.00' : $rowProdservForn['valor'];
                    $valconv = empty($rowProdservForn['valconv']) ? '0.00' : $rowProdservForn['valconv'];
                    $reducao = empty($rowProdservForn['reducao']) ? '0.00' : $rowProdservForn['reducao'];
                    $idprodservori = empty($rowProdservForn['idprodservori']) ? 0 : $rowProdservForn['idprodservori'];

                    $q1 = "INSERT INTO prodservforn (idempresa, idprodservformula, idprodserv, idpessoa, obs, codforn, unforn, status, validadopor, validadoem, qtd, valido, valor, converteest, valconv, reducao, obsbkp, idprodservori, multiempresa, criadopor, criadoem, alteradopor, alteradoem) 
                            VALUES (
                                $idempresaAMigrar, 
                                $idprodservformula, 
                                '$idNewProdserv', 
                                " . ($rowProdservForn['idpessoa'] ?? 'null') . ", 
                                '" . $rowProdservForn['obs'] . "',
                                '" . $rowProdservForn['codforn'] . "',
                                '" . $rowProdservForn['unforn'] . "',
                                '" . $rowProdservForn['status'] . "', 
                                '" . $rowProdservForn['validadopor'] . "',
                                '$validadoem',  
                                '$qtd',  
                                '" . $rowProdservForn['valido'] . "',  
                                '$valor', 
                                '" . $rowProdservForn['converteest'] . "',  
                                '$valconv',  
                                '$reducao',  
                                '" . $rowProdservForn['obsbkp'] . "',  
                                $idprodservori,  
                                '" . $rowProdservForn['multiempresa'] . "', 
                                'evento_$idevento', now(), 'evento_$idevento', now())";
                    d::b()->query($q1);
                    print_r($q1);
                    if (mysqli_error(d::b())) {
                        echo '<br>';
                        die("Erro ao Criar prodservforn: Erro: " . mysqli_error(d::b()) . "\n");
                    }

                    echo '<br>';
                    $idnew = mysqli_insert_id(d::b());
                }

                //Insere os Conta Item relacionados
                $sqlProdservContaItem = "SELECT * FROM prodservcontaitem WHERE idprodserv = " . $rowobj['idprodserv'];
                $resProdservContaItem = d::b()->query($sqlProdservContaItem);
                if (mysqli_error(d::b()))
                    print_r("Erro ao Buscar prodservforn: " . mysqli_error(d::b()) . "\n");

                print_r($sqlProdservContaItem);
                echo '<br>';

                while ($rowProdservContaItem = mysqli_fetch_assoc($resProdservContaItem)) {
                    $q2 = "INSERT INTO prodservcontaitem (idempresa, idprodserv, idcontaitem, status, criadopor, criadoem, alteradopor, alteradoem) 
                            VALUES ($idempresaAMigrar, '$idNewProdserv', " . $rowProdservContaItem['idcontaitem'] . ", '" . $rowProdservContaItem['status'] . "', 'evento_$idevento', now(), 'evento_$idevento', now())";
                    d::b()->query($q2);
                    if (mysqli_error(d::b()))
                        print_r("Erro ao Criar prodservcontaitem: Erro: " . mysqli_error(d::b()) . "\n");

                    print_r($q2);
                    echo '<br>';
                    $idnew = mysqli_insert_id(d::b());
                }
            }

            echo "// ----------------------------------------------------------------------------------------------------------------------------";

            return $idNewProdserv;
        }
    }

    return false;
}

function clonarFormula($formula, $idNewProdserv, $idEmpresaDestino, $idEventoOrigem)
{
    // if ($verificaSeExiste) {
        $buscarFormulaPorIdProdservSQL = "SELECT idprodservformula
                                        FROM prodservformula
                                        WHERE idprodserv = $idNewProdserv
                                        AND idempresa = $idEmpresaDestino
                                        AND rotulo = '{$formula['rotulo']}'";

        $resFormulas = d::b()->query($buscarFormulaPorIdProdservSQL);
        $resFormulaArr = mysqli_fetch_array($resFormulas);

        if (mysqli_error(d::b()))
            print_r("Erro ao Criar prodservformula: Erro: " . mysqli_error(d::b()) . "\n");

        if ($resFormulas->num_rows) {
            print_r("Já existe fórmulas para esta prodserv[$idNewProdserv] na empresa $idEmpresaDestino");
            echo '<br>';
            print_r($buscarFormulaPorIdProdservSQL);

            return $resFormulaArr['idprodservformula'];
        }
    // }

    $insertProdservFormulaSQL = "INSERT INTO prodservformula (idempresa, idprodserv, idfluxostatus, rotulo, dose, cor, qtdpadraof, qtdpadraof_exp, volumeformula, volumeformula_exp, un, idplantel, especie, vlrvenda, vlrcusto, comissao, idunidadeest, idunidadealerta, status, ordem, estmin, estmin_exp, estminautomatico, pedido, pedidoautomatico, temporeposicao, estoqueseguranca, tempocompra, pedido_automatico, tempoconsrateio, destoque, consumodias, consumodiasgraf, consumodiaslote, mediadiaria, sugestaocompra2, ultimoorcamento, qtdest, qtdest_exp, atualizaarvore, versao, editar, justificativa, criadopor, criadoem, alteradopor, alteradoem) 
    VALUES (
        $idEmpresaDestino,
        $idNewProdserv,
        " . ($formula['idfluxostatus'] ?? 'null') . ",
        '" . ($formula['rotulo'] ? $formula['rotulo'] : '') . "',
        " . ($formula['dose'] ? $formula['dose'] : 'null') . ",
        '" . ($formula['cor'] ? $formula['cor'] : '') . "',
        " . ($formula['qtdpadraof'] ? $formula['qtdpadraof'] : 'null') . ",
       ' " . ($formula['qtdpadraof_exp'] ? $formula['qtdpadraof_exp'] : '') . "',
        " . ($formula['volumeformula'] !== null ? $formula['volumeformula'] : 'null') . ",
        '" . ($formula['volumeformula_exp'] ? $formula['volumeformula_exp'] : '') . "',
        '" . ($formula['un'] ? $formula['un'] : '') . "',
        " . ($formula['idplantel'] ? $formula['idplantel'] : 'null') . ",
        '" . ($formula['especie'] ? $formula['especie'] : '') . "',
        " . ($formula['vlrvenda'] ? $formula['vlrvenda'] : 'null') . ",
        " . ($formula['vlrcusto'] ? $formula['vlrcusto'] : 'null') . ",
        " . ($formula['comissao'] ? $formula['comissao'] : 'null') . ",
        " . ($formula['idunidadeest'] ? $formula['idunidadeest'] : 'null') . ",
        " . ($formula['idunidadealerta'] ? $formula['idunidadealerta'] : 'null') . ",
        '" . ($formula['status'] ? $formula['status'] : 'ATIVO') . "',
        " . ($formula['ordem'] ? $formula['ordem'] : 'null') . ",
        " . ($formula['estmin'] ? $formula['estmin'] : 'null') . ",
        '" . ($formula['estmin_exp'] ? $formula['estmin_exp'] : '') . "',
        " . ($formula['estminautomatico'] ? $formula['estminautomatico'] : 'null') . ",
        '" . ($formula['pedido'] ? $formula['pedido'] : '') . "',
        " . ($formula['pedidoautomatico'] ? $formula['pedidoautomatico'] : 'null') . ",
        '" . ($formula['temporeposicao'] ? $formula['temporeposicao'] : '') . "',
        '" . ($formula['estoqueseguranca'] ? $formula['estoqueseguranca'] : '') . "',
        '" . ($formula['tempocompra'] ? $formula['tempocompra'] : '') . "',
        " . ($formula['pedido_automatico'] ? $formula['pedido_automatico'] : 'null') . ",
        " . ($formula['tempoconsrateio'] ? $formula['tempoconsrateio'] : 'null') . ",
        " . ($formula['destoque'] ? $formula['destoque'] : 'null') . ",
        " . ($formula['consumodias'] ? $formula['consumodias'] : 'null') . ",
        " . ($formula['consumodiasgraf'] ? $formula['consumodiasgraf'] : 'null') . ",
        " . ($formula['consumodiaslote'] ? $formula['consumodiaslote'] : 'null') . ",
        " . ($formula['mediadiaria'] ? $formula['mediadiaria'] : 'null') . ",
        " . ($formula['sugestaocompra2'] ? $formula['sugestaocompra2'] : 'null') . ",
        " . ($formula['ultimoorcamento'] ? $formula['ultimoorcamento'] : 'null') . ",
        " . ($formula['qtdest'] ? $formula['qtdest'] : 'null') . ",
        '" . ($formula['qtdest_exp'] ? $formula['qtdest_exp'] : '') . "',
        '" . ($formula['atualizaarvore'] ? $formula['atualizaarvore'] : '') . "',
        " . ($formula['versao'] ? $formula['versao'] : 'null') . ",
        '" . ($formula['editar'] ? $formula['editar'] : 'null') . "',
        '" . ($formula['justificativa'] ? $formula['justificativa'] : '') . "',
        'evento_$idEventoOrigem',
        now(),
        'evento_$idEventoOrigem',
        now()
    )";
    d::b()->query($insertProdservFormulaSQL);
    if (mysqli_error(d::b()))
        print_r("Erro ao Criar prodservformula: Erro: " . mysqli_error(d::b()) . "\n");

    echo '<br>';
    print_r($insertProdservFormulaSQL);

    // Recupera o Último ID inserido
    return  mysqli_insert_id(d::b());
}

function clonarPlantel($plantel, $idProdservClonada)
{
    global $idempresaAMigrar, $idevento;

    $idPlantel = false;

    /**
     * Verificando se plantel já existe na empresa destino
     */
    $buscarPlantelSQL = "SELECT idplantel
                        FROM plantel
                        WHERE idempresa = $idempresaAMigrar
                        AND plantel = '{$plantel['plantel']}';";

    $resPlantel = d::b()->query($buscarPlantelSQL);
    $resPlantelArr = mysqli_fetch_array($resPlantel) ?? [];
    if (mysqli_error(d::b()))
        print_r("Erro ao buscar plantel: Erro: " . mysqli_error(d::b()) . "\n");

    echo '<br>';
    print_r($buscarPlantelSQL);

    if (count($resPlantelArr)) {
        print_r("Plantel {$plantel['plantel']} já existe na empresa destino!");
        echo '<br>';

        $idPlantel = $resPlantelArr['idplantel'];
    } else {
        $insertPlantelSQL = "INSERT INTO plantel (campo ,idempresa ,idunidade ,plantel ,prodserv ,status ,criadopor ,criadoem ,alteradopor ,alteradoem)
                            VALUES(
                                " . ($plantel['campo'] ? $plantel['campo'] : 'null') . ",
                                $idempresaAMigrar,
                                null,
                                " . ($plantel['plantel'] ? "'" . $plantel['plantel'] . "'" : 'null') . ",
                                " . ($plantel['prodserv'] ? "'" . $plantel['prodserv'] . "'" : 'null') . ",
                                'ATIVO',
                                'evento_$idevento',
                                now(),
                                'evento_$idevento',
                                now()
                            )";

        d::b()->query($insertPlantelSQL);
        if (mysqli_error(d::b()))
            print_r("Erro ao Criar plantel: Erro: " . mysqli_error(d::b()) . "\n");

        echo '<br>';
        print_r($insertPlantelSQL);

        // Recupera o Último ID inserido
        $idPlantel = mysqli_insert_id(d::b());
    }

    if ($idPlantel) {
        // inserindo binculo plantel
        $insertPlantelObjetoSQL = "INSERT INTO plantelobjeto (idempresa ,idplantel ,idobjeto ,tipoobjeto ,criadopor ,criadoem ,alteradopor ,alteradoem)
                                    SELECT $idempresaAMigrar,
                                                $idPlantel,
                                                $idProdservClonada,
                                                'prodserv',
                                                'evento_$idevento',
                                                now(),
                                                'evento_$idevento',
                                                now()
                                        WHERE NOT EXISTS (
                                            SELECT  1
											FROM plantelobjeto
											WHERE idobjeto = $idProdservClonada    
											AND tipoobjeto = 'prodserv'
											AND idplantel = $idPlantel
                                            AND idempresa = $idempresaAMigrar
                                        )";

        d::b()->query($insertPlantelObjetoSQL);
        if (mysqli_error(d::b()))
            print_r("Erro ao Criar plantelobj: Erro: " . mysqli_error(d::b()) . "\n");

        echo '<br>';
        print_r($insertPlantelObjetoSQL);
    } else {
        echo '<br>';
        print_r("Nenhum plantel encontrado para vínculo!");
    }

    return $idPlantel;
}

function atualizarPlantelFormula($idFormulaOrigem, $idFormulaClonada, $idEmpresaOrigem)
{
    global $idempresaAMigrar;

    /**
     * Buscar plantel destino a partir do plantel vinculado a formula origem
     */
    $buscarFormulaOrigemSQL = "SELECT pc.idplantel as idplantelclonado
                                FROM plantel p
                                JOIN prodservformula f on f.idplantel = p.idplantel
                                JOIN plantel pc on pc.plantel = p.plantel
                                WHERE f.idprodservformula = $idFormulaOrigem
                                AND p.idempresa = $idEmpresaOrigem
                                AND pc.idempresa = $idempresaAMigrar;";

    $resPlantel = d::b()->query($buscarFormulaOrigemSQL);
    $resPlantelArr = mysqli_fetch_array($resPlantel);
    if (mysqli_error(d::b()))
        print_r("Erro ao buscar plantel origem (atualizarPlantelFormula): Erro: " . mysqli_error(d::b()) . "\n");

    echo '<br>';
    print_r($buscarFormulaOrigemSQL);

    if (count($resPlantelArr)) {
        $atualizarFormulaClonadaSQL = "UPDATE prodservformula set idplantel = {$resPlantelArr['idplantelclonado']} WHERE idprodservformula = $idFormulaClonada";

        d::b()->query($atualizarFormulaClonadaSQL);

        echo '<br>';

        if (mysqli_error(d::b()))
            print_r("Erro ao atualizar idplantel prodservformula origem (atualizarPlantelFormula): Erro: " . mysqli_error(d::b()) . "\n");

        echo '<br>';
        print_r($atualizarFormulaClonadaSQL);
    }
}

if (!empty($idprodserv) && !empty($idempresaAMigrar)) {
    $idProdservClonada = clonarProdserv($idprodserv, true);

    if ($idProdservClonada) {
        // Buscando planteis da prodserv origem
        $planteis = FormulaProcessoController::buscarPlantelPorIdObjetoETipoObjeto($idprodserv, 'prodserv');

        // Inserindo planteis na prodserv clonada
        if (count($planteis)) {
            foreach ($planteis as $plantel) {
                clonarPlantel($plantel, $idProdservClonada);
            }
        }

        // Buscando formulas da prodserv origem
        $formulas = FormulaProcessoController::listarProdservFormulaPlantel($idprodserv);

        if (count($formulas)) {
            foreach ($formulas as $id => $formula) {
                // Inserindo formulas na prodserv clonada
                $idFormulaClonada = clonarFormula($formula, $idProdservClonada, $idempresaAMigrar, $idevento);

                // Atualizando vinculo com plantel
                atualizarPlantelFormula($formula['idprodservformula'], $idFormulaClonada, $formula['idempresa']);

                // Inserindo insumos
                buscarInsumo($formula['idprodservformula'], $idFormulaClonada, 1);
            }
        }
    }

    // FIM - Clona Prodserv
    // ----------------------------------------------------------------------------------------------------------------------------
    echo '</pre>';
} else {
    header("HTTP/1.1 500 Parâmetros inválidos");
    die("Parâmetros Inválidos");
}
