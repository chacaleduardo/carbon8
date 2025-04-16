<?

class TagDimQuery
{
    public static function buscarTagDimPorIdTag()
    {
        return "SELECT td.*, t.caixa as maxcaixa 
                FROM tagdim td
                LEFT JOIN tag t ON(t.idtag = td.idtag)
                WHERE td.idtag=?idtag?";
    }    

    public static function inserirTagDim()
    {
        return "INSERT INTO tagdim (idempresa, idtag, coluna, linha, caixa, status, objeto, idobjeto, idprateleiradim, criadopor, criadoem, alteradopor, alteradoem)
                VALUES(?idempresa?, ?idtag?, ?coluna?, ?linha?, ?caixa?, '?status?', '?objeto?', ?idobjeto?, ?idprateleiradim?, '?usuario?', now(), '?usuario?', now())";
    }

    public static function buscarTagDimPorIdTagDim() {
        return "SELECT *
                FROM tagdim
                WHERE idtagdim IN (?idtagdim?)";
    }
}

?>