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

    $("#cancel_add").click(function() {

        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/members");

    });

    $("#cancel_edit").click(function() {

        var status = $("#display_status").val();
        var name = $("#display_name").val();

        window.location.replace('http://localhost/~allanhyde/dev_mpa/public/members');

    });

});