<?
    require_once __DIR__."/../../form/controllers/formularotulo_controller.php";
    require_once(__DIR__."/../../form/controllers/fluxo_controller.php");    

    if($_POST['_1_u_formularotulo_status'] === 'REVISAO' && $_POST['_1_u_formularotulo_idformularotulo'])
    {
        $formulaRotulo = [
            'frasco' => $_POST['frasco'],
            'rotulo' => $_POST['rotulo'],
            'descricao' => $_POST['descricao'],
            'conteudo' => $_POST['conteudo'],
            'programa' => $_POST['programa'],
            'modousar' => $_POST['modousar'],
        ];

        foreach($_POST as $key => $item) {
            $chave = explode('_', $key)[4];
            if($chave)
                $formulaRotulo[$chave] = $item;
        }

        unset($_POST['frasco']);
        unset($_POST['rotulo']);
        unset($_POST['descricao']);
        unset($_POST['conteudo']);
        unset($_POST['programa']);
        unset($_POST['modousar']);

        FormulaRotuloController::versionar($formulaRotulo);
    }

    if($_POST['_1_u_formularotulo_status'] === 'APROVADO' && $_POST['_1_u_formularotulo_idprodservformula']) {
        FormulaRotuloController::atualizarRotuloLote($_POST['_1_u_formularotulo_idprodservformula']);
    }

    if(!$_POST['idfluxostatus'])
    {
        $rowFluxoServico = FluxoController::getidfluxostatusInativo('formularotulo', 'REVISAO', 'INICIO');

        if($rowFluxoServico['idfluxostatus'])
        {
            $_POST['_1_u_formularotulo_idfluxostatus'] = $rowFluxoServico['idfluxostatus'];
            $_POST['_1_u_formularotulo_status'] = 'REVISAO';
        }
    }

    unset($_POST['idfluxostatus']);
?>