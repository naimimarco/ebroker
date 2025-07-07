// @ts-nocheck

$(document).ready(function () {


    $('#table_list').on('load-success.bs.table', function () {
        setTimeout(() => {
            //  console.log("loaded");
            const embeds = document.querySelectorAll('.svg-img');



            embeds.forEach(function (embed) {
                const svgDoc = embed.getSVGDocument();

                if (svgDoc) {
                    const svgElements = svgDoc.querySelectorAll('path');

                    svgElements.forEach(function (svgElement) {
                        svgElement.setAttribute('fill', fillColor);

                    });
                }
            });
        }, 2000);
    });
    setTimeout(() => {
        //  console.log("loaded");
        const embeds = document.querySelectorAll('.svg-img');



        embeds.forEach(function (embed) {
            const svgDoc = embed.getSVGDocument();

            if (svgDoc) {
                const svgElements = svgDoc.querySelectorAll('path');

                svgElements.forEach(function (svgElement) {
                    svgElement.setAttribute('fill', fillColor);

                });
            }
        });
    }, 2000);
});
$(document).ready(function () {

    /// START :: ACTIVE MENU CODE
    $(".menu a").each(function () {
        var pageUrl = window.location.href.split(/[?#]/)[0];

        if (this.href == pageUrl) {
            $(this).parent().parent().addClass("active");
            $(this).parent().addClass("active"); // add active to li of the current link
            $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().parent().addClass("active"); // add active class to an anchor
        }

        var subURL = $("a#subURL").attr("href");
        if (subURL != 'undefined') {
            if (this.href == subURL) {

                $(this).parent().addClass("active"); // add active to li of the current link
                $(this).parent().parent().addClass("active");
                $(this).parent().parent().prev().addClass("active"); // add active class to an anchor

                $(this).parent().parent().parent().addClass("active"); // add active class to an anchor

            }
        }
    });
    /// END :: ACTIVE MENU CODE

});

$(document).ready(function () {

    $('.select2-selection__clear').hide();


});





/// START :: TinyMCE
document.addEventListener("DOMContentLoaded", () => {
    tinymce.init({
        selector: '#tinymce_editor,.tinymce_editor',
        height: 400,
        menubar: true,
        plugins: [
            'advlist autolink lists link charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table contextmenu paste code help wordcount'
        ],

        toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        setup: function (editor) {
            editor.on("change keyup", function (e) {
                //tinyMCE.triggerSave(); // updates all instances
                editor.save(); // updates this instance's textarea
                $(editor.getElement()).trigger('change'); // for garlic to detect change
            });
        }
    });
});

$('body').append('<div id="loader-container"><div class="loader"></div></div>');
$(window).on('load', function () {
    $('#loader-container').fadeOut('slow');
});

//magnific popup
$(document).on('click', '.image-popup-no-margins', function () {

    $(this).magnificPopup({
        type: 'image',
        closeOnContentClick: true,
        closeBtnInside: false,
        fixedContentPos: true,

        image: {
            verticalFit: true
        },
        zoom: {
            enabled: true,
            duration: 300 // don't foget to change the duration also in CSS
        }

    }).magnificPopup('open');
    return false;
});



setTimeout(function () {
    $(".error-msg").fadeOut(1500)
}, 5000);

$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: {
            id: '-1', // the value of the option
            text: 'Select an option'
        },
        allowClear: false


    });
});

function show_error() {
    Swal.fire({
        title: 'Modification not allowed',
        icon: 'error',
        showDenyButton: true,

        confirmButtonText: 'Yes',
        denyCanceButtonText: `No`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */

    })
}

function confirmationDelete(e) {
    var url = e.currentTarget.getAttribute('href'); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
    $('#form-del').attr('action', url);
    Swal.fire({
        title: 'Are You Sure Want to Delete This Record??',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#d33',
        confirmButtonText: window.trans["Yes Delete"],
        cancelButtonText: window.trans['Cancel'],
        reverseButtons: true,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            $("#form-del").submit();
        } else {
            $('#form-del').attr('action', '');
        }
    })
    return false;
}

function chk(checkbox) {
    if (checkbox.checked) {

        active(event.target.id, 1, checkbox.getAttribute('data-url'));

    } else {

        active(event.target.id, 0, checkbox.getAttribute('data-url'));
    }
}


