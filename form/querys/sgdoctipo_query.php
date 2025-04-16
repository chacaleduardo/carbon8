<?

class SgDocTipoQuery
{
    public static function buscarTodosSgDocTipo()
    {
        return "SELECT s.idsgdoctipo, CONCAT(e.sigla, ' - ', s.rotulo) AS rotulo
                FROM sgdoctipo s
                JOIN empresa e ON e.idempresa = s.idempresa
                WHERE s.status = 'ATIVO'
                ORDER BY s.rotulo;";
    }
}

?>