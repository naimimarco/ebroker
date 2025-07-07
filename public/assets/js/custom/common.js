//Setup CSRF Token default in AJAX Request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('#edit-form,.edit-form,.edit-form-without-reset').on('submit', function (e) {
    e.preventDefault();
    let formElement = $(this);
    let submitButtonElement = $(this).find(':submit');
    let data = new FormData(this);
    data.append("_method", "PUT");
    let url = ""
    if(data.get('edit_id')){
        url = $(this).attr('action') + "/" + data.get('edit_id');
    }else{
        url = $(this).attr('action');
    }
    // let url = $(this).attr('action');
    let preSubmitFunction = $(this).data('pre-submit-function');
    if (preSubmitFunction) {
        //If custom function name is set in the Form tag then call that function using eval
        eval(preSubmitFunction + "()");
    }
    let customSuccessFunction = $(this).data('success-function');

    // noinspection JSUnusedLocalSymbols
    function successCallback(response) {
        $('#table_list').bootstrapTable('refresh');
        setTimeout(function () {
            $('#editModal').modal('hide');
            if (!$(formElement).hasClass('edit-form-without-reset')) {
                formElement[0].reset();
                if (FilePond.find(document.querySelector('.filepond'))) {
                    FilePond.find(document.querySelector('.filepond')).removeFiles();
                }
            }

        }, 1000)
        if (customSuccessFunction) {
            //If custom function name is set in the Form tag then call that function using eval
            eval(customSuccessFunction + "(response)");
        }
    }

    formAjaxRequest('POST', url, data, formElement, submitButtonElement, successCallback);
})

$('#create-form,.create-form,.create-form-without-reset').on('submit', function (e) {
    e.preventDefault();
    let formElement = $(this);
    let submitButtonElement = $(this).find(':submit');
    let url = $(this).attr('action');
    let submitButtonText = submitButtonElement.val();
    submitButtonElement.val('Please Wait...').attr('disabled', true);

    if (!formElement.parsley().isValid()) {
        submitButtonElement.val(submitButtonText).removeAttr('disabled');
        // If the form is not valid, trigger Parsley's validation messages
        formElement.parsley().validate();
    }else{
        setTimeout(() => {
            let data = new FormData(this);
            let preSubmitFunction = $(this).data('pre-submit-function');
            if (preSubmitFunction) {
                //If custom function name is set in the Form tag then call that function using eval
                eval(preSubmitFunction + "(formElement)");
            }
            let customSuccessFunction = $(this).data('success-function');
            // noinspection JSUnusedLocalSymbols
            function successCallback(response) {
                if (!$(formElement).hasClass('create-form-without-reset')) {
                    formElement[0].reset();
                }
                $('#table_list').bootstrapTable('refresh');
                if (customSuccessFunction) {
                    //If custom function name is set in the Form tag then call that function using eval
                    eval(customSuccessFunction + "(response)");
                }

            }
            submitButtonElement.val(submitButtonText).attr('disabled', false);
            formAjaxRequest('POST', url, data, formElement, submitButtonElement, successCallback);

        }, 300);
    }

})


$(document).on('click', '.delete-form', function (e) {
    e.preventDefault();
    showDeletePopupModal($(this).attr('href'), {
        successCallBack: function (response) {
            $('#table_list').bootstrapTable('refresh');
        }, errorCallBack: function (response) {
            showErrorToast(response.message);
        }
    })
})


$(document).on('click', '.update-status', function (e) {
    e.preventDefault();
    showSweetAlertConfirmPopup($(this).attr('href'), {
        successCallBack: function () {
            // $('#table_list').bootstrapTable('refresh');
        }, errorCallBack: function (response) {
            showErrorToast(response.message);
        }
    })
})
