@extends('layouts.main')

@section('title')
    {{ __('Report Reasons') }}
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
                @if (has_permissions('create', 'report_reason'))
                    <div class="col-md-12">
                        <div class="card">
                            <form action="{{ route('report-reasons.store') }}" class="needs-validation" method="post"
                                data-parsley-validate enctype="multipart/form-data">

                                {{ csrf_field() }}

                                <div class="card-body">
                                    <textarea id="user_id" name="user_id" style="display: none"></textarea>
                                    <textarea id="fcm_id" name="fcm_id" style="display: none"></textarea>

                                    <input type="hidden" name="type" value="0">

                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label class="form-label">{{ __('Reason') }}</label> <span
                                                class="text-danger">*</span>
                                            <textarea name="reason" class="form-control" placeholder={{ __('Reason') }} required></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-12 d-flex justify-content-end">
                                        <button class="btn btn-primary" type="submit"
                                            name="submit">{{ __('Submit') }}</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">




                                    <table class="table-light" aria-describedby="mydesc" class='table-striped'
                                        id="table_list" data-toggle="table" data-url="{{ url('report-reasons-list') }}"
                                        data-click-to-select="true" data-responsive="true" data-side-pagination="server"
                                        data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                        data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                                        data-show-refresh="true" data-trim-on-search="false" data-sort-name="id"
                                        data-sort-order="desc" data-pagination-successively-size="3"
                                        data-query-params="queryParams">

                                        <thead>
                                            <tr>

                                                <th scope="col" data-field="id" data-sortable="true">
                                                    {{ __('ID') }}</th>
                                                <th scope="col" data-field="reason" data-sortable="true">
                                                    {{ __('Reason') }}</th>
                                                <th scope="col" data-field="operate" data-events="actionEvents">
                                                    {{ __('Action') }}</th>
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
                            <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Report Reason') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('report-reasons-update') }}" class="form-horizontal"
                                enctype="multipart/form-data" method="POST" data-parsley-validate>
                                {{ csrf_field() }}

                                <input type="hidden" id="edit_id" name="edit_id">
                                <div class="row">
                                    <div class="col-md-12 col-12 ">
                                        <div class="form-group">
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


        </section>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js"></script>

    <script type="text/javascript">
        table = $('#users_list');
        var fcm_list = [];
        var user_list = [];


        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit_id").val("")
                $("#edit_reason").val("")
                $("#edit_id").val(row.id).trigger("change")
                $("#edit_reason").val(row.reason).trigger("change")
            }
        };

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
    </script>








    <script type="text/javascript">
        // function setValue(id) {
        //     $('#edit_id').val($("#" + id).parents('tr:first').find('td:nth-child(1)').text())
        //     $("#edit_reason").val($("#" + id).parents('tr:first').find('td:nth-child(2)').text());
        // }
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
