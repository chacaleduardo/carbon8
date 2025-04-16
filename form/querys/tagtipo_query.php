<?
class TagTipoQuery
{
    public static function listarTagsTipoNaoVinculadasAoTagTipo(){
        return "SELECT tagtipo as nome, idtagtipo
		    FROM tagtipo t 
		    WHERE t.status = 'ATIVO'
			    AND NOT EXISTS(
				    SELECT 1
				FROM objetovinculo ov 
				WHERE ov.tipoobjeto = 'tagtipo' 
					AND ov.idobjeto = ?idtagtipo?
					AND ov.idobjetovinc = t.idtagtipo
					AND ov.tipoobjetovinc = 'tagtipo'
                )
            ORDER BY tagtipo";
    }

	public static function listarTagsTipoLocalizacaoNaoVinculadasAoTagTipo(){
        return "SELECT tagtipo AS nome, idtagtipo
			FROM tagtipo t 
			WHERE t.status = 'ATIVO'
				AND NOT EXISTS(
					SELECT 1 
					FROM objetovinculo ov 
					WHERE ov.tipoobjetovinc = 'tagtipo' 
						AND ov.idobjetovinc = ?idtagtipo?
						AND ov.idobjeto = t.idtagtipo
						AND ov.tipoobjeto = 'tagtipo'
				) 
			ORDER BY tagtipo";
    }

	public static function buscarPorIdTagTipo(){
        return "SELECT *, idtagclass as grupo FROM tagtipo WHERE idtagtipo = ?idtagtipo?";
    }
	
	public static function buscarAtividadesVinculadasPorIdTagTipo(){
        return "SELECT 
				pa.idprativ,
				pa.ativ,
				pa.descr
			FROM tagtipo tt
				JOIN prativobj pro ON(tt.idtagtipo = pro.idobjeto AND pro.tipoobjeto = 'tagtipo')
				JOIN prativ pa ON (pa.idprativ = pro.idprativ)
			WHERE pa.status <> 'INATIVO'
				AND tt.idtagtipo = ?idtagtipo?
			ORDER BY pa.ativ";
    }

	public static function buscarTagTipoPorIdTagClass()
	{
		return "SELECT tt.*, concat(e.sigla, ' - ', tt.tagtipo) as 'tagtiposigla'  
				FROM tagtipo tt
				JOIN empresa e ON(e.idempresa = tt.idempresa)
				WHERE tt.idtagclass = ?idtagclass?
				AND tt.status = 'ATIVO'
				UNION 
				SELECT tt.*, if(tt.status = 'INATIVO', concat(e.sigla, ' - ', tt.tagtipo,' (Inativo)'), concat(e.sigla, ' - ', tt.tagtipo)) as 'tagtiposigla'  
				FROM tagtipo tt
				JOIN empresa e ON(e.idempresa = tt.idempresa)
				WHERE tt.idtagtipo = '?idtagtipo?'
				ORDER BY tagtipo";
	}

	public static function buscarTodosTagTipo()
	{
		return "SELECT idtagtipo, CONCAT(e.sigla, ' - ', t.tagtipo) AS tagtipo
				FROM tagtipo t
				JOIN empresa e ON e.idempresa = t.idempresa
				WHERE t.status = 'ATIVO'
				ORDER BY t.tagtipo;";
	}
	public static function buscarTodosTagTipoComObjetoVinculoResultado ()
	{
		return "SELECT idtagtipo, CONCAT(e.sigla, ' - ', t.tagtipo) AS tagtipo, o.idobjeto
				FROM tagtipo t
				JOIN empresa e ON e.idempresa = t.idempresa
				WHERE t.status = 'ATIVO'
				ORDER BY t.tagtipo;";
	}

	public static function buscarTagClassPorTipoObjetoEIdPrativ()
	{
			return "SELECT o.idprativobj, o.idobjeto, t.tagtipo 
					  FROM prativobj o JOIN tagtipo t ON t.idtagtipo = o.idobjeto
					 WHERE t.idtagclass = ?idtagclass?
					   AND o.tipoobjeto = '?tipoobjeto?'
					   AND o.idprativ = ?idprativ?";
	}

	public static function buscarTagPorIdTagClass()
	{
		return "SELECT t.idtagtipo, tagtipo
				  FROM tagtipo t
				 WHERE t.idtagclass = ?idtagclass? AND t.status = 'ATIVO'
					-- Sala configurada corretamente
				  AND EXISTS(SELECT 1 FROM tag t2  WHERE t2.idtagtipo = t.idtagtipo)
			 ORDER BY tagtipo";
	}

	public static function buscarTagPorIdTagClassEStatus()
	{
		return "SELECT t.idtagtipo, t.tagtipo
				  FROM tagtipo t
				 WHERE t.idtagclass = ?idtagclass? AND t.status = 'ATIVO'
			  ORDER BY t.tagtipo";
	}

	public static function buscarTagTipoPorIdTagClassEShare()
	{
		return "SELECT t.idtagtipo, t.tagtipo
				  FROM tagtipo t
				 WHERE t.idtagclass = ?idtagclass? AND t.status = 'ATIVO'
				 ?tagTipoAtividade?
			  ORDER BY t.tagtipo";
	}

	public static function buscarTagTipoSemVinculo()
	{
		return "SELECT * from tagtipo where status = 'ATIVO' and ((idtagclass = '' ) or idtagclass is null)";
	}

	public static function buscarTipoPorIdTagSala() {
		return  "SELECT tt.idtagtipo, CONCAT(e.sigla, '-', tt.tagtipo) as tagtipo
				FROM tagtipo tt
				JOIN empresa e ON e.idempresa = tt.idempresa
				WHERE EXISTS (
					SELECT 1
					FROM tagsala ts
					JOIN tag t on t.idtag = ts.idtag
					WHERE ts.idtagpai = ?idtagpai?
					AND tt.idtagtipo = t.idtagtipo 
				)";
	}
}
?>