<?
include 'vendor/autoload.php';

$codigo_banco = Cnab\Banco::ITAU;
$arquivo = new Cnab\Remessa\Cnab400\Arquivo($codigo_banco);
$arquivo->configure(array(
    'data_geracao'  => new DateTime(),
    'data_gravacao' => new DateTime(), 
    'nome_fantasia' => 'Laudo Laboratório', // seu nome de empresa
    'razao_social'  => 'Laudo Laboratório Avícola Uberlândia LTDA',  // sua razão social
    'cnpj'          => '23259427000104', // seu cnpj completo
    'banco'         => $codigo_banco, //código do banco
    'logradouro'    => 'RODOVIA BR 365, KM 615',
    'numero'        => 'S/N',
    'bairro'        => 'ALVORADA', 
    'cidade'        => 'UBERLÂNDIA',
    'uf'            => 'MG',
    'cep'           => '38407180',
    'agencia'       => '3083', 
    'conta'         => '37033', // número da conta
    'conta_dac'     => '2', // digito da conta
));

/*
 * NF PRODUTO
SELECT 
c.idcontapagar as nosso_numero,
c.idcontapagar as numero_documento,
p.nome as sacado_nome,
if(LENGTH(p.cpfcnpj)=11,'cpf','cnpj') as sacado_tipo,
p.cpfcnpj as sacado_cpf_cnpj,
concat(e.endereco,' ',e.numero,' ',e.complemento) as sacado_logradouro,
e.bairro as sacado_bairro,
e.cep as sacado_cep,
ci.cidade as sacado_cidade,
e.uf as  sacado_uf,
c.datapagto as data_vencimento,
DATE_FORMAT(now(), '%Y-%m-%d') as data_cadastro,
DATE_FORMAT(now(), '%Y-%m-%d') as data_desconto,
DATE_ADD(c.datapagto, INTERVAL 1 DAY) as data_multa
from contapagar c ,nf n,pessoa p,endereco e,nfscidadesiaf ci
where ci.codcidade = e.codcidade
and e.idendereco = n.idendereco
and p.idpessoa = n.idpessoa
and  c.formapagto ='BOLETO'
and c.idobjeto = n.idnf
and c.tipoobjeto='nf'
and n.idnf=12821;
 
 NF SERVICO
 * SELECT 
c.idcontapagar as nosso_numero,
c.idcontapagar as numero_documento,
p.nome as sacado_nome,
if(LENGTH(p.cpfcnpj)=11,'cpf','cnpj') as sacado_tipo,
p.cpfcnpj as sacado_cpf_cnpj,
concat(e.endereco,' ',e.numero,' ',e.complemento) as sacado_logradouro,
e.bairro as sacado_bairro,
e.cep as sacado_cep,
ci.cidade as sacado_cidade,
e.uf as  sacado_uf,
c.datapagto as data_vencimento,
DATE_FORMAT(now(), '%Y-%m-%d') as data_cadastro,
DATE_FORMAT(now(), '%Y-%m-%d') as data_desconto,
DATE_ADD(c.datapagto, INTERVAL 1 DAY) as data_multa
from contapagar c ,notafiscal n,pessoa p,endereco e,nfscidadesiaf ci
where ci.codcidade = e.codcidade
and e.status ='ATIVO'
and e.idtipoendereco = 2
and e.idpessoa = n.idpessoa
and p.idpessoa = n.idpessoa
and  c.formapagto ='BOLETO'
and c.idobjeto = n.idnotafiscal
and c.tipoobjeto='notafiscal'
and n.idnotafiscal=15458;
*/



