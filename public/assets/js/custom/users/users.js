"use strict";

$(".check-head").change(function() {
    var value = $(this).data('val');
    this.checked ? $('.'+value).prop('checked', true) : $('.'+value).prop('checked', false);
});


function queryParams(p) {
    return {
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

function setValue(id) {
    //$('#editUserForm').attr('action', ($('#editUserForm').attr('action') +'/edit/'+id));
    $("#edit_id").val(id);
    $("#editUserForm").closest('form').find("input[type=checkbox]").prop("checked",false);
    $("#edit_name").val($("#" + id).parents('tr:first').find('td:nth-child(2)').text());
    $("#edit_email").val($("#" + id).parents('tr:first').find('td:nth-child(3)').text());
    $("#status").val($("#" + id).data('status')).trigger('change');

    if($("#" + id).data('permission') != ''){
        var permissions =  atob($("#" + id).data('permission'));
        var myArray = JSON.parse(permissions);
        $.each(myArray, function (i, item) {
            $.each(item, function (j, field) {
                $("[name='permissions[" + i + "][" + j + "]']").each(function () {
                    $(this).prop("checked", true);
                });
            });
        });
    }



}



function setpasswordValue(id) {
    var allowsubmit = false;
    $("#pass_id").val(id);
    $('#confPassword').keyup(function (e) {
        //get values
        var pass = $('#newPassword').val();
        var confpass = $(this).val();

        //check the strings
        if (pass == confpass) {
            //if both are same remove the error and allow to submit
            $('.error').text('');
            allowsubmit = true;
        } else {
            //if not matching show error and not allow to submit
            $('.error').text('Password not matching');
            allowsubmit = false;
        }
    });

    //jquery form submit
    $('#resetform').submit(function () {

        var pass = $('#newPassword').val();
        var confpass = $('#confPassword').val();

        //just to make sure once again during submit
        //if both are true then only allow submit
        if (pass == confpass) {
            allowsubmit = true;
        }
        if (allowsubmit) {
            return true;
        } else {
            return false;
        }
    });
}
