<?
class IndicacaoUsoQuery
{
    public static function buscarInidicacaoUsoAtivo()
    {
        return "SELECT * FROM indicacaouso WHERE status = 'ATIVO'";
    }
}
?>