<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "loteativ";
$pagvalcampos = array(
	"idloteativ" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from loteativ where idloteativ = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
$sql = "SELECT 
            l.partida, l.exercicio, l.idlote, p.descr, p.idtipoprodserv,t.tipoprodserv,b.tipoanalise,b.idespeciefinalidade,b.idadeinicial,b.idadefinal,b.idbioterioanalise
        FROM
            loteativ a
                JOIN
            lote l ON (l.idlote = a.idlote)
                JOIN
            prodserv p ON (l.idprodserv = p.idprodserv)
            join 
            tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
            join 
            bioterioanalise b on(t.idtipoprodserv=b.idtipoprodserv and b.status='ATIVO' and b.cria='N' and b.idespeciefinalidade is not null and b.idadeinicial is not null and b.idadefinal is not null )
        WHERE
            a.idloteativ = ".$_1_u_loteativ_idloteativ;

      

$res = d::b()->query($sql) or die("A Consulta das configurações falhou :".mysql_error()."<br>Sql:".$sql); 
$qtdrows= mysqli_num_rows($res);
if($qtdrows<1){ die('Verificar configuração do produto da vacina se possui Subcategoria.');}

$ifi=0;					
while($row = mysqli_fetch_assoc($res)){
    $ifi++;		
?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-1 nowrap">
                </div>
                <div class="col-md-11 nowrap">
                    <h3>
                        <i class="fa icon-pesquisa7 fa-1x fade pointer hoverazul" title="Copiar link deste item" onclick="copiaLink()"></i>
                        <b><?=$row['tipoanalise']?>
                        </b>
                    </h3>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
<?


        $sql1 = "SELECT
                    r.idresultado
                    ,b.idregistro
                    ,b.exercicio
                    ,b.idbioensaio
                    ,p.descr
                    ,r.status
                FROM bioensaio b,analise a,servicoensaio s,resultado r,prodserv p
                WHERE b.idloteativ= $_1_u_loteativ_idloteativ
                and a.idbioterioanalise=".$row['idbioterioanalise']."
                AND a.idobjeto = b.idbioensaio
                AND a.objeto = 'bioensaio'
                AND s.idobjeto = a.idanalise
                AND s.tipoobjeto ='analise'
                AND r.idservicoensaio = s.idservicoensaio
                AND p.idprodserv = r.idtipoteste";
        $res1 = d::b()->query($sql1) or die("erro ao buscar bioensaio vinculado: ".mysqli_error(d::b()));
        $qtd1 = mysqli_num_rows($res1);
        if( $qtd1>0){
?>
    <div>
<?
            while($row1=mysqli_fetch_assoc($res1)){
                $cor = $row1['status'] == "ASSINADO" ? "verde !important" : "vermelho !important";
?>
						<div class="checkbox checked">
							<span class="<?=  $cor?>"><?=$row1['descr']?></span>
							<a target="_blank" href="?_modulo=bioensaio&_acao=u&idbioensaio=<?=$row1['idbioensaio']?>" title="B<?=$row1['idregistro']?>/<?=$row1['exercicio']?>">B<?=$row1['idregistro']?></a>
                            <i target="_blank" title="Registro Operacional" class="fa fa-print  pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relbioensaio.php?idbioensaio=<?=$row1['idbioensaio']?>')"></i>
						</div>

<?
            }
?>
    </div>
<?
        }else{
?>

                  <table>
                    <tr>
                        <td>Estudos Disponíveis:</td>
                        <td>
                            <select class='size20' name=""  id="idbioensaio<?=$ifi?>" >
                                <option value=""></option>
                                <? fillselect("SELECT 
                                                    b.idbioensaio,
                                                    CONCAT('B',
                                                            b.idregistro,
                                                            '/',
                                                            b.exercicio,
                                                            ' Idade ',
                                                            DATEDIFF(NOW(), b.nascimento),
                                                            ' dias') AS estudo
                                                FROM
                                                    bioensaio b 
                                                WHERE
                                                    b.idespeciefinalidade = ".$row['idespeciefinalidade']."
                                                        AND b.status = 'DISPONIVEL'
                                                        AND (b.idpessoa IS NULL OR b.idpessoa = '')
                                                        AND b.nascimento IS NOT NULL
                                                        AND b.idregistro IS NOT NULL
                                                        AND b.nascimento BETWEEN DATE_SUB(NOW(), INTERVAL ".$row['idadefinal']." DAY) AND DATE_SUB(NOW(), INTERVAL  ".$row['idadeinicial']." DAY)
                                                ORDER BY b.nascimento,b.idregistro"); ?>
                            </select>
                        </td>
                        <td>Início:</td>
                        <td>
                            <input idanalise="<?= $row['idanalise'] ?>" id="datadzero<?=$ifi?>" class="calendario datadzero" name="datadzero<?=$ifi?>" type="text" value="">
                        </td>
                        <td><i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="selbioensaio(<?=$ifi?>,<?=$_1_u_loteativ_idloteativ?>,<?=$row['idbioterioanalise']?>)" title="Inserir novo Protocolo"></i></td>
                    </tr>
                  </table>
<?
        }
?>
                </div>
            </div>
        </div>
    </div>
</div>
<?
    }
?>
<script>


function selbioensaio(linha,idloteativ,idbioterioanalise){

    const idbioensaio=$('#idbioensaio'+linha).val();


    if ($("[name=datadzero" + linha + "]").val() == "") {
        alertAtencao('Por favor, preencha o campo de data');
        $("[name=datadzero" + linha + "]").focus();
        return false
    } else {
        CB.post({
            objetos: "analise_idobjeto=" + idbioensaio + "&analise_objeto=bioensaio&_x_u_analise_idanalise=1&_x_u_analise_datadzero=" + $("[name=datadzero" + linha + "]").val() + "&_x_u_analise_idbioterioanalise=" + idbioterioanalise+ "&idloteativ="+idloteativ
            ,parcial:true
        });
    }
          
}

</script>