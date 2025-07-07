@extends('layouts.main')

@section('title')
    {{ __('Article') }}
@endsection

@section('page-title')
<div class="page-title">
	<div class="row">
		<div class="col-12 col-md-6 order-md-1 order-last">
			<h4>@yield('title')</h4>
		</div>
		<div class="col-12 col-md-6 order-md-2 order-first article_header">
            @if (has_permissions('create', 'article'))
                <a href="{{url('add_article') }}" class="btn btn-primary btn_add">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                        <path
                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z">
                        </path>
                    </svg>
                    {{ __('Add Article') }}
                </a>
            @endif
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
                        <table class="table table-striped"
                            id="table_list" data-toggle="table" data-url="{{ route('article_list') }}"
                            data-click-to-select="true" data-responsive="true" data-side-pagination="server"
                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                    <th scope="col" data-field="title" data-sortable="true">{{ __('Title') }}</th>
                                    <th scope="col" data-field="description" data-sortable="true">{{ __('Description') }}</th>
                                    <th scope="col" data-field="category_title" data-sortable="false">{{ __('Category Title') }}</th>
                                    <th scope="col" data-field="image" data-formatter="imageFormatter" data-sortable="false" data-align="center">{{ __('Image') }}</th>
                                    <th scope="col" data-field="meta_title" data-sortable="false" data-visible="false">{{ __('Meta Title') }}</th>
                                    <th scope="col" data-field="meta_description" data-sortable="false" data-visible="false">{{ __('Meta Description') }}</th>
                                    <th scope="col" data-field="operate" data-sortable="false" data-align="center"> {{ __('Action') }}</th>
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
            status: $('#status').val(),
            category: $('#category').val(),
            customer_id: $('#customerid').val(),
        };
    }
</script>
@endsection
