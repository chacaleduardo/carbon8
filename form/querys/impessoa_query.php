<?

class ImPessoaQuery
{
    public static function inativarPessoasQueNaoForamInseridasManualmente()
    {
        return "UPDATE impessoa SET status = 'INATIVAR' WHERE inseridomanualmente='N'";
    }

    public static function ativarPessoasQueNaoForamInseridasManualmente()
    {
        return "UPDATE impessoa SET status = 'ATIVAR' WHERE inseridomanualmente = 'N' AND idpessoa = ?idpessoa?";
    }

    public static function deletarPessoasComStatusInativar()
    {
        return "DELETE FROM impessoa WHERE status = 'INATIVAR' AND inseridomanualmente='N'";
    }

    public static function ativarPessoasComStatusAtivar()
    {
        return "UPDATE impessoa SET status='ATIVO' WHERE status='ATIVAR' AND inseridomanualmente='N'";
    }
}

?>