function active(id, value, url) {

    $.ajax({
        url: url,
        type: "POST",
        data: {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            "id": id,
            "status": value,
        },
        cache: false,
        success: function (result) {

            if (result.error == false) {
                Toastify({
                    text: result.message,
                    duration: 6000,
                    close: !0,
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                }).showToast();
                $('#table_list').bootstrapTable('refresh');
            } else {
                Toastify({
                    text: result.message,
                    duration: 6000,
                    close: !0,
                    backgroundColor: '#dc3545'
                }).showToast();
                $('#table_list').bootstrapTable('refresh');
            }
        },
        error: function (error) {

        }
    });
}










$("#category").change(function () {
    $('#parameter_type').empty();
    $('#facility').show();

    $('.parsley-error filled,.parsley-required').attr("aria-hidden", "true");

    var parameter_types = $(this).find(':selected').data('parametertypes');
    console.log(parameter_types);

    parameter_data = $.parseJSON($('#parameter_count').val());
    data_arr = (parameter_types + ',').split(",");


    $.each(data_arr, function (key, value) {
        let param = parameter_data.filter(parameter => parameter.id == value);

        var a = "";
        if (param[0]) {
            let mandatory = param[0].is_required == 1 ? "mandatory" : "";
            let isRequired = param[0].is_required == 1 ? "required" : "";
            if (param[0].type_of_parameter == "radiobutton") {

                $('#parameter_type').append(
                    '<div class="form-group '+mandatory+' col-md-3 chk"id=' + param[0].id + '><label for="' + param[0].name + '" class="form-label col-12">' + param[0].name + '</label></div>'
                );
                $.each(param[0].type_values, function (k, v) {
                    $('#' + param[0].id).append(
                        '<input name="par_' + param[0].id + '" type="radio" value="' + v + '" class="form-check-input ml-5" '+isRequired+'/>' + v + '  '
                    );
                });
            }
            if (param[0].type_of_parameter == "checkbox") {

                $('#parameter_type').append(
                    '<div class="form-group '+mandatory+' col-md-3 chk' + '"id=' + param[0].id + '><label for="' + param[0].name + '" class="form-label col-12">' + param[0].name + '</label></div>'
                );
                $.each(param[0].type_values, function (k, v) {
                    $('#' + param[0].id).append(
                        '<input name="par_' + param[0].id + '[]" type="checkbox" value="' + v + '" class="form-check-input" '+isRequired+'/>' + v + '  '
                    );
                });
            }

            if (param[0].type_of_parameter == "dropdown") {
                $('#parameter_type').append('<div class="form-group '+mandatory+' col-md-3"><label for="' + param[0].name + '" class="form-label col-12 ">' + param[0].name + '</label>' + '<select id=' + param[0].id + ' name="par_' + param[0].id + '" class="select2 form-select '+isRequired+' form-control-sm" ><option value="">choose option</option></select></div>');
                arr = (param[0].type_values);
                $.each(arr,
                    function (key, val) {
                        $('#' + param[0].id).append($(
                            '<option>', {
                            value: val,
                            text: val
                        }));
                    });
            }
            if (param[0].type_of_parameter == "textbox") {
                $('#parameter_type').append($(
                    '<div class="form-group col-md-3 '+mandatory+'"><label for="' + param[0].name + '" class="form-label  col-12">' + param[0].name +'</label><input class="form-control" '+isRequired+' type="text" id="' + param[0].id + '" name="par_' + param[0] .id + '"></div>'
                ));
            }
            if (param[0].type_of_parameter == "number") {
                $('#parameter_type').append($(
                    '<div class="form-group col-md-3 '+mandatory+'"><label for="' + param[0].name + '" class="form-label  col-12">' + param[0].name + '</label><input class="form-control" '+isRequired+' type="number" id="' + param[0].id + '" name="par_' + param[0] .id + '"></div>'
                ));
            }
            if (param[0].type_of_parameter == "textarea") {
                $('#parameter_type').append($(
                    '<div class="form-group col-md-3 '+mandatory+'"><label for="' + param[0].name + '" class="form-label  col-12">' + param[0].name + '</label><textarea name = "par_' + param[0].id + '" id = "' + param[0].id + '" class= "form-control" '+isRequired+' cols = "40" rows = "3"></textarea ></div >'
                ));
            }
            if (param[0].type_of_parameter == "file") {
                $('#parameter_type').append($(
                    '<div class="form-group col-md-3 '+mandatory+'"><label for="' + param[0].name + '" class="form-label  col-12">' + param[0].name + '</label><input class="form-control" '+isRequired+' type="file" id="' + param[0].id + '" name="par_' + param[0] .id + '"></div>'
                ));

            }

        }
    });
});


