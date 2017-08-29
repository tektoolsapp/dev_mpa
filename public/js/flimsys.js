$(document).ready(function() {

    $(".datepicker").datepicker({
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true
    });

    $(".date_start").datepicker({
        beforeShow: function(input, instance) {
            $(".datestart").datepicker('setDate', new Date());},
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true
    });

    $(".date_end").datepicker({
        beforeShow: function(input, instance) {
            $("#date_end").datepicker('setDate', new Date());},
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true,
        beforeShowDay: function(date){
            return [(date >= ($(".date_start").datepicker("getDate")
            || new Date()))];
        }
    });

    $("#select_member").autocomplete({
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

        $.ajax({
            url: 'http://localhost/~allanhyde/dev_mpa/public/flimsys/member/' + item,
            type: "GET",
            success: function (response) {
                //alert("RESP:" + JSON.stringify(response));
                //alert("CID: " + response.member.customer_id);
                $("#customer_id").val(response.member.customer_id);
                $("#business_address").val(response.member.business_address);
                $("#business_suburb").val(response.member.business_suburb);
                $("#business_state").val(response.member.business_state);
                $("#business_postcode").val(response.member.business_postcode);
                $("#accounts_email").val(response.member.accounts_email);
                $("#payment_method").val(response.member.payment_method);
                //RENDER THE CONTACT OPTIONS
                var contactSelect = '';
                $.each(response.contacts, function (i) {
                    contactSelect += '<option value="' + response.contacts[i].id + '">' + response.contacts[i].fullname + '</option>';
                });
                //console.log("CS: " + contactSelect);
                $("#ordered_by").append(contactSelect);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                alert("Got some error: " + errorThrown);
            }
        });
    }

    $("#ordered_by").change(function(){
        var contact = $(this).val();
        //alert("contact: " + contact);
        $.ajax({
            url: 'http://localhost/~allanhyde/dev_mpa/public/flimsys/contact/' + contact,
            type: "GET",
            success: function (response) {
                $("#order_firstname").val(response.firstname);
                $("#order_surname").val(response.surname);
                $("#order_phone").val(response.phone);
                $("#order_email").val(response.email);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                alert("Got some error: " + errorThrown);
            }
        });
    });

    $("#cancel_add").click(function(){
        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/flimsys");
    });

    $("#cancel_edit").click(function(){

        var updateSource = $("#update_source").val();

        if(updateSource == 'F'){
            window.location.replace("http://localhost/~allanhyde/dev_mpa/public/flimsys");
        } else {
            var getCustomerName = $("#invoice_customer_name").val();
            //alert("CN: " + getCustomerName);
            if(getCustomerName.length > 0) {
                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices/name/' + getCustomerName + '');
            } else {
                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
            }
        }
    });

    function updateFlimsyInvoice(Id, source){

        var flimsyUpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/flimsys/order/' + Id;

        $.ajax({
            url: flimsyUpdateUrl,
            type: "POST",
            //data: datastring,
            success: function (response) {
                //alert("RESP: " + response)
                if(source == 'F') {
                    window.location.replace('http://localhost/~allanhyde/dev_mpa/public/flimsys');
                } else {
                    //window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                    var getCustomerName = $("#invoice_customer_name").val();
                    if(getCustomerName.length > 0) {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices/name/' + getCustomerName + '');
                    } else {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }

    $("#submit_flimsy").click(function() {

        var source = $("#update_source").val();
        //alert("SRC: " + source);

        var mode = $("#update_flimsy_mode").val();
        var payment_status = $("#payment_status").val();
        var invoiceId = $("#invoice_id").val();
        //alert("PS: " + payment_status);
        var flimsyId = $("#flimsy_id").val();

        if(mode == 'add') {
            var flimsyUpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/flimsys/add';
        } else if(mode == 'edit'){
            var flimsyUpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/flimsys/edit/' + flimsyId;
        }

        var datastring = $("#flimsy_update").serialize();

        $.ajax({
            url: flimsyUpdateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                //alert("RESP: " + response);
                if(response == 'errors'){
                    //$.unblockUI();
                    if(mode == 'edit') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/flimsys/get/' + flimsyId + "/" + source + "/" + payment_status);
                    } else if(mode == 'add') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/flimsys/new');
                    }
                } else {
                    //alert("FLIMSY OK WITH SRC: " + source);
                    if(payment_status == 'I' && mode == 'edit') {
                        updateFlimsyInvoice(flimsyId, source);
                    } else {
                        if(source == 'F') {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/flimsys');
                        } else {
                            var getCustomerName = $("#invoice_customer_name").val();
                            if(getCustomerName.length > 0) {
                                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices/name/' + getCustomerName + '');
                            } else {
                                window.location.replace('http://localhost/~allanhyde/dev_mpa/public/invoices');
                            }
                        }
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    });

    $("#price_flimsy").click(function(){

        var currentOrderTotal = $("#order_total").val();

        if($(this).is(":checked")){
            currentOrderTotal = (parseFloat(currentOrderTotal) + 20).toFixed(2);;
        } else {
            currentOrderTotal = (parseFloat(currentOrderTotal) - 20).toFixed(2);;
        }

        $("#order_total").val(currentOrderTotal);
    });

    $("#price_sewer_junction").click(function(){

        var currentOrderTotal = $("#order_total").val();

        if($(this).is(":checked")){
            currentOrderTotal = (parseFloat(currentOrderTotal) + 10).toFixed(2);;
        } else {
            currentOrderTotal = (parseFloat(currentOrderTotal) - 10).toFixed(2);;
        }
        $("#order_total").val(currentOrderTotal);
    });

    /*
    function createFlimsyInvoice(data) {
        alert("create invoice with data: " + data)
    }
    */

    $("[id^=process_payment_]").click(function(event){

        event.preventDefault();

        var paymentId = $(this).prop('id');
        var paymentMethodArray = paymentId.split('_');
        var requestId = paymentMethodArray[2];
        var paymentMethod = paymentMethodArray[3];

        alert("METHOD: " + paymentMethod );

        if(paymentMethod == 'C'){
            //GENERATE AN INVOICE TX
            //GENERATE AN INVOICE PDF
            //EMAIL INVOICE PDF TO CUSTOMER
            //UPDATE FLIMSY PAYMENT STATUS TO INVOICED
            //ADD AN INVOICE EXPORT CHECKBOX TO FLIMSY INDEX

            var flimsyOrderUrl = 'http://localhost/~allanhyde/dev_mpa/public/flimsys/order/' + requestId;

            $.ajax({
                url: flimsyOrderUrl,
                type: "POST",
                //data: datastring,
                success: function (response) {
                    //var data = JSON.stringify(response, null, 4);
                    //alert("RESPX: " + data);
                    //createFlimsyInvoice(data);
                    window.location.replace('http://localhost/~allanhyde/dev_mpa/public/flimsys');
                }
            });

        } else {
            //PROCESS A CARD TRANSACTION
            //GENERATE AN INVOICE TX

        }

    });

    $("[id^=update_invoice_]").click(function(event){

        event.preventDefault();

        var update = $(this).prop('id');
        var updateArray = paymentId.split('_');
        var invoiceId = updateArray[1];

        alert("invoice: " + invoiceId );

        /*
        if(paymentMethod == 'C'){
            //GENERATE AN INVOICE TX
            //GENERATE AN INVOICE PDF
            //EMAIL INVOICE PDF TO CUSTOMER
            //UPDATE FLIMSY PAYMENT STATUS TO INVOICED
            //ADD AN INVOICE EXPORT CHECKBOX TO FLIMSY INDEX

            var flimsyOrderUrl = 'http://localhost/~allanhyde/dev_mpa/public/flimsys/order/' + requestId;

            $.ajax({
                url: flimsyOrderUrl,
                type: "POST",
                //data: datastring,
                success: function (response) {
                    var data = JSON.stringify(response, null, 4);
                    alert("RESP: " + data);
                    createFlimsyInvoice(data);
                }
            });

        } else {
            //PROCESS A CARD TRANSACTION
            //GENERATE AN INVOICE TX

        }
        */

    });

});