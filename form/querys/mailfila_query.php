<?
class MailFilaQuery
{
	public static function buscarMailFila()
    {
        return  "SELECT m.idmailfila, m.idsubtipoobjeto
				   FROM mailfila m
				  WHERE m.tipoobjeto in('cotacao', 'cotacaoaprovada')
				    AND m.idsubtipoobjeto IN (?idnfs?)
					AND m.subtipoobjeto = 'nf'
					?idempresa?
			   GROUP BY m.idsubtipoobjeto
			   ORDER BY idmailfila ASC";
    }

	public static function buscarMailFilaResultadoPorTipo()
    {
        return  "SELECT m.idmailfila
				from comunicacaoextitem i
						join comunicacaoext c on (c.idcomunicacaoext = i.idcomunicacaoext)
						join mailfila m on (m.idobjeto = c.idcomunicacaoext and m.tipoobjeto = 'comunicacaoext')
				where  i.tipoobjeto = 'resultado'
						and i.idobjeto = ?idresultado?
						and c.tipo = '?tipoemail?'
						and c.status != 'ERRO'
				ORDER BY
						idmailfila DESC limit 1";
    }

	public static function buscarMailFilaResultado()
    {
        return  "SELECT 
					m.idmailfila
				FROM
					mailfila m
					JOIN
					comunicacaoextitem c ON (c.idcomunicacaoext = m.idobjeto)
				WHERE
					m.tipoobjeto = 'comunicacaoext'
					AND c.idobjeto = ?idresultado?
					AND c.tipoobjeto = 'resultado'
					?getidempresa?
				ORDER BY
					idmailfila DESC LIMIT 1";
    }

	public static function aturalizarMailFilaPorSubTipoIdSubTipoObjeto()
    {
		return "UPDATE mailfila SET idobjeto = ?idobjeto? WHERE idsubtipoobjeto = ?idsubtipoobjeto? AND subtipoobjeto = '?subtipoobjeto?' AND tipoobjeto like '?tipoobjeto?%'";
	}

	public static function buscarEmailFilaNfPorId()
	{
		return "SELECT 
						m.idmailfila
					FROM
						mailfila m
					WHERE
						m.tipoobjeto = 'nfp'
							AND m.idobjeto = ?idnf?
					ORDER BY idmailfila DESC
					LIMIT 1";
	}

	public static function inserirMailfila()
	{
		return "INSERT into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
				values (?idempresa?,'?remetente?','?destinatario?','?queueid?','?status?',?idobjeto?,'?tipoobjeto?',?idsubtipoobjeto?,'?subtipoobjeto?',?idpessoa?,'?idenvio?','?enviadode?','?link?','?conteudoemail?',sysdate(),'?usuario?','?usuario?',sysdate())";
	}
}

?>