function initMap() {
    var latitude = parseFloat($('#latitude').val());
    var longitude = parseFloat($('#longitude').val());
    var map = new google.maps.Map(document.getElementById('map'), {

        center: {
            lat: latitude,
            lng: longitude
        },
        zoom: 13
    });
    var marker = new google.maps.Marker({
        position: {
            lat: latitude,
            lng: longitude
        },
        map: map,
        draggable: true,
        title: 'Marker Title'
    });
    google.maps.event.addListener(marker, 'dragend', function (event) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'latLng': event.latLng
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    var address_components = results[0].address_components;
                    var city, state, country, full_address;

                    for (var i = 0; i < address_components.length; i++) {
                        var types = address_components[i].types;
                        if (types.indexOf('locality') != -1) {
                            city = address_components[i].long_name;
                        } else if (types.indexOf('administrative_area_level_1') != -1) {
                            state = address_components[i].long_name;
                        } else if (types.indexOf('country') != -1) {
                            country = address_components[i].long_name;
                        }
                    }

                    full_address = results[0].formatted_address;

                    // Do something with the city, state, country, and full address

                    $('#city').val(city);
                    $('#country').val(state);
                    $('#state').val(country);
                    $('#address').val(full_address);


                    $('#latitude').val(event.latLng.lat());
                    $('#longitude').val(event.latLng.lng());

                } else {
                    console.log('No results found');
                }
            } else {
                console.log('Geocoder failed due to: ' + status);
            }
        });
    });

    var input = document.getElementById('searchInput');
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);

    var infowindow = new google.maps.InfoWindow();
    var marker = new google.maps.Marker({
        map: map,
        anchorPoint: new google.maps.Point(0, -29)
    });

    autocomplete.addListener('place_changed', function () {
        infowindow.close();
        marker.setVisible(false);
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            window.alert("Autocomplete's returned place contains no geometry");
            return;
        }

        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }
        marker.setIcon(({
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(35, 35)
        }));
        marker.setPosition(place.geometry.location);
        marker.setVisible(true);

        var address = '';
        if (place.address_components) {
            address = [
                (place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
        }

        infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
        infowindow.open(map, marker);

        // Location details
        for (var i = 0; i < place.address_components.length; i++) {
            console.log(place);

            if (place.address_components[i].types[0] == 'locality') {
                $('#city').val(place.address_components[i].long_name);


            }
            if (place.address_components[i].types[0] == 'country') {
                $('#country').val(place.address_components[i].long_name);


            }
            if (place.address_components[i].types[0] == 'administrative_area_level_1') {
                console.log(place.address_components[i].long_name);
                $('#state').val(place.address_components[i].long_name);


            }
        }


        var latitude = place.geometry.location.lat();
        var longitude = place.geometry.location.lng();
        $('#address').val(place.formatted_address);


        $('#latitude').val(place.geometry.location.lat());
        $('#longitude').val(place.geometry.location.lng());
    });
}
$(document).ready(function () {

    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType);

    $('.filepond').filepond({
        credits: null,
        allowFileSizeValidation: "true",
        maxFileSize: '25MB',
        labelMaxFileSizeExceeded: 'File is too large',
        labelMaxFileSize: 'Maximum file size is {filesize}',
        allowFileTypeValidation: true,
        acceptedFileTypes: ['image/*'],
        labelFileTypeNotAllowed: 'File of invalid type',
        fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
        storeAsFile: true,
        allowPdfPreview: true,
        pdfPreviewHeight: 320,
        pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',
        allowVideoPreview: true, // default true
        allowAudioPreview: true // default true
    });

    $('.doc-filepond').filepond({
        credits: null,
        allowFileSizeValidation: "true",
        maxFileSize: '25MB',
        labelMaxFileSizeExceeded: 'File is too large',
        labelMaxFileSize: 'Maximum file size is {filesize}',
        labelFileTypeNotAllowed: 'File of invalid type',
        fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
        storeAsFile: true,
        allowPdfPreview: true,
        pdfPreviewHeight: 320,
        pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',
        allowVideoPreview: true, // default true
        allowAudioPreview: true // default true
    });
});

