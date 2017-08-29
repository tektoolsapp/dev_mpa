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

    //PREVENT ERROR DISPLAY WHEN A GROUP ITEM IS VALID
    $('.form-group').on('click','input[type=radio]',function() {
        $(this).closest('.form-group').find('.radio-inline, .radio').removeClass('checked');
        $(this).closest('.radio-inline, .radio').addClass('checked');
    });

    $("[id^=submit_stakeholder_]").click(function() {

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

        var updateType = $("#update_type").val();
        var mode = $("#member_mode").val();

        //alert("MODE: "  + mode + " - UPDATE TYPE: " + updateType);

        var filter_query = $("#members_filter_query").val();
        var filter_name = filter_query.substring(0, 4);

        if(filter_query.length == 0) {
            var membersFilter = 'all';
        } else if(filter_query.length > 0 && filter_name == 'page'){
            var membersFilter = 'all?' + $("#members_filter_query").val();
        } else {
            var membersFilter = 'filter?' + $("#members_filter_query").val();
        }

        var datastring = $("#stakeholder_update").serialize();

        if(mode == 'edit') {
            var memberID = $("#member_id").val();
            var memberUpdateUrl = "http://localhost/~allanhyde/dev_mpa/public/stakeholder/edit/" + memberID;
        } else if(mode == 'add') {
            //alert("adding");
            var memberUpdateUrl = "http://localhost/~allanhyde/dev_mpa/public/stakeholder/add/";
        }

        $.ajax({
            url: memberUpdateUrl,
            type: "POST",
            data: datastring ,
            success: function (response) {

                //alert("RESPX: " + response)

                if(response == 'errors'){
                    if(mode == 'edit') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/members/stakeholders/' + memberID);
                    } else if(mode == 'add') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholder/new');
                    }
                } else {
                    var obj = $.parseJSON(response);
                    var next = obj.next;
                    var memberId = obj.update_id;

                    if (next == 'errors') {
                        if (mode == 'edit') {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholders/edit/' + memberID);
                        } else {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholder/add');
                        }
                    } else if (next == 'ok') {
                        if (mode == 'add') {
                            updateMyobCustomer(membersFilter, mode, memberId, updateType);
                        } else if (mode == 'edit') {
                            //alert("EDIT: " + updateType)
                            updateMyobCustomer(membersFilter, mode, memberID, updateType);
                        }
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    function updateMyobCustomer(filter, mode, memberId, updateType){
        //CHECK FOR AN MYOB CONNECTION
        testConnection(mode, memberId, updateType);
    }

    $("#cancel_add").click(function() {

        var getTradingName = $("#get_trading_name").val();
        //alert("GTNX: " + getTradingName);

        if(getTradingName.length == 0) {

            var filter_query = $("#members_filter_query").val();
            //alert("FQ: " + filter_query);

            var filter_name = filter_query.substring(0, 4);
            //alert("FQ: " + filter_query + " - " + filter_name);

            if (filter_query.length == 0) {
                var membersFilter = 'all';
            } else if (filter_query.length > 0 && filter_name == 'page') {
                var membersFilter = 'all?' + $("#members_filter_query").val();
            } else {
                var membersFilter = 'filter?' + $("#members_filter_query").val();
            }
        }

        if(getTradingName.length > 0) {
            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholder/name/' + getTradingName + '');
        } else {
            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholders/list/' + membersFilter + '');
        }

    });

    $("#cancel_edit").click(function() {

        var getTradingName = $("#get_trading_name").val();

        if(getTradingName.length == 0) {

            var filter_query = $("#members_filter_query").val();
            var filter_name = filter_query.substring(0, 4);

            if (filter_query.length == 0) {
                var membersFilter = 'all';
            } else if (filter_query.length > 0 && filter_name == 'page') {
                var membersFilter = 'all?' + $("#members_filter_query").val();
            } else {
                var membersFilter = 'filter?' + $("#members_filter_query").val();
            }
        }

        if(getTradingName.length > 0) {
            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholder/name/' + getTradingName + '');
        } else {
            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholders/list/' + membersFilter + '');
        }

    });

    $('.mailing_field').each(function () {

        if($("#set_mailing_address").prop('checked') == true) {
            $(this).closest('.mailing_field').find('div').removeClass('required').find('input,select').val('').prop('disabled', true).removeClass("has-error");
        } else {
            $(this).closest('.mailing_field').find('div').addClass('required').removeAttr('disabled');;
        }
    });

    $('.input-group-addon.beautiful').each(function (event) {

        var $widget = $(this),
            $input = $widget.find('input'),
            type = $input.attr('type');

        //$(this).css({"background-color" : "#767676"});

        settings = {
            checkbox: {
                on: { icon: 'fa fa-check-circle-o' },
                off: { icon: 'fa fa-circle-o' }
            },
            radio: {
                on: { icon: 'fa fa-dot-circle-o' },
                off: { icon: 'fa fa-circle-o' }
            }
        };

        $widget.prepend('<span class="' + settings[type].off.icon + '"></span>');

        $widget.on('click', function (event ) {
            $input.prop('checked', !$input.is(':checked'));
            updateDisplay();

            $('.mailing_field').each(function () {

                if($input.prop('checked') == true) {

                    //alert($(this).closest('.mailing_field').find('span').html());

                    //alert("set checked");

                    $(this).closest('.mailing_field').find('div')
                        .removeClass('required')
                        .removeClass('has-error')
                        .find('input,select').val('').prop('disabled', 'disabled');
                    $(this).closest('.mailing_field').find('.help-block').html("")

                } else {

                    //alert("set unchecked");

                    $(this).closest('.mailing_field').find('div')
                        .addClass('required')
                        .find('input,select').val('')
                        .removeAttr('disabled');
                }

            });

        });

        function updateDisplay() {

            var isChecked = $input.is(':checked') ? 'on' : 'off';

            $widget.find('.fa').attr('class', settings[type][isChecked].icon);

            isChecked = $input.is(':checked') ? 'the same as Business Address' : 'different to Business Address';
            $widget.closest('.input-group').find('input[type="text"]').val('Mailing Address is ' + isChecked)

            //$(".form-control").val('Mailing Address is ' + isChecked);

            $widget.closest('.input-group').find('input[type="text"]').val('Mailing Address is ' + isChecked);

        }

        updateDisplay();

    });

});