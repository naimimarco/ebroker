@extends('layouts.main')

@section('title')
{{ __('Users Packages') }}
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
    <div class="row">
        <div class="card"> @if (has_permissions('create', 'property'))
            <div class="card-header">

                <div class="row">

                </div>
                @endif

                <hr>
                <div class="card-body">


                    <div class="row">
                        <div class="col-12">

                            <table class="table table-striped"
                                id="table_list" data-toggle="table" data-url="{{ url('get_user_package_list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                data-search-align="right" data-toolbar="#toolbar" data-show-columns="true"
                                data-show-refresh="true" data-trim-on-search="false" data-responsive="true"
                                data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3"
                                data-query-params="queryParams">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true"> {{ __('ID') }}</th>
                                        <th scope="col" data-field="name" data-align="center" data-sortable="false"> {{ __('Package Name') }} </th>
                                        <th scope="col" data-field="start_date" data-align="center" data-sortable="true"> {{ __('Start Date') }} </th>
                                        <th scope="col" data-field="end_date" data-align="center" data-sortable="true"> {{ __('End Date') }} </th>
                                        <th scope="col" data-field="customer_name" data-align="false" data-sortable="false"> {{ __('Customer Name') }} </th>
                                        <th scope="col" data-field="subscription" data-align="center" data-sortable="false"> {{ __('Subscription') }} </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>


                </div>
            </div>


        </div>
        <!-- EDIT MODEL -->
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
</script>
@endsection
