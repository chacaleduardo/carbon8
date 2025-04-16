<?

class NfentradaxmlQuery{
    public static function buscarObsporIdnfentradaxml()
    {
        return "SELECT obs FROM nfentradaxml WHERE idnfentradaxml = ?idnfentradaxml?";
    }
}
?>