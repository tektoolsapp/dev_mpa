$(document).ready(function() {

    $.ui.dialog.prototype._focusTabbable = $.noop;

    $('#progress_dialog').dialog({
        autoOpen: false,
        width: 600,
        modal: true,
        buttons: {},
        open: function(){
            $(this).siblings('.ui-dialog-buttonpane').find('button').blur();
        }
    });

    $(".datepicker").datepicker({
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true
    });

    $("#select_customer").autocomplete({
        source: 'http://localhost/~allanhyde/dev_mpa/public/members/auto',
        minLength: 3,
        delay: 10,
        search: function(){
            $(this).addClass('ui-autocomplete-loading');
        },
        open: function(){$(this).removeClass('ui-autocomplete-loading');},
        select: function(event, ui) {getMemberDets(ui.item.value)}
    });

    function getMemberDets(item){
        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/invoices/name/' + item;
    }

    $(".date_start").datepicker({
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true,
        onSelect: function (selected) {
            var splitDate = selected.split("-");
            var updated = splitDate[1] + "-" + splitDate[0] + "-" + splitDate[2];
            var dt = new Date(updated);
            //dt.setDate(dt.getDate() + 1);
            dt.setDate(dt.getDate());
            $(".date_end").datepicker("option", "minDate", dt);
        }
    });
    $(".date_end").datepicker({
        //numberOfMonths: 2,
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true,
        onSelect: function (selected) {
            var splitDate = selected.split("-");
            var updated = splitDate[1] + "-" + splitDate[0] + "-" + splitDate[2];
            var dt = new Date(updated);
            //dt.setDate(dt.getDate() - 1);
            dt.setDate(dt.getDate());
            $(".date_start").datepicker("option", "maxDate", dt);
        }
    });

    $("#export_invoices").click(function() {

        var sel_inv = [];

        $('input[type=checkbox]').not("[disabled]").each(function () {
            if ($(this).prop('checked') && $(this).prop('className') == 'invoice') {
                sel_inv.push($(this).prop('name'));
            }
        });

        if(sel_inv.length > 0) {

            $('#progress_dialog').dialog('open');
            $("#progress_dialog").dialog( "option", { "title" : "EXPORTING INVOICES", "width" : 400});
            //return false;

            var numInvoices = sel_inv.length

            for (var i = 0, l = sel_inv.length; i < l; i++) {
                var invoiceId = sel_inv[i];
                var thisInvoice = i + 1;
                prepExportInvoice(invoiceId, numInvoices, thisInvoice);
            }

        } else {

           // alert("error");
            //THROW AN ERROR MESSAGE
            var errorUrl = "http://localhost/~allanhyde/dev_mpa/public/invoices/errors";

            $.ajax({
                url: errorUrl,
                type: "POST",
                data: {
                    "error_type": 'N'
                },
                success: function (response) {
                    window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                    //alert("RESP:" + response);
                }
            });
        }
    });

});