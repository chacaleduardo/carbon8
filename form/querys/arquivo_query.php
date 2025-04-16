<?
class ArquivoQuery
{
    public static function buscarArquivoPorTipoArquivoTipoobjetoIdobjeto()
    {
        return "SELECT idarquivo, nome, idobjeto, caminho, imagempadrao
				  FROM arquivo 
				 WHERE tipoarquivo = '?tipoarquivo?' AND tipoobjeto = '?tipoobjeto?' AND idobjeto IN (?idobjetos?)";
    }

    public static function buscarArquivoPorTipoArquivoTipoobjetoIdobjetoEvariosTipoObjeto()
    {
        return "SELECT idarquivo, nome, idobjeto, caminho, imagempadrao, tipoobjeto
				  FROM arquivo 
				 WHERE tipoarquivo = '?tipoarquivo?' AND tipoobjeto IN(?tipoobjeto?) AND idobjeto IN (?idobjetos?)";
    }

    public static function buscarAnexosPorTipoObjetoIdObjeto()
    {
        return "SELECT a.*,
                    dmahms(criadoem) as datacriacao 
                from arquivo a 
                where a.tipoobjeto = '?tipoobjeto?' 
                and a.idobjeto = ?idobjeto?
                and tipoarquivo = 'ANEXO' 
                order by idarquivo asc";
    }

    public static function buscarArquivoPorIdArquivoETipoObjeto()
    {
        return "SELECT nome, idobjeto as idevento
                FROM arquivo 
                WHERE tipoobjeto = '?tipoobjeto?' 
                AND idarquivo = ?idarquivo?";
    }

    public static function buscarArquivosNfPorIdNfItem()
    {
        return "SELECT a.*, dmahms(a.criadoem) as datacriacao 
                FROM arquivo a,nfitem ni
                WHERE a.tipoobjeto = 'nf' 
                AND a.idobjeto = ni.idnf
                AND ni.idnfitem = ?idnfitem?
                AND tipoarquivo = 'ANEXO'
                ORDER BY idarquivo ASC";
    }

    public static function buscarArquivoPorTipoObjetoEIdObjeto()
    {
        return "SELECT nome, idobjeto, caminho, idarquivo
                  FROM arquivo 
                 WHERE tipoobjeto = '?tipoobjeto?' 
                   AND idobjeto = ?idobjeto?";
    }

    public static function inserirArquivo(){
        return "INSERT INTO arquivo (idempresa, tipoarquivo, nomeoriginal, nome, caminho, tamanho, tamanhobytes, imagempadrao, idpessoa, idobjeto, tipoobjeto, criadoem) 
					    VALUES ('?idempresa?', '?tipoarquivo?', '?nomeoriginal?', '?nome?', '?caminho?', '?tamanho? KB', '?tamanhobytes?', '?imagempadrao?', '?idpessoa?', '?idobjeto?', '?tipoobjeto?', now())";
    }

    public static function apagarArquivoPorTipoArquivoObjetoETipoObjeto(){
        return "DELETE FROM arquivo WHERE idobjeto = '?idobjeto?' AND tipoobjeto = '?tipoobjeto?' AND tipoarquivo = '?tipoarquivo?';";
    }
    

}
