$(document).ready(function() {

    $.ui.dialog.prototype._focusTabbable = $.noop;

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

    $("#cancel_add").click(function() {

        var status = $("#display_status").val();
        var name = $("#display_name").val();

        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/members/list/" + status + "/" + name);

    });

    $("#cancel_edit").click(function() {

        var status = $("#display_status").val();
        var name = $("#display_name").val();

        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/members/list/" + status + "/" + name);

    });

    $("#renewal_email").click(function() {

        event.preventDefault();

        $.blockUI();

        //alert("email");

        //{{ path_for('renewals.email', {'id': value.id}) }}

        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/renewals/email/';

    });

    $.validator.setDefaults({
        errorElement: "span",
        errorClass: "help-block",
        highlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            if (element.parent('.input-group').length || element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });

    /*
    $("#save_contact").click(function() {

        //var csrfName = document.getElementById('csrf_name').value;
        //var csrfValue = document.getElementById('csrf_value').value;

        //var csrfData = {"csrf_name" : csrfName,"csrf_value" : csrfValue};

        $.ajax({
            url: "http://localhost/~allanhyde/dev_mpa/public/members/contacts/add/1",
            type: "post",
            //data: csrfData ,
            success: function (response) {
                alert("RESP: " + response);

                //$("#address").css({"background-color" : "red"});
                //window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/';
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }

        });

    });
    */

    $('#contact_update_dialog').dialog({
        autoOpen: false,
        width: 900,
        modal: true,
        beforeClose: function() {
            $("#contact_form").trigger("reset");
        },
        open: function(){
            $(this).siblings('.ui-dialog-buttonset').find('button').blur();
        }
    });

    $("#add_contact").click(function() {

        $(".form-group").removeClass('has-error');
        $(".help-block").html('');

        var memberID = $("#member_id").val();

        //alert("Add Contact");

        $('#contact_update_dialog').dialog({
            buttons: {
                "Cancel" : function() {
                    $(this).dialog("close");
                },
                "Save": function () {

                    $("#contact_form").validate({

                        rules: {
                            contact_firstname: "required",
                            contact_surname: "required"

                        },

                        messages: {
                            contact_firstname: "Please enter your Firstname",
                            contact_surname: "Please enter your Surname"

                        }

                    });

                    if ($("#contact_form").valid()) {

                        var datastring = $("#contact_form").serialize();

                        $.ajax({
                            url: "http://localhost/~allanhyde/dev_mpa/public/members/contacts/add/" + memberID,
                            type: "post",
                            data: datastring,
                            success: function (response) {

                                //alert("RESP: " + JSON.stringify(response));
                                window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/edit/' + memberID;
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(textStatus, errorThrown);
                                alert("Got some error: " + errorThrown);
                            }

                        });

                    }

                    }
                }

            }

        );

        $('#contact_update_dialog').dialog('option', {"title" : "ADD CONTACT"});
        $('#contact_update_dialog').dialog('open');
        $('#contact_update_dialog :button').blur();
        return false;

    });

    $("[id^=edit_contact_]").click(function(event) {

        event.preventDefault();

        var itemDets = $(this).attr('id');
        var splitID = itemDets.split("_");
        var contactID = splitID[2];

        var csrfName = document.getElementById('csrf_name').value;
        var csrfValue = document.getElementById('csrf_value').value;
        var csrfData = {"csrf_name" : csrfName,"csrf_value" : csrfValue};

        $.ajax({
            url: "http://localhost/~allanhyde/dev_mpa/public/members/contacts/get/" + contactID,
            type: "get",
            data: csrfData ,
            success: function (response) {

                var JSONObject = JSON.parse(response);

                $.each(JSONObject, function(i, item) {

                    //$("#contact_" + i).val(item);
                    if(i == 'role'){

                        $('input:radio[name=contact_role]').val([item]);
                        $("label[for=\"" + item + "\"]").addClass("active");

                    } else {

                        $("#contact_" + i).val(item);

                    }

                });

                $('#contact_update_dialog').dialog({
                        buttons: {
                            "Cancel" : function() {
                                $(this).dialog("close");
                            },
                            "Save": function () {

                                $("#contact_form").validate({

                                    rules: {
                                        contact_firstname: "required",
                                        contact_surname: "required"

                                    },

                                    messages: {
                                        contact_firstname: "Please enter your Firstname",
                                        contact_surname: "Please enter your Surname"

                                    }

                                });

                                if ($("#contact_form").valid()) {

                                    var datastring = $("#contact_form").serialize();

                                    $.ajax({
                                        url: "http://localhost/~allanhyde/dev_mpa/public/members/contacts/edit/" + contactID,
                                        type: "post",
                                        data: datastring,
                                        success: function (response) {

                                            //alert("RESP: " + JSON.stringify(response));
                                            window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/edit/1';
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            console.log(textStatus, errorThrown);
                                            alert("Got some error: " + errorThrown);
                                        }

                                    });

                                }

                            }

                        }

                    }

                );

                $('#contact_update_dialog').dialog('option', {"title" : "EDIT CONTACT"});
                $('#contact_update_dialog').dialog('open');
                return false;

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }

        });

    });

});