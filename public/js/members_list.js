$(document).ready(function() {

    $.ui.dialog.prototype._focusTabbable = $.noop;

    //MEMBER SELECT AUTO COMPLETE
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
        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/name/' + item;
    }

    $('#filter_members_dialog').dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        buttons: [
            {
                text: "Filter",
                click: function() {
                    filterMembers();
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

    $("#filter_members").click(function(event) {
        event.preventDefault();

        $('#filter_members_dialog').dialog('option', {"title" : "FILTER MEMBERS DISPLAY"});
        $('#filter_members_dialog').dialog('open');
        $('#filter_members_dialog :button').blur();
        return false;
    });

    function filterMembers() {
        var selected_status = [];

        $('#filter_member_status input:checked').each(function() {
            selected_status.push($(this).attr('value'));
        });
        //alert("SEL: " + selected_status)
        window.location.href = 'http://localhost/~allanhyde/dev_mpa/public/members/list/filter?types=' + selected_status;
    }

});