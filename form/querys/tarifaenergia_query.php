<?
class PrecoEnergiaQuery 
{
	public static function buscarStatusTarifa()
	{
		return "SELECT t.status
                FROM tarifaenergiapadrao t WHERE t.idtarifaenergiapadrao = ?idtarifaenergiapadrao?";
	}

    public static function buscarValordePico()
	{
		return "SELECT 
				t.idtarifaenergiapico,
				t.valor,
				t.inicio,
				t.fim
                FROM tarifaenergiapico t WHERE t.idtarifaenergiapadrao = ?idtarifaenergiapadrao?";
	}

	public static function buscarIntervalosExistentes()
	{
		return "SELECT 
				t.inicio,
				t.fim
				FROM
					tarifaenergiapico t
				WHERE
					t.idtarifaenergiapadrao = ?idtarifaenergiapadrao?";
	}

	public static function BuscarTarifaAtivoParaVinculo()
	{
		return "SELECT
				t.idtarifaenergiapadrao,
				t.valor
                FROM tarifaenergiapadrao t WHERE t.status = 'ATIVO'";
	}
	
}
?>