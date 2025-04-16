<?

class ModulocomQuery implements DefaultQuery
{
	public static $table = "modulocom";
	public static $pk = "idmodulocom";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ["table" => self::$table,"pk"=>self::$pk]) ;
	}

    public static function inserir()
    {
        return "INSERT INTO modulocom (
                    idempresa, idmodulo, modulo, 
                    descricao,status, criadopor, criadoem, 
                    alteradopor, alteradoem
                )
                VALUES (
                    ?idempresa?, ?idmodulo?, '?modulo?', 
                    '?descricao?', '?status?', '?criadopor?', ?criadoem?, 
                    '?alteradopor?', ?alteradoem?
                );";
    }

    public static function buscarListaDeComentariosDoEvento()
    {
        return "SELECT
                    IF(p.nomecurto is null, p.nome, p.nomecurto) AS nomecurto,
                    e.* , 
                    et.anonimo,
                    if(ev.idpessoa = p.idpessoa, 'Y', 'N') as dono
                FROM modulocom e JOIN pessoa p ON(p.usuario=e.criadopor)
                JOIN evento ev ON ev.idevento = e.idmodulo AND e.modulo = 'evento'
                JOIN eventotipo et ON et.ideventotipo = ev.ideventotipo
                WHERE e.idmodulo = ?idevento?
                AND e.status = 'ATIVO' 
                ORDER BY e.criadoem desc";
    }

    public static function deletarPorIdEventoEDescricao()
    {
        return "DELETE FROM modulocom 
                WHERE modulo = 'evento' 
                AND idmodulo = ?idevento? 
                AND descricao
                LIKE '%?descricao?%'";
    }

    public static function buscarComentariosCoferenciaDeAmostra()
    {
        return "SELECT idmodulocom,
                        alteradoem,
                        alteradopor,
                        criadopor,
                        descricao
                FROM modulocom
                WHERE idmodulo = ?idamostra?
                        AND status = 'ATIVO'
                ORDER BY idmodulocom DESC";
    }

    public static function buscarStatusDosEventos()
    {
        return "SELECT 
                    idmodulocom, 
                    ec.idempresa, 
                    e.idevento, 
                    ec.descricao, 
                    ec.criadopor, 
                    ec.criadoem, 
                    ec.alteradopor, 
                    ec.alteradoem, 
                    nomecurto, 
                    es.rotuloresp AS STATUS,
                    et.anonimo, 
                    if(e.idpessoa = p.idpessoa, 'Y', 'N') AS dono
                FROM modulocom ec 
                JOIN evento e ON e.idevento = ec.idmodulo AND ec.modulo = 'evento'
                JOIN fluxostatus fs ON fs.idfluxostatus = e.idfluxostatus
                LEFT JOIN "._DBCARBON."._status es ON es.idstatus = fs.idstatus
                JOIN pessoa p ON p.usuario = ec.criadopor
                JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
                WHERE ec.idmodulo = '?idevento?'  
                AND ec.modulo = 'evento'
                ORDER BY ec.criadoem DESC";
    }

    public static function desativarComentario()
    {
        return "UPDATE modulocom SET status = 'INATIVO' where idmodulocom = ?idmodulocom? ";
    }

    public static function atualizarComentario()
    {
        return "UPDATE modulocom SET descricao = '?descricao?', alteradopor = '?usuario?', alteradoem = now() where idmodulocom = ?idmodulocom?";
    }
}

?>