function getWordCount(fiels_type = "", field_counter = "", font = "0px arial") {
    let textArea = document.getElementById(fiels_type);
    let characterCounter = document.getElementById(field_counter);
    const text = textArea.value;
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    context.font = font;
    const width = context.measureText(text).width;
    const finalWidth = Math.ceil(width);
    textdata = "";
    info_data = "";
    var fiels_type_value = "";
    if (fiels_type == "meta_title") {
        fiels_type_value = "Meta title";
    } else if (fiels_type == "meta_description") {
        fiels_type_value = "Meta description";
    } else if (fiels_type == "edit_meta_title") {
        fiels_type_value = "Meta title";
    } else if (fiels_type == "edit_meta_description") {
        fiels_type_value = "Meta description";
    }
    if (fiels_type == "meta_title") {
        less_equal = 240;
        less_equal2 = 580;
        textdata =
            "<span>Title " +
            textdata +
            " is <b>" +
            finalWidth +
            "</b> pixel(s) long</span>";
    } else if (fiels_type == "meta_description") {
        less_equal = 395;
        less_equal2 = 920;
        textdata =
            "<span>Meta Description " +
            textdata +
            " is <b>" +
            finalWidth +
            "</b> pixel(s) long</span>";
    } else if (fiels_type == "edit_meta_title") {
        less_equal = 240;
        less_equal2 = 580;
        textdata =
            "<span>Title " +
            textdata +
            " is <b>" +
            finalWidth +
            "</b> pixel(s) long</span>";
    } else if (fiels_type == "edit_meta_description") {
        less_equal = 395;
        less_equal2 = 920;
        textdata =
            "<span>Meta Description " +
            textdata +
            " is <b>" +
            finalWidth +
            "</b> pixel(s) long</span>";
    }
    if (finalWidth <= less_equal) {
        info_class = "text-danger";
        info_icon =
            '<i class="fa fa-exclamation-triangle ' + info_class + '"></i>';
        info_data =
            "<span class=" +
            info_class +
            ">--Your page " + fiels_type_value + " is too short.</span>";
    } else if (finalWidth > less_equal && finalWidth <= less_equal2) {
        info_class = "text-success";
        info_icon = '<i class="fa fa-check-circle ' + info_class + '"></i>';
        info_data =
            "<span class=" +
            info_class +
            ">--Your page " + fiels_type_value + " is an acceptable length.</span>";
    } else if (finalWidth > less_equal2) {
        info_class = "text-danger";
        info_icon =
            '<i class="fa fa-exclamation-triangle ' + info_class + '"></i>';
        info_data =
            "<span class=" +
            info_class +
            ">--Page " + fiels_type_value + " should be around " +
            less_equal2 +
            " pixels in length.</span>";
    }
    // let numOfEnteredChars = textArea.value.length;
    // characterCounter.textContent = numOfEnteredChars;
    characterCounter.innerHTML = info_icon + " " + textdata + info_data;
}
$(document).on('click', '.accordion-item-header', function (event) {
    if ($(this).hasClass('active')) {
        // Close the clicked accordion if it's already active
        $(this).removeClass('active');
        $(this).next('.accordion-item-body').css('max-height', 0);
    } else {
        var currentActive = $('.accordion-item-header.active');
        if (currentActive && currentActive !== $(this)) {
            currentActive.removeClass('active');
            currentActive.next('.accordion-item-body').css('max-height', 0);
        }

        $(this).toggleClass('active');
        var accordionBody = $(this).next('.accordion-item-body');
        if ($(this).hasClass('active')) {
            accordionBody.css('max-height', accordionBody.prop('scrollHeight') + 'px');
        } else {
            accordionBody.css('max-height', 0);
        }
    }
});


