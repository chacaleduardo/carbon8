<?

class VinculosQuery
{
    public static function buscarDocumentosPorIdObjetoETipoObjeto()
    {
        return "SELECT d.titulo,d.idsgdoc
                FROM vinculos v,sgdoc d
                where v.idobjetode = d.idsgdoc
                AND v.tipoobjetode = 'sgdoc' 
                AND v.idobjetopara= ?idobjeto?
                AND v.tipoobjetopara='?tipoobjeto?'
                ORDER BY d.titulo";
    }
}

?>