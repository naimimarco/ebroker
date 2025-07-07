@extends('layouts.main')

@section('title')
    {{ __('Payment') }}
@endsection


@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>

            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">

            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ route('payment.list') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="customer.name" data-align="center" data-sortable="false"> {{ __('Client Name') }}</th>
                                    <th scope="col" data-field="package.name" data-align="center" data-sortable="false"> {{ __('Package Name') }} </th>
                                    <th scope="col" data-field="amount" data-align="center" data-sortable="true" data-formatter="paymentAmountFormatter"> {{ __('Amount') }} </th>
                                    <th scope="col" data-field="payment_type" data-align="center" data-sortable="true">{{ __('Payment Type') }} </th>
                                    <th scope="col" data-field="transaction_id" data-align="center" data-sortable="true">{{ __('Transaction Id') }} </th>
                                    <th scope="col" data-field="payment_gateway" data-align="center" data-sortable="true">{{ __('Payment Gateway') }} </th>
                                    <th scope="col" data-field="payment_status" data-align="center" data-sortable="true" data-formatter="paymentStatusFormatter"> {{ __('Status') }}</th>
                                    <th scope="col" data-field="created_at" data-align="center" data-sortable="true" data-visible="false"> {{ __('Payment Date') }} </th>
                                    <th scope="col" data-field="updated_at" data-align="center" data-sortable="true" data-visible="false"> {{ __('Payment Update Date') }} </th>
                                    <th scope="col" data-field="operate" data-align="center" data-sortable="false" data-events="operateEvents"> {{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Files Modal --}}
    <div class="modal fade" id="viewFilesModal" tabindex="-1" aria-labelledby="viewFilesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFilesModalLabel">{{ __('View Files') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="filesContainer" class="row">
                        <div class="modal-body documents-div row">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#status').val(),
                category: $('#category').val(),
                customer_id: $('#customerid').val(),
            };
        }

        function operateEvents(e) {
            return {
                'click .view-files': function(e, value, row, index) {
                    if(row.bank_receipt_files.length){
                        $('.documents-div').empty();
                        $.each(row.bank_receipt_files, function(key, value) {
                            var url = value.file; // Your URL
                            var filename = value.file_name;
                            var documentSvgImage = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M208 64h66.75a32 32 0 0122.62 9.37l141.26 141.26a32 32 0 019.37 22.62V432a48 48 0 01-48 48H192a48 48 0 01-48-48V304"></path><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M288 72v120a32 32 0 0032 32h120"></path><path fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M160 80v152a23.69 23.69 0 01-24 24c-12 0-24-9.1-24-24V88c0-30.59 16.57-56 48-56s48 24.8 48 55.38v138.75c0 43-27.82 77.87-72 77.87s-72-34.86-72-77.87V144"></path></svg>`;
                            var downloadImg = `<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m12 16 4-5h-3V4h-2v7H8z"></path><path d="M20 18H4v-7H2v7c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2v-7h-2v7z"></path></svg>`;
                            var downloadText = "{{ __('Download') }}";

                            $('.documents-div').append(
                                `<div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 bg-light rounded m-2 p-2">
                                    <div class="docs_main_div">
                                        <div class="doc_icon">
                                            ${documentSvgImage}
                                        </div>
                                        <div class="doc_title">
                                            <span title="${filename}">${filename}</span>
                                        </div>
                                        <div class="doc_download_button">
                                            <a href="${url}" target="_blank">
                                                <span>
                                                    ${downloadImg}
                                                </span>
                                                <span>${downloadText}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>`
                            );
                        });
                    }else{
                        $('.documents-div').append(
                            `<span class="no-data-found-span">
                                ${window.trans["No Data Found"]}
                            </span>`
                        );
                    }
                }
            };
        }

        // Change Event on Verification Required for user toggle
        $(document).on('click', ".payment-status-btn", function(e) {
            e.preventDefault();
            let status = $(this).data("status");
            let url = $(this).data("url");
            let id = $(this).data("id");
            let data = new FormData();
            data.append('status', status);
            data.append('id', id);

            if(status == 'success'){
                Swal.fire({
                    title: window.trans["Are you sure to make this payment success ?"],
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: window.trans["Confirm"],
                    cancelButtonText: window.trans["Cancel"],
                }).then((result) => {
                    if (result.isConfirmed) {
                        function successCallback(response) {
                            showSuccessToast(response.message);
                            $('#table_list').bootstrapTable('refresh');
                        }

                        function errorCallback(response) {
                            showErrorToast(response.message);
                        }

                        ajaxRequest("POST", url, data, null, successCallback, errorCallback);
                    }
                });
            } else {
                Swal.fire({
                    title: status == 'rejected' ? window.trans["Are you sure to make this payment reject ?"] : window.trans["Are you sure to make this payment cancel ?"],
                    icon: status == 'rejected' ? 'warning' : 'error',
                    showCancelButton: true,
                    input: "textarea",
                    inputAttributes: {
                        autocapitalize: "off",
                        placeholder: window.trans["Enter Reject Reason"],
                        maxlength: 255,
                    },
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: window.trans["Confirm"],
                    cancelButtonText: window.trans["Cancel"],
                    allowOutsideClick: () => !Swal.isLoading(),
                    didClose: () => {
                        // Ensure cleanup of any remaining overlay
                        $('.swal2-container').remove();
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        data.append('reject_reason', result.value);
                        function successCallback(response) {
                            showSuccessToast(response.message);
                            $('#table_list').bootstrapTable('refresh');
                        }

                        function errorCallback(response) {
                            showErrorToast(response.message);
                        }

                        ajaxRequest("POST", url, data, null, successCallback, errorCallback);
                    }
                    // Ensure cleanup in both confirm and cancel cases
                    $('.swal2-container').remove();
                });
            }
        });
    </script>
@endsection
