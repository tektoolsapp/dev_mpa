function getMyobCustomerDets(custUID) {
    var result = false;

    $.ajax({
        type: "get",
        async: false,
        url: "http://localhost/~allanhyde/dev_mpa/public/myob/customers/get-cust/get?custUID=" + custUID,
        success: function (data) {
            result = data;
        }
    });

    return result;
}

function updateCustomerDets(mode, updateCompanyName, updatedJson, updateMode, updateUID, id, thisUpdate, numUpdates, updateType){
    //UPDATE THE CUSTOMER IN MYOB
    var getTradingName = $("#get_trading_name").val();
    //alert("GTN: " + getTradingName);

    if(getTradingName.length == 0) {

        var filter_query = $("#members_filter_query").val();
        var filter_name = filter_query.substring(0, 4);

        //console.log("FQ: " + filter_query + " LEN:" + filter_query.length);

        if (filter_query.length == 0) {
            var membersFilter = 'all';
        } else if (filter_query.length > 0 && filter_name == 'page') {
            var membersFilter = 'all?' + $("#members_filter_query").val();
        } else {
            var membersFilter = 'filter?' + $("#members_filter_query").val();
        }

        //console.log("MF: " + membersFilter);
        //console.log("MODE: " + mode);
    }

    if(mode == 'invoice') {
        updateMode = mode;
    }

    //console.log("UPDATE MODE: " + updateMode);

    $.ajax({
        type: "POST",
        //async: false,
        url: "http://localhost/~allanhyde/dev_mpa/public/myob/customers/update-cust/update?type=" + updateType + "&mode=" + updateMode + "&payload=" + updatedJson,
        success: function (data) {
            if (mode == 'invoice') {
                console.log("INV: " + data);
                if(data == 'update_errors') {
                    window.location.replace('http://localhost/~allanhyde/dev_mpa/public/events/invoice');
                    //console.log("errors here")
                } else {
                    //PROCESS INVOICE
                    alert("PROCESS INVOICE");
                }
            } else {
                //RETURN TO MEMBERS INDEX WHEN ALL REQUIRED UPDATES HAVE BEEN PROCESSED
                if(thisUpdate == numUpdates) {

                    //alert("END:" + updateType);

                    if(updateType == 'M') {

                        if (getTradingName.length > 0) {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/members/name/' + getTradingName + '');
                        } else {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/members/list/' + membersFilter + '');
                            //console.log("ok here")
                        }

                    } else if(updateType == 'S') {

                        if (getTradingName.length > 0) {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/members/name/' + getTradingName + '');
                        } else {
                            window.location.replace('http://localhost/~allanhyde/dev_mpa/public/stakeholders/list/' + membersFilter + '');
                            //console.log("SS ok here")
                        }

                    }

                }
            }
        }
    });
}

