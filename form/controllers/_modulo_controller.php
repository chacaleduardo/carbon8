<?
require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/../querys/tag_query.php");
require_once(__DIR__ . "/../querys/share_query.php");
require_once(__DIR__ . "/../querys/evento_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/etiqueta_query.php");
require_once(__DIR__ . "/../querys/_mtotabcol_query.php");
require_once(__DIR__ . "/../querys/unidadeobjeto_query.php");
require_once(__DIR__ . "/../querys/fluxostatushistmotivo_query.php");

class _moduloController extends Controller{

    // ----- FUNÇÕES -----
    public static function buscarPorChavePrimaria ( $idmodulo ){
        $result = (SQL::ini(_ModuloQuery::buscarPorChavePrimaria(),['pkval'=>$idmodulo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
    
    }

    public static function verificaModVinc($modulo){
        $result = (SQL::ini(_ModuloQuery::verificaModVinc(),['modulo'=>$modulo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return ($result->numRows() > 0);
        }
    }

    public static function buscarTabModVinc($modulo){
        $result = (SQL::ini(_ModuloQuery::buscarTabModVinc(),['modulo'=>$modulo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  $result->data[0];
        }
    }

    public static function jsonLpsDisponiveis($modulo,$idpessoa){
        $result = (SQL::ini(_ModuloQuery::jsonLpsDisponiveisPorObjEmpresa(),[
            'modulo'=>$modulo,
            'idpessoa' => $idpessoa
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  parent::toJson($result->data);
        }
    }

    public static function jsonEtiquetasDisponiveis($idmodulo){
        $result = (SQL::ini(EtiquetaQuery::adicionarEtiquetasAoModulo(),['idmodulo'=>$idmodulo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  parent::toJson($result->data);
        }
    }

    public static function jsonImpressorasDisponiveis($idmodulo){
        $result = (SQL::ini(TagQuery::buscarImpressorasSemModulo(),['idmodulo'=>$idmodulo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  parent::toJson($result->data);
        }
    }

    public static function listaTabelasVinculadasAoForm($modulo,$urlDestino){
        $result = (SQL::ini(_ModuloQuery::listaTabelasVinculadasAoForm(),[
            'modulo'=>$modulo,
            'urldestino'=>$urlDestino,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  ($result->data);
        }
    }

    public static function listaAjaxVinculadosAoForm($modulo,$urlDestino){
        $result = (SQL::ini(_ModuloQuery::listaAjaxVinculadosAoForm(),[
            'modulo'=>$modulo,
            'urldestino'=>$urlDestino,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return  ($result->data);
        }
    }

    public static function insertModuloFiltros($modulo,$tab){
        $result = (SQL::ini(_ModuloQuery::insertModuloFiltros(),[
            'modulo'=>$modulo,
            'tab'=>$tab,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return  true;
        }
    }

    public static function montaArrayConfPesquisa($modulo){
        $result = (SQL::ini(_ModuloQuery::montaArrayConfPesquisa(),[
            'moduloReal'=>$modulo,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function jsonTabelasCarbonApp(){
        $result = (SQL::ini(_ModuloQuery::jsonTabelasCarbonApp(),[])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function verificaColunasFtsDicionario($tab){
        $result = (SQL::ini(_MtotabcolQuery::contarColunasFtsKey(),["tab"=>$tab])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return 0;
        }else{
            return ($result->numRows());
        }
    }

    public static function contarColunasInexistentesNoDB($tab){
        $result = (SQL::ini(_MtotabcolQuery::contarColunasInexistentesNoDB(),["tab"=>$tab])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return 0;
        }else{
            return ($result->numRows());
        }
    }

    public static function jsonRepDisponiveis($mod){
        $result = (SQL::ini(_ModuloQuery::jsonRepDisponiveis(),[
            "db_rep"=>getDbTabela("_rep"),
            "db_modulorep"=>getDbTabela("_modulorep"),
            "modulo"=>$mod,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function buscarDashboardsDoModulo($modulo){
        $result = (SQL::ini(_ModuloQuery::buscarDashboardsDoModulo(),[
            "modulo" => $modulo
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarModuloTipo(){
        $result = (SQL::ini(_ModuloQuery::buscarModuloTipo(),[])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarEmpresasParaVincularAoModulo($idmod){
        $result = (SQL::ini(_ModuloQuery::buscarEmpresasParaVincularAoModulo(),["idmodulo"=>$idmod])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarUnidadesParaVincularAoModulo($idmod,$mod){
        $result = (SQL::ini(_ModuloQuery::buscarUnidadesParaVincularAoModulo(),[
            "idmodulo"=>$idmod,
            "modulo"=>$mod,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarEmpresasVinculadasAoModulo($idmod,$toFillSelect = false){
        $result = (SQL::ini(_ModuloQuery::buscarEmpresasVinculadasAoModulo(),["idmodulo"=>$idmod])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            if($toFillSelect){
                return parent::toFillSelect($result->data);
            }else{
                return ($result->data);
            }
        }
    }

    public static function buscarUnidadesVinculadasAoModulo($idmod,$idempresa){
        $result = (SQL::ini(_ModuloQuery::buscarUnidadesVinculadasAoModulo(),[
            "idmodulo"=>$idmod,
            "idempresa"=>$idempresa,
            ])::exec());
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function adicionarModVinculados(){
        $result = (SQL::ini(_ModuloQuery::adicionarModVinculados(),[])::exec());
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarModpar( $clausula ){
        $result = (SQL::ini(_ModuloQuery::buscarModpar(),[
            "clausula" => $clausula
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarMotivosDeRestauracao( $mod ){
        $result = (SQL::ini(FluxostatushistmotivoQuery::buscarMotivosPorModulo(),[
            "modulo" => $mod
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarModVinculados( $mod ){
        $result = (SQL::ini(_ModuloQuery::buscarModVinculados(),[
            "modulo" => $mod
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarRelatoriosVinculados( $mod ){
        $result = (SQL::ini(_ModuloQuery::RepsVinculadosAoModulo(),[
            "db_rep"=> getDbTabela("_rep"),
            "db_modulorep"=> getDbTabela("_modulorep"),
            "modulo"=> $mod,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarEtiquetasVinculadasAoModulo( $idmodulo ){
        $result = (SQL::ini(EtiquetaQuery::buscarEtiquetasVinculadasAoModulo(),[
            "idmodulo"=> $idmodulo,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarImpressorasVinculadasAoModulo( $idmodulo ){
        $result = (SQL::ini(_ModuloQuery::buscarImpressorasVinculadasAoModulo(),[
            "idmodulo"=> $idmodulo,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarLpsVinculadasAoModulo( $mod,$idpessoa ){
        $result = (SQL::ini(_ModuloQuery::buscarLpsVinculadasAoModuloPorEmpresa(),[
            "idpessoa" => $idpessoa,
            "modulo"=> $mod,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function consultaEventoDBCarbon(){
        $result = (SQL::ini("SHOW EVENTS FROM "._DBCARBON." where Name='fts' and Status='ENABLED'",[])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarHostsParaFts($mod){
        $result = (SQL::ini(_ModuloQuery::buscarHostsParaFts(),["modulo" => $mod])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarUltimosLogsDeFts($tab){
        $result = (SQL::ini(_ModuloQuery::buscarUltimosLogDeFts(),["tab" => $tab])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function deletaUnidadeObjeto($idUnidadeObjeto){
        $result = (SQL::ini(UnidadeObjetoQuery::deletaUnidadeObjetoPorChavePrimaria(),["idunidadeobjeto" => $idUnidadeObjeto])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function buscarRegrasShareModuloFiltrosPesquisa($mod){
        $result = (SQL::ini(ShareQuery::buscarRegraPorModuloSharemetodo(),["modulo" => $mod])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarUnidadesDisponiveisParaShare($idmod){
        $result = (SQL::ini(_ModuloQuery::buscarUnidadesDisponiveisParaShare(),["idmodulo" => $idmod])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarEventosPorTipoEClassificacao($classificacao,$ideventotipo){
        $result = (SQL::ini(EventoQuery::buscarEventosPorTipoEClassificacao(),[
            "classificacao" => $classificacao,
            "ideventotipo" => $ideventotipo
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarRotuloMenu($_modulo){
        $result = (SQL::ini(_ModuloQuery::buscarRotuloMenu(),[
            "_modulo" => $_modulo
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade($moduloTipo, $getIdEmpresa, $idTipoUnidade)
    {
        $unidades = SQL::ini(_ModuloQuery::buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade(), [
            'modulotipo' => $moduloTipo,
            'getidempresa' => $getIdEmpresa,
            'idtipounidade' => $idTipoUnidade
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function buscarModulosComUnidadesVinculadasPorGetIdEmpresa($idtipounidade, $getIdEmpresa)
    {
        $modulos = SQL::ini(_ModuloQuery::buscarModulosComUnidadesVinculadasPorGetIdEmpresa(), [
            'idtipounidade' => $idtipounidade,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if($modulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulos->errorMessage());
            return [];
        }

        return $modulos->data[0];
    }

    public static function buscarModuloPorUnidade($idUnidade)
    {
        $modulos = SQL::ini(_ModuloQuery::buscarModuloPorUnidade(), [
            'idunidade' => $idUnidade
        ])::exec();

        if($modulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulos->errorMessage());
            return [];
        }

        return $modulos->data;
    }

    public static function buscarModuloPorUnidadeEIdEmpresa($idUnidade, $idEmpresa)
    {
        $modulos = SQL::ini(_ModuloQuery::buscarModuloPorUnidadeEIdEmpresa(), [
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        if($modulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulos->errorMessage());
            return [];
        }

        return $modulos->data;
    }

    public static function buscarModulosDisponiveisParaVinculoEmUnidades($idUnidade, $idEmpresa)
    {
        $modulos = SQL::ini(_ModuloQuery::buscarModulosDisponiveisParaVinculoEmUnidades(),[
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        if($modulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulos->errorMessage());
            return [];
        }

        return $modulos->data;
    }

    public static function buscarModulosPorTipo($tipo, $toFillSelect = false)
    {
        $modulos = SQL::ini(_ModuloQuery::buscarModulosPortipo(), [
            'tipo' => $tipo
        ])::exec();

        if($modulos->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulos->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($modulos->data as $modulo)
            {
                $arrRetorno[$modulo['modulo']] = $modulo['rotulomenu'];
            }

            return $arrRetorno;
        }

        return $modulos->data;
    }

    public static function buscarModuloETabComPK($montarArrayPorModulo = false)
    {
        $modulo = SQL::ini(_ModuloQuery::buscarModuloETabComPK())::exec();

        if($modulo->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulo->errorMessage());
            return [];
        }

        if($montarArrayPorModulo)
        {
            $arrRetorno = [];

            foreach($modulo->data as $modulo)
            {
                $arrRetorno[$modulo['modulo']] = $modulo;
            }

            return $arrRetorno;
        }

        return $modulo->data;
    }


    public static function buscarModparAtivo( $mod ){
        $result = (SQL::ini(_ModuloQuery::buscarModparAtivo(),[
            "modulo" => $mod
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarUnidadesTabelaModulo($modulo){
        $result = SQL::ini(UnidadeObjetoQuery::buscarUnidadesTabelaModulo(),[
            "modulo" => $modulo
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
    }

    public static function buscarUnidadesTabelas($tabela, $idunidades){
        $result = (SQL::ini(_ModuloQuery::buscarUnidadesTabelas(),[
            'tabela' => $tabela,
            'idunidades' => $idunidades
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return $result->numRows();
        }
    }


    public static $ArrayStatus = array("ATIVO"=>"Ativo","INATIVO"=>"Inativo");

    public static $ArrayOnReady = array('FILTROS'=>'Filtros','URL'=>'Url');

    public static $ArrayYN = array('N' => 'Não', 'Y' => 'Sim');

    public static $ArrayLCR = array('L'=>'L','C'=>'C','R'=>'R');

    public static $ArrayMascara = array('N'=>'Num','M'=>'R$');

    public static $ArrayNovaJanela = array('L'=>'Link', 'M'=>'Modulo Carbon', 'N'=>'Não');

    public static $ArrayNovoFormObj = array("tabelacbpost"=>"Tabela","ajax"=>"Form. Ajax");

    public static $ArrayTipos = array(
                                'DROP'=>'Menu Superior (Drop)'
                                ,'LINK'=>'Módulo'
                                ,'LINKUSUARIO'=>'Links do [Menu Usuário]'
                                ,'LINKHOME'=>'Homepage'
                                ,'BTINV'=>'Funcionalidade'
                                ,'BTPR'=>'Padrão'
                                ,'MODVINC'=>'Módulo Vinculado'
                                ,'EMAIL'=>'Módulo Email'
                                ,'POPUP'=>'PopUp'
                                ,'SNIPPET'=>'Snippet'
                            );
}
?>
