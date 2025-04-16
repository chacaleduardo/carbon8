<?
require_once(__DIR__ . "/../form/controllers/inclusaoresultado_controller.php");


$infoResultado = InclusaoResultadoController::buscarInformacoesResultadoPorIdResultado($_GET['idresultado']);
$resultado = json_decode($infoResultado['jsonresultado']);
$grupoConfiguracao = json_decode($infoResultado['jsonconfig']);
$jsonCongelado = InclusaoResultadoController::buscarJsonConfigJsonResultadoCongelado($_GET['idresultado']);

function array_some($array, $fn) {
  foreach ($array as $value) {
      if($fn($value)) {
          return true;
      }
  }
  return false;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Emissão resultado</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
        font-size: 60% !important;
    }

    @media print {
			#edify {
				display: none;
			}
		}
  </style>
</head>
<body>
  <header class="flex bg-no-repeat bg-cover py-5 mb-5" style="background-image: url(/form/img/bg-resultado-inata.png);">
    <div class="w-7/12 flex justify-center">
      <img class="h-[85px]" src="./../form/img/logo-laudo.png" alt="Logo empresa" />
    </div>
    <div class="w-5/12 ms-auto flex flex-col text-white">
      <span class="font-bold">
        <?= $infoResultado['razaosocial'] ?> <br>
        CNPJ: <?= $infoResultado['cnpj'] ?> <br>
        <?= $infoResultado['endereco'] ?> - <?= $infoResultado['bairro'] ?>, <?= $infoResultado['cidade'] ?> - <?= $infoResultado['uf'] ?>
      </span>
      <div class="flex gap-3">
        <svg class="w-[11px] fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
          <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
        </svg>
        <span class="font-bold"><?= "({$infoResultado['ddd']}) {$infoResultado['telefone']}" ?></span>
      </div>
      <div class="flex gap-3">
        <svg class="w-[11px] fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
          <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
        </svg>
        <span class="font-bold"><?= $infoResultado['email'] ?></span>
      </div>
    </div>
  </header>
  <main class="">
    <!-- Dados Exame -->
    <div class="border-black border-2 flex rounded px-4 py-2">
      <div class="w-4/12 flex flex-col">
        <div class="flex w-full">
          <strong class="w-6/12">Reg. Amostra: </strong>
          <span><?= $infoResultado['idamostra'] ?></span>
        </div>
        <div class="flex w-full">
          <strong class="w-6/12">Tutor: </strong>
          <span><?= $infoResultado['tutor'] ?? '-' ?></span>
        </div>
        <div class="flex w-full">
          <strong class="w-6/12">Paciente: </strong>
          <span><?= $infoResultado['paciente'] ?? '-' ?></span>
        </div>
        <div class="flex w-full">
          <strong class="w-6/12">Espécie: </strong>
          <span><?= $infoResultado['especie'] ?></span>
        </div>
        <div class="flex w-full">
          <strong class="w-6/12">Sexo: </strong>
          <span><?= $infoResultado['sexo'] ?></span>
        </div>
        <div class="flex w-full">
          <strong class="w-6/12">Idade: </strong>
          <span><?= "{$infoResultado['idade']} - {$infoResultado['tipoidade']}" ?></span>
        </div>
      </div>
      <div class="ms-auto w-4/12 flex flex-col">
        <div class="flex w-full justify-between">
          <strong>Data da Solicitação:</strong>
          <span class="w-7/12"><?= $infoResultado['criadoem'] ?></span>
        </div>
        <div class="flex w-full justify-between">
          <strong>Médico Vet:</strong>
          <span class="w-7/12"><?= $infoResultado['responsavel'] ?? '-' ?></span>
        </div>
        <div class="flex w-full justify-between">
          <strong>CRMV:</strong>
          <span class="w-7/12"><?= $infoResultado['crmv'] ?? '-' ?></span>
        </div>
      </div>
    </div>
    <!-- Exames -->
    <div class="w-100 mb-4">
      <h1 class="uppercase my-10 text-xl text-center fon-bold"><?= $infoResultado['descr'] ?> - Versão <?= $infoResultado['versao'] ?></h1>
      <small class="w-full text-end block my-2">
        <?= $infoResultado['idresultado'] ?>
      </small>
      <hr class="border-2">
      <div class="w-100 mt-2 mb-4">
        <? foreach ($resultado->grupo as $indiceGrupo => $grupo) { ?>
          <!-- Cabecalho -->
          <div class="w-100 flex uppercase font-bold">
            <div class="w-6/12">
              <h3><?= $grupoConfiguracao[$indiceGrupo]->nome ?></h3>
            </div>
            <div class="w-2/12 text-center">
              <h3>Resultados</h3>
            </div>
            <div class="w-4/12 text-end">
              <h3>Valores de referência</h3>
            </div>
          </div>
          <? foreach ($grupo->testes as $indiceTeste => $teste) { ?>
            <!-- Valores -->
            <div class="w-100 flex uppercase font-light">
              <div class="w-6/12">
                <h3><?= $grupoConfiguracao[$indiceGrupo]->testes[$indiceTeste]->nome ?></h3>
              </div>
              <div class="w-2/12 text-end flex <?= $teste->resultadoum && $teste->resultadodois ? 'justify-between' : 'justify-center' ?>">
                <span><?= $teste->resultadoum ?? '-' ?></span>
                <span><?= $teste->resultadodois ?? '-' ?></span>
              </div>
              <div class="w-4/12 text-end">
                <h3><?= "{$grupoConfiguracao[$indiceGrupo]->testes[$indiceTeste]->referencias[0]->valorReferencia->min} - {$grupoConfiguracao[$indiceGrupo]->testes[$indiceTeste]->referencias[0]->valorReferencia->max}" ?></h3>
              </div>
            </div>
          <? } ?>
          <div class="w-100 mt-3 font-light">
            <span><strong class="font-bold"><?= $grupoConfiguracao[$indiceGrupo]->obslabel ?? 'Observações' ?></strong>: <?= $grupo->resultadoobs ?? '-' ?></span>
          </div>
          <hr class="border-2 my-3">
        <? } ?>
      </div>
    </div>
    <!-- Informacoes amostra -->
    <div class="w-100 flex flex-col font-light">
      <span><strong class="font-bold"><?= $infoResultado['titulotextopadrao'] ?></strong> <?= $infoResultado['textopadrao'] ?>.</span>
    </div>
    <div class="w-full flex justify-center flex-col items-center">
      <img src="/inc/img/sig<?= strtolower(trim($infoResultado['idpessoa'])) ?>.gif" alt="Sem assinatura" />
      <span><?= $infoResultado['responsaveltecnico'] ?></span>
      <span><?= $infoResultado['crmvrt'] ?></span>
    </div>
  </main>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            clifford: '#da373d',
          }
        }
      }
    }
  </script>
</body>

</html>