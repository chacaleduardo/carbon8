<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
$geraarquivo=$_GET['geraarquivo'];
//$gravaarquivo=$_GET['gravaarquivo'];
$idloterotulo=$_GET['idloterotulo'];
$idprodservformula=$_GET['idprodservformula'];
ob_start();
?> 
<html>
<head>
<?
IF(!empty($idloterotulo)){
$sql="select p.*, f.imagemfrasco, f.whidtimagem     
        from loterotulo p , frasco f
        where p.idloterotulo = ".$idloterotulo."
        and p.idfrasco = f.idfrasco";
}elseif($idprodservformula){
$sql="select IFNULL(NULLIF(fr.indicacao, ''), p.indicacao) as indicacao ,
IFNULL(NULLIF(fr.formula, ''), p.formula) as formula,
p.modousar,
p.programa,
IFNULL(NULLIF(fr.cepas, ''), p.cepas) as cepas,
f.*
from formularotulo fr
join prodservformularotulo p on fr.idprodservformularotulo = p.idprodservformularotulo
join frasco f on p.idfrasco = f.idfrasco
where fr.idprodservformula=$idprodservformula";
}
$res = d::b()->query($sql) or die("erro ao buscar rotulo" . mysqli_error(d::b()) . "<p>SQL: ".$sql);
$row=mysqli_fetch_assoc($res);

$alt_rotulo=$row['arotulo']; //altura do rotulo
$alt_ind=$row['aind'];// altura indicacoes
$alt_formula=$row['aformula'];//altura para formula
$alt_modusar=$row['amodousar'];//altura modo de usar
$alt_cepas=$row['acepas'];//altura modo de usar
$alt_descricao=$row['adescricao'];
$alt_accepas=$row['accepas'];


$larg_inf=$row['linf']; //largura do informações
$larg_cepas=$row['lcepas'];//largura do espaço para cepas
$larg_esp=$row['lesp'];//largura espaço vazio
$esp_sob_partida=$row['espsobpart'];// espaço acima da partida
$esp_pos_partida= $row['esppospart'];//padding apos a partida da borda do rotulo

$f_titulo=$row['ftitulo'];//fonte titulo
$f_texto=$row['ftexto'];//fonte do texto
$f_texto_cepas=$row['ftextocepas'];//fonte do texto das cepas
$f_partida=$row['fpartida'];//fonte para partida
$f_descricao=$row['fdescricao'];
$f_imagemfrasco=$row['imagemfrasco'];//Caminho da imagem do Rótulo (Lidiane - 12-03-2020)
$f_larguraimagem=$row['whidtimagem'];//Tamanho da imagem do Rótulo (Lidiane - 12-03-2020)

/*
$alt_rotulo="370"; //altura do rotulo
$larg_inf="270"; //largura do informaçàµes
$larg_cepas="280";//largura do espeço para cepas
$larg_esp="190";//largura espaço vazio
$esp_sob_partida="200";// espaço acima da partida
$esp_pos_partida="15";//padding apos a partida da borda do rotulo

$alt_ind="70";// altura indicacoes
$alt_formula="70";//altura para formula
$alt_modusar="modousar";//altura modo de usar

$f_titulo="9";//fonte titulo
$f_texto="10";//fonte do texto
$f_texto_cepas="12";//fonte do texto das cepas
$f_partida="9";//fonte para partida

 */
?>    
<style>

@media screen{
    .rotulo500{        
        height: <?=$alt_rotulo?>px;
        border: 1px solid black;
        font-family:Helvetica;
        border-spacing:0px;
        padding-left: 5px;
    }
}
@media print {
    .rotulo500{       
        height: <?=$alt_rotulo?>px;      
        font-family:Helvetica;
        border-spacing:0px;
        padding-left: 5px;
    }
	
	.imagemfundo{
		display: none !important;
	}
}

    .informacoes{
        width: <?=$larg_inf?>;       
        vertical-align: top;         
        position: relative;
        float: left;  
        padding:0px;
    }
    .titulo{
        font-weight:bold;
        font-size: <?=$f_titulo?>px;
        padding:0px;
    }
    .texto{
        font-size: <?=$f_texto?>px;
        font-family:Helvetica;
        padding:0px;
        text-align: justify;
        padding:0px;
    }
    .textoend{
               font-size: <?=$f_texto-1?>px;
        font-family:Helvetica;
        padding:0px;
        text-align: justify;
        padding:0px; 
    }
    .indicacoes{
        margin-top: <?=$alt_ind?>px;
        text-align: justify;
    }
    .formula{
        height: <?=$alt_formula?>px;
        text-align: justify;
    }
    .modousar{
        height: <?=$alt_modusar?>px;
        text-align: justify;
    }
    .endereco{}

    .espaco{
        width: <?=$larg_esp?>px;      
        position: relative;  
       
    }

    .formula td{
        padding:0px !important;
    }
    .cepas{
        width: <?=$larg_cepas?>px;        
    }
    .tabcepas{
         width: <?=$larg_cepas?>;  
         margin-right:auto; 
         margin-left:auto;
         margin-top: <?=$alt_accepas?>px;
    }
    .tdcepas{           
        /*font-weight:bold;*/
        font-size: <?=$f_texto_cepas?>px; 
      /*  width: 280px; */
    }
    .tddescricao{           
        font-weight:normal;
		font-weight:bold;
        font-size: <?=$f_descricao?>px; 
      /*  width: 280px; */
    }

    .espacocabpart{
        height: <?=$esp_sob_partida?>px;
    }
    .partida{       
        float: right;       
        padding-right: <?=$esp_pos_partida?>px;
        font-size: <?=$f_partida?>px;
        font-family:Helvetica;
        font-weight:bold;
        
    } 
	.imagemfundo{
		border: none;
		position:fixed;
		z-index:-100;
	}
    html{margin:1px 1px}
