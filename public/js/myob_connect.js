
 function showConnected(mode, memberId, updateType) {

     //alert("reached here: " + mode + " - ID: " + memberId + "UDT:" + updateType);

     /*
      var now_connected = $.cookie("myob_connected");

      if(now_connected != 'Connected') {
      $('#connect_status').removeClass('i-online').addClass('i-offline');
      } else {
      $('#connect_status').removeClass('i-offline').addClass('i-online');
      }
      */

     //alert("Connected to MYOB! - " + Cookies.get('myob_connected') + " - Mode: " + mode + " - ID: " + memberId );

     if (mode == 'invoice') {

         //alert("Connected to MYOB! - " + Cookies.get('myob_connected') + " - Mode: " + mode + " - ID: " + memberId );

         //CHECK FOR ANY CUSTOMER UPDATES RELATED TO THE CURRENT MEMBER
         updateInvoiceCustomer(mode, memberId)

     } else {
        updateCustomer(mode, memberId, updateType)
    }
 }

 function testConnection(mode, memberId, updateType) {

     //alert("TEST UDT:" + updateType);

     $.ajax({
         type: "get",
         //url: "accounting_objects_api_dev.php?object=info",
         url: "http://localhost/~allanhyde/dev_mpa/public/myob/connection",
         success: function(data) {
             //console.log(data);
             if(data != 'no_connection') {
                 var obj = $.parseJSON(data);
                 var coy_name = obj.CompanyName;
                 var err_code = obj.Errors;
                 var access_denied = obj.Message;
             } else {
                 var notConnected = data;
                 //alert("NC: " + notConnected);
             }
             if(typeof notConnected !== 'undefined' && notConnected == 'no_connection') {
                 //alert("No MYOB Connection");
                 $.unblockUI();
                 $("#myob_error").dialog({
                     buttons: {
                         "Close" : function() {
                             $.unblockUI();
                             $( this ).dialog( "close" );
                         }
                     }
                 });
                 $("#myob_error_type").html('<p>MYOB Company File is off-line.</p><p>Open MYOB to establish connection.</p>');
                 $("#myob_error").dialog( "open" );
                 $("#myob_error" ).dialog( "option", { "title" : "CONNECTION ERROR", "width" : 400});
                 var coyName = 'MPA';
                 var connected = "Disconnected";
                 Cookies.set('myob_connected', connected, { expires : 1, path    : '/' });
                 Cookies.set('myob_company', coy_name, { expires : 1, path    : '/' });

                 $("#myob_company_name").html(coy_name);
                 showConnected(mode, memberId, updateType);

             } else {

                 if(typeof coy_name !== 'undefined') {
                     //alert("coy Name: " + coy_name);
                     var connected = "Connected";
                     Cookies.set('myob_connected', connected, { expires : 1, path    : '/' });
                     Cookies.set('myob_company', coy_name, { expires : 1, path    : '/' });
                     $("#myob_company_name").html(coy_name);
                     //alert("UDTS:" + updateType)
                     showConnected(mode, memberId, updateType);
                     $.unblockUI();
                 } else if(typeof err_code !== 'undefined') {
                     //alert("err code" + coy_name);
                     alert("connect");
                     var connected = "Connected";
                     Cookies.set('myob_connected', connected, { expires : 1, path    : '/' });
                     Cookies.set('myob_company', coy_name, { expires : 1, path    : '/' });
                     $("#myob_company_name").html(coy_name);
                     showConnected(mode, memberId, updateType);
                     $.unblockUI();
                 } else if(typeof access_denied !== 'undefined') {
                     //alert("Not Logged in for MYOB Access.");
                     $.unblockUI();
                     var connected = "Disconnected";
                     showConnected(mode, memberId, updateType);
                 } else {
                     $.unblockUI();
                     var connected = "Disconnected";
                     alert("Error Accessing Company File. Please try again");
                 }

             }

         }
     });

 }

 $("#connect_dialog").dialog({
     dialogClass: "no-close",
     autoOpen: false,
     modal: true,
     open: function(){
         $(".ui-dialog-titlebar").addClass("ui-state-error");
         //$(".ui-dialog-titlebar-close").hide();
         $(this).siblings('.ui-dialog-buttonpane').find('button').blur();
     }
 });

 $("#myob_error").dialog({
     dialogClass: "no-close",
     autoOpen: false,
     modal: true,
     open: function(){
         $(".ui-dialog-titlebar").addClass("ui-state-error");
         $(".ui-dialog-titlebar-close").hide();
         $(this).siblings('.ui-dialog-buttonpane').find('button').blur();
     },
     buttons: {
         "Close" : function() {
             $( this ).dialog( "close" );
         }
     }
 });

 $("#connect").click(function() {

     $("#connect_dialog").dialog({
         buttons: {
             "Cancel" : function() {
                 //alert("SET CK3");
                 $.cookie("myob_api_connect", 'N', { expires : 1, path    : '/' });
                 $(this).dialog("close");
             },
             "Connect" : function() {
                 $(this).dialog("close");
                 testConnection();
             }
         }
     });
     $("#connect_dialog").dialog( "open" );
     $("#connect_dialog" ).dialog( "option", { "title" : "CONNECT TO MYOB", "width" : 500});

 });