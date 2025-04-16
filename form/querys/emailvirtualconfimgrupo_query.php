<?

class EmailVirtualConfImGrupoQuery
{
    public static function buscarEmailsVirtuaisPorIdImGrupoEGetIdEmpresa()
    {
        return "SELECT e.idemailvirtualconfimgrupo,em.email_original,e.idemailvirtualconf
                FROM emailvirtualconfimgrupo e 
                JOIN emailvirtualconf em on (e.idemailvirtualconf = em.idemailvirtualconf)
                WHERE 1
                ?getidempresa?
                AND em.status = 'ATIVO'
                AND e.idimgrupo = '?idimgrupo?'";
    }
}

?>