</style> 
<?if(!empty($row['idlote'])){
    $sqlt="select l.partida,l.exercicio from lote l where l.idlote= ".$row['idlote'];
    $rest = d::b()->query($sqlt) or die("erro ao buscar lote para o titulo" . mysqli_error(d::b()) . "<p>SQL: ".$sqlt);
    $rowt=mysqli_fetch_assoc($rest);
        
}

  ?>
<title><?=$rowt['partida']?>-<?=$rowt['exercicio']?></title>
</head>
<body>	
	<img class="imagemfundo" id="imagemfundo" border="0" src="<?=$f_imagemfrasco?>" width="<?=$f_larguraimagem?>px"/>
    <table class="rotulo500">
        <tr>
            <td class="informacoes">
                <table class="indicacoes">
                    <tr>
                        <td class="titulo">INDICAÇÕES</td>
                    </tr>
                    <tr>
                        <td class="texto">
                        <?=$row["indicacao"]?>
                        </td>
                    </tr>
                </table>
                
                <table class="formula">
                    <tr>
                        <td class="titulo">FÓRMULA:</td>
                    </tr>
                    <tr>
                        <td  class="texto">
                        <?=nl2br(espaco2nbsp($row["formula"]))?>
                        <?//=$row["formula"]?>
                        </td>
                    </tr>                             
                </table>

                <table class="modousar">
                    <tr>
                        <td class="titulo">MODO DE USAR:</td>
                    </tr>
                    <tr>
                        <td  class="texto">
                            <?=$row["modousar"]?>       
                        </td>
                    </tr>
                    <?
                    if(!empty($row["programa"])){
                    ?>
                    <tr>
                        <td  class="texto">
                            <?=$row["programa"]?>       
                        </td>
                    </tr>
                    <?
                    }
                    ?>
                </table>
        
                <table class="endereco">
                    <tr>
                        <td class="titulo">PROPRIEDADE A QUE SE DESTINA:</td>
                    </tr>
                    <tr>
                        <td  class="texto">
						<p>
                            <?//=$row["endereco"]?>    
                            <?=nl2br(espaco2nbsp($row["endereco"]))?></p>
                        </td>
                    </tr>                    
                </table>

            </td>
            <td class="cepas" >
                <table class="tabcepas">
                    <tr>
                        <td  class="tdcepas" style="text-align:center; vertical-align: top;">
                            <?=nl2br(espaco2nbsp($row["cepas"]))?>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: <?=$alt_cepas?>px"></td>
                    </tr>
                    <tr>
                        <td class="tddescricao" style="text-align:center;  vertical-align: top; height:5px">
                            <?if(!empty($row["descricao"])){?>
								<br>
								<?=nl2br(espaco2nbsp($row["descricao"]))?>
							<?}?>
							<?if(!empty($row["conteudo"])){?>
								<br>
								<?=nl2br(espaco2nbsp($row["conteudo"]))?>
							<?}?>
                            
                        </td>
                    </tr>
                    <tr>
                        <td style="height: <?=$alt_descricao?>px"></td>
                    </tr>
                </table>
            </td>
            <td class="espaco" >&nbsp;</td>
            <td >
                <table>
                    <tr>
                        <td class="espacocabpart" >&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="partida">
                          <?=nl2br(espaco2nbsp($row["partida"]))?> 
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<?
if($geraarquivo=='Y'){

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	//echo($html);die;


	// Incluà­mos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");
	 
	

	// Instanciamos a classe
	$dompdf = new DOMPDF();
	 
	// Passamos o conteúdo que será convertido para PDF
	$dompdf->load_html($html);
	
	//$dompdf->option('defaultFont', 'Times New Roman?');

	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	$dompdf->render();

        // e exibido para o usuário
        $dompdf->stream("rotulo".$_1_u_nf_idnf.".pdf");

}

?>
