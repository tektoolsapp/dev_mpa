$(document).ready(function() {

    $(".datepicker").datepicker({
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true
    });

    $('.timepicker').timepicker();

    $('.timepicker').timepicker({
        'timeFormat': 'h:i A',
        'minTime': '10:00am',
        'maxTime': '11:30pm'
    });

    $("#cancel_add").click(function(){
        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/email/lists");
    });

    $("#cancel_edit").click(function(){
        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/email/lists");
    });

    $("#submit_list").click(function(e) {

        e.preventDefault();

        //alert("add list");

        var mode = $("#update_list_mode").val();

        if(mode == 'add') {
            var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/email/updates/list/new';
        } else if(mode == 'edit'){
            var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/edit/' + eventId;
        }

        var datastring = $("#list_update").serialize();

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                alert("RESP: " + response);
                /*
                if(response == 'errors'){
                    if(mode == 'edit') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/event/get/' + eventId);
                    } else if(mode == 'add') {
                        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/event/new');
                    }
                } else if(response == 'ok') {
                    //window.location.replace('http://localhost/~allanhyde/dev_mpa/public/events');
                    window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/events';
                }
                */
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    });

    //$("#submit_member").click(function(e) {



    $("[id^=submit_members_]").click(function(e) {

        e.preventDefault();

        var itemDets = $(this).attr('id');
        var splitID = itemDets.split("_");
        var listId = splitID[2];

        alert("ADDING: " + listId);

        var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/email/members/add/' + listId;

        //var datastring = $("#list_update").serialize();

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            //data: datastring,
            success: function (response) {
                alert("RESP: " + response);

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    });

});