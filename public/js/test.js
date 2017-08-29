$(document).ready(function() {

    $('#modal-simple').dialog({
        autoOpen: false,
        width: 600,
        modal: true,
        buttons: {
            "Ok": function () {
                $(this).dialog("close");
            },
            "Cancel": function () {
                $(this).dialog("close");
            }
        },
        open: function(){
            $(this).siblings('.ui-dialog-buttonpane').find('button').blur();
        }
    });

    $('#btn-dialog-simple').click(function () {
        $('#modal-simple').dialog('open');
        return false;

        alert("clicked");
    });

});