// você pode adicionar vários boletos em uma remessa
$arquivo->insertDetalhe(array(
    'codigo_ocorrencia' => 1, // 1 = Entrada de título, futuramente poderemos ter uma constante
    'nosso_numero'      => '000000',
    'numero_documento'  => '67405',
    'carteira'          => '104',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Você pode consultar as especies Cnab\Especie
    'valor'             => 0.01, // Valor do boleto
    'instrucao1'        => '00', // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => '00', // preenchido com zeros
    'sacado_nome'       => 'Hermes Pedro Borges', // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => 'cpf', //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => '052.170.716.13',
    'sacado_logradouro' => 'AV. Suiça 771',
    'sacado_bairro'     => 'Tibery',
    'sacado_cep'        => '38405024', // sem hífem
    'sacado_cidade'     => 'uberlândia',
    'sacado_uf'         => 'MG',
    'data_vencimento'   => new DateTime('2017-09-15'),
    'data_cadastro'     => new DateTime('2017-09-01'),
    'juros_de_um_dia'     => 00.03, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime('2017-09-05'),
    'valor_desconto'      => 00.0, // Valor do desconto
    'prazo'               => 10, // prazo de dias para o cliente pagar após o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
    'mensagem'            => ' ',
    'data_multa'          => new DateTime('2017-10-15'), // data da multa
    'valor_multa'         => 02.0, // valor da multa
));

// você pode adicionar vários boletos em uma remessa
$arquivo->insertDetalhe(array(
    'codigo_ocorrencia' => 1, // 1 = Entrada de título, futuramente poderemos ter uma constante
    'nosso_numero'      => '000000',
    'numero_documento'  => '67406',
    'carteira'          => '112',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Você pode consultar as especies Cnab\Especie
    'valor'             => 0.01, // Valor do boleto
    'instrucao1'        => '00', // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => '00', // preenchido com zeros
    'sacado_nome'       => 'Hermes Pedro Borges', // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => 'cpf', //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => '052.170.716.13',
    'sacado_logradouro' => 'AV. Suiça 771',
    'sacado_bairro'     => 'Tibery',
    'sacado_cep'        => '38405024', // sem hífem
    'sacado_cidade'     => 'uberlândia',
    'sacado_uf'         => 'MG',
    'data_vencimento'   => new DateTime('2017-09-15'),
    'data_cadastro'     => new DateTime('2017-09-05'),
    'juros_de_um_dia'     => 00.03, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime('2017-09-15'),
    'valor_desconto'      => 00.0, // Valor do desconto
    'prazo'               => 10, // prazo de dias para o cliente pagar após o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
    'mensagem'            => ' ',
    'data_multa'          => new DateTime('2017-10-15'), // data da multa
    'valor_multa'         => 02.0, // valor da multa
));

$arquivo->insertDetalhe(array(
    'codigo_ocorrencia' => 1, // 1 = Entrada de título, futuramente poderemos ter uma constante
    'nosso_numero'      => '000000',
    'numero_documento'  => '67407',
    'carteira'          => '109',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Você pode consultar as especies Cnab\Especie
    'valor'             => 0.01, // Valor do boleto
    'instrucao1'        => '18', // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => '29', // preenchido com zeros
    'sacado_nome'       => 'Hermes Pedro Borges', // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => 'cpf', //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => '052.170.716.13',
    'sacado_logradouro' => 'AV. Suiça 771',
    'sacado_bairro'     => 'Tibery',
    'sacado_cep'        => '38405024', // sem hífem
    'sacado_cidade'     => 'uberlândia',
    'sacado_uf'         => 'MG',
    'data_vencimento'   => new DateTime('2017-09-15'),
    'data_cadastro'     => new DateTime('2017-09-05'),
    'juros_de_um_dia'     => 00.03, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime('2017-09-15'),
    'valor_desconto'      => 00.0, // Valor do desconto
    'prazo'               => 10, // prazo de dias para o cliente pagar após o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
    'mensagem'            => ' ',
    'data_multa'          => new DateTime('2017-09-15'), // data da multa
    'valor_multa'         => 02.0, // valor da multa
));

/* instrucao DE COBRANÇA
 * 18 DEVOLVER APÓS 90 DIAS DO VENCIMENTO
 * 29 NÃO RECEBER APÓS 55 DIAS DO VENCIMENTO
 */

// para salvar
$arquivo->save('meunomedearquivo.txt');
?>