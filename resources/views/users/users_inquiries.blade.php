@extends('layouts.main')

@section('title')
{{ __('Users Inquiries') }}
@endsection

@section('page-title')
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h4>@yield('title')</h4>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first"> </div>
    </div>
</div>
@endsection


@section('content')
<section class="section">
    <div class="card">
        <div class="card-header"> </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped" id="table_list"
                        data-toggle="table" data-url="{{ url('get_users_inquiries') }}" data-click-to-select="true"
                        data-side-pagination="server" data-pagination="true"
                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                        data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                        data-responsive="true" data-sort-name="id" data-sort-order="desc"
                        data-pagination-successively-size="3" data-query-params="queryParams">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                <th scope="col" data-field="first_name" data-sortable="true" data-align="center">{{ __("First Name") }} </th>
                                <th scope="col" data-field="last_name" data-sortable="true" data-align="center">{{ __('Last Name') }}</th>
                                <th scope="col" data-field="email" data-sortable="true" data-align="center">{{ __('Email') }}</th>
                                <th scope="col" data-field="subject" data-sortable="true">{{ __('Subject') }}</th>
                                <th scope="col" data-field="message" data-sortable="true">{{ __('Message') }}</th>
                            </tr>
                        </thead>
                    </table>
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
</script>
@endsection
