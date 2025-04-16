<?

require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/bannerlogin_query.php");

class BannerLoginController extends Controller
{
    public static function buscarBanners($data = '', $todos = false)
    {
        $condicaoData = $data ? "AND (('{$data}' BETWEEN b.datainicio AND b.datafim) OR ('{$data}' >= b.datainicio AND isnull(b.datafim))) " : '';
        $condicaoStatus = "where b.status = 'ATIVO'";

        if ($todos) $condicaoStatus = '';

        $banners = SQL::ini(BannerLoginQuery::buscarBannerPorIdEmpresa(), [
            'condicaoData' => $condicaoData,
            'condicaoStatus' => $condicaoStatus
        ])::exec();

        if ($banners->error()) {
            parent::error(__CLASS__, __FUNCTION__, $banners->errorMessage());
            return [];
        }

        $arrRetorno = [
            'desktop' => array_filter($banners->data, function ($item) {
                return $item['tipoarquivo'] == 'BANNER';
            }),
            'mobile' => array_filter($banners->data, function ($item) {
                return $item['tipoarquivo'] == 'BANNERMOBILE';
            })
        ];

        return $arrRetorno;
    }

    public static function buscarBannersPorIdBannerLogin($idBannerLogin)
    {
        $banners = SQL::ini(BannerLoginQuery::buscarBannersPorIdBannerLogin(), [
            'idbannerlogin' => $idBannerLogin
        ])::exec();

        if ($banners->error()) {
            parent::error(__CLASS__, __FUNCTION__, $banners->errorMessage());
            return [];
        }

        $arrRetorno = [
            'desktop' => array_filter($banners->data, function ($item) {
                return $item['tipoarquivo'] == 'BANNER';
            }),
            'mobile' => array_filter($banners->data, function ($item) {
                return $item['tipoarquivo'] == 'BANNERMOBILE';
            })
        ];

        return $arrRetorno;
    }
}
