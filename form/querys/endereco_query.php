<?
class EnderecoQuery{

    public static function buscarEnderecoPorIdComnfscidadesiaf(){
        return "SELECT e.logradouro,
                        e.cep,
                        e.endereco,
                        e.numero,
                        e.complemento,
                        e.bairro,
                        c.cidade,
                        c.uf,
                        e.obsentrega
                FROM endereco e 
                    LEFT JOIN nfscidadesiaf c on (c.codcidade = e.codcidade )
                WHERE  e.idendereco= ?idendereco?";
    }

    public static function buscarCodcidadeCidade(){
        return "SELECT codcidade,cidade FROM nfscidadesiaf WHERE uf = '?uf?' ORDER BY cidade";
    }

    public static function buscarTipoEndereco(){
        return "SELECT idtipoendereco, tipoendereco FROM tipoendereco WHERE status = 'ATIVO' ORDER BY tipoendereco";
    }

    public static function buscarEnderecoPessoa()
    {
        return "SELECT * 
                FROM pessoa p INNER JOIN endereco e ON p.idpessoa = e.idpessoa
                JOIN nfscidadesiaf c ON e.codcidade = c.codcidade
                WHERE p.idpessoa = ?idpessoa?";
    }

    public static function buscarEnderecoFaturamentoPorPessoa()
    {
        return "SELECT 
                    e.idendereco, CONCAT(e.endereco, '-', e.uf) AS endereco
                FROM
                    endereco e,
                    tipoendereco t
                WHERE
                    t.idtipoendereco = e.idtipoendereco
                        AND t.faturamento = 'Y'
                        AND e.status = 'ATIVO'
                        AND e.idpessoa = ?idpessoa?";
    }

    public static function listarEnderecoFaturamentoPorPessoa()
    {
        return "SELECT DISTINCT
                    t.tipoendereco,
                    c.cidade,
                    e.logradouro,
                    e.endereco,
                    e.numero,
                    e.complemento,
                    e.bairro,
                    e.cep,
                    e.uf,
                    e.obsentrega
                FROM
                    nfscidadesiaf c,
                    endereco e,
                    tipoendereco t
                WHERE
                    c.codcidade = e.codcidade
                        AND t.faturamento = 'Y'
                        AND t.idtipoendereco = e.idtipoendereco
                        AND e.status = 'ATIVO'
                        AND e.idpessoa = ?idpessoa?";
    }

    public static function listarEnderecoFaturamentoPorId()
    {
        return "SELECT DISTINCT
                    t.tipoendereco,
                    c.cidade,
                    e.logradouro,
                    e.endereco,
                    e.numero,
                    e.complemento,
                    e.bairro,
                    e.cep,
                    e.uf,
                    e.obsentrega,
                    e.localizacao
                FROM nfscidadesiaf c JOIN endereco e ON e.codcidade = c.codcidade
                JOIN tipoendereco t ON t.idtipoendereco = e.idtipoendereco
               WHERE e.status = 'ATIVO'
                 AND e.idendereco = ?idendereco?";
    }

    public static function listarEnderecoPessoaPorTipo()
    {
        return "SELECT 
                    e.idendereco,
                    t.idtipoendereco,
                    CONCAT(e.logradouro,
                            ' ',
                            e.endereco,
                            ', ',
                            e.numero,
                            ' - ',
                            c.cidade,
                            '-',
                            e.uf,
                            ' (',t.tipoendereco,')') AS endereco
                FROM
                    endereco e,
                    tipoendereco t,
                    nfscidadesiaf c
                WHERE
                    t.idtipoendereco = e.idtipoendereco
                        AND c.codcidade = e.codcidade
                        AND e.status = 'ATIVO'
                        AND e.idtipoendereco IN (?idtipoendereco?)
                        AND e.idpessoa IN (?idpessoa?)
                ORDER BY t.idtipoendereco DESC";
    }

    
    public static function buscarEnderecoPessoaPorTipo()
    {
        return "SELECT DISTINCT
                    t.tipoendereco,
                    c.cidade,
                    e.logradouro,
                    e.endereco,
                    e.numero,
                    e.complemento,
                    e.bairro,
                    e.cep,
                    e.uf,
                    e.obsentrega
                FROM
                    nfscidadesiaf c,
                    endereco e,
                    tipoendereco t
                WHERE
                    c.codcidade = e.codcidade
                        AND t.idtipoendereco = e.idtipoendereco
                        AND e.status = 'ATIVO'
                        AND e.idtipoendereco IN (?idtipoendereco?)
                        AND e.idpessoa IN (?idpessoa?)";
    }

