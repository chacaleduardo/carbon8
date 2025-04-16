<?

class TagSalaQuery
{
    public static function buscarTagPaiPeloIdTagFilho()
    {
        return "SELECT t.*, ts.*
                FROM tagsala ts
                JOIN tag t ON(t.idtag = ts.idtagpai)
                WHERE ts.idtag = ?idtag?";
    }

    public static function atualizarIdTagPaiPeloIdTagSala()
    {
        return "UPDATE tagsala
                SET idtagpai = ?idtagpai?
                WHERE idtagsala = ?idtagsala?";
    }

    public static function inserirTagSala()
    {
        return "INSERT INTO tagsala
                (idtagsala, idempresa, idtag, idtagpai, criadopor, criadoem, alteradopor, alteradoem)
                VALUES(
                    null, ?idempresa?, ?idtag?, ?idtagpai?, '?criadopor?', '?criadoem?', '?alteradopor?', '?alteradoem?'
                )";
    }

    public static function buscarTagPaiOuFilho()
    {
        return "SELECT idtagsala, idtag, idtagpai
                FROM tagsala
                WHERE ?coluna? = ?valor?";
    }

    public static function buscarTagSalaPorColunaEValor()
    {
        return "SELECT idtagsala
                FROM tagsala
                WHERE ?coluna? = ?valor?";
    }

    public static function buscarTagSalaPorIdTag()
    {
        return "SELECT * FROM tagsala where idtag = ?idtag? ORDER BY criadoem DESC";
    }

    public static function buscarTagPorIdTag()
    {
        return "SELECT * FROM tagsala WHERE idtag = ?idtag? ORDER BY criadoem DESC";
    }

    public static function deletarTagSalaPorIdTag()
    {
        return "DELETE FROM tagsala WHERE idtag = ?idtag?";
    }
}

?>