<?

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/empresa_query.php");
require_once(__DIR__."/../querys/sgarea_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/sgconselho_query.php");
require_once(__DIR__."/../querys/sgsetor_query.php");
require_once(__DIR__."/../querys/lpobjeto_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/unidade_controller.php");

class OrganogramaController extends Controller
{
    public static function buscarConselhosPorIdConselhoERetornarEmUmArray($arrRetorno = [], $idConselho, $idEmpresa)
    {
		$clausula = null;

		if($idConselho && $idConselho != "_ghost_" )
		{
			$clausula = " AND conselho in($idConselho)";
		}

		if(!$clausula || $idConselho != "_ghost_")
		{
			$j = 3;
			
			$conselhos = SQL::ini(SgConselhoQuery::buscarSgConselhoPorIdEmpresaEClausula(), [
                'idempresa' => $idEmpresa,
                'clausula' => $clausula
            ])::exec();

            if($conselhos->error()){
                parent::error(__CLASS__, __FUNCTION__, $conselhos->errorMessage());
                return [];
            }

			if(!$conselhos->numRows())
			{
				$organograma = [
					"id" 	  		=> $j++,
					"nome"	  		=> "Conselho não encontrado para esta empresa",
					"idobjeto" 		=> 0,
					"pai"	  		=> 2,
					"cor"	  		=> "#666666",
					"tipo"	 		=> "CONSELHO",
					"responsavel" 	=> "",
					"nresp"		  	=> 0
				];

				array_push($arrRetorno, $organograma);

				return $arrRetorno;
			}

			foreach($conselhos->data as $item)
			{
                $unidades = UnidadeController::buscarUnidadeIdObjetoETipoObjeto($item['idsgconselho'], 'sgconselho', getidempresa("u.idempresa", "unidade"));

                $resultPessoa = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaStatusAtivo(), [
                    'idobjeto' => $item['idsgconselho'],
                    'tipoobjeto' => 'sgconselho'
                ])::exec();

				$_count = $resultPessoa->numRows();

                if(!$_count || !$unidades)
                {
                    $pessoas = '';   
                }

                if($unidades)
                {
                    $pessoas = "";

                    foreach($unidades as $unidade)
                    {
                        $pessoas .= "<br><br><span style='font-size: 12px;'>({$unidade["unidade"]})</span>";
                    }
                }

				foreach($resultPessoa->data as $key => $_item)
				{
                    if(!$unidades && $_count == 1)
                    {
                        $pessoas = "<br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";
                        continue;
                    }

                    if($key == 0)
                    {
                        $pessoas .= "<br><br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";    
                        continue;
                    }

					$pessoas .= "<br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";
				}

				$conselhoNome = strtoupper($item['conselho']);

				$organograma = [
					"id" 	  		=> $j++,
					"nome"	  		=> $conselhoNome,
					"idobjeto" 		=> $item["idsgconselho"],
					"pai"	  		=> 2,
					"cor"	  		=> "#666666",
					"tipo"	 		=> "CONSELHO",
					"responsavel" 	=> $pessoas,
					"nresp"		  	=> $_count
				];

				array_push($arrRetorno, $organograma);
			}

			return $arrRetorno;
		}
    }

    public static function buscarAreasPorIdConselhoERetornarEmUmArray($arrRetorno = [], $idArea, $idEmpresa)
    {
        $clausula = "";

		if(!empty($idArea) and $idArea != "_ghost_")
        {
			$clausula = "AND area IN($idArea)";
		}
		
		if(empty($idArea) OR $idArea != "_ghost_"){
			$j = count($arrRetorno) + 1;
			
            $areas = SQL::ini(ObjetoVinculoQuery::buscarAreasPorIdEmpresaEClausula(), [
                'idempresa' => $idEmpresa,
                'clausula' => $clausula
            ])::exec();

			foreach($areas->data as $area){
                $unidades = UnidadeController::buscarUnidadeIdObjetoETipoObjeto($area['idsgarea'], 'sgarea', getidempresa("u.idempresa", "unidade"));

				foreach ($arrRetorno as $key => $value)
				{
					if(($value["idobjeto"] == $area["idobjeto"]) AND ($value["tipo"] == "CONSELHO"))
                    {
						$resultPessoa = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaStatusAtivo(), [
                            'idobjeto' => $area['idsgarea'],
                            'tipoobjeto' => 'sgarea'
                        ])::exec();

                        $_num = $resultPessoa->numRows();

						if(!$_num || !$unidades)
						{
							$aux = '';
						}

                        if($unidades)
                        {
                            $aux = "";

                            foreach($unidades as $unidade)
                            {
                                $aux .= "<br><br><span style='font-size: 12px;'>({$unidade["unidade"]})</span>";
                            }
                        }

						foreach($resultPessoa->data as $key => $_item)
						{
                            if(!$_item["nomecurto"])
                            {
                                continue;
                            }

                            if(!$unidades && $_num == 1)
                            {
                                $aux = "<br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";
                                continue;
                            }

                            if($key == 0)
                            {
                                $aux .= "<br><br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";
                                continue;
                            }

							$aux .= "<br><span style='font-size: 12px;'>({$_item["nomecurto"]})</span>";
						}

						$areaUPPER = mb_strtoupper($area["area"]);
						array_push($arrRetorno,[
							"id"		  => $j++,
							"nome"		  => $areaUPPER,
							"idobjeto"	  => $area["idsgarea"],
							"pai"		  => $value["id"],
							"cor"		  => "#d00038",
							"tipo"		  => "AREA",
							"responsavel" => $aux,
							"nresp"		  => $_num
						]);
					}
				}

			}
		}
		if(empty($idArea) OR $idArea == "_ghost_"){
			array_push($arrRetorno, [
                "id"=> -1 ,
                "nome" => "",
                "idobjeto" => -1,
                "pai" => 2,
                "cor" => "transparent;width:40px !important;height:40px !important;border-radius:20px;",
                "tipo" => "AREA",
                "responsavel" => "",
                "nresp" => 0,
                "classe"=>'left'
            ]);
		}
		
		return $arrRetorno;
    }

    public static function buscarDepartamentosPorIdConselhoERetornarEmUmArray($arrRetorno = [], $idDepartamento, $idEmpresa, $flag = false)
    {
		$clausula = "";

		if(!empty($idDepartamento))
        {
			$clausula = "AND d.departamento IN($idDepartamento)";
		}
		if(!$flag)
        {
			$j = count($arrRetorno)+1;

			$departamentos = SQL::ini(SgDepartamentoQuery::buscarDepartamentosSemVinculoPorIdEmpresaEClausula(), [
                'idempresa' => $idEmpresa,
                'clausula' => $clausula
            ])::exec();

			foreach($departamentos->data as $departamento)
            {
                $unidades = UnidadeController::buscarUnidadeIdObjetoETipoObjeto($departamento['idsgdepartamento'], 'sgdepartamento', getidempresa("u.idempresa", "unidade"));

                $resultPessoa = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaStatusAtivo(), [
                    'idobjeto' => $departamento['idsgdepartamento'],
                    'tipoobjeto' => 'sgdepartamento'
                ])::exec();

				$_num1 = $resultPessoa->numRows();

                if(!$_num1 || !$unidades)
                {
                    $aux1 = "";
                }

                if($unidades)
                {
                    $aux1 = "";

                    foreach($unidades as $unidade)
                    {
                        $aux1 .= "<br><br><span style='font-size: 12px;'><br>({$unidade["unidade"]})</span>";
                    }
                }

                foreach($resultPessoa->data as $key => $pessoa)
                {
                    if(!$unidades && $_num1 == 1)
                    {
                        $aux1 = "<br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";

                        continue;
                    }

                    if($key == 0)
                    {
                        $aux1 .= "<br><br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                        continue;
                    }

                    $aux1 .= "<br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                }

				$deptUPPER = mb_strtoupper($departamento["departamento"]);

				array_push($arrRetorno,[
					"id"		  => $j++,
					"nome"		  => $deptUPPER,
					"idobjeto"	  => $departamento["idsgdepartamento"],
					"pai"		  => -1,
					"cor"         => "#4e73df",
					"tipo"		  => "DEPARTAMENTO",
					"responsavel" => $aux1,
					"nresp"		  => $_num1
				]);
			}
		}

		$j = count($arrRetorno) + 1;

        $departamentos = SQL::ini(ObjetoVinculoQuery::buscarSgDepartamentoPorIdEmpresaEClausula(), [
            'idempresa' => $idEmpresa,
            'clausula' => $clausula
        ])::exec();

		foreach($departamentos->data as $departamento)
        {
            $unidades = UnidadeController::buscarUnidadeIdObjetoETipoObjeto($departamento['idsgdepartamento'], 'sgdepartamento', getidempresa("u.idempresa", "unidade"));

			foreach ($arrRetorno as $key => $value) {
				if(($value["idobjeto"] == $departamento["idobjeto"]) AND ($value["tipo"] == "AREA"))
                {
                    $resultPessoa = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaStatusAtivo(), [
                        'idobjeto' => $departamento['idsgdepartamento'],
                        'tipoobjeto' => 'sgdepartamento'
                    ])::exec();

					$_num = $resultPessoa->numRows();

                    if(!$_num || !$unidades)
                    {
                        $aux = "";
                    }
                    
                    if($unidades)
                    {
                        $aux = "";

                        foreach($unidades as $unidade)
                        {
                            $aux .= "<br><br><span style='font-size: 12px;'>({$unidade["unidade"]})</span>";
                        }
                    }

                    foreach($resultPessoa->data as $key => $pessoa)
                    {
                        if(!$unidades && $_num == 1)
                        {
                            $aux = "<br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                            continue;
                        }

                        if($key == 0)
                        {
                            $aux .= "<br><br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";    
                            continue;
                        }

                        $aux .= "<br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                    }

					$deptUPPER = mb_strtoupper($departamento["departamento"]);
					array_push($arrRetorno,["id"=>$j++,"nome"=>$deptUPPER,"idobjeto"=>$departamento["idsgdepartamento"],"pai"=>$value["id"],"cor"=>"#4e73df","tipo"=>"DEPARTAMENTO","responsavel"=>$aux,"nresp"=>$_num]);
				}
			}
		}
		
		
		return $arrRetorno;
    }

    public static function buscarSetoresPorIdConselhoERetornarEmUmArray($arrRetorno = [], $idSetor, $idEmpresa)
    {
        $clausula = "";

		if(!empty($idSetor))
        {
			$clausula = "AND s.setor IN($idSetor)";
		}

		$j = count($arrRetorno) + 1;

        $setores  = SQL::ini(ObjetoVinculoQuery::buscarSgSetorPorIdempresaEClausula(), [
            'idempresa' => $idEmpresa,
            'clausula' => $clausula
        ])::exec();

		foreach($setores->data as $setor)
        {
            $unidades = UnidadeController::buscarUnidadeIdObjetoETipoObjeto($setor['idsgsetor'], 'sgsetor', getidempresa("u.idempresa", "unidade"));

			foreach ($arrRetorno as $key => $value)
            {
				if(($value["idobjeto"] == $setor["idobjeto"]) AND ($value["tipo"] == "DEPARTAMENTO"))
                {
                    $resultPessoa = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaResponsavelEStatusAtivo(), [
                        'idobjeto' => $setor['idsgsetor'],
                        'tipoobjeto' => 'sgsetor'
                    ])::exec();

					$_num = $resultPessoa->numRows();

                    if(!$_num || !$unidades)
                    {
                        $aux = "";
                    }

                    if($unidades)
                    {
                        $aux = "";

                        foreach($unidades as $unidade)
                        {
                            $aux .= "<br><br><span style='font-size: 12px;'>({$unidade["unidade"]})</span>";
                        }
                    }					

                    foreach($resultPessoa->data as $key => $pessoa)
                    {
                        if(!$unidades && $_num == 1)
                        {
                            $aux = "<br><br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                            continue;
                        }

                        if($key == 0)
                        {
                            $aux .= "<br><br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                            continue;
                        }

                        $aux .= "<br><span style='font-size: 12px;'>({$pessoa["nomecurto"]})</span>";
                    }

					$setorUPPER = mb_strtoupper($setor["setor"]);
					array_push($arrRetorno,["id"=>$j++,"nome"=>$setorUPPER,"idobjeto"=>$setor["idsgsetor"],"pai"=>$value["id"],"cor"=>"#96c965","tipo"=>"SETOR", "responsavel"=> $aux,"nresp"=>$_num]);
				}
			}
		}
		return $arrRetorno;
    }

    public static function buscarEmpresaPorIdEmpresa($idEmpresa)
    {
        $empresa = SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($empresa->error()){
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }

        return $empresa->data[0];
    }

    public static function buscaConselhoPorArea($areasString, $idEmpresa)
    {
        $areas = SQL::ini(SgAreaQuery::buscarSgAreaPorAreaEIdEmpresa(), [
            'area' => $areasString,
            'idempresa' => $idEmpresa
        ])::exec();

		$n = $areas->numRows();

        if(!$n)
        {
            return "_vazio_";
        }

        $areasDeUmConselho = SQL::ini(ObjetoVinculoQuery::buscarAreasDeUmConselhoPorAreaEIdEmpresa(), [
            'area' => $areasString,
            'idempresa' => $idEmpresa
        ])::exec();

        $numRow = $areasDeUmConselho->numRows();

        if(!$numRow)
        {
            return "_ghost_";
        }
        // Conselhos q serao utilizados para filtragem
        $conselhos = [];

        foreach($areasDeUmConselho->data as $conselho)
        {
            $conselhos[] = "'{$conselho['conselho']}'";
        }

        return implode(',', $conselhos);
    }

    public static function buscarAreaPorDepartamento($departamentosString, $idEmpresa)
    {
        $departamentos = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorDepartamentoEIdEmpresa(), [
            'departamento' => $departamentosString,
            'idempresa' => $idEmpresa
        ])::exec();

		$n = $departamentos->numRows();

        if(!$n)
        {
            return "_vazio_";
        }

        $areasResult = SQL::ini(ObjetoVinculoQuery::buscarSgAreaPorDepartamentoEIdEmpresa(), [
            'departamento' => $departamentosString,
            'idempresa' => $idEmpresa
        ])::exec();

        $numRow = $areasResult->numRows();

        if(!$numRow)
        {
            return "_ghost_";
        }

        // Area q serao utilizadas para filtragem
        $areas = [];

        foreach($areasResult->data as $area)
        {
            $areas[] = "'{$area['area']}'";
        }

        return implode(',', $areas);
    }

    public static function buscarDepartamentoPorSetor($setorString, $idEmpresa)
    {
        $departamentosResult = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorSetorEIdEmpresa(), [
            'setor' => $setorString,
            'idempresa' => $idEmpresa
        ])::exec();

		// $res = d::b()->query($sql);
		$numRow = $departamentosResult->numRows();

		if(!$numRow)
        {
			return "_vazio_";
		}

		if($numRow == 1)
        {
			return "'{$departamentosResult->data[0]['departamento']}'";
		}

		// Departamentos q serao utilizados para filtragem
		$departamentos = [];

		foreach($departamentosResult->data as $departamento)
		{
			$departamentos[] = "'{$departamento['departamento']}'";
		}

		return implode(',', $departamentos);
    }

    public static function buscarSetorPorFuncionario($funcionarioString)
    {
        $setoresResult = SQL::ini(PessoaobjetoQuery::buscarSetorPorFuncionario(), [
            'funcionario' => $funcionarioString
        ])::exec();

		$numRow = $setoresResult->numRows();

		if(!$numRow)
        {
			return "_vazio_";
		}

		if($numRow == 1)
        {
			return "'{$setoresResult->data[0]['setor']}'";
		}

		// setores q serao utilizadas para filtragem
		$setores = [];

		foreach($setoresResult->data as $setor)
		{
			$setores[] = "'{$setor['setor']}'";
		}

		return implode(',', $setores);
    }

    public static function buscarPessoasPorIdEmpresa($JsonAux = [], $idEmpresa)
    {
        $pessoas = SQL::ini(PessoaobjetoQuery::buscarPessoasPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

		foreach($pessoas->data as $pessoa){
			$setorUPPER = mb_strtoupper($pessoa["setor"]);
			$nomeUPPER = mb_strtoupper($pessoa["nomecurto"]);
			$cargoUPPER = mb_strtoupper($pessoa["cargo"]);
			array_push($JsonAux,["nome"=>$nomeUPPER,"setor"=>$setorUPPER,"cargo"=>$cargoUPPER]);
		}
		return $JsonAux;
    }

    public static function buscarEmpresasAtivas()
    {
        $empresas = SQL::ini(EmpresaQuery::buscarEmpresasAtivas())::exec();

        if($empresas->error()){
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function buscarSgConselhoPorIdEmpresa($idEmpresa)
    {
        $conselhos = SQL::ini(SgConselhoQuery::buscarSgConselhoPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($conselhos->error()){
            parent::error(__CLASS__, __FUNCTION__, $conselhos->errorMessage());
            return [];
        }

        return $conselhos->data;
    }

    public static function buscarSgAreaPorIdEmpresa($idEmpresa)
    {
        $areas = SQL::ini(SgAreaQuery::buscarSgAreaPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        return $areas->data;
    }

    public static function buscarSgDepartamentoPorIdEmpresa($idEmpresa)
    {
        $departamentos = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($departamentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamentos->errorMessage());
            return [];
        }

        return $departamentos->data;
    }

    public static function buscarSgSetorPorIdEmpresa($idEmpresa)
    {
        $setores = SQL::ini(SgsetorQuery::buscarSgSetorPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        return $setores->data;
    }

    public static function buscarPessoasAtivasComVinculoEmUmSetor()
    {
        $pessoas = SQL::ini(PessoaobjetoQuery::buscarPessoasVinculadasAUmSetor())::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function removerVinculosPorIdObjetoETipoObjeto($idObjeto, $tipoObjeto)
    {
        $deletandoVinculoDaPessoaObjeto = SQL::ini(PessoaObjetoQuery::deletarVinculoPorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        $deletandoVinculoDoObjetoVinculo = SQL::ini(ObjetoVinculoQuery::deletarVinculoPorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        $deletandoVinculoDaLpObjeto = SQL::ini(LpObjetoQuery::deletarVinculoPorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();
    }

    public static function buscarPermissaoVisualizacaoOrganograma($idpessoa)
    {
        $pessoas = SQL::ini(PessoaQuery::buscarPermissaoVisualizacaoOrganograma(),[
            "idpessoa" => $idpessoa
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return "";
        } else {
            return $pessoas->data[0];
        }        
    }
}

?>