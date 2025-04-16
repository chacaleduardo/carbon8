<?
require_once(__DIR__."/_iquery.php");

class PlantelObjetoQuery
{
    public static function buscarPlantelObjeto()
    {
        return "SELECT IFNULL(GROUP_CONCAT(idplantel), 0) AS planteis
                  FROM plantelobjeto
                 WHERE tipoobjeto = '?tipoobjeto?'
                   AND idobjeto = ?idobjeto?";
    }

    public static function inserirPlantelObjeto()
    {
        return "INSERT INTO plantelobjeto (
            idobjeto, tipoobjeto, idplantel, idempresa, criadopor,
            criadoem, alteradopor, alteradoem
        ) VALUES (
            ?idobjeto?, '?tipoobjeto?', ?idplantel?, ?idempresa? ,'?criadopor?',
            '?criadoem?', '?alteradopor?', '?alteradoem?'
        )";
    }

    public static function deletarPlanteisDeUmObjeto()
    {
        return "DELETE from plantelobjeto where idobjeto = ?idobjeto? and tipoobjeto = '?tipoobjeto?'";
    }
}