$('.type-field').on('change', function (e) {
    e.preventDefault();

    const inputValue = $(this).val();
    const optionSection = $('.default-values-section');

    // Show/hide the "default-values-section" based on the selected value using a switch statement
    switch (inputValue) {
        case 'dropdown':
        case 'radio':
        case 'checkbox':
            optionSection.show(500).find('input').attr('required', true);
            break;
        default:
            optionSection.hide(500).find('input').removeAttr('required');
            break;
    }

});


// Repeater On Default Values section's Option Section
var defaultValuesRepeater = $('.default-values-section').repeater({
    show: function () {
        let optionNumber = parseInt($('.option-section:nth-last-child(2)').find('.option-number').text()) + 1;

        if (!optionNumber) {
            optionNumber = 1;
        }

        $(this).find('.option-number').text(optionNumber);

        $(this).slideDown();

        toggleAccessOfDeleteButtons();

    },
    hide: function (deleteElement) {
        $(this).slideUp(deleteElement,function(){
            $('.remove-default-option').attr('disabled', true)
            $(this).remove();
            setTimeout(() => {
                toggleAccessOfDeleteButtons();
            }, 500);
        });
    }
});

$('.verify-customer-status-form').on('submit', function (e) {
    e.preventDefault();
    let formElement = $(this);
    let submitButtonElement = $(this).find('#btn_submit');
    let url = $(this).attr('action');
    let submitButtonText = submitButtonElement.val();
    submitButtonElement.val('Please Wait...').attr('disabled', true);
    let modalElement = formElement.closest('.modal');
    modalElement.find('.btn-close').attr('disabled', true);
    modalElement.find('.close-btn').attr('disabled', true);
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
                eval(preSubmitFunction + "()");
            }
            let customSuccessFunction = $(this).data('success-function');
            // noinspection JSUnusedLocalSymbols
            function successCallback(response) {
                if (!$(formElement).hasClass('create-form-without-reset')) {
                    formElement[0].reset();
                }
                $('#table_list').bootstrapTable('refresh');
                $('#editModal').modal('hide');
                modalElement.find('.btn-close').removeAttr('disabled');
                modalElement.find('.close-btn').removeAttr('disabled');
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

// Fill Currency Symbol in Symbol input based on country
$("#currency-code").on("change", function(e){
    let value = $(this).val();
    if(value){
        let url = $("#url-for-currency-symbol").val()

        axios.get(url, {
            params: {
              country_code: value
            }
        }).then(function (response) {
            if(response.data.error == false){
                $("#currency-symbol").val(response.data.data)
            }else{
                console.log(response);
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
})


// Repeater On Project's Floor Plans
let IdProjectFloorPlanCounter = 0;
var projectFloorPlanRepeater = $('.projects-floor-plans').repeater({
    initEmpty: true, // Ensure the first item is empty
    show: function () {
        let floorNumber = parseInt($('.floor-section:nth-last-child(2)').find('.floor-number').text()) + 1;

        if (!floorNumber) {
            floorNumber = 1;
        }

        let newFloorImageId = 'floor-image' + IdProjectFloorPlanCounter;
        let newFloorImagePreviewId = 'floor-image-preview-' + IdProjectFloorPlanCounter;
        let newFloorImageRequired = 'floor-image-required-' + IdProjectFloorPlanCounter;
        let newRemoveFloor = 'remove-floor-' + IdProjectFloorPlanCounter;
        $(this).find('.floor-number').text(floorNumber);
        $(this).find('.floor-image').attr('id', newFloorImageId)
        $(this).find('.floor-image-preview').attr('id', newFloorImagePreviewId)
        $(this).find('.floor-image-required').attr('id', newFloorImageRequired)
        $(this).find('.remove-floor').attr('id', newRemoveFloor);
        $(this).slideDown();
        IdProjectFloorPlanCounter++;
    },
    hide: function (deleteElement) {
        $this = $(this);
        if($(this).find('.remove-floor').data('id')){
            let url = $(this).find('.remove-floor').data('url');
            showDeletePopupModal(url, {
                successCallBack: function () {
                    $this.slideUp(deleteElement, function () {
                        $this.remove();
                    });
                }, errorCallBack: function (response) {
                    showErrorToast(response.message);
                }
            })
        }else{
            $this.slideUp(deleteElement, function () {
                $this.remove();
            });
        }
    }
});

$(document).on('click', '.block-user', function () {
    let $this = $(this);
    let url = $(this).data('url');
    showSweetAlertConfirmPopup(url,'POST', {},{
        successCallBack: function (response) {
            if(response.error == false){
                window.location.reload(true);
            }else{
                $("#chat_form").show();
                $(".blocked-user-message-div").hide();
                $(".for-blocked-by-admin").hide();
                $(".for-blocked-by-user").hide();
                $this.hide();
                showErrorToast(response.message);
            }
        }, errorCallBack: function (response) {

        }
    })
});

$(document).on('click', '.unblock-user', function () {
    let blockUserElement = $(document).find('.block-user');
    let url = $(this).data('url');

    let options = {
        text: window.trans["You wants to unblock ?"],
    }
    showSweetAlertConfirmPopup(url,'POST', options,{
        successCallBack: function (response) {
            if(response.error == false){
                let data = response.data;
                if(data){
                    window.location.reload(true);
                    // if(data.is_blocked_by_user == 1){
                    //     $("#chat_form").hide();
                    //     $(".blocked-user-message-div").show();
                    //     $(".for-blocked-by-admin").hide();
                    //     $(".for-blocked-by-user").show();
                    //     blockUserElement.show();
                    // }else{
                    //     $("#chat_form").show();
                    //     $(".blocked-user-message-div").hide();
                    //     $(".for-blocked-by-admin").hide();
                    //     $(".for-blocked-by-user").hide();
                    //     blockUserElement.show();
                    // }
                }else{

                }
            }else{
                $("#chat_form").hide();
                $(".blocked-user-message-div").show();
                blockUserElement.hide();
                showErrorToast(response.message);
            }
        }, errorCallBack: function (response) {

        }
    })

});


$(document).on("click",'.request-status', function(){
    if($(this).val() == 'rejected'){
        $(".reject-reason-text-div").show();
    }else{
        $(".reject-reason-text-div").hide();
    }
});


// Repeater On Features section's of packages
let featureCounter = 1;
var packageFeatureRepeater = $('.feature-sections').repeater({
    initEmpty: true,
    show: function () {
        // Add ID in Unlimited Radio
        let newFeatureTypeUnlimitedId = 'feature-type-unlimited-' + featureCounter;
        $(this).find('.feature-type-unlimited').attr('id', newFeatureTypeUnlimitedId)
        $(this).find('.feature-type-unlimited-label').attr('for', newFeatureTypeUnlimitedId)

        // Add ID in Limited Radio
        let newFeatureTypeLimitedId = 'feature-type-limited-' + featureCounter;
        $(this).find('.feature-type-limited').attr('id', newFeatureTypeLimitedId)
        $(this).find('.feature-type-limited-label').attr('for',newFeatureTypeLimitedId)

        // Add ID in Limit Div
        let newLimitDiv = 'limit-div-' + featureCounter;
        $(this).find('.limit-div').attr('id', newLimitDiv)


        // Add ID in Limit text
        let newLimitText = 'limit' + featureCounter;
        $(this).find('.limit').attr('id', newLimitText)

        $(this).slideDown();
        featureCounter++;
        checkDuplicateFeatures('.features','.add-new-feature'); // Check duplicates after adding a new row
    },
    hide: function (deleteElement) {
        $(this).slideUp(deleteElement,function(){
            $(this).remove();
            checkDuplicateFeatures('.features','.add-new-feature'); // Check duplicates after adding a new row

        });
    }
});
// Initialize jQuery Repeater for Bank Details
const bankDetailsRepeater = $('.bank-details-repeater').repeater({
    // Set initEmpty to false so it doesn't clear existing items
    initEmpty: true,
    show: function() {
        $(this).slideDown();
    },
    hide: function(deleteElement) {
        Swal.fire({
            title: window.trans["Are you sure"],
            text: window.trans["You wants to change it ?"],
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: window.trans["Yes"],
            cancelButtonText: window.trans["No"],
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).slideUp(deleteElement);
            }
        });
    }
});