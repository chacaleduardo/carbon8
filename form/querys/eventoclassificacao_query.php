<?
class EventoClassificacaoQuery
{
    public static function buscarClassificacaoPorId()
    {
        return "SELECT p.idpessoa, c.*
                FROM eventoclassificacao c
                JOIN pessoa p ON(p.usuario = c.responsavel)
                WHERE c.id = '?id?'";
    }

    public static function buscarClassificacoes()
    {
        return "SELECT id, classificacao
                FROM eventoclassificacao
                ORDER BY classificacao";
    }
}

?>