<?

class EventoChecklistQuery
{
    public static function inserirEventoChecklist()
    {
        return "INSERT INTO eventochecklist (titulo, idempresa, idevento, criadopor, criadoem, alteradopor, alteradoem)
                VALUES('?titulo?', ?idempresa?, ?idevento?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?)";
    }

    public static function buscarChecklistPorIdEvento()
    {
        return "SELECT *
                FROM eventochecklist
                WHERE idevento = ?idevento?;";
    }
}

?>