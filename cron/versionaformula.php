<?
include_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");
require_once(__DIR__."/../form/controllers/formulaprocesso_controller.php");


$sql="select ps.idprodserv,p.versao,p.idprodservformula,p.idprodserv 
from prodservformulains i 
JOIN prodservformula p on(p.idprodservformula =i.idprodservformula and p.status != 'INATIVO' )
join prodserv ps on(ps.idprodserv=p.idprodserv and ps.especial='Y' and ps.status='ATIVO' AND ps.idempresa =2)
where i.idprodserv in (7626) and i.status='ATIVO'
and not exists(SELECT 1 FROM objetojson o WHERE o.idobjeto =  p.idprodservformula AND o.tipoobjeto = 'prodservformula' and o.criadopor ='@881281' )
 group by p.idprodservformula";
$re= d::b()->query($sql)or die("erro <p>SQL: ".$sql);
while($rw=mysqli_fetch_assoc($re)){

    if (!empty($rw['idprodservformula']) && !empty($rw['idprodserv'])) 
    {
        $idprodserformula = $rw['idprodservformula'];
        $versaoform = $rw['versao'];
        $idprodserv = $rw['idprodserv'];
            // FORMULA/INSUMOS
           
            $aForm = array();
            $sqlProdServ = FormulaProcessoController::retornarSqlProdserv($idprodserv);
            $aForm['prodserv']['sql'] = $sqlProdServ;
            $aForm['prodserv']['res'] = sql2array($sqlProdServ, true);
    
            $arrayforms = FormulaProcessoController::listarProdservFormulaPlantel($idprodserv);
            $aForm['prodservformula']['res'] = $arrayforms[$idprodserformula];
    
            $arrayObjetoJson = [
                "idempresa" => 2,
                "idobjeto" => $idprodserformula,
                "tipoobjeto" => 'prodservformula',
                "jobjeto" => base64_encode(serialize($aForm)),
                "versaoobjeto" => $versaoform,
                "criadopor" => '@881281',
                "alteradopor" => '@881281',
            ];
            FormulaProcessoController::inserirObjetoJson($arrayObjetoJson);
    
            $arrayAuditoria = [
                "idempresa" => 2,
                "linha" => 1,
                "acao" => 'i',
                "objeto" => 'objetojson',
                "idobjeto" => $idprodserformula,
                "coluna" => 'jobjeto',
                "valor" => base64_encode(serialize($aForm)),
                "criadopor" => '@881281',
                "tela" => $_SERVER["HTTP_REFERER"]
            ];
            FormulaProcessoController::inserirAuditoria($arrayAuditoria);
    
            //PROCESSO/INSUMOS
            $aPROCIN = array();
            $sqlProcessos = FormulaProcessoController::retornarProcessosPorIdProdserv($idprodserv);
            $aPROCIN['prodservprproc']['sql'] = $sqlProcessos;
            $aPROCIN['prodservprproc']['res'] = sql2array($sqlProcessos, true, array(), true);
    
            foreach($aPROCIN['prodservprproc']['res'] as $key => $value) 
            {
                $sqlPrativ = FormulaProcessoController::buscarSqlProcessosPorIdProdservPrProc($value['idprodservprproc']);
                $aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['sql'] = $sqlPrativ;
                $aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res'] = sql2array($sqlPrativ, true, array(), true);
                foreach ($aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res'] as $k => $val) 
                {
                    $insumos = FormulaProcessoController::buscarInsumosEFormulasProdsev($val['idprativ']);
                    if($insumos['qtdLinhas'] > 0) 
                    {
                        $aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res']['prativ'][$val['idprativ']]['sql'] =  $insumos['sql'];
                        $aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res']['prativ'][$val['idprativ']]['res'] = sql2array($insumos['sql'], true, array(), true);
                    }
                }
    
                $arrayObjetoJson = [
                    "idempresa" => 2,
                    "idobjeto" => $value['idprodservprproc'],
                    "tipoobjeto" => 'prodservprproc',
                    "jobjeto" => base64_encode(serialize($aPROCIN)),
                    "versaoobjeto" => $value['versao'],
                    "criadopor" => '@881281',
                    "alteradopor" => '@881281',
                ];
                FormulaProcessoController::inserirObjetoJson($arrayObjetoJson);
    
                $arrayAuditoria = [
                    "idempresa" => 2,
                    "linha" => 1,
                    "acao" => 'i',
                    "objeto" => 'objetojson',
                    "idobjeto" => $value['idprodservprproc'] ,
                    "coluna" => 'jobjeto',
                    "valor" => base64_encode(serialize($aPROCIN)),
                    "criadopor" => '@881281',
                    "tela" => $_SERVER["HTTP_REFERER"]
                ];
                FormulaProcessoController::inserirAuditoria($arrayAuditoria);
    
                FormulaProcessoController::atualizarVersaoProdservPrProc($value['idprodservprproc']);
            }
            //PROCESSO/INSUMOS
        
    }



}

echo("FIM");

?>