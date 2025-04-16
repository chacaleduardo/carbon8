<?
require_once(__DIR__."/_iquery.php");


class ResultadoProdservFormulaQuery{

    public static function verificarSeExisteRegistroNaTableaResultadoProdserFormula(){
        return "SELECT 
            count(1) as nresult
        FROM
            resultadoprodservformula
        WHERE
            idresultado = ?idresultado?";
    }

    public static function insertResultadoProdservFormula(){
        return "INSERT INTO `laudo`.`resultadoprodservformula` 
        (`idempresa`, `idresultado`, `idprodservformula`, `criadopor`, `criadoem`) 
        VALUES ( ?idempresa?, ?idresultado?, ?idprodservformula?, '?criadopor?', now());";
    }

    public static function buscarRegistroProdservFormulaServico(){
        return "SELECT 
            *
        FROM
            resultadoprodservformula
        WHERE
            idresultado = ?idresultado?
                AND status = 'ATIVO'
                AND idprodservformula = ?idprodservformula?";
    }


}
