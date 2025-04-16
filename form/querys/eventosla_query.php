<?

class EventoSlaQuery
{
    public static function buscarEventoSlaPorIdEventoTipo()
    {
        return "SELECT * 
                FROM eventosla 
                WHERE ideventotipo = ?ideventotipo?
                ?status?
                ORDER BY servico,ideventosla";
    }
}

?>