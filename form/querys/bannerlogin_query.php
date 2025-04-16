<?

class BannerLoginQuery
{
    public static function buscarBannerPorIdEmpresa()
    {
        return "SELECT b.idbannerlogin, a.idarquivo, a.tipoarquivo, a.caminho, b.titulo, b.datainicio, b.datafim, a.nomeoriginal
                from bannerlogin b
                join arquivo a ON a.idobjeto = b.idbannerlogin and a.tipoobjeto = 'bannerlogin'
                ?condicaoStatus?
                ?condicaoData?";
    }

    public static function buscarBannersPorIdBannerLogin()
    {
        return "SELECT b.idbannerlogin, a.idarquivo, a.tipoarquivo, a.caminho, b.titulo, b.datainicio, b.datafim, a.nomeoriginal
                from bannerlogin b
                join arquivo a ON a.idobjeto = b.idbannerlogin and a.tipoobjeto = 'bannerlogin'
                WHERE b.idbannerlogin = ?idbannerlogin?";
    }
}
