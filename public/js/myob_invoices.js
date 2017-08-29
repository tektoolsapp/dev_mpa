function updateInvoiceDetails(id, UID, num, rowVersion, exportType, numInvoices, thisInvoice){
    //UPDATE EXPORTED INVOICE DETAILS
    $.ajax({
        type: "POST",
        async: false,
        data: {
            "id" : id,
            "UID" : UID,
            "num" : num,
            "row_version" : rowVersion,
            "update_type" : exportType
        },
        url: 'http://localhost/~allanhyde/dev_mpa/public/invoices/myob_update',
        success: function (data) {
            //alert("UPDATED");
            if(numInvoices == thisInvoice) {
                //window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                $("#progress_dialog").dialog({
                    buttons: {
                        "Close" : function() {
                            //$( this ).dialog( "close" );
                            //window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                            var getCustomerName = $("#invoice_customer_name").val();
                            //alert("GCN: " + getCustomerName);
                            if(getCustomerName.length > 0) {
                                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices/name/' + getCustomerName + '');
                            } else {
                                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                            }
                        }
                    }
                });
                $('#progress_dialog').dialog('open');
                $("#progress_dialog").dialog( "option", { "title" : "EXPORT COMPLETE", "width" : 400});
            }
        }
    });
}

function exportInvoice(invoiceId, payload, exportType, InvUID, numInvoices, thisInvoice){

    //EXPORT INVOICE

    $('#progress_dialog').dialog('open');
    $("#progress_dialog").dialog( "option", { "title" : "EXPORTING INVOICES", "width" : 400});

    $.ajax({
         type: "POST",
         async: false,
         data: {
             "type" : exportType,
             "UID" : InvUID,
             "payload" : payload
         },
         url: 'http://localhost/~allanhyde/dev_mpa/public/invoices/myob_export',
         success: function (data) {
            console.log(data);
            var updateObj = JSON.parse(data);
            var invoiceUID = updateObj.UID;
            var invoiceNum = updateObj.Number;
            var rowVersion = updateObj.RowVersion;

            updateInvoiceDetails(invoiceId, invoiceUID, invoiceNum, rowVersion, exportType, numInvoices, thisInvoice)
         }
    });
}

function prepExportInvoice(invoiceId, numInvoices, thisInvoice){
    //PREP INVOICE FOR EXPORT

    /*
    $.blockUI({ css: {
        border: 'none',
        padding: '15px',
        backgroundColor: '#000',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        opacity: .5,
        color: '#fff',
        fontSize:'10px'
    }
    });
    */

    var prepInvExportUrl = "http://localhost/~allanhyde/dev_mpa/public/invoices/prep/" + invoiceId;

    $('#progress_dialog').dialog('open');
    $("#progress_dialog").dialog( "option", { "title" : "EXPORTING INVOICES", "width" : 400});

    $.ajax({
         url: prepInvExportUrl,
         async: false,
         type: "GET",
         success: function (response) {
             //POST THE INVOICE
             var data = JSON.parse(response);
             var InvUID = data.UID;

             if (typeof InvUID !== "undefined") {
                 if(InvUID.length > 0){
                     var exportType = 'edit';
                 } else {
                     var exportType = 'add';
                 }
             } else {
                 var exportType = 'add';
             }

             exportInvoice(invoiceId,response, exportType, InvUID, numInvoices, thisInvoice);
         }
     });
}