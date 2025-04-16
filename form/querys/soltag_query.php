<?

class SolTagQuery
{
    public static function buscarIdTransacao()
    {
        return "select FLOOR(RAND()*1000000000) as idtransacao";
    }
}

?>