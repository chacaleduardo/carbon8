<?

class AssinaturaEmailCamposQuery
{
    public static function buscarAssinaturaEmailCamposPorIdObjeto()
    {
        return "SELECT * FROM assinaturaemailcampos WHERE tipoobjeto = '?tipoobjeto?' AND idobjeto = ?idobjeto?";
    }

    public static function buscarAssinaturaEmailCamposPorIdObjetoEGetIdEmpresa()
    {
        return "SELECT * FROM assinaturaemailcampos WHERE tipoobjeto = '?tipoobjeto?' AND idobjeto = ?idobjeto? ?getidempresa?";
    }
}

?>