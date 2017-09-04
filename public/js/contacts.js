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

    $('#filter_contacts_dialog').dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        buttons: [
            {
                text: "Filter",
                click: function() {
                    filterContacts();
                },
                "class":"ui-button-inverse"
            },
            {
                text: "Close",
                click: function() {
                    $(this).dialog("close");
                }
            }
        ],
        open: function(){
            $(this).siblings('.ui-dialog-buttonset').find('button').blur();
        }
    });

    $("#filter_contacts").click(function(event) {
        event.preventDefault();

        $('#filter_contacts_dialog').dialog('option', {"title" : "FILTER CONTACTS DISPLAY"});
        $('#filter_contacts_dialog').dialog('open');
        $('#filter_contacts_dialog :button').blur();
        return false;
    });

    function isEmpty(obj) {
        for(var prop in obj) {
            if(obj.hasOwnProperty(prop))
                return false;
        }
        return true;
    }

    function getFormData($form){
        var unindexed_array = $form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function(n, i){
            var thisName = n['name'];
            var thisValue = n['value'];
            if((thisName.substring(0, 7) != 'contact' && thisName.substring(0, 6) != 'update' && thisName.substring(0, 13) != 'select_member' && thisValue.length > 0)) {
                if(thisName == 'journal_opt' && thisValue == 'A') {
                    //IGNORE
                } else {
                    indexed_array[n['name']] = n['value'];
                }
            }
        });

        return indexed_array;
    }

    function filterContacts() {
        var $form = $("#filter_contacts_form");
        var filterData = getFormData($form);

        if(isEmpty(filterData)) {
            filterQueryString = 'all';
        } else {
            var filterQueryString = 'filter?';
            var filterCounter = 0;

            $.each(filterData, function(key, value) {
                //console.log(key, value);
                if(filterCounter == 0) {
                    filterQueryString += key + "=" + value;
                } else {
                    filterQueryString += "&" + key + "=" + value;
                }
                filterCounter++;
            });
        }
        //console.log(encodeURI(filterQueryString));

        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/contacts/list/' + filterQueryString;
    }

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

    $.validator.addMethod("notEqual", function(value, element, param) {
        return this.optional(element) || value != param;
    }, "A XStatus must be selected");

    $.validator.addMethod("exactlength", function(value, element, param) {
        return this.optional(element) || value.length == param;
    }, $.validator.format("Please enter exactly {0} characters."));

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

        var memberID = $("#member_id").val();
        var updateType = $("#update_type").val();
        $("#contact_type").val(updateType);

        alert("MID: " + memberID + " UDT: " + updateType);

        $(".form-group").removeClass('has-error');
        $(".help-block").html('');

        $("#contact_members_id").val(memberID);
        $('#contact_form')[0].reset();


        $('#role_o').parent().addClass('active');
        //SET THE DEFAULT VALUE
        $('#role_o').prop('checked', true);
        $('#journal_y').parent().addClass('active');
        //SET THE DEFAULT VALUE
        $('#journal_y').prop('checked', true);;

        if(updateType == 'S') {
            alert("disabling");
            $('#role_p').prop('disabled', true);
            $('#role_v').prop('disabled', true);
        }

        $('#contact_update_dialog').dialog({
                buttons: {
                    "Cancel" : function() {
                        $(this).dialog("close");
                    },
                    "Save": function () {

                        $("#contact_form").validate({
                            rules: {
                                contact_firstname: "required",
                                contact_surname: "required",
                                contact_phone: {
                                    required: true,
                                    exactlength: 10
                                },
                                contact_mobile: {
                                    exactlength: 10
                                },
                                contact_fax: {
                                    exactlength: 10
                                },
                                contact_email : {
                                    required: true,
                                    email: true
                                },
                                contact_status : {
                                    notEqual: "N"
                                }
                            },
                            messages: {
                                contact_firstname: "Firstname must not be empty",
                                contact_surname: "Surname must not be empty",
                                contact_phone: {
                                    required: "Phone must not be empty",
                                    exactlength: "Enter as 10 digits with no spaces or symbols"
                                },
                                contact_mobile: {
                                    exactlength: "Enter as 10 digits with no spaces or symbols"
                                },
                                contact_fax: {
                                    exactlength: "Enter as 10 digits with no spaces or symbols"
                                },
                                contact_email: {
                                    required: "Email must not be empty",
                                    email: "Email address is not valid"
                                },
                                contact_status: "A Status must be selected"
                            }
                        });

                        if ($("#contact_form").valid()) {
                            var datastring = $("#contact_form").serialize();

                            $.ajax({
                                url: 'http://localhost/~allanhyde/dev_mpa/public/contact/add',
                                type: "post",
                                data: datastring,
                                success: function (response) {
                                    //alert("RESP: " + JSON.stringify(response));
                                    if(updateType == 'M') {
                                        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/edit/' + memberID;
                                    } else if(updateType == 'S') {
                                        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/stakeholder/edit/' + memberID;
                                    }
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

        var updateSource = $("#update_source").val();
        //alert("UPS SRC: " + updateSource);

        var updateType = $("#update_type").val();
        $("#contact_type").val(updateType);

        if(updateSource === 'C') {
            //UPDATE SOURCE IS CONTACT EDIT
            //MAKE THE QUERY STRING STICKY
            if ($("#contact_filter_query").length > 0) {
                var filter_query = $("#contact_filter_query").val();
            } else {
                filter_query = '';
            }

            if (filter_query.length > 0) {
                var queryArray = JSON.parse(filter_query);
            } else {
                var queryArray = [];
            }
            var filter_name = filter_query.substring(1, 5);
            //alert("FQ: " + filter_query + "FN: " + filter_name);

            if (queryArray.length == 0) {
                //alert("1");
                var contactsFilter = 'all';
            } else if (queryArray.length > 0 && filter_name == 'page') {
                //alert("2");
                var pageStr = filter_query.replace(/\"/g, "");
                var contactsFilter = 'all?' + pageStr;
            } else {
                //alert("3");
                var useFilter = $.param(queryArray);
                var contactsFilter = 'filter?' + useFilter;
            }

        } else {
            //UPDATE SOURCE IS MEMBER EDIT
            var memberID = $("#member_id").val();
            //alert("MID: " + memberID);
        }

        $(".form-group").removeClass('has-error');
        $(".help-block").html('');

        var itemDets = $(this).attr('id');
        var splitID = itemDets.split("_");
        var contactID = splitID[2];

        var csrfName = document.getElementById('csrf_name').value;
        var csrfValue = document.getElementById('csrf_value').value;
        var csrfData = {"csrf_name" : csrfName,"csrf_value" : csrfValue};

        $.ajax({
            url: "http://localhost/~allanhyde/dev_mpa/public/contact/get/" + contactID,
            type: "get",
            data: csrfData ,
            success: function (response) {
                //alert("RESP: " + response);
                var JSONObject = JSON.parse(response);
                $.each(JSONObject, function(i, item) {
                    if(i == 'role') {
                        $("label[for='P']").removeClass("active");
                        $("label[for='V']").removeClass("active");
                        $("label[for='O']").removeClass("active");
                        $('input:radio[name=contact_role]').val([item]);
                        $("label[for=\"" + item + "\"]").addClass("active");
                    } else if(i == 'journal'){
                            $("label[for='Y']").removeClass("active");
                            $("label[for='N']").removeClass("active");
                            $('input:radio[name=contact_journal]').val([item]);
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
                                    contact_surname: "required",
                                    contact_phone: {
                                        required: true,
                                        exactlength: 10
                                    },
                                    contact_mobile: {
                                        exactlength: 10
                                    },
                                    contact_fax: {
                                        exactlength: 10
                                    },
                                    contact_email : {
                                        required: true,
                                        email: true
                                    },
                                    contact_status : {
                                        notEqual: "N"
                                    }
                                },
                                messages: {
                                    contact_firstname: "Firstname must not be empty",
                                    contact_surname: "Surname must not be empty",
                                    contact_phone: {
                                        required: "Phone must not be empty",
                                        exactlength: "Enter as 10 digits with no spaces or symbols"
                                    },
                                    contact_mobile: {
                                        exactlength: "Enter as 10 digits with no spaces or symbols"
                                    },
                                    contact_fax: {
                                        exactlength: "Enter as 10 digits with no spaces or symbols"
                                    },
                                    contact_email: {
                                        required: "Email must not be empty",
                                        email: "Email address is not valid"
                                    },
                                    contact_status: "A Status must be selected"
                                }
                            });

                            if ($("#contact_form").valid()) {
                                var datastring = $("#contact_form").serialize();
                                $.ajax({
                                    url: "http://localhost/~allanhyde/dev_mpa/public/contact/edit/" + contactID,
                                    type: "post",
                                    data: datastring,
                                    success: function (response) {

                                        if(updateSource === 'C') {
                                            window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/contacts/list/' + contactsFilter;
                                        } else {
                                            if(updateType == 'M') {
                                                window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/edit/' + memberID;
                                            } else if(updateType == 'S') {
                                                window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/stakeholder/edit/' + memberID;
                                            }
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        console.log(textStatus, errorThrown);
                                        alert("Got some error: " + errorThrown);
                                    }
                                });
                            }
                        }
                    }
                });

                $('#contact_update_dialog').dialog('option', {"title" : "EDIT CONTACT"});
                $('#contact_update_dialog').dialog('open');
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $("#select_contact").autocomplete({
        source: 'http://localhost/~allanhyde/dev_mpa/public/contacts/auto',
        minLength: 3,
        delay: 10,
        search: function(){
            $(this).addClass('ui-autocomplete-loading');
        },
        open: function(){$(this).removeClass('ui-autocomplete-loading');},
        select: function(event, ui) {getContactDets(ui.item.value)}
    });

    function getContactDets(item){
        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/contacts/name/' + item;
    }

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
        //alert("ITEM: " + item);

        $.ajax({
            url: 'http://localhost/~allanhyde/dev_mpa/public/contacts/member/' + item,
            type: "GET",
            //data: datastring,
            success: function (response) {
                //alert("RESP: " + response)
                $("#member_id").val(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                alert("Got some error: " + errorThrown);
            }
        });
    }

});