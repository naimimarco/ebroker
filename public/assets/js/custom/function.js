// function isRTL() {
//     var dir = $('html').attr('dir');
//     if (dir === 'rtl') {
//         return true;
//     } else {
//         return false;
//     }
// }

// let toast_position = 'top-right';
// if (isRTL()) {
//     toast_position = 'top-left';
// } else {
//     toast_position = 'top-right';
// }


function showErrorToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: !0,
        backgroundColor: '#dc3545',
        // position: toast_position
    }).showToast();
}

function showSuccessToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: !0,
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
        // position: toast_position
    }).showToast();
}

function showWarningToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: !0,
        backgroundColor: "yellow",
        // position: toast_position
    }).showToast();
}


/**
 *
 * @param type
 * @param url
 * @param data
 * @param {function} beforeSendCallback
 * @param {function} successCallback - This function will be executed if no Error will occur
 * @param {function} errorCallback - This function will be executed if some error will occur
 * @param {function} finalCallback - This function will be executed after all the functions are executed
 * @param processData
 */
function ajaxRequest(type, url, data, beforeSendCallback = null, successCallback = null, errorCallback = null, finalCallback = null, processData = false) {
    $.ajax({
        type: type,
        url: url,
        data: data,
        cache: false,
        processData: processData,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            if (beforeSendCallback != null) {
                beforeSendCallback();
            }
        },
        success: function (data) {
            if (!data.error) {
                if (successCallback != null) {
                    successCallback(data);
                }
            } else {
                if (errorCallback != null) {
                    errorCallback(data);
                }
            }

            if (finalCallback != null) {
                finalCallback(data);
            }
        }, error: function (jqXHR) {
            console.log(jqXHR);
            if (jqXHR.responseJSON) {
                showErrorToast(jqXHR.responseJSON.message);
            }
            if (finalCallback != null) {
                finalCallback();
            }
        }
    })
}

function formAjaxRequest(type, url, data, formElement, submitButtonElement, successCallback = null, errorCallback = null) {
    formElement.validate();
    if (formElement.valid()) {
        let submitButtonText = submitButtonElement.val();

        function beforeSendCallback() {
            submitButtonElement.val('Please Wait...').attr('disabled', true);
        }

        function mainSuccessCallback(response) {
            if (response.warning) {
                showWarningToast(response.message);
            } else {
                showSuccessToast(response.message);
            }

            if (successCallback != null) {
                successCallback(response);
            }
        }

        function mainErrorCallback(response) {
            showErrorToast(response.message);
            if (errorCallback != null) {
                errorCallback(response);
            }
        }

        function finalCallback() {
            submitButtonElement.val(submitButtonText).attr('disabled', false);
        }

        ajaxRequest(type, url, data, beforeSendCallback, mainSuccessCallback, mainErrorCallback, finalCallback)
    }
}

/**
 * @param {string} [url] - Ajax URL that will be called when the Confirm button will be clicked
 * @param {string} [method] - GET / POST / PUT / PATCH / DELETE
 * @param {Object} [options] - Options to Configure SweetAlert
 * @param {string} [options.title] - Are you sure
 * @param {string} [options.text] - You won't be able to revert this
 * @param {string} [options.icon] - 'warning'
 * @param {boolean} [options.showCancelButton] - true
 * @param {string} [options.confirmButtonColor] - '#3085d6'
 * @param {string} [options.cancelButtonColor] - '#d33'
 * @param {string} [options.confirmButtonText] - Confirm
 * @param {string} [options.cancelButtonText] - Cancel
 * @param {function} [options.successCallBack] - function()
 * @param {function} [options.errorCallBack] - function()
 */