function getCustomerDets(mode, id, thisUpdate, numUpdates, updateType) {

    //GET THE CUSTOMER DETAILS FOR MYOB INTEGRATION

    if(updateType == 'M') {
        var memberUpdateUrl = "http://localhost/~allanhyde/dev_mpa/public/members/update/" + id;
    } else if(updateType == 'S') {
        var memberUpdateUrl = "http://localhost/~allanhyde/dev_mpa/public/stakeholder/update/" + id;
    }

    $.ajax({
        url: memberUpdateUrl,
        async: false,
        type: "GET",
        success: function (response) {

            //alert("UPDATE MYOB: " + response )

            var updateObj = JSON.parse(response);
            var custUID = updateObj.UID;

            if(custUID.length == 0) {

                //ADD A NEW MYOB CUSTOMER
                var updateMode = 'add';

                var updateCompanyName = encodeURIComponent(updateObj.CompanyName);
                updateObj.CompanyName = updateCompanyName;
                var updateFirstName = encodeURIComponent(updateObj.FirstName);
                updateObj.FirstName = updateFirstName;
                var updateLastName = encodeURIComponent(updateObj.LastName);
                updateObj.LastName = updateLastName;
                var updateCustomerStreet = encodeURIComponent(updateObj.Addresses[0].Street);
                updateObj.Addresses[0].Street = updateCustomerStreet;
                var updateCustomerCity = encodeURIComponent(updateObj.Addresses[0].City);
                updateObj.Addresses[0].City = updateCustomerCity;
                var updateContactName = encodeURIComponent(updateObj.Addresses[0].ContactName);
                updateObj.Addresses[0].ContactName = updateContactName;

                delete updateObj.UID;

                var updatedJson = JSON.stringify(updateObj);
                var updateUID = '';

                updateCustomerDets(mode, updateCompanyName, updatedJson, updateMode, updateUID, id, thisUpdate, numUpdates, updateType);

            } else {

                //UPDATE AN EXISTING MYOB CUSTOMER
                var updateMode = 'edit'

                var updateObj = JSON.parse(response);
                var updateUID = updateObj.UID;
                var updateIsActive = updateObj.IsActive;
                //alert("MAS: " + updateIsActive);
                var updateCompanyName = updateObj.CompanyName;
                var updateCompanyName = encodeURIComponent(updateCompanyName);
                var updateFirstName = updateObj.FirstName;
                var updateFirstName = encodeURIComponent(updateFirstName);
                var updateLastName = updateObj.LastName;
                var updateLastName = encodeURIComponent(updateLastName);
                var updateIsIndividual = updateObj.IsIndividual;
                var updateStreet = updateObj.Addresses[0].Street;
                var updateStreet = encodeURIComponent(updateStreet);
                var updateCity= updateObj.Addresses[0].City;
                var updateCity = encodeURIComponent(updateCity);
                var updateState = updateObj.Addresses[0].State;
                var updatePostCode = updateObj.Addresses[0].PostCode;
                var updateCountry = updateObj.Addresses[0].Country;
                var updatePhone1 = updateObj.Addresses[0].Phone1;
                var updatePhone2 = updateObj.Addresses[0].Phone2;
                var updatePhone3 = updateObj.Addresses[0].Phone3;
                var updateEmail = updateObj.Addresses[0].Email;

                var updateContactName = encodeURIComponent(updateObj.Addresses[0].ContactName);
                var updateIncomeAccount = updateObj.SellingDetails.IncomeAccount.UID;
                var updateABN = updateObj.SellingDetails.ABN;
                var updateTaxCode = updateObj.SellingDetails.TaxCode.UID;
                var updateFreightTaxCode = updateObj.SellingDetails.FreightTaxCode.UID;
                var updatePaymentTerms = updateObj.SellingDetails.Terms.PaymentIsDue;
                var updatePaymentDays = updateObj.SellingDetails.Terms.BalanceDueDate;

                //GET EXISTING MYOB CUSTOMER DETS FOR UPDATING

                var existingMYOBDets = getMyobCustomerDets(updateUID);

                var obj = JSON.parse(existingMYOBDets);
                if (obj.Items[0].SellingDetails.IncomeAccount === null) {
                    var IncomeAccount = {
                        "UID": updateIncomeAccount
                    };

                    obj.Items[0].SellingDetails.IncomeAccount = IncomeAccount;

                }

                if (obj.Items[0].PaymentDetails !== null) {

                    var bank_account_name = encodeURIComponent(obj.Items[0].PaymentDetails.BankAccountName);

                } else {
                    var bank_account_name = '';
                }

                var account_name = encodeURIComponent(obj.Items[0].SellingDetails.IncomeAccount.Name);
                //var bank_account_name = encodeURIComponent(obj.Items[0].PaymentDetails.BankAccountName);
                var notes = encodeURIComponent(obj.Items[0].Notes);

                obj.Items[0].IsActive = updateIsActive;
                obj.Items[0].CompanyName = updateCompanyName;
                obj.Items[0].FirstName = updateFirstName;
                obj.Items[0].LastName = updateLastName;
                obj.Items[0].IsIndividual = updateIsIndividual;
                obj.Items[0].Addresses[0].Street = updateStreet;
                obj.Items[0].Addresses[0].City = updateCity;
                obj.Items[0].Addresses[0].State = updateState;
                obj.Items[0].Addresses[0].PostCode = updatePostCode;
                obj.Items[0].Addresses[0].Country = updateCountry;
                obj.Items[0].Addresses[0].Phone1 = updatePhone1;
                obj.Items[0].Addresses[0].Phone2 = updatePhone2;
                obj.Items[0].Addresses[0].Phone3 = updatePhone3;
                obj.Items[0].Addresses[0].Email = updateEmail;
                obj.Items[0].Addresses[0].Website = encodeURIComponent(obj.Items[0].Addresses[0].Website);
                obj.Items[0].Addresses[0].ContactName = updateContactName;
                obj.Items[0].Notes = notes;
                obj.Items[0].SellingDetails.IncomeAccount.UID = updateIncomeAccount;
                obj.Items[0].SellingDetails.IncomeAccount.Name = account_name;
                obj.Items[0].SellingDetails.ABN = updateABN;
                obj.Items[0].SellingDetails.TaxCode.UID = updateTaxCode;
                obj.Items[0].SellingDetails.FreightTaxCode.UID = updateFreightTaxCode;
                obj.Items[0].SellingDetails.Terms.PaymentIsDue = updatePaymentTerms;
                obj.Items[0].SellingDetails.Terms.BalanceDueDate = updatePaymentDays;
                if (obj.Items[0].PaymentDetails !== null) {
                    obj.Items[0].PaymentDetails.BankAccountName = bank_account_name;
                }

                //var myobRowVersion = obj.Items[0].RowVersion;
                //var ttRowVersion = row_version;

                //SET CUSTOMER NAME FOR ERROR MESSAGE
                if(updateIsIndividual == 'true') {
                    var getCompanyName = updateFirstName + " " + updateLastName;
                } else {
                    var getCompanyName = updateCompanyName;
                }

                var updatedJson = obj.Items[0];
                var updatedJson = JSON.stringify(updatedJson);

                updateCustomerDets(mode, getCompanyName, updatedJson, updateMode, updateUID, id, thisUpdate, numUpdates, updateType);

            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }

    });
}

function updateCustomer(mode, memberId, updateType) {

    //GET ALL THE IDS THAT REQUIRE UPDATING IN MYOB

    alert("update type: " + updateType)

    if(updateType == 'M') {
        var getCustomerUpdatesUrl = "http://localhost/~allanhyde/dev_mpa/public/members/updates";
    } else if (updateType == 'S'){
        var getCustomerUpdatesUrl = "http://localhost/~allanhyde/dev_mpa/public/stakeholders/updates";
    }

    $.ajax({
        url: getCustomerUpdatesUrl,
        type: "GET",
        success: function (response) {

            alert("RESPC: " + response)

            var data = $.parseJSON(response);
            var numUpdates = data.length;

            $.each(data, function(i, item) {
                //INCREMENT THE UPDATE TO CONTROL RETURN TO MEMBERS INDEX VIEW
                var thisUpdate = i + 1;
                getCustomerDets(mode, item, thisUpdate, numUpdates, updateType);
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function updateInvoiceCustomer(mode, memberId) {
    //GET ALL THE IDS THAT REQUIRE UPDATING IN MYOB

    var mode = 'invoice';
    var item = memberId;
    var thisUpdate = 1;
    var numUpdates = 1;

    getCustomerDets(mode, item, thisUpdate, numUpdates);
}