<?
require_once("../inc/php/validaacesso.php");

?>
<style>
    #notificacoesContent{
        width: 50%;
        margin: auto;
    }

    #_ncBellItens_{
        max-height: none;
    }

    #notificacoesContent ._ncBellItens_{
        max-height: none !important;
    }
    #notificacoesContent ._ncBellFiltrosModulos_{
        max-width: none !important;
    }
    @media screen and (max-width: 800px){
        #notificacoesContent{
            width: 100%;
        }

        #notificacoesContent ._ncBellFiltrosModulos_{
            max-width: 420px !important;
        }
    }
</style>

<div id="notificacoesContent"></div>

<script>
    NV.isSnippet = false;
    NV.init($("#notificacoesContent"));
    NV.on('preInit', function( view ){
        if(view.mod == 'notificacoes'){
            let urlParams = new URLSearchParams(window.location.search);
            let mod = urlParams.get('mod');

            view.selector = $("#notificacoesContent");
            view.selectorStr = view.selector.selector;
            view.isSnippet = false;

            if(mod != null){
                view.controller.filtroModulos.push(mod);
            }
        }
    });
</script>