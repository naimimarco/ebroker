@extends('layouts.main')

@section('title')
    {{ __('Advertisement') }}
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
                        <table class="table table-striped" id="table_list"
                            data-toggle="table" data-url="{{ url('featured_properties_list') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="for" data-align="center" data-sortable="true" data-formatter="advertisementTypeFormatter">{{ __('Advertisement Type') }}</th>
                                    <th scope="col" data-field="image" data-formatter="imageFormatter" data-align="center"> {{ __('Image') }} </th>
                                    <th scope="col" data-field="start_date" data-align="center" data-sortable="true">{{ __('Start Date') }}</th>
                                    <th scope="col" data-field="end_date" data-align="center" data-sortable="true">{{ __('End Date') }}</th>
                                    <th scope="col" data-field="customer.name" data-align="center"> {{ __('Customer Name') }}</th>
                                    <th scope="col" data-field="customer.mobile" data-align="center" data-visible="false">{{ __('Customer Contact') }}</th>
                                    <th scope="col" data-field="user_email" data-align="center" data-visible="false" data-sortable="false">{{ __('Customer Email') }}</th>
                                    <th scope="col" data-field="status" data-align="center" data-sortable="false"> {{ __('Status') }} </th>
                                    <th scope="col" data-field="is_enable" data-formatter="enableDisableSwitchFormatter" data-sortable="false" data-align="center" data-width="5%"> {{ __('Enable/Disable') }}</th>
                                    @if (has_permissions('update', 'advertisement'))
                                        <th scope="col" data-field="operate" data-align="center" data-sortable="false"> {{ __('Action') }}</th>
                                    @endif

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODEL MODEL -->
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">{{ __('Advertisement Status') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <form action="{{ url('adv-status-update') }}" class="form-horizontal"
                            enctype="multipart/form-data" method="POST" data-parsley-validate>

                            {{ csrf_field() }}

                            <div class="row">

                                <div class="col-sm-12">

                                    <select name="edit_adv_status" id="edit_adv_status" class="chosen-select form-select"
                                        style="width: 100%">

                                        <option value='0'>{{ __('Approved') }}</option>
                                        <option value='1'>{{ __('Pending') }}</option>
                                        <option value='2'>{{ __('Rejected') }}</option>

                                    </select>
                                    <input type="hidden" name="id" id="id">

                                </div>

                            </div>
                            <div class="modal-footer" style="padding: 2% 0%">
                                <button type="button" class="btn btn-secondary waves-effect"
                                    data-bs-dismiss="modal">{{ __('Close') }}</button>

                                <button type="submit"
                                    class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
                        </form>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <input type="hidden" id="customerid" value="{{ isset($_GET['customer']) ? $_GET['customer'] : '' }}">
    </section>
@endsection

@section('script')
    <script>
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });

        $('#category').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $(document).ready(function() {
            var params = new window.URLSearchParams(window.location.search);
            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
        });



        function setValue(id) {

            $("#id").val(id);
            // $("#edit_category").val($("#" + id).parents('tr:first').find('td:nth-child(3)').text());
        }

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
    </script>
@endsection
