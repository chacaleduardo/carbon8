<div id="modal-snippet" class="modal-snippet-action">
    <div class="modal-snippet-action-content">
        <div class="w-100">
            <div class="modal-snippet-action-header flex align-items-center relative w-100 mb-4">
                <span class="mx-auto text-gray-10">Selecione uma opção</span>
                <i class="fa fa-remove text-gray-20 absolute right-0 close-modal pointer"></i>
            </div>
            <div class="modal-snippet-action-body"></div>
        </div>
        <div class="modal-empresa w-100"></div>
   </div>
   <div class="overlay"></div>
</div>
<? if($_GET['_modulo'] && $_GET['_modulo'] == 'modalsnippetacaoapp') { ?>
    <style type="text/Css">
        .close-modal{display: none !important;}
    </style>
    <script type="text/Javascript">
        (_ => {
            montaMenuCarbon(false, false, true);

            ocultarModalEmpresa();
            mostrarBlocoModalSnippetAcao();
            mostrarModalSnippetAcao();
        })();
    </script>
<?}?>