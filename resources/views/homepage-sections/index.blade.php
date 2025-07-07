@extends('layouts.main')

@section('title')
    {{ __('Homepage Sections') }}
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

        {{-- Add Homepage Section Button --}}
        @if(has_permissions('create', 'homepage-sections'))
            <div class="col-md-12 text-end">
                <button class="btn mb-3 btn-primary add-homepage-section-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                        <path
                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z">
                        </path>
                    </svg>
                    {{ __('Add Homepage Section') }}
                </button>
            </div>
        @endif
        {{-- Create Homepage Section Section --}}
        <div class="card add-homepage-section mt-3" style="display: none;">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Create Homepage Section') }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        {!! Form::open(['url' => route('homepage-sections.store'), 'data-parsley-validate', 'class' => 'create-form']) !!}
                        <div class=" row">

                            {{-- Title --}}
                            <div class="col-lg-12 col-xl-6 form-group mandatory">
                                {{ Form::label('title', __('Title'), ['class' => 'form-label text-center']) }}
                                {{ Form::text('title', '', [ 'class' => 'form-control', 'placeholder' => trans('Title'), 'data-parsley-required' => 'true', 'id' => 'title']) }}
                            </div>

                            {{-- Section Type --}}
                            <div class="col-lg-12 col-xl-6 form-group mandatory">
                                {{ Form::label('section_type', __('Section Type'), ['class' => 'form-label text-center']) }}
                                {{ Form::select('section_type', $sectionTypes, '', [ 'class' => 'form-control form-select', 'placeholder' => trans('Section Type'), 'data-parsley-required' => 'true', 'id' => 'section_type']) }}
                            </div>

                            {{-- Save --}}
                            <div class="col-sm-12 col-md-12 text-end" style="margin-top:2%;">
                                {{ Form::submit('Save', ['class' => 'btn btn-primary me-1 mb-1']) }}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

    </section>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="toolbar">
                            <span class="d-block mb-4 mt-2 text-danger small mt-4">{{ __('NOTE :- Drag and drop to change the order and click on update order button to save the order') }}</span>
                            <button id="button" class="btn btn-secondary"> {{ __('Update Order') }} </button>
                        </div>
                        <table class="table table-striped"
                            id="table_list" data-toggle="table" data-url="{{ route('homepage-sections.show',1) }}"
                            data-click-to-select="true" data-responsive="true" data-side-pagination="server"
                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-sort-name="sort_order" data-sort-order="asc"
                            data-pagination-successively-size="3" data-query-params="queryParams"
                            data-use-row-attr-func="true"
                            data-reorderable-rows="true" data-reorderable-rows-handle=".reorder-rows-handle"
                            data-reorder-rows-on-drag-class="reorder-rows-on-drag-class">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                    <th scope="col" data-field="title" data-sortable="true">{{ __('Title') }}</th>
                                    <th scope="col" data-field="section_type" data-sortable="true" data-formatter="homepageSectionTypeFormatter">{{ __('Section Type') }}</th>
                                    <th scope="col" data-field="sort_order" data-sortable="true" data-align="center" data-width="5%"> {{ __('Order') }}</th>
                                    @if(has_permissions('update', 'homepage-sections'))
                                        <th scope="col" data-field="is_active" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                    @else
                                        <th scope="col" data-field="is_active" data-sortable="false" data-align="center" data-width="5%" data-formatter="statusFormatter"> {{ __('Status') }}</th>
                                    @endif
                                    <th scope="col" data-field="operate" data-sortable="false" data-align="center" data-events="actionEvents"> {{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- EDIT MODEL MODEL -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="FaqEditModal"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="HomepageSectionEditModal">{{ __('Edit Homepage Section') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal edit-form" action="{{ url('homepage-sections') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" id="edit-id" name="edit_id">
                        {{-- Title --}}
                        <div class="col-lg-12 form-group">
                            {{ Form::label('edit-title', __('Title'), ['class' => 'form-label text-center']) }}
                            {{ Form::text('title', '', [ 'class' => 'form-control', 'placeholder' => trans('Title'), 'required' => true, 'id' => 'edit-title']) }}
                        </div>

                        {{-- Section Type --}}
                        <div class="col-lg-12 form-group">
                            {{ Form::label('edit-section_type', __('Section Type'), ['class' => 'form-label text-center']) }}
                            {{ Form::select('section_type', $sectionTypes, '', ['class' => 'form-control form-select', 'placeholder' => trans('Section Type'), 'required' => true, 'id' => 'edit-section_type']) }}
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn_submit">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- EDIT MODEL -->
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.add-homepage-section-button').click(function() {
                var homepageSection = $('.add-homepage-section');
                if(homepageSection.is(':visible')) {
                    homepageSection.hide(500);
                } else {
                    homepageSection.show(500);
                }
            });

            // Make sure to include the reorder-rows extension
            // Initialize the table with reorderable rows
            $('#button').click(function () {
                const updatedRows = $('#table_list').bootstrapTable('getData').map((row, index) => {
                    return {
                        id: row.id,
                        sort_order: index + 1  // Start from 1
                    };
                });

                // Send the updated order to the server
                $.ajax({
                    url: "{{ route('homepage-sections.update-order') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        sections: updatedRows
                    },
                    success: function(response) {
                        console.log('AJAX success', response); // Debug logging
                        if (response.error) {
                            showErrorToast(response.message);
                        } else {
                            showSuccessToast(response.message);
                            $('#table_list').bootstrapTable('refresh');
                        }
                    },
                    error: function(xhr) {
                        console.log('AJAX error', xhr); // Debug logging
                        showErrorToast("Error updating order");
                    }
                });
            })
        });

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
            'click .edit_btn': function(e, value, row, index) {
                $("#edit-id").val(row.id);
                $("#edit-title").val(row.title);
                $("#edit-section_type").val(row.section_type);
            }
        }

        function homepageSectionTypeFormatter(value){
            let AgentsList = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.AGENTS_LIST_SECTION.TITLE')) }}";
            let Articles = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.ARTICLES_SECTION.TITLE')) }}";
            let Categories = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.CATEGORIES_SECTION.TITLE')) }}";
            let Faqs = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.FAQS_SECTION.TITLE')) }}";
            let FeaturedProperties = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROPERTIES_SECTION.TITLE')) }}";
            let FeaturedProjects = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROJECTS_SECTION.TITLE')) }}";
            let MostLikedProperties = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.MOST_LIKED_PROPERTIES_SECTION.TITLE')) }}";
            let MostViewedProperties = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.MOST_VIEWED_PROPERTIES_SECTION.TITLE')) }}";
            let NearbyProperties = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.NEARBY_PROPERTIES_SECTION.TITLE')) }}";
            let Projects = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.PROJECTS_SECTION.TITLE')) }}";
            let PremiumProperties = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.PREMIUM_PROPERTIES_SECTION.TITLE')) }}";
            let UserRecommendations = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.USER_RECOMMENDATIONS_SECTION.TITLE')) }}";
            let PropertiesByCities = "{{ __(config('constants.HOMEPAGE_SECTION_TYPES.PROPERTIES_BY_CITIES_SECTION.TITLE')) }}";
            if(value == 'agents_list_section'){
                return AgentsList;
            }else if(value == 'articles_section'){
                return Articles;
            }else if(value == 'categories_section'){
                return Categories;
            }else if(value == 'faqs_section'){
                return Faqs;
            }else if(value == 'featured_properties_section'){
                return FeaturedProperties;
            }else if(value == 'featured_projects_section'){
                return FeaturedProjects;
            }else if(value == 'most_liked_properties_section'){
                return MostLikedProperties;
            }else if(value == 'most_viewed_properties_section'){
                return MostViewedProperties;
            }else if(value == 'nearby_properties_section'){
                return NearbyProperties;
            }else if(value == 'projects_section'){
                return Projects;
            }else if(value == 'premium_properties_section'){
                return PremiumProperties;
            }else if(value == 'user_recommendations_section'){
                return UserRecommendations;
            }else if(value == 'properties_by_cities_section'){
                return PropertiesByCities;
            }
            return value;
        }

        function statusFormatter(value) {
            if(value == 1) {
                return '<span class="badge bg-success">{{ __("Active") }}</span>';
            } else {
                return '<span class="badge bg-danger">{{ __("Deactive") }}</span>';
            }
        }
    </script>
@endsection
