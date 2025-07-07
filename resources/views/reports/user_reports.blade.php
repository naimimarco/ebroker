@extends('layouts.main')

@section('title')
    {{ __('User Reports') }}
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
    <div class="row">

        <section class="section">

            <div class="row">

                <div class="col-md-12">
                    <div class="card">

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <table class="table-light" aria-describedby="mydesc" class='table-striped'
                                        id="table_list" data-toggle="table" data-url="{{ url('user_reports_list') }}"
                                        data-click-to-select="true" data-responsive="true" data-side-pagination="server"
                                        data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                        data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                                        data-show-refresh="true" data-trim-on-search="false" data-sort-name="id"
                                        data-sort-order="desc" data-pagination-successively-size="3"
                                        data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                                <th scope="col" data-field="reason" data-align="center"> {{ __('Reason') }}</th>
                                                <th scope="col" data-sort-name="customer_name" data-field="customer.name" data-align="center"> {{ __('User') }}</th>
                                                <th scope="col" data-field="property_title" data-align="center" data-events="actionEvents"> {{ __('Property') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Type') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('report-reasons-update') }}" class="form-horizontal"
                                enctype="multipart/form-data" method="POST" data-parsley-validate>
                                {{ csrf_field() }}

                                <input type="hidden" id="edit_id" name="edit_id">
                                <div class="row">
                                    <div class="col-md-12 col-12 ">
                                        <div class="form-group mandatory">
                                            <label for="edit_reason" class="form-label col-12">{{ __('Reason') }}</label>

                                            <textarea name="edit_reason" id="edit_reason" class="form-control" placeholder={{ __('Reason') }} required></textarea>

                                        </div>
                                    </div>
                                </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect"
                                data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light"
                                id="btn_submit">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>

            {{-- View Property Modal --}}
            <div id="ViewPropertyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myModalLabel1">{{ __('View Property') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="margin: 0;"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">

                                {{-- Title --}}
                                <div class="col-md-12 col-lg-6">
                                    <div class="col-md-12 col-12 form-group">
                                        <label for="title" class="form-label col-12">{{ __('Title') }}</label>
                                        <input class="form-control " placeholder="Title" id="title" readonly type="text">
                                    </div>
                                </div>

                                <div class="col-md-12 col-lg-6">
                                    <div class="row">
                                        {{-- City --}}
                                        <div class="col-md-12 col-lg-4 form-group">
                                            <label for="city" class="form-label col-12">{{ __('City') }}</label>
                                            <input class="form-control" placeholder="City" readonly="true" id="city" type="text">
                                        </div>

                                        {{-- State --}}
                                        <div class="col-md-12 col-lg-4 form-group">
                                            <label for="state" class="form-label col-12">{{ __('State') }}</label>
                                            <input class="form-control " placeholder="State" id="state" readonly type="text">
                                        </div>

                                        {{-- Country --}}
                                        <div class="col-md-12 col-lg-4 form-group">
                                            <label for="country" class="form-label col-12">{{ __('Country') }}</label>
                                            <input class="form-control " placeholder="Country" id="country" readonly type="text">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-lg-6">
                                    <div class="row">

                                        {{-- Property Type --}}
                                        <div class="col-md-12 col-lg-6 form-group">
                                            <label for="property_type" class="form-label col-12">{{ __('Property Type') }}</label>
                                            <input class="form-control " placeholder="Property Type" id="property_type" readonly type="text">
                                        </div>

                                        {{-- Category --}}
                                        <div class="col-md-12 col-lg-6 form-group">
                                            <label for="category" class="form-label col-12">{{ __('Category') }}</label>
                                            <input class="form-control " placeholder="Category" id="category" readonly type="text">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-lg-6">
                                    <div class="row">

                                        {{-- Price --}}
                                        <div class="col-md-6 col-12 form-group">
                                            <label for="price" class="form-label col-12">{{ __('Price') }}</label>
                                            <input class="form-control " placeholder="Price" id="price" readonly type="text">
                                        </div>

                                        {{-- Rent Duration --}}
                                        <div class="col-md-6 col-12 form-group rent-duration-div" style="display: none">
                                            <label for="property_type" class="form-label col-12">{{ __('Rent Duration') }}</label>
                                            <input class="form-control " placeholder="Property Type" id="rentduration" readonly type="text">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="row">
                                        {{-- Client Address --}}
                                        <div class="col-md-6 col-12 form-group">
                                            <label for="client_address" class="form-label col-12">{{ __('Client Address') }}</label>
                                            <textarea class="form-control " placeholder="Client Address" rows="2" id="client_address" autocomplete="off" cols="50" readonly></textarea>
                                        </div>

                                        {{-- Address --}}
                                        <div class="col-md-6 col-12 form-group">
                                            <label for="address" class="form-label col-12">{{ __('Address') }}</label>
                                            <textarea class="form-control " placeholder="Address" rows="2" id="address" autocomplete="off" cols="50" readonly></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <label for="description" class="form-label col-12">{{ __('Description') }}</label>
                                    <textarea class="form-control " placeholder="Address" rows="2" id="description" autocomplete="off" cols="50" readonly></textarea>
                                </div>

                                {{-- Title Image --}}
                                <div class="col-md-12 col-lg-6 mt-1">
                                    <label for="description" class="form-label col-12">{{ __('Title Image') }}</label>
                                    <img src="" alt="" id="title_image" height="50%" width="50%">
                                </div>
                            </div>

                            {{-- Enable & Disable Button --}}
                            {{-- <div class="row enable_disable mt-2" style="justify-content: center"></div> --}}
                        </div>
                    </div>
                </div>
            </div>
            {{-- View Property Modal --}}


        </section>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js"></script>

    <script type="text/javascript">
        table = $('#users_list');
        var fcm_list = [];
        var user_list = [];


        function queryParams_1(p) {
            return {
                "status": $('#filter_status').val(),
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }

        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }
        window.actionEvents = {
            'click .view-property': function(e, value, row, index) {
                $('.enable_disable').empty();
                $('.gallary_images').empty();
                if(row.property){
                    $.each(row.property.gallery, function(key, value) {
                        $('.gallary_images').append(
                            `<div class="col-md-3 col-12">
                                <img src=${value.image_url} alt="" id="3d_image" width="100%" height="100%">
                            </div>`
                        );
                    });
                }
                property_status = row.property.status == 1 ? 'checked' : '';

                $('.enable_disable').append(

                    'Enable/Disable <div class="form-check form-switch" style="justify-content:center;display:flex;">' +
                    ' <input class = "form-check-input switch1" id = "' + row.property.id +
                    '"onclick = "chk(this);" type = "checkbox" role = "switch"' + property_status +
                    ' style="width: 4rem;height: 2rem;"> '
                );
                // Make First letter Capital
                let propertyTypeText = row.property.propery_type.charAt(0).toUpperCase() + row.property.propery_type.slice(1);
                if(row.property.propery_type == 'rent'){
                    $(".rent-duration-div").show();
                    $("#rentduration").val(window.trans[row.property.rentduration]);
                }else{
                    $(".rent-duration-div").hide();
                    $("#rentduration").val("");
                }
                $("#property_type").val(propertyTypeText);
                $("#title").val(row.property.title);
                $("#category").val(row.property.category.category);
                $("#state").val(row.property.state);
                $("#city").val(row.property.city);
                $("#country").val(row.property.country);
                $("#state").val(row.property.state);
                $("#price").val(row.property.price);
                $("#latitude").val(row.property.latitude);
                $("#longitude").val(row.property.longitude);
                $("#client_address").val(row.property.client_address);
                $("#address").val(row.property.address);
                $("#description").html(row.property.description);
                $('#title_image').attr('src', row.property.title_image);
                $('#3d_image').attr('src', row.property.three_d_image);
            }
        }
    </script>

    <script type="text/javascript">
        function setValue(id) {
            $('#edit_id').val($("#" + id).parents('tr:first').find('td:nth-child(1)').text())
            $("#edit_reason").val($("#" + id).parents('tr:first').find('td:nth-child(2)').text());
        }
        $(document).on('click', '.delete-data', function() {
            if (confirm('Are you sure? Want to delete ?')) {
                var id = $(this).data("id");
                var image = $(this).data("image");
                $.ajax({
                    url: "{{ url('notification-delete') }}",
                    type: "GET",
                    data: {
                        id: id,
                        image: image
                    },
                    success: function(result) {
                        if (result.error) {
                            errorMsg(result.message);
                        } else {
                            $('#table_list1').bootstrapTable('refresh');
                            successMsg(result.message);
                        }
                    }
                });
            }
        });
    </script>

    <script type="text/javascript">
        $('#delete_multiple').on('click', function(e) {
            table = $('#table_list1');
            delete_button = $('#delete_multiple');
            selected = table.bootstrapTable('getSelections');
            ids = "";
            $.each(selected, function(i, e) {
                ids += e.id + ",";
            });
            ids = ids.slice(0, -1);
            if (ids == "") {
                alert('please Select Some Data');
            } else {
                if (confirm('Are You Sure Delete Selected Data')) {
                    $.ajax({
                        url: "{{ url('notification-multiple-delete') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            id: ids
                        },
                        beforeSend: function() {
                            delete_button.html('<em class="fa fa-spinner fa-pulse"></em>');
                        },
                        success: function(result) {
                            if (result.error) {
                                errorMsg(result.message);
                            } else {
                                delete_button.html('<em class="fa fa-trash"></em>');
                                $('#table_list1').bootstrapTable('refresh');
                                successMsg(result.message);
                            }
                        }
                    });
                }
            }
        });


        var $table = $('#users_list')
        var selections = []

        function responseHandler(res) {
            $.each(res.rows, function(i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        $(function() {
            $table.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function(e, rowsAfter, rowsBefore) {
                    var rows = rowsAfter

                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore
                    }

                    var ids = $.map(!$.isArray(rows) ? [rows] : rows, function(row) {
                        return row.id
                    })

                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'union' : 'difference'
                    selections = window._[func](selections, ids)
                })
        })
    </script>
@endsection
