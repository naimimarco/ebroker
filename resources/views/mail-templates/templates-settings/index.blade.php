@extends('layouts.main')

@section('title')
    {{ __('Email Templates') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('Email Templates') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">

                    <section class="section">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <table class="table table-striped"
                                            id="table_list" data-toggle="table" data-url="{{ route('email-templates.list') }}"
                                            data-click-to-select="false" data-responsive="true" data-side-pagination="server"
                                            data-pagination="false" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                            data-trim-on-search="false" data-sort="false" data-sort-name="id" data-sort-order="desc"
                                            data-pagination-successively-size="3" data-query-params="queryParams">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th scope="col" data-field="no" data-align="center">{{ __('No') }}</th>
                                                    <th scope="col" data-field="title">{{ __('Title') }}</th>
                                                    <th scope="col" data-field="operate" data-sortable="false" data-align="center"> {{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>

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
@endsection
