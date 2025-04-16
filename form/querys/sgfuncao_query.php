<?

class SgfuncaoQuery
{
    public static function buscarFuncoesDiponiveisParaVinculoPorIdSgcargo()
    {
        return "SELECT sf.idsgfuncao,sf.funcao
                FROM sgfuncao sf
                WHERE sf.status='ATIVO' 
                AND NOT EXISTS(
                    SELECT 1
                    FROM sgcargofuncao v
                    WHERE  v.idsgcargo = ?idsgcargo?
                    AND v.idsgfuncao=sf.idsgfuncao		
                    AND	v.status = 'ATIVO'					
                )
                ORDER BY sf.funcao";
    }
}

?>