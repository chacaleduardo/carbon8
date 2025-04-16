<?
class ModuloHistoricoQuery
{
    public static function buscarHistoricoAlteracao()
	{
        return "SELECT h.valor, 
					   valor_old,
					   h.justificativa,
					   h.criadoem,
					   p.nomecurto,
					   h.campo
				  FROM modulohistorico h JOIN pessoa p ON (p.usuario = h.criadopor)
				 WHERE h.idobjeto = ?idobjeto? AND h.tipoobjeto = '?tipoobjeto?' ?campo?
			  ORDER BY h.criadoem DESC 
			     LIMIT 15";
    }

	public static function buscarHistoricoSped()
	{
        return "SELECT valor, 
					   criadopor, 
					   criadoem
				  FROM modulohistorico
				 WHERE idobjeto = ?idobjeto?
				   AND tipoobjeto = '?tipoobjeto?'
				   AND campo = '?campo?'
			  ORDER BY criadoem DESC";
    }

	public static function inserirHistorico() {
		return "INSERT INTO 
					modulohistorico (campo, idobjeto, tipoobjeto, valor_old, valor, justificativa, idempresa, criadopor, criadoem) 
				VALUES('?campo?', '?idobjeto?', '?tipoobjeto?', '?valor_old?', '?valor?', '?justificativa?', ?idempresa?, '?criadopor?', NOW())";
	}
}
?>