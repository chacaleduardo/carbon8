<?
class RheventoFolhaItemQuery
{
	public static function buscarContaItemRhEventoFolhaPessoa()
	{
		return "SELECT refi.idrheventofolhaitem, 
					   CONCAT(rte.evento, ' - ', p.nome) AS descricao,
					   rte.evento,
					   tp.idpessoa,
					   refi.idtipoprodserv,
					   refi.idcontaitem
				  FROM pessoa p JOIN rheventofolha ref ON ref.idrheventofolha = p.idrheventofolha
				  JOIN rheventofolhaitem refi ON refi.idrheventofolha = ref.idrheventofolha
				  JOIN rhtipoevento rte ON rte.idrhtipoevento = refi.idrhtipoevento
				  JOIN tipoprodserv tp ON tp.idtipoprodserv = refi.idtipoprodserv
				 WHERE ?campoColuna? = ?campores?
				   AND tp.app = 'Y'
			  ORDER BY descricao";
	}
}
?>