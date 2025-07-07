@extends('layouts.main')

@section('title')
    {{ __('Features') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-striped"
                                    id="table_list" data-toggle="table" data-url="{{ route('package-features.show',1) }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-search-align="right" data-toolbar="#toolbar" data-show-columns="true"
                                    data-show-refresh="true" data-trim-on-search="false" data-responsive="true"
                                    data-sort-name="id" data-sort-order="asc" data-pagination-successively-size="3"
                                    data-query-params="queryParams">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                            <th scope="col" data-field="name" data-sortable="true"> {{ __('Name') }} </th>
                                            @if (has_permissions('update', 'package-feature'))
                                                <th scope="col" data-field="status" data-sortable="true" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                            @else
                                                <th scope="col" data-field="status" data-sortable="true" data-align="center" data-width="5%" data-formatter="yesNoStatusFormatter"> {{ __('Is Active ?') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>
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

            };
        }

        function chk(checkbox) {
            if (checkbox.checked) {
                active(event.target.id);
            } else {
                disable(event.target.id);
            }
        }
    </script>

    <script>
        function disable(id) {
            $.ajax({
                url: "{{ route('package-features.status-update') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 0,
                },
                cache: false,
                success: function(result) {
                    let text = '{{ trans("Status Updated Successfully") }}';
                    if (result.error == false) {
                        Toastify({
                            text: text,
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
                            backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"

                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                },
                error: function(error) {
                    Toastify({
                        text: result.message,
                        duration: 6000,
                        close: !0,
                        backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                    }).showToast();
                }
            });
        }

        function active(id) {
            $.ajax({
                url: "{{ route('package-features.status-update') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 1,
                },
                cache: false,
                success: function(result) {
                    if (result.error == false) {
                        let text = '{{ trans("Status Updated Successfully") }}';
                        Toastify({
                            text: text,
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
                            backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                },
                error: function(error) {
                    Toastify({
                        text: result.message,
                        duration: 6000,
                        close: !0,
                        backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                    }).showToast();
                }
            });
        }
    </script>
@endsection
