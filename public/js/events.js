$(document).ready(function() {

    $.ui.dialog.prototype._focusTabbable = $.noop;

    $(".datepicker").datepicker({
        dateFormat: 'dd-mm-yy',
        closeText: "Close",
        showButtonPanel: true
    });

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

    $('.timepicker').timepicker({
        'timeFormat': 'h:i A',
        'minTime': '06:00am',
        'maxTime': '11:59pm'
    });

    $("#cancel_add").click(function(){
        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/events");
    });

    $("#cancel_edit").click(function(){
        window.location.replace("http://localhost/~allanhyde/dev_mpa/public/events");
    });

    $("#submit_event").click(function(e) {

        e.preventDefault();

        //var source = $("#update_source").val();
        var mode = $("#update_event_mode").val();
        //alert("mode: " + mode);
        var eventId = $("#event_id").val();
        //alert("ID: " + eventId)

        if(mode == 'add') {
            var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/add';
        } else if(mode == 'edit'){
            var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/edit/' + eventId;
        }

        var datastring = $("#event_update").serialize();

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                //alert("RESP: " + response);
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
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $("#build_attendees").click(function(e) {

        var eventId = $("#event_id").val();
        var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/build/attendees/' + eventId;
        var datastring = $("#attendees_update").serialize();

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                alert("RESP: " + response);
                if(response != 'none') {
                    var contacts = response.data;
                    var contacts_total = response.total;
                    var contacts_per_page = response.per_page;
                    var num_pages = contacts_total / contacts_per_page;
                    var num_pages = Math.ceil(num_pages);
                    var url = 'http://localhost/~allanhyde/dev_mpa/public/event/display/attendees/' + eventId;

                    $("#attendees_header").css({"display": "none"});
                    $("#attendees_list").html("");

                    var html = '<div class="panel-body-subheader-app" style="float:left;margin-top:15px;">ATTENDANCE LIST</div>';
                    html += '<div class="mt-12" style="text-align:right;">';
                    html += '<ul class="pagination">';
                    html += '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';

                    for (i = 0; i < num_pages; i++) {

                        var page = i + 1;

                        if (i == 0) {
                            html += '<li class="page-item active"><a href="' + url + '?page=' + page + '" class="page-link">' + page + '</a></li>';
                        } else {
                            html += '<li class="page-item"><a href="' + url + '?page=' + page + '" class="page-link">' + page + '</a></li>';
                        }
                    }

                    html += '</ul>';
                    html += '</div>';
                    html += '<table class="table table-bordered table-striped">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th style="width: 5%">ID</th>';
                    html += '<th style="width: 5%">Type</th>';
                    html += '<th style="width: 25%">Name</th>';
                    html += '<th style="width: 40%">Email Address</th>';
                    html += '<th style="width: 25%">Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    $.each(contacts, function (i) {
                        html += '<tr>';
                        html += '<td>' + contacts[i].entity_id + '</td>';
                        html += '<td>' + contacts[i].option_type + '</td>';
                        html += '<td>' + contacts[i].fullname + '</td>';
                        html += '<td>' + contacts[i].email + '</td>';
                        html += '<td><a href="">Do Something</a>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody>';
                    html += '</table>';
                    //PAGINATION
                    html += '<div class="mt-12" style="text-align:right;">';
                    html += '<ul class="pagination">';
                    html += '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';

                    for (i = 0; i < num_pages; i++) {

                        var page = i + 1;

                        if (i == 0) {
                            html += '<li class="page-item active"><a href="' + url + '?page=' + page + '" class="page-link">' + page + '</a></li>';
                        } else {
                            html += '<li class="page-item"><a href="' + url + '?page=' + page + '" class="page-link">' + page + '</a></li>';
                        }
                    }

                    html += '</ul>';
                    html += '</div>';
                } else {

                    $("#attendees_header").css({"display" : "none"});

                    var html = '<div class="alert alert-danger">No Attendees for current selection options</div>';
                }

                $("#attendees_list").html("");
                $("#attendees_list").append(html);

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $("#confirm_attendees").click(function(e) {

        var eventId = $("#event_id").val();
        var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/confirm/attendees/' + eventId;
        var datastring = $("#attendees_update").serialize();

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                alert("RESP: " + response)

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $("#select_all").click(function(e) {

        if($(this).is(':checked')) {
            //alert("checked");
            $("#attendee_updates").css({"display" : "block"});
            $(".attendee").prop("checked",true);
        } else {
            $(".attendee").prop("checked",false);
            $("#attendee_updates").css({"display" : "none"});
        }
    });

    $("#delete_selected").click(function(e) {

        var eventId = $("#event_id").val();

        var selDel = [];

        $('input.attendee:checkbox:checked').each(function () {
            selDel.push($(this).val());
        });

        //alert("SEL DEL" + selDel);

        var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/delete/attendees/' + eventId;

        $.ajax({
            url: UpdateUrl,
            type: "POST",
            data: {
                "contacts" : selDel
            },
            success: function (response) {
                alert("RESP: " + response)

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    });

    function updateSchedule(updateUrl){

        var datastring = $("#event_schedule_update").serialize();

        $.ajax({
            url: updateUrl,
            type: "POST",
            data: datastring,
            success: function (response) {
                //alert("RESP: " + response);
                if(response != 'ok'){
                    //alert("show errors");
                    //console.log(response);
                    var obj = $.parseJSON(response);
                    var errors = obj.errors;
                    console.log(errors);

                    var currKey = 'none';
                    $.each(errors, function(key, value) {
                        if(key != currKey){
                            var br = '<br>';
                        } else {
                            var br = '';
                        }
                        $("#error_" + key).html(value + br);
                        $("#error_" + key).closest("div").addClass("has-error");
                        currKey = key;
                    });
                } else {
                    $("#eventContent").dialog("close");
                    $('#calendar').fullCalendar( 'refetchEvents' );

                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    }

    $('#eventContent').dialog({
        autoOpen: false,
        width: 900,
        modal: true,
        beforeClose: function() {
            $("#event_schedule_update").trigger("reset");
        },
        open: function(){
            $(this).siblings('.ui-dialog-buttonset').find('button').blur();
        }
    });

    var nowDate = new Date();

    $('#calendar').fullCalendar({
        schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
        now: nowDate,
        editable: true,
        contentHeight : "auto",
        //aspectRatio: 1.8,
        scrollTime: '00:00',
        header: {
            left: 'today prev,next',
            center: 'title',
            right: 'timelineDay,timelineTenDay,timelineMonth,timelineYear'
        },
        defaultView: 'timelineDay',
        views: {
            timelineDay: {
                buttonText: 'Standard',
                slotDuration: '00:15',
                snapDuration: '00:15'
            },
            timelineTenDay: {
                buttonText: 'Day',
                type: 'timeline',
                duration: { days: 1 }
            }
        },
        eventResize: function(event, delta, revertFunc) {

            if (!confirm("Is this OK?")) {

                revertFunc();

            } else {
                //console.log(event);
                //console.log(event);
                $("#schedule_id").val(event.id);
                var scheduleId = event.id;
                $("#event_title").val(event.title);
                $("#event_description").val(event.description);
                $("#start_date").val(moment(event.start).format('L'));
                $("#start_time").val(moment(event.start).format('LT'));
                $("#end_date").val(moment(event.end).format('L'));
                $("#end_time").val(moment(event.end).format('LT'));
                var start = moment(event.start);
                var end = moment(event.end);
                console.log(event.start);
                console.log(event.end);
                var duration = moment.duration(end.diff(start)).asMinutes();
                console.log("DUR:" + duration);
                if(duration < 60) {
                    var hours = '';
                    var mins = duration;
                } else if(duration == 60) {
                    var hours = 1;
                    var mins = '';
                } else if(duration > 60) {
                    var hours = parseInt(duration/60);
                    var mins = duration % 60;
                }
                console.log("HRS: " + hours);
                console.log("MINS: " + mins);
                $("#hours").val(hours);
                $("#mins").val(mins);
                $("#resource").val(event.resourceId);

                var updateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/update/' + scheduleId;

                updateSchedule(updateUrl);
            }

        },
        eventDrop: function(event, delta, revertFunc) {

            console.log(event);

            if (!confirm("Are you sure about this change?")) {
                revertFunc();
            } else {
                //alert("save");
                //console.log(event);
                //console.log(event);
                $("#schedule_id").val(event.id);
                var scheduleId = event.id;
                $("#event_title").val(event.title);
                $("#event_description").val(event.description);
                $("#start_date").val(moment(event.start).format('L'));
                $("#start_time").val(moment(event.start).format('LT'));
                $("#end_date").val(moment(event.end).format('L'));
                $("#end_time").val(moment(event.end).format('LT'));
                var start = moment(event.start);
                var end = moment(event.end);
                //console.log(event.start);
                //console.log(event.end);
                var duration = moment.duration(end.diff(start)).asMinutes();
                //console.log("DUR:" + duration);
                if(duration < 60) {
                    var hours = '';
                    var mins = duration;
                } else if(duration == 60) {
                    var hours = 1;
                    var mins = '';
                } else if(duration > 60) {
                    var hours = parseInt(duration/60);
                    var mins = duration % 60;
                }
                //console.log("HRS: " + hours);
                //console.log("MINS: " + mins);
                $("#hours").val(hours);
                $("#mins").val(mins);
                $("#resource").val(event.resourceId);

                var updateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/update/' + scheduleId;

                updateSchedule(updateUrl);
            }
        },
        eventRender: function(event, element) {

            /*
            element.bind('dblclick', function() {
                alert('double click!');
            });
            */

            element.find(".fc-title").remove();
            var new_description =
                moment(event.start).format("HH:mm") + '-'
                + moment(event.end).format("HH:mm") + '<br/>'
                + '<strong>Address: </strong><br/>' + event.address + '<br/>'
                + '<strong>Task: </strong><br/>' + event.task + '<br/>'
                + '<strong>Place: </strong>' + event.place + '<br/>'
                ;
            element.append(new_description);

        },
        minTime : "06:00:00",
        maxTime : "19:00:00",
        navLinks: true,
        resourceAreaWidth: '15%',
        resourceLabelText: 'Employees',
        resources: [
            { id: '1', title: 'Allan Hyde', eventColor: 'green' },
            { id: '2', title: 'Joe Black', eventColor: 'orange' },
            { id: '3', title: 'Frank Reddy', eventColor: 'red' },
        ],

        events: 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/fetch',

        dayClick: function( date, allDay, jsEvent, view ) {

            console.log(view);

            $("#start_date").val(moment(jsEvent.start).format('L'));
            $("#start_time").val(moment(date).format('LT'));
            $("#resource").val(view.id);

            $("#eventContent").dialog({
                buttons: {
                    "Save" : function() {
                        var datastring = $("#event_schedule_update").serialize();
                        var updateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/add';
                        updateSchedule(updateUrl);
                    },
                    "Close" : function() {
                        $(this).dialog("close");
                    }
                }
            });

            //RESET ANY ERRORS
            $(".form-group").removeClass('has-error');
            $(".help-block").html('');

            $("#eventContent" ).dialog( "option", { "title" : 'ADD EVENT', "width" : 900});
            $(".date_start").datepicker("disable");
            $(".date_end").datepicker("disable");
            $("#eventContent").dialog("open");
            $(".date_start").datepicker("enable");
            $(".date_end").datepicker("enable");

        },
        eventClick: function(event) {
            console.log(event);
            console.log(event);
            $("#schedule_id").val(event.id);
            var scheduleId = event.id;
            $("#event_title").val(event.title);
            $("#event_description").val(event.description);
            $("#start_date").val(moment(event.start).format('L'));
            $("#start_time").val(moment(event.start).format('LT'));
            $("#end_date").val(moment(event.end).format('L'));
            $("#end_time").val(moment(event.end).format('LT'));
            var start = moment(event.start);
            var end = moment(event.end);
            console.log(event.start);
            console.log(event.end);
            var duration = moment.duration(end.diff(start)).asMinutes();
            console.log("DUR:" + duration);
            if(duration < 60) {
                var hours = '';
                var mins = duration;
            } else if(duration == 60) {
                var hours = 1;
                var mins = '';
            } else if(duration > 60) {
                var hours = parseInt(duration/60);
                var mins = duration % 60;
            }
            console.log("HRS: " + hours);
            console.log("MINS: " + mins);
            $("#hours").val(hours);
            $("#mins").val(mins);
            $("#resource").val(event.resourceId);

            $("#eventContent").dialog({
                buttons: {
                    "Save" : function() {
                        //var UpdateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/add';
                        var datastring = $("#event_schedule_update").serialize();
                        var updateUrl = 'http://localhost/~allanhyde/dev_mpa/public/event/schedule/update/' + scheduleId;
                        updateSchedule(updateUrl);
                    },
                    "Close" : function() {
                        $(this).dialog("close");
                    }
                }
            });

            //RESET ANY ERRORS
            $(".form-group").removeClass('has-error');
            $(".help-block").html('');

            $("#eventContent" ).dialog( "option", { "title" : 'EDIT EVENT', "width" : 900});
            $(".date_start").datepicker("disable");
            $(".date_end").datepicker("disable");
            $("#eventContent").dialog("open");
            $(".date_start").datepicker("enable");
            $(".date_end").datepicker("enable");

        }
    });

});