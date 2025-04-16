<?
class VwEventoQuery
{
    public static function buscarSolicitantesDeEventos()
    {
        return "SELECT distinct e.criadopor, p.nome
                FROM evento e
                JOIN pessoa p on(p.usuario = e.criadopor)
                ORDER BY p.nome";
    }
}

?>