    public static function bucarEnderecoPorId()
    {
        return "SELECT 
                        *
                    FROM
                        endereco
                    WHERE
                        idendereco= ?idendereco?";
    }

    public static function buscarEnderecoFaturamentoPorId()
    {
        return "SELECT DISTINCT
                    t.tipoendereco,
                    c.cidade,
                    e.logradouro,
                    e.endereco,
                    e.numero,
                    e.complemento,
                    e.bairro,
                    e.cep,
                    e.uf,
                    e.obsentrega
                FROM
                    nfscidadesiaf c,
                    endereco e,
                    tipoendereco t
                WHERE
                    c.codcidade = e.codcidade
                        AND t.idtipoendereco = e.idtipoendereco
                        AND e.status = 'ATIVO'
                        AND e.idendereco = ?idendereco?";
    }


    public static function buscarRotaPorEndereco()
    {
        return "SELECT 
                    r.idrotaorigem, r.obs
                FROM
                    rotapara r,
                    endereco e
                WHERE
                    r.idpessoa = ?idpessoa?
                        AND e.codcidade = r.codcidade
                        AND e.idendereco = ?idendereco?";
    }

    public static function buscarEnderecoDeEntregaParaEmail()
    {
        return "SELECT c.cidade,
                        c.uf,
                        e.logradouro,
                        e.cep,
                        e.endereco,
                        e.numero,
                        e.complemento,
                        e.bairro,
                        e.obsentrega
				from endereco e,nfscidadesiaf c
				where e.idtipoendereco in (3,2)
				and c.codcidade = e.codcidade
				and e.status='ATIVO'
				and e.idendereco = ?idendrotulo?
				and e.idpessoa = ?idpessoa?";
    }

    public static function buscarEnderecoPessoaPorIdEndereco()
    {
        return "SELECT e.*, c.cidade, p.dddfixo, p.telfixo, p.email 
                FROM pessoa p INNER JOIN endereco e ON p.idpessoa = e.idpessoa
                JOIN nfscidadesiaf c ON e.codcidade = c.codcidade
                WHERE e.idendereco = ?idendereco?";
    }

    public static function buscarEnderecoPorIdpessoa(){
        return "SELECT CONCAT(e.logradouro,' ',e.endereco,', ',e.numero,' - ',e.bairro,' - ',c.cidade, '/', e.uf) AS endereco,
                       e.idtipoendereco,
                       e.idendereco,
                       CONCAT('(', p.dddfixo, ') ', p.telfixo) as telefone,
                       p.email
                  FROM endereco e JOIN pessoa p ON p.idpessoa = e.idpessoa
                  JOIN nfscidadesiaf c ON c.codcidade = e.codcidade
                 WHERE e.status = 'ATIVO'
                   AND e.idpessoa = ?idpessoa?";
    }

    public static function buscarEnderecoPorIdEndereco(){
        return "SELECT CONCAT(e.logradouro,' ',e.endereco,', ',e.numero,' - ',e.bairro,' - ',c.cidade, '/', e.uf) AS endereco
                  FROM endereco e JOIN pessoa p ON p.idpessoa = e.idpessoa
                  JOIN nfscidadesiaf c ON c.codcidade = e.codcidade
                 WHERE e.status = 'ATIVO'
                   AND e.idendereco = ?idendereco?";
    }
}
?>