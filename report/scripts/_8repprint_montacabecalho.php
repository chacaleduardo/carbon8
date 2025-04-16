<?
/*
* MONTA O CABECALHO
*/
$conteudoexport; // guarda o conteudo para exportar para csv
$strtabheader = "\n<thead><tr class='header'>";
//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
if ($_showtotalcounter == "Y") {
    $strtabheader .= "<td class='tdcounter'></td>";
}

foreach (array_keys($relatorios[0]) as $coluna) {
    $_arridxcol[$_i] = $coluna;
    if ($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["visres"] == 'Y') {

        //A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
        if (!empty($conteudoexport)) {
            $conteudoexport .= ";";
        }

        if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]), ' as ') !== false) {
            $val = explode(' as ', strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]));
            $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"] = $val[1];
        }

        $strtabheader .= "<td class='header' id='" . MenuRelatorioController::urlAmigavel(str_replace('`', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"])) . "' style=\"text-align:" . $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["align"] . "\">" . str_replace('`', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]) . "</td>";
    }
    $conteudoexport .= "\"" . $arrRep["_filtros"][$coluna]["rotulo"] . "\""; // GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
    $_i++;
}

$conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV
$strtabheader .= "</tr></thead><tbody>";

/*
* Variaveis para cabecalho do report
*/
$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão " . $_nomeimpressao . "</legend></fieldset>";
$strtabini = "\n<table class='normal'>";
$strtabheader = $strtabheader;
