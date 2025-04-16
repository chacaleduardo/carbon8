<?
class NfItemComissaoQuery{
    public static function buscarPessoasComissaoNf() {
        return "SELECT IFNULL(p.nomecurto, p.nome) AS nome, SUM(pcomissao) AS pcomissaototal, c.idpessoa
                  FROM nfitemcomissao c JOIN pessoa p ON (p.idpessoa = c.idpessoa)
                  JOIN nfitem ni ON ni.idnfitem = c.idnfitem
                 WHERE ni.idnf = ?idnf?
              GROUP BY idpessoa
              ORDER BY nome";
    }

    public static function deletarNfitemComissao(){
        return "DELETE FROM nfitemcomissao 
                WHERE
                    idnfitem =?idnfitem?";
    }
}

?>
 