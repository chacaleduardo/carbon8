<?

class PessoaSgfuncaoQuery
{
    public static function inserirPessoaSgfuncao()
    {
        return "INSERT INTO pessoasgfuncao (
                    idempresa, idpessoa, idsgfuncao, status, criadopor,
                    criadoem, alteradopor, alteradoem
                )
                VALUES (
                    ?idempresa?, ?idpessoa?, ?idsgfuncao?, '?status?', '?criadopor?',
                    ?criadoem?, '?alteradopor?', ?alteradoem?
                )";
    }

    public static function deletarPorIdPessoaSgfuncao()
    {
        return "DELETE FROM pessoasgfuncao WHERE idpessoasgfuncao = ?idpessoasgfuncao?";
    }
}

?>