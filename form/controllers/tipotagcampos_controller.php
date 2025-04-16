<?

class TipoTagCamposController extends Controller
{
    public static function buscarCampos($idTagTipo)
    {
        $camposVinculadoAoIdTagTipo = SQL::ini(TipoTagCamposQuery::buscarPeloIdTagTipo(), [
            'idtagtipo' => $idTagTipo
        ])::exec()->data;
        
        return array_map(function($item){return $item['campo'];}, $camposVinculadoAoIdTagTipo);
    }
}

?>