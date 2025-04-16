<? require_once("../inc/php/validaacesso.php"); ?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
    <script src="../inc/js/functions.js"></script>
    <link href="../inc/css/fontawesome/font-awesome.min.css" rel="stylesheet">
    <title>Impressão Danfe</title>
</head>
<style>
    body {
        margin: 0;
        overflow: hidden;
    }

    .navbar {
        overflow: hidden;
        background-color: #333;
        position: fixed;
        bottom: 0;
        width: 100%;
    }

    .navbar a {
        float: left;
        display: block;
        color: #f2f2f2;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
        font-size: 17px;
    }

    .navbar a:hover {
        background: #f1f1f1;
        color: black;
    }

    .navbar a.active {
        background-color: #04AA6D;
        color: white;
    }

    .btn_acao {
        cursor: pointer;
    }
</style>

<body>

    <div class="navbar">
        <a style="margin-left: 47%;" href="" target="_blank" class="active">IDNF <span id="numnf"></span></a>
        <a style="margin-left: -216px;" acao="prev" class="btn_acao">Anterior</a>
        <a acao="next" class="btn_acao">Próxima</a>
    </div>
    <div id="appenddiv">

    </div>

</body>

</html>


<?
$queryidcomxml = "select idnf from nf where idnf in (" . $_GET['idnf'] . ") and xmlret is not null";
$queryid = d::b()->query($queryidcomxml) or die($queryidcomxml . "--- printdanfe.php --- Erro ao verificar se o idnf tem xmlret: " . mysql_error());
$arrid = [];

while ($_row = mysql_fetch_array($queryid)) {
    $arrid[] = $_row[0];
}
?>

<script>
    var jArray = <?php echo json_encode($arrid); ?>;
    var i = 0;
    var url = window.location.origin + "/?_modulo=pedido&_acao=u&idnf=";

    function callIframe(idnf) {
        $("#appenddiv").html(`
            <iframe style="width: 100%;height: 100vh;" id="printnf_${idnf}"  name="iframe" src="../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=${idnf}" title="N° Pedido ${idnf}"> </iframe>
        `);
    }

    $(document).ready(function() {
        $(window).ready(function() {
            $('[acao="prev"]').hide();
            $('.active').attr('href', url + jArray[i]);
            $("#numnf").text(jArray[i]);
            callIframe(jArray[i]);

            if (jArray.length == 1) {
                $('[acao="next"]').hide();
                $('[acao="prev"]').hide();
            }
        })
    })

    $(".btn_acao").on('click', function() {
        let acao = $(this).attr('acao');

        if (acao == "next") {
            i++;
            callIframe(jArray[i]);
        } else {
            i--;
            callIframe(jArray[i]);
        }

        $('.active').attr('href', url + jArray[i]);
        $("#numnf").text(jArray[i]);
        $(".btn_acao").show();

        if (i == 0) {
            $('[acao="prev"]').hide();
        } else if (i == jArray.length - 1) {
            $('[acao="next"]').hide();
        }
    });
</script>