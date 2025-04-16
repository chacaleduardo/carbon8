<?
class FormulaRotuloQuery implements DefaultQuery
{
    public static $table = 'tag';
    public static $pk = 'idtag';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function buscarFormulaRotuloPorIdProdservFormula()
    {
		return "SELECT * FROM formularotulo WHERE idprodservformula = ?idprodservformula?";
	}

    public static function buscarProdServEFormulaRotuloPorIdProdServFormula()
    {
        return "SELECT r.idformularotulo, rf.indicacao, rf.formula, rf.modousar, rf.cepas, rf.descricao, rf.conteudo, rf.programa
                FROM formularotulo r
                JOIN prodservformularotulo rf on rf.idprodservformularotulo = r.idprodservformularotulo
                WHERE r.idprodservformula = ?idprodservformula?";
    }
}

?>