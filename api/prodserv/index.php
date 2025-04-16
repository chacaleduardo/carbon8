<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/nf_controller.php");
//ini_set("display_errors",1);
//error_reporting(E_ALL);

class cprod
{
    static public $valortotalconsumo = 0;

    static public function buscavalorproduto($inidprodservformula, $percentagem, $valoritem)
    {

        //$valoritem=0;
        $sql = "select  i.qtdi,i.idprodserv,p.fabricado,p.descr,fi.idprodservformula,ifnull((i.qtdi/fi.qtdpadraof),1) as perc
                from prodservformula f 
                join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                join prodserv p on(p.idprodserv = i.idprodserv) 
                left join prodservformula fi on(fi.status='ATIVO' 
                                                and fi.idprodserv=i.idprodserv
                                                and( fi.idplantel=f.idplantel or fi.idplantel is null or fi.idplantel='') )
                where f.idprodservformula= ".$inidprodservformula." order by p.descr";
        $res = d::b()->query($sql);

        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
                $valoritem = cprod::buscavalorproduto($row['idprodservformula'], $row['perc'], $valoritem);
            } elseif ($row['fabricado'] == 'N') {
                $valor = cprod::buscavaloritem($row['idprodserv'], $row['qtdi']);
                $valor = $valor * $percentagem;
                $valoritem = $valoritem + $valor;
                // echo($valoritem.'<br>');
            }
        } //while($row=mysqli_fetch_assoc($res)){        
        return  $valoritem;
    } //function buscarvalorform($inidprodservformula,$inidplantel){


    static public function buscavaloritem($inidprodserv, $qtdi)
    {
        $sql = "SELECT IFNULL(l.vlrlote, 0) as valoritem, l.idlote 
                    FROM lote l 
                    WHERE l.idprodserv = $inidprodserv AND vlrlote > 0  and l.status!='CANCELADO' ORDER BY idlote DESC LIMIT 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);

        if ($qtdi > 1) {
            return $row['valoritem'] * $qtdi;
        }

        return $row['valoritem'];
    }

    static public  function buscavalorloteprod($inidprodserv, $qtdi = 1)
    {   
        $sql = "SELECT IFNULL(l.vlrlote,0) as valoritem, l.idlote 
                    FROM lote l 
                    WHERE l.idprodserv = $inidprodserv AND vlrlote > 0 and l.status!='CANCELADO' ORDER BY idlote DESC LIMIT 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);
        if ($qtdi > 1) {
            return $row['valoritem'] * $qtdi;
        }

        return $row['valoritem'];
    }

    static public  function buscavaloritemProgramado($inidprodserv)
    {   
        $impostoImportacao = NfController::buscarValorImpostoTotalItem($inidprodserv, 'prodserv');
        if($impostoImportacao['internacional'] == 'Y'){            
            $valor = round((($impostoImportacao['vlritem'] + $impostoImportacao['valorcomimpostoitem'] + $impostoImportacao['valorcomimposto'])), 4);
        } else {
            $sql = "SELECT (IFNULL(ni.total, 0) + IFNULL(ni.valipi, 0) + IFNULL(ni.frete, 0)) / (ni.qtd * IF(pf.valconv < 1 OR pf.valconv IS NULL, 1, pf.valconv)) as valoritem, 
                           n.status
                      FROM nfitem ni JOIN nf n ON n.idnf = ni.idnf
                 LEFT JOIN prodservforn pf ON pf.idprodserv = ni.idprodserv AND pf.idprodservforn = ni.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa
                     WHERE ni.idprodserv = $inidprodserv AND nfe = 'Y' 
                       AND n.status IN('PREVISAO', 'APROVADO', 'INICIO RECEBIMENTO', 'CONFERIDO', 'CONCLUIDO')
                  ORDER BY idnfitem DESC LIMIT 1";
            $res = d::b()->query($sql);
            $row = mysqli_fetch_assoc($res);
            $valor = ($row['status'] == 'CONCLUIDO') ? cprod::buscavalorloteprod($inidprodserv, $qtdi) : round(($row['valoritem']), 4);    
            
        }

        return $valor;
    }

    static public function buscavalorlote($inidlote, $percentual, $zerar)
    {
        if ($zerar == 'Y') {
            global  $valor;
            $valor = 0;
        } else {
            global $valor;
        }

        $sql = "select 
            idloteinsumo as idlote,qtdd, vlrlote, 
                          qtdproduzido,
                          idprodservformula,comprado,fabricado,descr
              from vw8LoteConsInsumo   
              where idlote=".$inidlote;

        $res = d::b()->query($sql);
        $valorc = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['fabricado'] == 'Y') {
                $percentualcon = $row['qtdd'] / $row['qtdproduzido'];
                $percent = $percentual * $percentualcon;
                $valorform = cprod::buscavalorlote($row['idlote'], $percent, 'N');
                // echo($row['idlote']." ".$valorlote."<br>");
                //$valorf =$valorf + (( $valorform/$row['qtdprod']) * $row['qtdd']);                 
            } elseif ($row['fabricado'] == 'N' and $row['vlrlote'] > 0) {

                $valorcp = ($row['vlrlote'] * $row['qtdd']) * $percentual;
                // $valoritem=$valoritem+$valor;
                $valorc = $valorc + $valorcp;
                // echo($valoritem.'<br>');
            }
        } //while($row=mysqli_fetch_assoc($res)){   
        $valor = $valor +  ($valorc);
        return $valor;
    } //function buscarvalorform($inidprodservformula,$inidplantel){


    static public function buscavalorprodformula($inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0, $insumosAvoNeto = false)
    {
        global $excel, $insumosAvoNeto, $arrayProduto, $cacheProd;

        if ($lvl > 0) {
            $m = $lvl * 15;
            $margin = "margin-left:".$m."px;";
        } else {
            $margin = "";
        }
        global $valoritem;
        if($lvl == 0){
            $valoritem = 0;
        }
        
        $sql = "SELECT u.*, 
                    
                    (
                        SELECT informacao
					    FROM(
                            SELECT  
                                CONCAT(IFNULL(p1.nome, ''), ';', IFNULL(DMA(n1.dtemissao), ''), ';', IFNULL(s1.cidade, ''), ';', IFNULL(e1.uf, ''),';',n1.nnfe,';',n1.idnfe) AS informacao,
                                dtemissao
                            FROM nf n1 JOIN nfitem i1 ON n1.idnf = i1.idnf AND i1.nfe = 'Y' AND i1.qtd > 0
							JOIN pessoa p1 ON p1.idpessoa = n1.idpessoa
                            left JOIN endereco e1 on e1.idpessoa = p1.idpessoa AND e1.status = 'ATIVO' and e1.idtipoendereco = 2
                            left join nfscidadesiaf s1 on s1.codcidadeint = e1.codcidade*1
                            WHERE n1.tiponf NOT IN ('R' , 'D', 'T', 'E', 'V') 
                            AND i1.idprodserv =u.idprodserv		  	
						    AND n1.status IN ('APROVADO', 'DIVERGENCIA', 'CONCLUIDO')) AS uc 
					   ORDER BY dtemissao DESC LIMIT 1
                    ) as ultimacompra
                       
                       FROM (
                                SELECT i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                      p.un,
                                      fi.idprodservformula,
                                      IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
                                      f.idprodserv as idprodservpai,
                                      e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                      
                                 FROM prodservformula f 
                                 JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                  JOIN empresa e ON e.idempresa = i.idempresa
                             left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                 left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                 left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
                                WHERE f.idprodservformula = '$inidprodservformula' 
                         UNION 
                            SELECT i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                      p.un,
                                      fi.idprodservformula,
                                      IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
                                      f.idprodserv as idprodservpai,
                                        e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                      
                                 FROM prodservformula f 
                                 JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
                                 JOIN empresa e ON e.idempresa = i.idempresa
                                 left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                 left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                 left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
                                WHERE f.idprodservformula = '$inidprodservformula'
                                  AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
                         UNION 
                            SELECT i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      '' AS rotulo,
                                      p.un,
                                      
                                      NULL,
                                      IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc,
                                      f.idprodserv as idprodservpai,
                                        e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN empresa e ON e.idempresa = i.idempresa
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                    left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                 left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                 left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv
                                )
                            ) AS u
                             GROUP BY idprodservformulains
                             ORDER BY fabricado
                            ";

        $res = d::b()->query($sql);
        
        while ($row = mysqli_fetch_assoc($res)) {
            $linha = $linha + 1;            

            // Concatena os contadores dos níveis para formar $nivel
            if($lvl == 0){
                cb::$session["nivel_old"] = $nivel;
                $nivel = $nivel + 1;
                $negritoInicial = '<b>';
                $negritoFinal = '</b>';
            } else {
                $arrayNivel = explode('.', $nivel);
                $contador = count($arrayNivel);
                if($lvl_old <> $lvl){ 
                    $nivel = $nivel.'.1';
                } else {
                    $arrayNivel[$contador - 1]++;
                    $nivel = implode('.', $arrayNivel);
                }
            }

            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
                if ($detalhado == "Y") {
                    ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
                        <div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
                        <div class="col-md-2">
                            <span href="#collapse-vallote-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="pointer">
                                <i class="fa fa-angle-right" style="padding: 5px 10px;"></i>
                            </span>
                            <? if (empty($row['qtdi_exp'])) {
                                $valorQtd = number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.');
                                $valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
                            } else {
                                $valorQtd = recuperaExpoente(tratanumero($row['qtdi'] * $percentagem), $row['qtdi_exp']);
                                $valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
                            }
                            ?> 
                            <span class="qtdun-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$valorQtdValue;?>"><?=$valorQtd?></span>
                        </div>
                        <div class="col-md-1"><?=$row['un']?></div>
                        <div class="col-md-5">
                            <a class="pointer" onclick="janelamodal('?_modulo=formulaprocesso&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>

                        </div>
                        <div class="col-md-1 valloteunacumulado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteunacumulado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>
                        <div class="col-md-2 valloteacumulado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteacumulado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>
                    </div>
                    <div id="collapse-vallote-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="hidden">
                    <?
                    $ultimacompra = explode(';',$row['ultimacompra']);
                    $excel .= '<tr>
                                <td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
                                <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                <td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.')." ".$negritoFinal.'</td>
                                <td style="width: 50px;">'.$negritoInicial.$row['un'].$negritoFinal.'</td>
                                <td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
                                <td >'.$row['nomefantasia'].'</td>
                                <td >'.$row['idprodservpai'].'</td>                      
                                <td style="width: 50px;" class="idlotecons-valloteunacumulado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
                                <td style="width: 50px;" class="idlotecons-valloteacumulado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
                            </tr>';
                }                   

                $lvl_old = $lvl;

                if(strlen($lvl) == 1){
                    cb::$session['arvore'][$lvl][ 'idprodserv'][] = $row['idprodserv'];
                }
                
                //Não deixa entrar me loop infinito quando o pai tem como filho o próprio pai
                if(in_array($_GET['idprodserv'], cb::$session['arvore'][1]['idprodserv'])){
                    $insumosAvoNeto = true;
                    $arrayProduto['idprodserv'] = $row['idprodservpai'];
                } else {
                     cprod::buscavalorprodformula($row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old, $insumosAvoNeto);
                }
                if ($detalhado == "Y") {
                    ?>
                    </div>
                <?
                }
            } elseif ($row['fabricado'] == 'N') {
                if (!$cacheProd[$row['idprodserv']]) {
                   $cacheProd[$row['idprodserv']] = array();
                }

                $valor = $cacheProd[$row['idprodserv']]['buscavaloritem'];
                if (!$valor) {
                    $valor = cprod::buscavaloritem($row['idprodserv'], 1);
                    $cacheProd[$row['idprodserv']]['buscavaloritem'] = $valor;
                }
                $valor = $valor * $row['qtdi'];
                $valor = round(($valor), 4);

                $valorlote = $cacheProd[$row['idprodserv']]['buscavalorloteprod'];
                if (!$valorlote) {
                    $valorlote = cprod::buscavalorloteprod($row['idprodserv'], 1);
                    $cacheProd[$row['idprodserv']]['buscavalorloteprod'] = $valorlote;
                }
                $valorlote = $valorlote * $row['qtdi'];
                $valorlote = round(($valorlote), 4);

                $valorun = $cacheProd[$row['idprodserv']]['buscavalorloteprod'];
                $valorun = round(($valorun), 4);

                $valor = $valor * $percentagem;

                if ($detalhado == "Y") { ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
                    <div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
                        <div class="col-md-2">
                            <span>
                                <i class="fa" style="padding: 5px 12px;"></i>
                            </span>
                            <? $valorQtd = tratanumero($row['qtdi'] * $percentagem) ?>
                            <span class="qtdun-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=number_format(tratanumero($row['qtdi']), 4, ',', '.');?>"><?=number_format(tratanumero($row['qtdi']), 4, '.', ',')?></span>
                        </div>
                        <div class="col-md-1"><?=$row['un']?></div>
                        <div class="col-md-5">
                            <a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>
                        </div>
                        <div class="col-md-1" style="text-align: right;" valloteun="<?=$valorun?>">R$ <?=number_format(tratanumero($valorun), 4, ',', '.')?></div>
                        <div class="col-md-2" vallote="<?=$valor ?>">
                            <? //echo('('.$row['vlrlote'].'*'.$row['qtdd'].')*'.$percent.')= ')
                            ?>
                            <span style="float:right" title="R$: <?=$valor ?> / Valor Lote R$: <?=$valorun ?>">R$ <?=number_format(tratanumero($valor), 4, ',', '.') ?></span>

                            <? //=$valor
                            ?>
                        </div>
                    </div>
                    
                    <? 
                    $ultimacompra = explode(';',$row['ultimacompra']);
                    $excel .= '<tr>
                                    <td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
                                    <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                    <td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.').$negritoFinal.'</td>
                                    <td style="width: 50px;">'.$negritoInicial.$row['un'].$negritoFinal.'</td>
                                    <td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
                                    <td >'.$row['nomefantasia'].'</td>
                                    <td >'.$row['idprodservpai'].'</td> 
                                    <td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valorun), 4, ',', '.').$negritoFinal.'</td>
                                    <td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valor), 4, ',', '.').$negritoFinal.'</td>
                                </tr>';
                }

                $valoritem = $valoritem + $valor;
                $lvl_old = $lvl;
            }
        } //while($row=mysqli_fetch_assoc($res)){
        
        cb::$session['arvore'] = NULL;

        return  number_format(tratanumero($valoritem), 4, ',', '.');
    } //function buscarvalorform($inidprodservformula,$inidplantel){
        
    static public function buscavalorprodformula2($inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0, $insumosAvoNeto = false)
    {
        global $excelClone, $insumosAvoNeto, $arrayProduto, $cacheProd;

        if ($lvl > 0) {
            $m = $lvl * 15;
            $margin = "margin-left:".$m."px;";
        } else {
            $margin = "";
        }
        global $valoritem;
        if($lvl == 0){
            $valoritem = 0;
        }
        
        $sql = "SELECT u.*,                     
                    (
                        SELECT informacao
					    FROM(
                            SELECT  
                                CONCAT(IFNULL(p1.nome, ''), ';', IFNULL(DMA(n1.dtemissao), ''), ';', IFNULL(s1.cidade, ''), ';', IFNULL(e1.uf, ''),';',n1.nnfe,';',n1.idnfe) AS informacao,
                                dtemissao
                            FROM nf n1 JOIN nfitem i1 ON n1.idnf = i1.idnf AND i1.nfe = 'Y' AND i1.qtd > 0
							JOIN pessoa p1 ON p1.idpessoa = n1.idpessoa
                            left JOIN endereco e1 on e1.idpessoa = p1.idpessoa AND e1.status = 'ATIVO' and e1.idtipoendereco = 2
                            left join nfscidadesiaf s1 on s1.codcidadeint = e1.codcidade*1
                            WHERE n1.tiponf NOT IN ('R' , 'D', 'T', 'E', 'V') 
                            AND i1.idprodserv =u.idprodserv		  	
						    AND n1.status IN ('APROVADO', 'DIVERGENCIA', 'CONCLUIDO')) AS uc 
					   ORDER BY dtemissao DESC LIMIT 1
                    ) as ultimacompra
                    
                     FROM (SELECT i.idprodservformulains,
                                        i.qtdi,
                                        i.qtdi_exp,
                                        i.idprodserv,
                                        p.fabricado,
                                        p.descr,
                                        CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                        p.un,
                                        fi.idprodservformula,
                                        IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
                                        f.idprodserv as idprodservpai,
                                        e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                    FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                    JOIN empresa e ON e.idempresa = i.idempresa
                                    JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                     left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                 left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                 left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                    JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
                                WHERE f.idprodservformula = '$inidprodservformula' 
                            UNION SELECT i.idprodservformulains,
                                        i.qtdi,
                                        i.qtdi_exp,
                                        i.idprodserv,
                                        p.fabricado,
                                        p.descr,
                                        CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                        p.un,
                                        fi.idprodservformula,
                                        IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
                                        f.idprodserv as idprodservpai,
                                        e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                    FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                    JOIN empresa e ON e.idempresa = i.idempresa
                                    JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
                                      left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                 left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                 left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                    JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
                                WHERE f.idprodservformula = '$inidprodservformula'
                                    AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
                            UNION SELECT i.idprodservformulains,
                                        i.qtdi,
                                        i.qtdi_exp,
                                        i.idprodserv,
                                        p.fabricado,
                                        p.descr,
                                        '' AS rotulo,
                                        p.un,
                                        NULL,
                                        IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc,
                                        f.idprodserv as idprodservpai,
                                        e.nomefantasia,
                                      t.tipoprodserv as subcategoria,
                                      c.contaitem as categoria
                                    FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                    JOIN empresa e ON e.idempresa = i.idempresa
                                    JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                      left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                                    left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                                    left join contaitem c on(c.idcontaitem=ci.idcontaitem)
                                WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
                                GROUP BY idprodservformulains
                                ORDER BY fabricado";

        $res = d::b()->query($sql);
        
        while ($row = mysqli_fetch_assoc($res)) {
            $linha = $linha + 1;            

            // Concatena os contadores dos níveis para formar $nivel
            if($lvl == 0){
                cb::$session["nivel_old"] = $nivel;
                $nivel = $nivel + 1;
                $negritoInicial = '<b>';
                $negritoFinal = '</b>';
            } else {
                $arrayNivel = explode('.', $nivel);
                $contador = count($arrayNivel);
                if($lvl_old <> $lvl){ 
                    $nivel = $nivel.'.1';
                } else {
                    $arrayNivel[$contador - 1]++;
                    $nivel = implode('.', $arrayNivel);
                }
            }

            $valorUnLote = $cacheProd[$row['idprodserv']]['buscavalorloteprod'];
            if (!$valorUnLote) {
                $valorUnLote = cprod::buscavalorloteprod($row['idprodserv']);
                $cacheProd[$row['idprodserv']]['buscavalorloteprod'] = $valorUnLote;
            }
            $valorUnLote = ($valorUnLote > 0) ? number_format(tratanumero($valorUnLote), 4, ',', '.') : 'Sem Lote';

            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
                if ($detalhado == "Y") {
                    ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
                        <div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
                        <div class="col-md-1">
                            <span href="#collapse-vallote-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="pointer">
                                <i class="fa fa-angle-right" style="padding: 5px 10px;"></i>
                            </span>
                            <? if (empty($row['qtdi_exp'])) {
                                $valorQtd = number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.');
                                $valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
                            } else {
                                $valorQtd = recuperaExpoente(tratanumero($row['qtdi'] * $percentagem), $row['qtdi_exp']);
                                $valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
                            }
                            ?> 
                            <span class="qtdun-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$valorQtdValue;?>"><?=$valorQtd?></span>
                        </div>
                        <div class="col-md-1">
                            <?=$row['un']?>
                        </div>
                        <div class="col-md-4">
                            <a class="pointer" onclick="janelamodal('?_modulo=formulaprocesso&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>
                        </div>   
                        <div class="col-md-1" style="text-align: right;"><?=$row['nomefantasia'];?></div>                     
                        <div class="col-md-1" style="text-align: right;"></div>                     
                        <div class="col-md-1 valloteunacumulado-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteunacumulado-duplicado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>      
                        <div class="col-md-1 valloteacumulado-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteacumulado-duplicado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>
                    </div>
                    <div id="collapse-vallote-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="hidden">
                    <?
                    $ultimacompra = explode(';',$row['ultimacompra']);
                    $excelClone .= '<tr>
                                <td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
                                <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                <td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.').$negritoFinal.'</td>
                                <td style="width: 50px;">'.$negritoInicial.$row['un'].$negritoFinal.'</td>
                                <td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
                                <td>'.$row['categoria'].'</td>
                                <td>'.$row['subcategoria'].'</td>
                                <td>'.$ultimacompra[0].'</td>
                                <td>'.$ultimacompra[2].'</td>
                                <td>'.$ultimacompra[3].'</td>
                                <td>'.$ultimacompra[1].'</td>
                                <td>'.$ultimacompra[4].'</td>
                                <td>\''.$ultimacompra[5].'\'</td>
                                <td style="width: 50px;" class="idlotecons-valloteunacumulado-duplicado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
                                <td style="width: 50px;" class="idlotecons-valloteacumulado-duplicado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
                            </tr>';
                }                   

                $lvl_old = $lvl;

                if(strlen($lvl) == 1){
                    cb::$session['arvore'][$lvl][ 'idprodserv'][] = $row['idprodserv'];
                }
                
                //Não deixa entrar me loop infinito quando o pai tem como filho o próprio pai
                if(in_array($_GET['idprodserv'], cb::$session['arvore'][1]['idprodserv'])){
                    $insumosAvoNeto = true;
                    $arrayProduto['idprodserv'] = $row['idprodservpai'];
                } else {
                        cprod::buscavalorprodformula2($row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old, $insumosAvoNeto);
                }
                if ($detalhado == "Y") {
                    ?>
                    </div>
                <?
                }
            } elseif ($row['fabricado'] == 'N') {
                $valor = $cacheProd[$row['idprodserv']]['buscavaloritemProgramado'];
                if (!$valor) {
                    $valor = cprod::buscavaloritemProgramado($row['idprodserv']);
                    $cacheProd[$row['idprodserv']]['buscavaloritemProgramado'] = $valor;
                }
                $valor = $valor * $row['qtdi'];
                
                $valorun = $cacheProd[$row['idprodserv']]['buscavaloritemProgramado'];
                if (!$valorun) {
                    $valorun = cprod::buscavaloritemProgramado($row['idprodserv']);
                    $cacheProd[$row['idprodserv']]['buscavaloritemProgramado'] = $valorun;
                }

                $prodservduplicada = 0;

                //@735847 - Custos de insumos MB-HB   
                if($valor == 0){
                    $prodservduplicada_sql = "SELECT idprodserv2, e.nomefantasia
                                                FROM prodservduplicada pd JOIN prodserv p ON p.idprodserv = pd.idprodserv2
                                                JOIN empresa e ON e.idempresa = p.idempresa
                                               WHERE pd.idprodserv = ".$row['idprodserv']."";
                    $prodservduplicada = d::b()->query($prodservduplicada_sql)->fetch_assoc();
                    if($prodservduplicada['idprodserv2']){
                        $valor = cprod::buscavaloritemProgramado($prodservduplicada['idprodserv2'], $row['qtdi']);
                        $valorun = cprod::buscavaloritemProgramado($prodservduplicada['idprodserv2'], 1);
                    }

                    $valorUnLote = $cacheProd[$prodservduplicada['idprodserv2']]['buscavalorloteprod'];
                    if (!$valorUnLote) {
                        $valorUnLote = cprod::buscavalorloteprod($prodservduplicada['idprodserv2']);
                        $cacheProd[$prodservduplicada['idprodserv2']]['buscavalorloteprod'] = $valorUnLote;
                    }
                    $valorUnLote = ($valorUnLote > 0) ? number_format(tratanumero($valorUnLote), 4, ',', '.') : 'Sem Lote';
                }
                $valor = $valor * $percentagem;

                if ($detalhado == "Y") { ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
                    <div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
                        <div class="col-md-1">
                            <span>
                                <i class="fa" style="padding: 5px 12px;"></i>
                            </span>
                            <? $valorQtd = tratanumero($row['qtdi'] * $percentagem) ?>
                            <span class="qtdun-duplicado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=number_format(tratanumero($row['qtdi']), 4, ',', '.');?>"><?=number_format(tratanumero($row['qtdi']), 4, '.', ',') ?></span>
                        </div>
                        <div class="col-md-1">
                            <?=$row['un']?>
                        </div>
                        <div class="col-md-4">
                            <a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>
                        </div>
                        <div class="col-md-1" style="text-align: right;"><?=empty($prodservduplicada['idprodserv2']) ? $row['nomefantasia'] : $prodservduplicada['nomefantasia'];?></div>                     
                        <div class="col-md-1" style="text-align: right;">
                            <a class="pointer" onclick="janelamodal('?_modulo=calculosestoque&_acao=u&idprodserv=<?=$prodservduplicada['idprodserv2'] ?>')"><?=$prodservduplicada['idprodserv2'] ?></a>    
                        </div> 
                        <div class="col-md-1" style="text-align: right;<?= isset($prodservduplicada['idprodserv2'])?'color:red;':'';?>" valloteun-duplicado="<?=$valorun?>">R$ <?=number_format(tratanumero($valorun), 4, ',', '.')?></div>
                        <div class="col-md-1" vallote-duplicado="<?=$valor ?>">
                            <span style="float:right; <?=isset($prodservduplicada['idprodserv2'])?'color:red;':'';?>" title="R$: <?=$valor ?> / Valor Lote R$: <?=$valorun ?>">R$ <?=number_format(tratanumero($valor), 4, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <?
                    $ultimacompra = explode(';',$row['ultimacompra']);                    
                    $excelClone .= '<tr>
                                    <td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
                                    <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                    <td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.')." ".$negritoFinal.'</td>
                                    <td style="width: 50px;">'.$negritoInicial.$row['un'].$negritoFinal.'</td>
                                    <td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
                                    
                                    <td>'.$row['categoria'].'</td>
                                    <td>'.$row['subcategoria'].'</td>
                                    <td>'.$ultimacompra[0].'</td>
                                    <td>'.$ultimacompra[2].'</td>
                                    <td>'.$ultimacompra[3].'</td>
                                    <td>'.$ultimacompra[1].'</td>
                                    <td>'.$ultimacompra[4].'</td>
                                    <td>\''.$ultimacompra[5].'\'</td>
                                    <td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valorun), 4, ',', '.').$negritoFinal.'</td>
                                    <td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valor), 4, ',', '.').$negritoFinal.'</td>
                                </tr>';
                }

                $valoritem = $valoritem + $valor;
                $lvl_old = $lvl;
            }
        } //while($row=mysqli_fetch_assoc($res)){
        
        cb::$session['arvore'] = NULL;

        return  number_format(tratanumero($valoritem), 4, ',', '.');
    }
    static public function buscavalorprodservservico($idprodserv, $detalhado)
    { global $excel;
             
        $sql ="select i.qtdi,i.idprodserv,p.descr,p.codprodserv,ifnull(p.vlrvenda,0) as vlrvenda, t.tipoprodserv as subcategoria,ct.contaitem as categoria
                    from prodservloteservico c
                        join prodservloteservicoins i on(i.idprodservloteservico = c.idprodservloteservico and i.status !='INATIVO' and i.qtdi>0 )
                        join prodserv p on(p.idprodserv=i.idprodserv)
                        left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                        left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                        left join contaitem ct on(ct.idcontaitem=ci.idcontaitem)
                    where c.status !='INATIVO' 
                    and c.idprodserv =".$idprodserv;

        $res = d::b()->query($sql);
        $valoritem=0;
        $nservico=0;
        while ($row = mysqli_fetch_assoc($res)) {
            $nservico= $nservico+1;
         
                $servico_valor = FormulaProcessoController::buscarValorServico($row['idprodserv']);

                if(empty($servico_valor['vlrun'])){
                    $servico_valor['vlrun']=$row['vlrvenda'];
                }

                $valor=($servico_valor['vlrun']*$row["qtdi"]);
                
                $valoritem = $valoritem + $valor;        
                $valorun = $servico_valor['vlrun'];
             

                if ($detalhado == "Y") { ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;">
                    <div class="col-md-1"><? echo "SERVIÇO ".$nservico ?></div>
                        <div class="col-md-2">
                            <span>
                                <i class="fa" style="padding: 5px 12px;"></i>
                            </span>
                            <?=number_format(tratanumero($row['qtdi']), 2, ',', '.') ?></span> 
                        </div>
                        <div class="col-md-6">
                            <a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?></a>
                        </div>
                        <div class="col-md-1" style="text-align: right;" valloteun="<?=$valorun ?>">R$ <?=number_format(tratanumero($valorun), 2, ',', '.')?></div>
                        <div class="col-md-2" vallote="<?=$valor ?>">
                            <? //echo('('.$row['vlrlote'].'*'.$row['qtdd'].')*'.$percent.')= ')
                            ?>
                            <span style="float:right" title="R$: <?=$valor ?> / Valor Lote R$: <?=$valorun ?>">R$ <?=number_format(tratanumero($valor), 2, ',', '.') ?></span>

                            <? //=$valor
                            ?>
                        </div>
                    </div>
                    
                <? 
                $excel .= '<tr>
                                <td style="text-align: right; width: 50px;">SERVIÇO '.$nservico .'</td>
                                <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                <td style="width: 50px;">'.number_format(tratanumero($row['qtdi']), 2, ',', '.').'</td>
                                <td style="width: 50px;"> </td>
                                <td style="width: 200px;">'.$row['descr'].'</td>
                                <td>'.$row['categoria'].'</td>
                                <td>'.$row['subcategoria'].'</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="width: 50px;">R$ '.number_format(tratanumero($valorun), 4, ',', '.').'</td>
                                <td style="width: 50px;">R$ '.number_format(tratanumero($valor), 4, ',', '.').'</td>
                            </tr>';    
            }
            
        } //while($row=mysqli_fetch_assoc($res)){

        return  number_format(tratanumero($valoritem), 2, ',', '.');
    } //function buscarvalorform($inidprodservformula,$inidplantel){

    static public function buscavalorprodservservico2($idprodserv, $detalhado)
    { global $excelClone;
             
        $sql ="select i.qtdi,i.idprodserv,p.descr,p.codprodserv,ifnull(p.vlrvenda,0) as vlrvenda, t.tipoprodserv as subcategoria,ct.contaitem as categoria
                    from prodservloteservico c
                        join prodservloteservicoins i on(i.idprodservloteservico = c.idprodservloteservico and i.status !='INATIVO' and i.qtdi>0 )
                        join prodserv p on(p.idprodserv=i.idprodserv)
                        left JOIN tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
                        left join prodservcontaitem ci on(ci.idprodserv=p.idprodserv and ci.status='ATIVO')
                        left join contaitem ct on(ct.idcontaitem=ci.idcontaitem)
                    where c.status !='INATIVO' 
                    and c.idprodserv =".$idprodserv;

        $res = d::b()->query($sql);
        $valoritem=0;
        $nservico=0;
        while ($row = mysqli_fetch_assoc($res)) {
            $nservico= $nservico+1;
         
                $servico_valor = FormulaProcessoController::buscarValorServico($row['idprodserv']);

                if(empty($servico_valor['vlrun'])){
                    $servico_valor['vlrun']=$row['vlrvenda'];
                }

                $valor=($servico_valor['vlrun']*$row["qtdi"]);
                
                $valoritem = $valoritem + $valor;        
                $valorun = $servico_valor['vlrun'];
             

                if ($detalhado == "Y") { ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;">
                    <div class="col-md-1"><? echo "SERVIÇO ".$nservico ?></div>
                        <div class="col-md-2">
                            <span>
                                <i class="fa" style="padding: 5px 12px;"></i>
                            </span>
                            <?=number_format(tratanumero($row['qtdi']), 2, ',', '.') ?></span> 
                        </div>
                        <div class="col-md-6">
                            <a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?></a>
                        </div>
                        <div class="col-md-1" style="text-align: right;" valloteun="<?=$valorun ?>">R$ <?=number_format(tratanumero($valorun), 2, ',', '.')?></div>
                        <div class="col-md-2" vallote="<?=$valor ?>">
                            <? //echo('('.$row['vlrlote'].'*'.$row['qtdd'].')*'.$percent.')= ')
                            ?>
                            <span style="float:right" title="R$: <?=$valor ?> / Valor Lote R$: <?=$valorun ?>">R$ <?=number_format(tratanumero($valor), 2, ',', '.') ?></span>

                            <? //=$valor
                            ?>
                        </div>
                    </div>
                    
                <? 
                $excelClone .= '<tr>
                                <td style="text-align: right; width: 50px;">SERVIÇO '.$nservico .'</td>
                                <td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
                                <td style="width: 50px;">'.number_format(tratanumero($row['qtdi']), 2, ',', '.').'</td>
                                <td style="width: 50px;"> </td>
                                <td style="width: 200px;">'.$row['descr'].'</td>
                                <td>'.$row['categoria'].'</td>
                                <td>'.$row['subcategoria'].'</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="width: 50px;">R$ '.number_format(tratanumero($valorun), 4, ',', '.').'</td>
                                <td style="width: 50px;">R$ '.number_format(tratanumero($valor), 4, ',', '.').'</td>
                            </tr>';    
            }
            
        } //while($row=mysqli_fetch_assoc($res)){

        return  number_format(tratanumero($valoritem), 2, ',', '.');
    } //function buscarvalorform($inidprodservformula,$inidplantel){
    
    static public function listavalorlote($inidlote, $percentual, $lvl = 0)
    {
        if ($lvl > 0) {
            $m = $lvl * 15;
            $margin = "margin-left:".$m."px;";
        } else {
            $margin = "";
        }

        //$valoritem=0;
        $sql = "SELECT o.idobjeto as linkmodulo,l.partida,l.exercicio,l.idlote,c.qtdd,c.qtdd_exp,
                    ifnull(l.vlrlote,0) as vlrlote,l.idprodservformula,p.comprado,p.fabricado,p.descr,p.idprodserv,
                   c.idlotecons,
                    CASE
                        WHEN l.qtdprod  < 1 THEN 1   
                        ELSE l.qtdprod 
                        END as qtdproduzido,l.qtdprod_exp,ifnull(l.valconvori,1) as  valconvori,
                    p.un
                from lotecons c 
                join lote l on(l.idlote= c.idlote) join prodserv p on(p.idprodserv=l.idprodserv)
                join unidadeobjeto o on  (o.tipoobjeto='modulo' 				
                                    and o.idunidade = l.idunidade)
                join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote' and m.status = 'ATIVO')
                          
           where c.idobjeto =" . $inidlote . " and c.tipoobjeto ='lote' and c.status NOT IN ('INATIVO' , 'ALIQUOTA') and c.qtdd>0 group by c.idlotecons order by p.fabricado,p.descr,l.idlote";
        $res = d::b()->query($sql);
        //echo($sql);
        while ($row = mysqli_fetch_assoc($res)) {

            $vqtdprod=($row['qtdproduzido'] * $row['valconvori']);

            if($vqtdprod < $row['qtdd']){
                $alerta='red';
                $title="O valor consumido é maior que o produzido deste produto.";
            }else{
                $alerta='';
                $title="";
            }


            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
                ?>

                <div title="<?=$title?>" class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>; color:<?=$alerta?>" lvl="<?=$lvl ?>">
                    <div class="col-md-2" title="Quantidade Utilizada">
                        <span href="#collapse-vallote-<?=$row['idlotecons'] ?>" class="pointer" title="Listar Insumos">
                            <i class="fa fa-angle-right" style="padding: 5px 10px;"></i>
                        </span>
                        <?
                        if(empty($row["qtdd_exp"])){
                            echo number_format(tratanumero($row['qtdd']), 2, ',', '.');
                        }else{
                            echo recuperaExpoente(tratanumero($row['qtdd']),$row["qtdd_exp"]);
                        } 
                        ?> - <?=$row['un'] ?>
                    </div>
                    <div class="col-md-1" title="Partida Utilizada">
                        <a class="pointer" onclick="janelamodal('?_modulo=<?=$row['linkmodulo'] ?>&_acao=u&idlote=<?=$row['idlote'] ?>')"><?=$row['partida'] ?>/<?=$row['exercicio'] ?></a>
                    </div>
                    <div class="col-md-7" title="Produto Utilizado">
                        <a title="Produto Utilizado" class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')">
                            <?= $row['descr'] ?>
                        </a>
                    </div>
                    <div class="col-md-1" title="Valor R$ Unitário">
                        <span style="float:right" title="Valor R$ Unitário">
                            <?=number_format(tratanumero($row['vlrlote']), 4, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="col-md-1" idlotecons-valloteacumulado="<?=$row['idlotecons'] ?>" title="Valor em R$ utilizado"></div>
                    <?
                    // $valorlote=cprod::buscavalorlote($row['idlote'],$percentual,'Y');
                    // echo("R$: ".number_format(tratanumero($valorlote), 2, ',', '.'));
                    ?>

                </div>
                <div id="collapse-vallote-<?=$row['idlotecons'] ?>" class="hidden">
                    <?
                    $percentualcon = $row['qtdd'] / ($row['qtdproduzido'] * $row['valconvori']);
                    $percent = $percentual * $percentualcon;
                    cprod::listavalorlote($row['idlote'], $percent, $lvl + 1);
                    ?>
                </div>
                <?
            } elseif ($row['fabricado'] == 'N') {

                $valor = ($row['vlrlote'] * $row['qtdd']) * $percentual;
                $qtdcons = ($row['qtdd'] * $percentual);
                if ($valor > 0) { ?>
                    <div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?> ; color:<?=$alerta?>" lvl="<?=$lvl ?>">
                        <div class="col-md-2" title="Quantidade Utilizada">
                            <span>
                                <i class="fa" style="padding: 5px 12px;"></i>
                            </span>
                            <? //echo('('.$row['qtdd'].'*'.$percent.')= ')
                            ?>
                            <? //=($row['qtdd']*$percent)
                            ?>
                            <?
                             if(empty($row["qtdd_exp"])){
                                echo number_format(tratanumero($qtdcons), 2, ',', '.'); 
                            }else{
                                echo recuperaExpoente(tratanumero($qtdcons),$row["qtdd_exp"]);
                            }
                            ?> - <?=$row['un'] ?>
                        </div>
                        <div class="col-md-1" title="Lote Utilizado">
                            <a title="Lote Utilizado" class="pointer" onclick="janelamodal('?_modulo=<?=$row['linkmodulo'] ?>&_acao=u&idlote=<?=$row['idlote'] ?>')">
                                <span><?=$row['partida'] ?>/<?=$row['exercicio'] ?></span>
                            </a>
                        </div>
                        <div class="col-md-7" title="Produto Utilizado">
                            <a title="Produto Utilizado" class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')">
                                <?=$row['descr'] ?>
                            </a>
                        </div>
                        <div class="col-md-1" title="Valor Unitário R$">
                            <span style="float:right" title="Valor Unitário R$">
                                <?=number_format(tratanumero($row['vlrlote']), 4, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="col-md-1" vallote="<?=$valor ?>" title=" R$: <?=$valor ?> / Valor Lote R$:<?=$row['vlrlote'] ?>">
                            <? //echo('('.$row['vlrlote'].'*'.$row['qtdd'].')*'.$percent.')= ')
                            ?>
                            <span style="float:right" title=" R$: <?=$valor ?> / Valor Lote R$:<?=$row['vlrlote'] ?>">
                                <?=number_format(tratanumero($valor), 4, ',', '.'); ?>
                            </span>
                            <? //=$valor
                            ?>
                        </div>
                    </div>
                <? }
            }
        }
    }
    
    static public function buscartestes($inidlote)
    {
        $sql = "
            SELECT 
                round(sum(c.qtdd*l.vlrlote),4)as valor,
                c.idobjeto as idresultado,
                r.quantidade,
                am.idregistro,
                am.exercicio,
                p.descr, 
                p.idprodserv
            FROM loteativ a
            JOIN objetovinculo o ON o.tipoobjetovinc  = 'loteativ' AND o.idobjetovinc = a.idloteativ
            JOIN lotecons c ON c.idobjeto=o.idobjeto AND c.tipoobjeto='resultado' AND c.qtdd >0 AND c.status='ABERTO'
            JOIN lote l ON l.idlote=c.idlote
            JOIN resultado r ON r.idresultado=c.idobjeto 
            JOIN amostra am ON am.idamostra=r.idamostra
            JOIN prodserv p ON p.idprodserv=r.idtipoteste
            WHERE  a.idlote='{$inidlote}'
            GROUP BY idresultado
            UNION
            select
				ifnull(r.custo,0) as valor,
				r.idresultado,
                r.quantidade,
				am.idregistro,
				am.exercicio,
				p.descr,
				p.idprodserv
            from loteativ at 
                join  bioensaio b on(b.idloteativ) =at.idloteativ
                join analise a on(a.idobjeto = b.idbioensaio AND a.objeto = 'bioensaio')
                join servicoensaio s on( s.idobjeto = a.idanalise AND s.tipoobjeto ='analise')
                join resultado r on(r.idservicoensaio = s.idservicoensaio and r.status != 'CANCELADO')
                join prodserv p on(p.idprodserv = r.idtipoteste)
                join amostra am on(am.idamostra = r.idamostra)
                where at.idlote='{$inidlote}'
            ;
        ";
        $res = d::b()->query($sql);

        //echo($sql);
        $lista = '';
        $valortotal = 0;
        if(mysqli_num_rows($res)){
            while ($row = mysqli_fetch_assoc($res)){
                $valorformatado = number_format(tratanumero($row['valor']), 4, ',', '.');
                $valortotal += $row['valor']*$row['quantidade'];
                $valortotalformatado = number_format(tratanumero($row['valor']*$row['quantidade']), 4, ',', '.');
                $lista .= "
                <div class='col-md-12' style='border-bottom: 1px solid #cec8c8b3;'>
                    <div class='col-md-1' title='Quantidade'>
                        <i class='fa' style='padding: 5px 12px;'></i>
                        x{$row['quantidade']}
                    </div>
                    <div class='col-md-1' title='Resultado'>
                        <a title='Resultado' class='pointer'>
                            {$row['idresultado']}
                        </a>
                    </div>
                    <div class='col-md-1' title='Registro'>
                        <a title='Registro' class='pointer'>
                            {$row['idregistro']} / {$row['exercicio']}
                        </a>
                    </div>
                    <div class='col-md-7' title='Produto Utilizado'>
                        <a title='Produto Utilizado' class='pointer' onclick='janelamodal('?_modulo=prodserv&_acao=u&idprodserv={$row['idprodserv']}')'>
                            {$row['descr']}
                        </a>
                    </div>
                    <div class='col-md-1' title='Valor unitário R$'>
                        <span style='float:right' title='Valor unitário R$'>
                            {$valorformatado}
                        </span>
                    </div>
                    <div class='col-md-1' vallote='{$row['valor']}' title='Valor total R$'>
                        <span style='float:right' title='Valor total R$'>
                            {$valortotalformatado}
                        </span>
                    </div>
                </div>
                ";
            }
        }
        return ['valor' => $valortotal, 'lista' => $lista];
    }

    static public function buscarateios($inidlote){
        $sql = "
            SELECT
            lc.idlotecusto,
            lc.idlote,
            lc.idrateiocusto,
            lc.idempresa,
            lc.idobjeto AS idunidade,
            lc.criadoem,
            lc.valor,
            e.sigla,
            e.empresa,
            u.unidade,
            u.tipocusto,
            rc.datainicio,
            rc.datafim
        FROM
            lotecusto lc
            JOIN empresa e ON e.idempresa = lc.idempresa
            JOIN unidade u ON u.idunidade = lc.idobjeto AND lc.tipoobjeto = 'unidade'
            JOIN rateiocusto rc ON rc.idrateiocusto = lc.idrateiocusto
        WHERE
            lc.idlote = '{$inidlote}'
            ";
        
        $res = d::b()->query($sql);

        $valortotal = 0;
        $lista = '';
        if(mysqli_num_rows($res)){
            while ($row = mysqli_fetch_assoc($res)){
                $valortotal += $row['valor'];
                $valortotalformatado = number_format(tratanumero($row['valor']), 4, ',', '.'); ;
                $row['tipocusto'] = $row['tipocusto']=='CI'?'Custo Indireto':'Custo Direto';
                $lista .= "
                    <div class='col-md-12' style='border-bottom: 1px solid #cec8c8b3;'>
                        <div class='col-md-1' title=''>
                            {$row['datainicio']}
                        </div>
                        <div class='col-md-1' title=''>
                            {$row['datafim']}
                        </div>
                        
                        <div class='col-md-2' title='Empresa'>
                            <a title='Empresa' class='pointer'>
                                {$row['empresa']}
                            </a>
                        </div>
                        <div class='col-md-5' title='Empresa'>
                            <a title='Empresa' class='pointer'>
                                {$row['unidade']}
                            </a>
                        </div>
                        <div class='col-md-2' title='Tipo do custo'>
                            <span title='Tipo do custo' class='pointer' onclick=''>
                                {$row['tipocusto']}
                            </span>
                        </div>
                        <div class='col-md-1' title='Valor unitário R$'>
                            <span style='float:right' title='Valor unitário R$'>
                                {$valortotalformatado}
                            </span>
                        </div>
                    </div>
                ";
            }
        }

        return ['valor' => $valortotal, 'lista' => $lista];
    }

    static public function buscavalortestes($inidlote){
        $sql = "
            SELECT round(sum(valor),4) as valor
                from (
                SELECT round(sum(c.qtdd*l.vlrlote*r.quantidade),4) as valor
                FROM loteativ a
                JOIN objetovinculo o ON o.tipoobjetovinc  = 'loteativ' AND o.idobjetovinc = a.idloteativ
                JOIN lotecons c ON c.idobjeto=o.idobjeto AND c.tipoobjeto='resultado' AND c.qtdd >0 AND c.status='ABERTO'
                JOIN lote l ON l.idlote=c.idlote and l.status!='CANCELADO'
                JOIN resultado r ON r.idresultado=c.idobjeto
                JOIN amostra am ON am.idamostra=r.idamostra
                JOIN prodserv p ON p.idprodserv=r.idtipoteste
                WHERE  a.idlote='{$inidlote}'
                GROUP BY idregistro
                UNION
                select round(ifnull(r.custo,0)*r.quantidade,4) as valor
                from loteativ at 
                join  bioensaio b on(b.idloteativ) =at.idloteativ
                join analise a on(a.idobjeto = b.idbioensaio AND a.objeto = 'bioensaio')
                join servicoensaio s on( s.idobjeto = a.idanalise AND s.tipoobjeto ='analise')
                join resultado r on(r.idservicoensaio = s.idservicoensaio and r.status != 'CANCELADO')
                join prodserv p on(p.idprodserv = r.idtipoteste)
                join amostra am on(am.idamostra = r.idamostra)
                where at.idlote='{$inidlote}'
            ) as testes
        ";
        
        $res = d::b()->query($sql);
            
        if(mysqli_num_rows($res)){
            $row = mysqli_fetch_assoc($res);
            if($row['valor']){
                return $row['valor'];
            }
        }
        
        return 0;
    }
    
    static public function buscavalorrateios($inidlote){
        $sql = "
        SELECT SUM(lc.valor) as valor
        FROM
            lotecusto lc
            JOIN empresa e ON e.idempresa = lc.idempresa
            JOIN unidade u ON u.idunidade = lc.idobjeto AND lc.tipoobjeto = 'unidade'
            JOIN rateiocusto rc ON rc.idrateiocusto = lc.idrateiocusto
        WHERE
            lc.idlote = '{$inidlote}'
            ";
        
        $res = d::b()->query($sql);
    
        if(mysqli_num_rows($res)){
            $row = mysqli_fetch_assoc($res);
            if($row['valor']){
                return $row['valor'];
            }
        }
        return 0;
    }
}
?>