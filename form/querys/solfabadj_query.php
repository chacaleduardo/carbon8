<?
class SolfabDjQuery
{ 
    public static function buscarPessoasLigadasSolfabAdjacente()
    {
        return "SELECT GROUP_CONCAT(s.idpessoa) AS idadjacente
                  FROM solfabadj s
                 WHERE s.idsolfab = ?idsolfab?";

    }

    public static function buscarNomePessoasLigadasSolfabAdjacente()
    {
        return "SELECT p.nome, s.*
                  FROM solfabadj s JOIN pessoa p ON (p.idpessoa = s.idpessoa)
                 WHERE s.idsolfab = ?idsolfab?";
    }
}
?>