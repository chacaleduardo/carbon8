<?
require_once(__DIR__."/_controller.php");


require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/_modulo_query.php");
require_once(__DIR__."/../querys/tipopessoa_query.php");
require_once(__DIR__."/../querys/empresa_query.php");
require_once(__DIR__."/../querys/plantel_query.php");
require_once(__DIR__."/../querys/_lpgrupo_query.php");
require_once(__DIR__."/../querys/_lpmodulo_query.php");
require_once(__DIR__."/../querys/_lpobjeto_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");
require_once(__DIR__."/../querys/plantelobjeto_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");


class _LpController extends Controller{

    // ----- FUNÇÕES -----
    public static function buscarPorChavePrimaria ( $idLp ){
        $result = (SQL::ini(_LpGrupoQuery::buscarPorChavePrimaria(),['pkval'=>$idLp])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
        
    }

    public static function buscarGruposPorLpgrupopar( $idLpgrupo ){
        $result = (SQL::ini(_LpGrupoQuery::buscarGruposPorLpgrupopar(),['idlpgrupo'=>$idLpgrupo])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarLPsPorIdLprupo( $idLpgrupo, $idpessoa ){
        $result = (SQL::ini(_LpQuery::buscarLPsPorIdLprupo(),[
            'idlpgrupo'=>$idLpgrupo,
            'idpessoa'=>$idpessoa,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarSiglaECorsistemaDaEmpresa( $idEmpresa ){
        $result = (SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresa(),['idempresa'=> $idEmpresa])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
    }

    public static function jsonEmpresasNaoVinculadasALp( $idEmpresa, $idLp ){
        $result = (SQL::ini(_LpQuery::jsonEmpresasNaoVinculadasALp(),[
            'idempresa'=> $idEmpresa,
            'idlp'=> $idLp,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function listaSnippets( $getIdEmpresa, $idLp ){
        $result = (SQL::ini(_LpQuery::listaSnippets(),[
            'getidempresa'=> $getIdEmpresa,
            'idlp'=> $idLp,
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarPessoasSetorDepartamentoArea( $table, $id ){
        if($table == 'sgsetor'){
            $result = (SQL::ini(_LpQuery::buscarPessoasSetorDepartamentoAreaComUnion(),[
                'alias'=> substr($table,2),
                'table'=> $table,
                'id'=> $id,
                ])::exec());
    
            if($result->error()){
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return [];
            }else{
                $arr = array();
    
                foreach($result->data as $k => $rw ){
                    $arr['nome'] = $rw['nome'];
                    $arr['pessoas'][$k]['pessoa'] = $rw['sigla'].' - '.$rw['pessoa'];
                    $arr['pessoas'][$k]['idpessoa'] = $rw['idpessoa'];
                }
    
                return $arr;
            }
        }else{
            $result = (SQL::ini(_LpQuery::buscarPessoasSetorDepartamentoArea(),[
                'alias'=> substr($table,2),
                'table'=> $table,
                'id'=> $id,
                ])::exec());
    
            if($result->error()){
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return [];
            }else{
                $arr = array();
    
                foreach($result->data as $k => $rw ){
                    $arr['nome'] = $rw['nome'];
                    $arr['pessoas'][$k]['pessoa'] = $rw['sigla'].' - '.$rw['pessoa'];
                    $arr['pessoas'][$k]['idpessoa'] = $rw['idpessoa'];
                }
    
                return $arr;
            }
        }
        
    }

    public static function jsonFuncionariosSetoresDepartamentosAreaConselho( $idlp ){
        $result = (SQL::ini(_LpQuery::buscarPessoasSetorDepartamentoAreaConselho(),[
            'idlp'=> $idlp
            ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function buscarModulosPadrao(  ){
        $result = (SQL::ini(_LpQuery::buscarModulosPadrao(),[])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach( $result->data as $k => $rmod){

                //switch para controlar o estado do modulo
                switch ($rmod["permissao"]) {
                    case "r":
                        $rotPermissao="Permissão Somente Leitura";
                        $classeBt="btn-primary";//azul
                        $icoPermissao="fa fa-lock";
                        $corIco="azul";
                        break;

                    case "w":
                        $rotPermissao="Permissão de Alteração";
                        $classeBt="btn-danger";//vermelho
                        $icoPermissao="fa fa-pencil";
                        $corIco="vermelho";
                        break;
                    default:
                        $rotPermissao="Sem Permissão";
                        $classeBt="";//vermelho
                        $icoPermissao="fa fa-ban";
                        $corIco="cinza";
                        break;
                }

                $arrRet[$rmod["modulo"]]["idlpmodulo"] = $rmod["idlpmodulo"];
                $arrRet[$rmod["modulo"]]["cssicone"] = $rmod["cssicone"];
                $arrRet[$rmod["modulo"]]["rotulomenu"] = $rmod["rotulomenu"];
                $arrRet[$rmod["modulo"]]["permissao"] = $rmod["permissao"];
                $arrRet[$rmod["modulo"]]["rotPermissao"] = $rotPermissao;
                $arrRet[$rmod["modulo"]]["classeBt"] = $classeBt;
                $arrRet[$rmod["modulo"]]["icoPermissao"] = $icoPermissao;
                $arrRet[$rmod["modulo"]]["corIco"] = $corIco;
            }
            return $arrRet;
        }
    }

    public static function buscarModulosDisponiveis( $getIdEmpresa, $idLp ){
        $result = (SQL::ini(_LpQuery::buscarModulosDisponiveis(),[
            'getidempresa' => $getIdEmpresa, 
            'idlp' => $idLp, 
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach($result->data as $k => $rmod ){
                $arrRet[$rmod["modulo"]]["tipo"] = $rmod["tipo"];
                $arrRet[$rmod["modulo"]]["cssicone"] = $rmod["cssicone"];
                $arrRet[$rmod["modulo"]]["rotulomenu"] = $rmod["rotulomenu"];
             
            }
            return $arrRet;
        }
    }

    public static function buscarModulosSelecionados( $idEmpresa, $idLp, $_sigla){
        $result = (SQL::ini(_LpQuery::buscarModulosSelecionados(),[
            'idempresa' => $idEmpresa, 
            'idlp' => $idLp, 
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach($result->data as $k => $rmod){
                
                //switch para controlar o estado do modulo
                switch ($rmod["permissao"]) {
                    case "r":
                        $rotPermissao="Permissão Somente Leitura";
                        $classeBt="btn-primary";//azul
                        $icoPermissao="fa fa-lock";
                        $corIco="azul";
                        $corPerm= '#286090';
                        break;

                    case "w":
                        $rotPermissao="Permissão de Alteração";
                        $classeBt="btn-danger";//vermelho
                        $icoPermissao="fa fa-pencil";
                        $corIco="vermelho";
                        $corPerm= '#c9302c';
                        break;
                    default:
                        $rotPermissao="Sem Permissão";
                        $classeBt="nopermission";//vermelho
                        $icoPermissao="fa fa-ban";
                        $corIco="cinza";
                        $corPerm = '';
                        break;
                }
                $arrRet[$rmod["modulo"]]["idmodulo"] = $rmod["idmodulo"];
                $arrRet[$rmod["modulo"]]["tipo"] = $rmod["tipo"];
                $arrRet[$rmod["modulo"]]["divisao"] = $rmod["divisao"];
                $arrRet[$rmod["modulo"]]["idlpmodulo"] = $rmod["idlpmodulo"];
                $arrRet[$rmod["modulo"]]["cssicone"] = $rmod["cssicone"];
                $arrRet[$rmod["modulo"]]["rotulomenu"] = $rmod["rotulomenu"];
                $arrRet[$rmod["modulo"]]["permissao"] = $rmod["permissao"];
                $arrRet[$rmod["modulo"]]["solassinatura"] = $rmod["solassinatura"];
                $arrRet[$rmod["modulo"]]["rotPermissao"] = $rotPermissao;
                $arrRet[$rmod["modulo"]]["classeBt"] = $classeBt;
                $arrRet[$rmod["modulo"]]["icoPermissao"] = $icoPermissao;
                $arrRet[$rmod["modulo"]]["corIco"] = $corIco;
                $arrRet[$rmod["modulo"]]["corPerm"] = $corPerm;
                if ($rmod["sigla"] != ''){
                    $arrRet[$rmod["modulo"]]["sigla"] = $rmod["sigla"];
                }else{
                    $arrRet[$rmod["modulo"]]["sigla"] = $_sigla;
                }
            
            }
                return ($arrRet);
            }
    }

    public static function buscarModulosSelecionados2( $idEmpresa, $idLp, $getIdEmpresa, $_sigla){
        $result = (SQL::ini(_LpQuery::buscarModulosSelecionados2(),[
            'idempresa' => $idEmpresa,
            'idlp' => $idLp,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarModulosFilhos( $idEmpresa, $idLp, $getIdEmpresa, $inmod, $_sigla){
        $result = (SQL::ini(_LpQuery::buscarModulosFilhos(),[
            'idempresa' => $idEmpresa, 
            'idlp' => $idLp, 
            'getidempresa' => $getIdEmpresa, 
            'inmod' => $inmod, 
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach($result->data as $k => $rmod ){

                //switch para controlar o estado do modulo
                switch ($rmod["permissao"]) {
                    case "r":
                        $rotPermissao="Permissão Somente Leitura";
                        $classeBt="btn-primary";//azul
                        $icoPermissao="fa fa-lock";
                        $corIco="azul";
                        $corPerm= '#286090';
                        break;

                    case "w":
                        $rotPermissao="Permissão de Alteração";
                        $classeBt="btn-danger";//vermelho
                        $icoPermissao="fa fa-pencil";
                        $corIco="vermelho";
                        $corPerm= '#c9302c';
                        break;
                    default:
                        $rotPermissao="Sem Permissão";
                        $classeBt="nopermission";//vermelho
                        $icoPermissao="fa fa-ban";
                        $corIco="cinza";
                        $corPerm= '';
                        break;
                }

                $arrRet[$rmod["modulo"]]["idlpmodulo"] = $rmod["idlpmodulo"];
                $arrRet[$rmod["modulo"]]["idmodulo"] = $rmod["idmodulo"];
                $arrRet[$rmod["modulo"]]["modulo"] = $rmod["modulo"];
                $arrRet[$rmod["modulo"]]["cssicone"] = $rmod["cssicone"];
                $arrRet[$rmod["modulo"]]["rotulomenu"] = $rmod["rotulomenu"];
                $arrRet[$rmod["modulo"]]["permissao"] = $rmod["permissao"];
                $arrRet[$rmod["modulo"]]["solassinatura"] = $rmod["solassinatura"];
                $arrRet[$rmod["modulo"]]["status"] = $rmod["status"];
                $arrRet[$rmod["modulo"]]["rotPermissao"] = $rotPermissao;
                $arrRet[$rmod["modulo"]]["classeBt"] = $classeBt;
                $arrRet[$rmod["modulo"]]["icoPermissao"] = $icoPermissao;
                $arrRet[$rmod["modulo"]]["corIco"] = $corIco;
                $arrRet[$rmod["modulo"]]["corPerm"] = $corPerm;
                if ($rmod["sigla"] != ''){
                    $arrRet[$rmod["modulo"]]["sigla"] = $rmod["sigla"];
                }else{
                    $arrRet[$rmod["modulo"]]["sigla"] = $_sigla;
                }

            }
            return $arrRet;
        }
    }

    public static function buscarModulosFilhosDosFilhos( $idEmpresa, $idLp, $inmod, $_sigla){
        $result = (SQL::ini(_LpQuery::buscarModulosFilhosDosFilhos(),[
            'idempresa' => $idEmpresa, 
            'idlp' => $idLp,
            'inmod' => $inmod, 
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach($result->data as $k => $rmod){

                //switch para controlar o estado do modulo
                switch ($rmod["permissao"]) {
                    case "r":
                        $rotPermissao="Permissão Somente Leitura";
                        $classeBt="btn-primary";//azul
                        $icoPermissao="fa fa-lock";
                        $corIco="azul";
                        $corPerm = '#286090';
                        break;

                    case "w":
                        $rotPermissao="Permissão de Alteração";
                        $classeBt="btn-danger";//vermelho
                        $icoPermissao="fa fa-pencil";
                        $corIco="vermelho";
                        $corPerm= '#c9302c';
                        break;
                    default:
                        $rotPermissao="Sem Permissão";
                        $classeBt="nopermission";//vermelho
                        $icoPermissao="fa fa-ban";
                        $corIco="cinza";
                        $corPerm='#666666';
                        break;
                }

                $arrRet[$rmod["modulo"]]["idlpmodulo"] = $rmod["idlpmodulo"];
                $arrRet[$rmod["modulo"]]["idmodulo"] = $rmod["idmodulo"];
                $arrRet[$rmod["modulo"]]["modulo"] = $rmod["modulo"];
                $arrRet[$rmod["modulo"]]["cssicone"] = $rmod["cssicone"];
                $arrRet[$rmod["modulo"]]["rotulomenu"] = $rmod["rotulomenu"];
                $arrRet[$rmod["modulo"]]["permissao"] = $rmod["permissao"];
                $arrRet[$rmod["modulo"]]["solassinatura"] = $rmod["solassinatura"];
                $arrRet[$rmod["modulo"]]["status"] = $rmod["status"];
                $arrRet[$rmod["modulo"]]["rotPermissao"] = $rotPermissao;
                $arrRet[$rmod["modulo"]]["classeBt"] = $classeBt;
                $arrRet[$rmod["modulo"]]["icoPermissao"] = $icoPermissao;
                $arrRet[$rmod["modulo"]]["corIco"] = $corIco;
                $arrRet[$rmod["modulo"]]["corPerm"] = $corPerm;
                if ($rmod["sigla"] != ''){
                    $arrRet[$rmod["modulo"]]["sigla"] = $rmod["sigla"];
                }else{
                    $arrRet[$rmod["modulo"]]["sigla"] = $_sigla;
                }
                
            }
            return $arrRet;
        }
    }

    public static function buscarRepsDoModulo( $idLp, $inmod){
        $result = (SQL::ini(_LpQuery::buscarRepsDoModulo(),[
            'idLp' => $idLp,
            'inmod' => $inmod, 
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $arrRet=array();
            foreach($result->data as $k => $rmod){

                //switch para controlar o estado do modulo
                $displayBtnUnidade="";
                if($rmod['btnrep']==''){
                    $displayBtnUnidade="display:none";
                }else{
                    $displayBtnUnidade="display:block";
                }
                if($rmod['btnrepcti']==''){
                    $displayBtnContaItem="display:none";
                }else{
                    $displayBtnContaItem="display:block";
                }
                if($rmod['btnreporg']==''){
                    $displayBtnOrganograma="display:none";
                }else{
                    $displayBtnOrganograma="display:block";
                }
            
                    if ($rmod["idlprep"]) {
            
                            $rotPermissao="Permissão";
                            $classeBt="btn-primary";//azul
                            $corIco="branco";
                            $icoPermissao="fa fa-lock";
                            $corHex= '#337ab7';
                            
                        }else{
                            $rotPermissao="Sem Permissão";
                            $classeBt="nopermission";//vermelho
                            $corIco="cinza";
                            $icoPermissao="fa fa-ban";
                            $classeBt="btn-default";//azul
                            $corHex= '#666666';

                    }

                    if ($rmod["flgunidade"] == 'Y') {
            
                        $rotPermissaoU="Permissão";
                        $classeBtU="btn-primary";//azul
                        $corIcoU="branco";
                        $icoPermissaoU="fa fa-building";

                    }else{
                            $rotPermissaoU="Sem Permissão";
                            $classeBtU="btn-default";//vermelho
                            $corIcoU="preto";
                            $icoPermissaoU="fa fa-building";


                    }

                    if ($rmod["flgcontaitem"] == 'Y') {
            
                        $rotPermissaoCT="Permissão";
                        $classeBtCT="btn-primary";//branco
                        $corIcoCT="branco";
                        $icoPermissaoCT="fa fa-credit-card-alt";

                    }else{
                        $rotPermissaoCT="Sem Permissão";
                        $classeBtCT="btn-default";//vermelho
                        $corIcoCT="preto";
                        $icoPermissaoCT="fa fa-credit-card-alt";


                    }

                    if ($rmod["flgidpessoa"] == 'Y') {
            
                        $rotPermissaoO="Permissão";
                        $classeBtO="btn-primary";//branco
                        $corIcoO="branco";
                        $icoPermissaoO="fa fa-sitemap";

                    }else{
                        $rotPermissaoO="Sem Permissão";
                        $classeBtO="btn-default";//vermelho
                        $corIcoO="preto";
                        $icoPermissaoO="fa fa-sitemap";
                    }


                    $arrRet[$rmod["idrep"]]["idlprep"] = $rmod["idlprep"];
                    $arrRet[$rmod["idrep"]]["flgunidade"] = $rmod["flgunidade"];
                    $arrRet[$rmod["idrep"]]["flgidpessoa"] = $rmod["flgidpessoa"];
                    $arrRet[$rmod["idrep"]]["flgcontaitem"] = $rmod["flgcontaitem"];
                    $arrRet[$rmod["idrep"]]["rep"] = $rmod["rep"];
                    $arrRet[$rmod["idrep"]]["idrep"] = $rmod["idrep"];
                    $arrRet[$rmod["idrep"]]["corIco"] = $corIco;
                    $arrRet[$rmod["idrep"]]["icoPermissao"] = $icoPermissao;
                    $arrRet[$rmod["idrep"]]["classeBt"] = $classeBt;
                    $arrRet[$rmod["idrep"]]["rotPermissao"] = $rotPermissao;
                    $arrRet[$rmod["idrep"]]["displayBtnUnidade"] = $displayBtnUnidade;
                    $arrRet[$rmod["idrep"]]["displayBtnContaItem"] = $displayBtnContaItem;
                    $arrRet[$rmod["idrep"]]["corIcoU"] = $corIcoU;
                    $arrRet[$rmod["idrep"]]["icoPermissaoU"] = $icoPermissaoU;
                    $arrRet[$rmod["idrep"]]["classeBtU"] = $classeBtU;
                    $arrRet[$rmod["idrep"]]["rotPermissaoU"] = $rotPermissaoU;
                    $arrRet[$rmod["idrep"]]["displayBtnOrganograma"] = $displayBtnOrganograma;
                    $arrRet[$rmod["idrep"]]["corIcoO"] = $corIcoO;
                    $arrRet[$rmod["idrep"]]["icoPermissaoO"] = $icoPermissaoO;
                    $arrRet[$rmod["idrep"]]["classeBtO"] = $classeBtO;
                    $arrRet[$rmod["idrep"]]["rotPermissaoO"] = $rotPermissaoO;
                    $arrRet[$rmod["idrep"]]["rotPermissaoCT"] = $rotPermissaoCT;
                    $arrRet[$rmod["idrep"]]["classeBtCT"] = $classeBtCT;
                    $arrRet[$rmod["idrep"]]["corIcoCT"] = $corIcoCT;
                    $arrRet[$rmod["idrep"]]["icoPermissaoCT"] = $icoPermissaoCT;
                    $arrRet[$rmod["idrep"]]["reptipo"] = $rmod['reptipo'];
                    $arrRet[$rmod["idrep"]]["idreptipo"] = $rmod['idreptipo'];
                    $arrRet[$rmod["idrep"]]["corhex"] = $corHex;
                }
        
        
            return $arrRet;
        }
    }

    public static function buscarLpEEmpresa( $idLp, $idLpgrupo=NULL){
        if($idLpgrupo){
            $result = (SQL::ini(_LpQuery::buscarLpEEmpresa(),[
                'idlp' => $idLp,
                'idlpgrupo' => $idLpgrupo,
            ])::exec());
        }else{
            $result = (SQL::ini(_LpQuery::buscarLpEEmpresa2(),[
                'idlp' => $idLp
            ])::exec());
        }
    
            if($result->error()){
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return [];
            }else{
                return ($result->data);
            }
    }

    public static function buscarLpPorIdbi( $idLp, $idempresa){
        if($idLp){
            $result = (SQL::ini(_LpQuery::buscarLpPorIdbi(),[
                'idlp' => $idLp,
                'idempresa' => $idempresa
            ])::exec());
        }
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            $bi = [];

            foreach ($result->data as $key => $b) {
                if(is_null($b['bipai'])){
                    if(!isset($bi[$b['idbi']])){
                        $bi[$b['idbi']]['sigla'] = $b['sigla'];
                        $bi[$b['idbi']]['nome'] = $b['nome'];
                        $bi[$b['idbi']]['idbi'] = $b['idbi'];
                        $bi[$b['idbi']]['idlpbi'] = $b['idlpbi'];
                        $bi[$b['idbi']]['bipai'] = null;
                        $bi[$b['idbi']]['filhos'] = [];
                    }
                    if(!isset($bi[$b['idbi']]['filhos'])){
                        $bi[$b['idbi']]['filhos'] = [];
                    }
                }else{
                    $bi[$b['bipai']]['filhos'][] = $b;
                }
            }

            return $bi;
        }
    }

    public static function buscarLpobjetoPorEmpresa( $idLp, $idempresa){
        $result = (SQL::ini(_LpobjetoQuery::buscarPorEmpresa(),[
            'idlp' => $idLp,
            'idempresa' => $idempresa,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }


    public static function buscarObjetosVinculadosALp( $idLp ){
        $result = (SQL::ini(_LpQuery::buscarObjetosVinculadosALp(),[
            'idlp' => $idLp,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarPessoasPorTipoPessoa( $idtipopessoa ){
        $result = (SQL::ini(PessoaQuery::listarPessoaPorIdTipoPessoa(),[
            'idtipopessoa' => $idtipopessoa,
            'status' => 'and p.status = "ATIVO"',
            'pessoaPorCbUserIdempresa' => '',
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function jsonRepDisponiveis( $idLp ){
        $result = (SQL::ini(_LpQuery::jsonRepDisponiveis(),[
            'idlp' => $idLp,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function buscarPlanteis( $idLp, $getIdEmpresa ){
        $result = (SQL::ini(PlantelQuery::buscarPlantelPorIdobjetoTipoobjeto(),[
            'idobjeto' => $idLp,
            'tipoobjeto' => 'lp',
            'getidempresa' => $getIdEmpresa,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarTipopessoaVinculadoALp( $idLp ){
        $result = (SQL::ini(_LpQuery::buscarTipopessoaVinculadoALp(),[
            'idlp' => $idLp,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarAgencias( $idLp, $idempresa = 0, $habilitarMatriz = 'N'){
        if(!$idempresa) $idempresa = cb::idempresa();

        $clausulaMatriz = "AND a.idempresa = $idempresa";

        if($habilitarMatriz == 'Y') {
            $clausulaMatriz = "AND EXISTS (
					select 1 
					from agencia qr
					join matrizconf m on m.idmatriz = $idempresa
					where qr.idagencia = a.idagencia
					AND qr.idempresa = m.idempresa
				)";
        }

        $result = (SQL::ini(_LpQuery::buscarAgencias(),[
            'idlp' => $idLp,
            'clausulamatriz' => $clausulaMatriz
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarIdempresasDaLp( $idLp ){
        $result = (SQL::ini(_LpQuery::buscarIdempresasDaLp(),[
            'idlp' => $idLp,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarUnidades( $idLp, $idEmpresas ){
        $result = (SQL::ini(_LpQuery::buscarUnidades(),[
            'idlp' => $idLp,
            'idempresas' => $idEmpresas,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarContaitem( $idLp, $idempresa = 0 , $habilitarMatriz = 'N'){
        if(!$idempresa) $idempresa = cb::idempresa();

        $clausulaMatriz = "AND c.idempresa = $idempresa";

        if($habilitarMatriz == 'Y') {
            $clausulaMatriz = "AND EXISTS (
					select 1 
					from contaitem ci
					join matrizconf m on m.idmatriz = $idempresa
					where ci.idcontaitem = c.idcontaitem 
					AND ci.idempresa = m.idempresa
				)
				OR c.idempresa = $idempresa";
        }

        $result = (SQL::ini(_LpQuery::buscarContaitem(),[
            'idlp' => $idLp,
            'clausulamatriz' => $clausulaMatriz,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarFormaPagamento( $idLp, $idempresa = 0, $habilitarMatriz = 'N' ){
        if(!$idempresa) $idempresa = cb::idempresa();

        $clausulaMatriz = "WHERE f.idempresa = $idempresa";

        if($habilitarMatriz == 'Y') {
            $clausulaMatriz = "WHERE EXISTS (
					select 1 
					from formapagamento fp
					join matrizconf m on m.idmatriz = $idempresa
					where fp.idformapagamento = f.idformapagamento
					AND fp.idempresa = m.idempresa
				)";
        }


        $result = (SQL::ini(_LpQuery::buscarFormapagamento(),[
            'idlp' => $idLp,
            'clausulamatriz' => $clausulaMatriz
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarDashboards( $idLp, $idEmpresa ){
        $result = (SQL::ini(_LpQuery::buscarDashboards(),[
            'idlp' => $idLp,
            'idempresa' => $idEmpresa,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarDashboardsDisponiveis( $idLp, $idEmpresa ){
        $result = (SQL::ini(_LpQuery::buscarDashboardsDisponiveis(),[
            'idlp' => $idLp,
            'idempresa' => $idEmpresa,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarIdlpobjetoPorIdobjetoTipoobjetoIdlp( $idLp, $idobjeto, $tipoobjeto ){
        $result = (SQL::ini(_LpobjetoQuery::buscarIdlpobjetoPorIdobjetoTipoobjetoIdlp(),[
            'idlp' => $idLp,
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarObjetovinculoEObjetosVinculados( $idobjeto, $tipoobjeto, $idobjetovinc, $tipoobjetovinc ){
        $result = (SQL::ini(ObjetoVinculoQuery::buscarObjetovinculoEObjetosVinculados(),[
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'idobjetovinc' => $idobjetovinc,
            'tipoobjetovinc' => $tipoobjetovinc,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function criarLp( $idempresa, $grupo, $fullaccess, $descr ){
        $result = (SQL::ini(_LpQuery::criarLp(),[
            'idempresa' => $idempresa,
            'grupo' => $grupo,
            'fullaccess' => $fullaccess,
            'descr' => $descr,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return 0;
        }else{
            return $result->lastInsertId();
        }
    }

    public static function vincularLp( $idlp, $idlpgrupo, $usuario ){
        $result = (SQL::ini(_LpQuery::vincularLp(),[
            'idlp' => $idlp,
            'idlpgrupo' => $idlpgrupo,
            'usuario' => $usuario,
        ])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return 0;
        }else{
            return $result->lastInsertId();
        }
    }

    public static function alterarPermissaoModVinculados( $acao, $idLp, $modFilho ){
        // Verifica se é um módulo vinculado
        $result = SQL::ini(_ModuloQuery::buscarModuloVinculadoPorModulo(),[
            'modulo' => $modFilho,
        ])::exec();

        if ( $result->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        } else if( empty($result->data[0]) ) {
            return true;
        }

        $modPai = $result->data[0]['modvinculado'];

        // Verifica se é o módulo pai possui alguma permissão na LP atual
        $result = SQL::ini(_LpModuloQuery::buscarIdLpModuloPorLpeModulo(),[
            'idlp' => $idLp,
            'modulo' => $modPai,
        ])::exec();

        if ( $result->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }

        if(($acao == 'd' AND empty($result->data[0])) || ($acao == 'i' AND !empty($result->data[0]))) 
            return true;

        if ( in_array($acao, ['i', 'u']) AND empty($result->data[0]) ) {
            // criar lpmodulo
            $permissao = ($acao == 'i') ? 'r': 'w';

            $result = SQL::ini(_LpModuloQuery::inserirLpModulo(),[
                'idlp' => $idLp,
                'modulo' => $modPai,
                'permissao' => $permissao,
            ])::exec();

            if ( $result->error() ) {
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return false;
            }

            return true;
        }

        if ( $acao == 'u' AND !empty($result->data[0]) ) {
            // atualiza permissao
            $idlpmodulo = $result->data[0]['idlpmodulo'];

            $result = SQL::ini(_LpModuloQuery::atualizarPermissaoLpModulo(),[
                'idlpmodulo' => $idlpmodulo,
                'permissao' => 'w',
            ])::exec();

            if ( $result->error() ) {
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return false;
            }

            return true;
        }
        
        if( $acao == 'd' AND !empty($result->data[0]) ) {

            $idlpmodulo = $result->data[0]['idlpmodulo'];

            $result = SQL::ini(_LpModuloQuery::buscarIdLpModuloPorLpeModuloVinculado(),[
                'idlp' => $idLp,
                'modulo' => $modPai,
            ])::exec();

            if ( $result->error() ) {
                parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
                return false;
            } else if( $result->numRows() > 0 ) {
                return true;
            }

            // deletar
            SQL::ini(_LpModuloQuery::deletarLpModulo(),[
                'idlpmodulo' => $idlpmodulo,
            ])::exec();

            return true;
        }

        return true;
    }

    public static function buscarLpDashPorIdLpEIdEmpresa($idLps)
    {
        $lps = SQL::ini(_LpQuery::buscarLpDashPorIdLpEIdEmpresa(), [
            'idlp' => $idLps,
            'idempresa' => cb::idempresa()
        ])::exec();

        if ($lps->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarModulosPorModuloEIdLp($modulo, $idLps)
    {
        $lps = SQL::ini(_LpModuloQuery::buscarModulosPorModuloEIdLp(), [
            'modulo' => $modulo,
            'idlp' => $idLps
        ])::exec();

        if ($lps->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return "";
        } else {
            $dados['dados'] = $lps->data;
            $dados['qtdLinhas'] = $lps->numRows();
            return $dados;
        }
    }

    public static function alterarObjetoVinculoPlantelObjeto($acao,$table,$idlp,$idobjeto,$tipoobjeto,$criadopor,$criadoem,$alteradopor,$alteradoem)
    {
        if($acao == 'i'){
            if($table == 'plantelobjeto'){
                $results = SQL::ini(PlantelObjetoQuery::inserirPlantelObjeto(), [
                    'idobjeto' => $idlp,
                    'tipoobjeto' => 'lp',
                    'idplantel' => $idobjeto,
                    'idempresa' => cb::idempresa(),
                    'criadopor' => $criadopor,
                    'criadoem' => $criadoem,
                    'alteradopor' => $alteradopor,
                    'alteradoem' => $alteradoem,
                ])::exec();
            }elseif($table == 'empresa'){
                $results = SQL::ini(_LpobjetoQuery::inserirObjeto(), [
                    'idlp' => $idlp,
                    'idobjeto' => $idobjeto,
                    'tipoobjeto' => 'empresa',
                    'criadopor' => $criadopor,
                    'criadoem' => $criadoem,
                    'alteradopor' => $alteradopor,
                    'alteradoem' => $alteradoem
                ])::exec();
            }else{
                $results = SQL::ini(ObjetoVinculoQuery::inserirObjetoVinculo(), [
                    'idobjeto' => $idlp,
                    'tipoobjeto' => '_lp',
                    'idobjetovinc' => $idobjeto,
                    'tipoobjetovinc' => $table,
                    'criadopor' => $criadopor,
                    'criadoem' => $criadoem,
                    'alteradopor' => $alteradopor,
                    'alteradoem' => $alteradoem,
                ])::exec(); 
            }
        }else{
            if($table == 'plantelobjeto'){
                $results = SQL::ini(PlantelObjetoQuery::deletarPlanteisDeUmObjeto(), [
                    'idobjeto' => $idlp,
                    'tipoobjeto' => 'lp',
                ])::exec();
            }elseif($table == 'empresa'){
                $results = SQL::ini(_LpobjetoQuery::apagarObjetoVinculadosNaLp(), [
                    'tipoobjeto' => 'empresa',
                    'idlp' => $idlp
                ])::exec();
            }else{
                $results = SQL::ini(ObjetoVinculoQuery::deletarVinculoPorIdObjetoETipoObjeto(), [
                    'idobjeto' => $idlp,
                    'tipoobjeto' => '_lp',
                    'tipoobjetovinc' => $table,
                ])::exec();
                echo var_dump($results);
            }
        }
        

        if ($results->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        }

        return $results;
    }

    public static function buscarLpPorModuloEIdLp($modulo, $idLp)
    {
        $lps = SQL::ini(_LpModuloQuery::buscarPorModuloEIdLp(), [
            'modulo' => $modulo,
            'idlp' => $idLp
        ])::exec();

        if ($lps->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function BuscarLpPorDescricao($descricao){
        $results = SQL::ini(_LpQuery::buscarLpPorDescricao(), [
            'descricao' => $descricao
        ])::exec();

        if ($results->error() ) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static $ArrayYN = array("Y"=>'Sim',"N"=>"Não");
    public static $ArrayStatus = array("ATIVO"=>"Ativo","INATIVO"=>"Inativo");
}
