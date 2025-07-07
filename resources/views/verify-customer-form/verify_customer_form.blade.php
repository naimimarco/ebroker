@extends('layouts.main')

@section('title')
    {{ __('Verify Agent Form') }}
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
    @if (has_permissions('create', 'verify_customer_form'))
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="divider">
                        <div class="divider-text">
                            <h4>{{ __('Create Form Field') }}</h4>
                        </div>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                {!! Form::open(['url' => route('verify-customer-form.store'), 'data-parsley-validate', 'files' => true, 'class' => 'create-form','data-pre-submit-function','data-success-function'=> "formSuccessFunction"]) !!}
                                    @csrf

                                    <div class="row">
                                        {{-- Name --}}
                                        <div class="col-sm-12 col-md-6 form-group mandatory">
                                            {{ Form::label('type', __('Name'), ['class' => 'form-label text-center']) }}
                                            {{ Form::text('name', '', ['class' => 'form-control', 'placeholder' => trans('Name'), 'data-parsley-required' => 'true']) }}
                                        </div>

                                        {{-- Field Type --}}
                                        <div class="col-sm-12 col-md-6 form-group mandatory">
                                            {{ Form::label('field-type', __('Field Type'), ['class' => 'form-label text-center']) }}
                                            <select name="field_type" id="type-field" class="form-select form-control-sm type-field" data-parsley-required=true>
                                                <option value="">{{ __('Select Type') }}</option>
                                                <option value="text">{{ __('Text Box') }}</option>
                                                <option value="number">{{ __('Number') }}</option>
                                                <option value="radio">{{ __('Radio Button') }}</option>
                                                <option value="checkbox">{{ __('Checkbox') }}</option>
                                                <option value="dropdown">{{ __('Dropdown') }}</option>
                                                <option value="textarea">{{ __('Text Area') }}</option>
                                                <option value="file">{{ __('File') }}</option>
                                            </select>
                                        </div>

                                        {{-- Option Section --}}
                                        <div class="default-values-section" style="display: none">
                                            <div class="mt-4" data-repeater-list="option_data">
                                                <div class="col-md-5 pl-0 mb-4">
                                                    <button type="button" class="btn btn-success add-new-option" data-repeater-create title="Add new row">
                                                        <span><i class="fa fa-plus"></i> {{__('Add New Option')}}</span>
                                                    </button>
                                                </div>
                                                <div class="row option-section" data-repeater-item>
                                                    <div class="form-group col-md-5">
                                                        <label>{{ __('Option') }} - <span class="option-number">1</span> <span class="text-danger">*</span></label>
                                                        <input type="text" name="option" placeholder="{{__('Text')}}" class="form-control" required>
                                                    </div>
                                                    <div class="form-group col-md-1 pl-0 mt-4">
                                                        <button data-repeater-delete type="button" class="btn btn-icon btn-danger remove-default-option" title="{{__('Remove Option')}}" disabled>
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Save --}}
                                        <div class="col-12  d-flex justify-content-end pt-3">
                                            {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1', 'id' => 'btn_submit']) }}
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (has_permissions('read', 'verify_customer_form'))
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">

                            <table class="table table-striped"
                                id="table_list" data-toggle="table" data-url="{{ route('verify-customer-form.show') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                data-responsive="true" data-sort-name="id" data-sort-order="desc"
                                data-pagination-successively-size="3" data-query-params="queryParams">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                        <th scope="col" data-field="name" data-sortable="true">{{ __('Name') }}</th>
                                        <th scope="col" data-field="field_type" data-sortable="true" data-formatter="fieldTypeFormatter">{{ __('Field Type') }}</th>
                                        <th scope="col" data-field="form_fields_values" data-formatter="fieldValuesFormatter">{{ __('Field Values') }}</th>
                                        <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
                                        @if (has_permissions('update', 'verify_customer_form'))
                                            <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">{{ __('Action') }} </th>
                                        @endif
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif


    <!-- EDIT MODEL MODEL -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="verifyCustomerFormEditModal"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="verifyCustomerFormEditModal">{{ __('Edit Form Field') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal create-form" action="{{ route('verify-customer-form.update') }}" enctype="multipart/form-data" data-parsley-validate data-success-function="editFormSuccessFunction">
                        {{ csrf_field() }}
                        <input type="hidden" id="edit-id" name="id">
                        {{-- Name --}}
                        <div class="col-12 form-group mandatory">
                            {{ Form::label('type', __('Name'), ['class' => 'form-label text-center']) }}
                            {{ Form::text('name', '', ['class' => 'form-control', 'id' => 'edit-name','placeholder' => trans('Name'), 'data-parsley-required' => 'true']) }}
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
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }
        function formSuccessFunction () {
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit-id").val(row.id);
                $("#edit-name").val(row.name);
            }
        }

        function editFormSuccessFunction(){
            setTimeout(() => {
                $('#editModal').modal('hide');
            }, 1000);
        }
    </script>
@endsection
