<?
class InterpretacaoQuery
{
	public static function listarInterpretacoesRelacionadasServico()
	{
		return "SELECT i.idinterpretacao, i.titulo
				  FROM interpretacao i
				  WHERE i.status = 'ATIVO'
		AND NOT EXISTS(SELECT 1 FROM intertipoteste mtm
						WHERE mtm.idinterpretacao = i.idinterpretacao 
						  AND mtm.idtipoteste = ?idprodserv?)
			  ORDER BY i.titulo";
	}

	public static function listarInterpretacoesRelacionadasServicoSelecionadas()
	{
    return "SELECT it.idintertipoteste, i.titulo, i.idinterpretacao
              FROM intertipoteste it JOIN interpretacao i ON i.idinterpretacao = it.idinterpretacao
             WHERE it.idtipoteste = ?idtipoteste?
          ORDER BY titulo";
	}
}
?>