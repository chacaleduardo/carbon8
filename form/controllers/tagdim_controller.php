<?
require_once(__DIR__."/../../inc/php/functions.php");

// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/tagdim_query.php");
require_once(__DIR__."/../../form/querys/log_query.php");

// CONTROLLERS
require_once(__DIR__.'/_controller.php');

class TagDimController extends Controller
{
    public static function inserirPrateleiraNaTagDim($idtag, $linha, $coluna, $caixa)
    {
        if(!d::b()->query("START TRANSACTION"))
        {
            return false;
        }

        $linha  = (int)$linha;
        $coluna = (int)$coluna;
        $caixa = (int)$caixa;
        
        for($posicaoColuna = 0; $posicaoColuna <= $coluna; $posicaoColuna++)
        {
            for($posicaoLinha = 1; $posicaoLinha <= $linha; $posicaoLinha++)
            {
                for($posicaoCaixa = 1; $posicaoCaixa <= $caixa; $posicaoCaixa++)
                {
                    $dadosParaInsert = [
                        'idempresa' => cb::idempresa(),
                        'idtag' => $idtag,
                        'coluna' => $posicaoColuna,
                        'linha' => $posicaoLinha,
                        'caixa' => $posicaoCaixa,
                        'status' => 'null',
                        'objeto' => 'null',
                        'idobjeto' => 'null',
                        'idprateleiradim' => 'null',
                        'usuario' => $_SESSION["SESSAO"]["USUARIO"]
                    ];

                    $tagInserida = SQL::ini(TagDimQuery::inserirTagDim(), $dadosParaInsert)::exec();
        
                    if($tagInserida->error())
                    {
                        d::b()->query("ROLLBACK;");

                        parent::error(__CLASS__, __FUNCTION__, $tagInserida->errorMessage());

                        return false;
                    }
                }				
            }			
        }	

        return d::b()->query("COMMIT");
    }
}

?>