function showSweetAlertConfirmPopup(url, method, options = {}, responseFunction = {}) {
    if(options){
        opt = {
            title: options.title ? options.title :window.trans["Are you sure"],
            text: options.text ? options.text : window.trans["You wants to change it ?"],
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: options.confirmText ? options.confirmText : window.trans["Yes"],
            cancelButtonText: options.cancelText ? options.cancelText : window.trans["No"],
            reverseButtons: true,
            successCallBack: function () {
            },
            errorCallBack: function (response) {
            },

            ...responseFunction,
        }
    }else{
        opt = {
            title: window.trans["Are you sure"],
            text: window.trans["You wants to change it ?"],
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: window.trans["Yes"],
            cancelButtonText: window.trans["No"],
            reverseButtons: true,
            successCallBack: function () {
            },
            errorCallBack: function (response) {
            },
            ...responseFunction,
        }
    }


    Swal.fire({
        title: opt.title,
        text: opt.text,
        icon: opt.icon,
        showCancelButton: opt.showCancelButton,
        confirmButtonColor: opt.confirmButtonColor,
        cancelButtonColor: opt.cancelButtonColor,
        confirmButtonText: opt.confirmButtonText,
        cancelButtonText: opt.cancelButtonText,
        reverseButtons: opt.reverseButtons ?? false
    }).then((result) => {
        if (result.isConfirmed) {
            function successCallback(response) {
                showSuccessToast(response.message);
                opt.successCallBack(response);
            }

            function errorCallback(response) {
                showErrorToast(response.message);
                opt.errorCallBack(response);
            }

            ajaxRequest(method, url, null, null, successCallback, errorCallback);
        }
    })
}


/**
 *
 * @param {string} [url] - Ajax URL that will be called when the Delete will be successfully
 * @param {Object} [options] - Options to Configure SweetAlert
 * @param {string} [options.text] - "Are you sure?"
 * @param {string} [options.title] - "You won't be able to revert this!"
 * @param {string} [options.icon] - "warning"
 * @param {boolean} [options.showCancelButton] - true
 * @param {string} [options.confirmButtonColor] - "#3085d6"
 * @param {string} [options.cancelButtonColor] - "#d33"
 * @param {string} [options.confirmButtonText] - "Yes, delete it!"
 * @param {string} [options.cancelButtonText] - "Cancel"
 * @param {function} [options.successCallBack] - function()
 * @param {function} [options.errorCallBack] - function()
 */
function showDeletePopupModal(url, options = {}) {

    // To Preserve OLD
    let opt = {
        title: window.trans["Are you sure"],
        text: window.trans["You wont be able to revert this"],
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#d33',
        confirmButtonText: window.trans["Yes Delete"],
        cancelButtonText: window.trans['Cancel'],
        reverseButtons: true,
        successCallBack: function () {
        },
        errorCallBack: function (response) {
        },
        ...options,
    }

    showSweetAlertConfirmPopup(url, 'DELETE', opt,options);
}
// Function to make remove button accessible on the basis of Option Section Length
let toggleAccessOfDeleteButtons = () => {
    if ($('.option-section').length >= 3) {
        $('.remove-default-option').removeAttr('disabled');
    } else {
        $('.remove-default-option').attr('disabled', true);
    }
}

// Close the alert manually
function closeLoading() {
    Swal.close();  // Close the alert
}



// Function to check for duplicate selections
function checkDuplicateFeatures(elementClass, newRowButton) {

    let selectedElement = [];
    let duplicateFound = false;
    let submitButton = $(newRowButton).closest('form').find(':submit');

    $(elementClass).each(function () {
        let value = $(this).val();
        let $parent = $(this).closest('.form-group'); // Get parent form-group

        // Remove previous error messages
        $parent.find('.duplicate-error').remove();

        if (value) {
            if (selectedElement.includes(value)) {
                duplicateFound = true;

                // Show error message below the select dropdown
                $parent.append('<span class="text-danger duplicate-error">Duplicate value</span>');
            }
            selectedElement.push(value);
        }
    });

    // Disable "Add New Feature" button if duplicate is found
    $(newRowButton).prop('disabled', duplicateFound);
    submitButton.prop('disabled', duplicateFound);
}
