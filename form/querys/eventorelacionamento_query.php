<?

class EventoRelacionamentoQuery
{
    public static function buscarEventoRelacionamentoPorIdEventoTipo()
    {
        return "SELECT * 
                  FROM eventorelacionamento 
                 WHERE ideventotipo = ?ideventotipo?
              ORDER BY CASE WHEN descricao IS NULL OR descricao = '' THEN 1 ELSE 0 END, descricao";
    }

    public static function buscarRelacionamento() {
        return "SELECT ideventorelacionamento, descricao 
                  FROM eventorelacionamento 
                 WHERE ideventotipo = ?ideventotipo?
                   AND status = 'A'
              ORDER BY descricao";
    }
}

?>