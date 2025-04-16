<?
class ProdservFormulaRotuloQuery implements DefaultQuery
{
	public static $table = 'prodservformularotulo';
    public static $pk = 'idprodservformularotulo';
	
	public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    } 

	public static function buscarFormulaRotulo()
	{
		return "SELECT *
				from prodservformularotulo";
	}
}
?>