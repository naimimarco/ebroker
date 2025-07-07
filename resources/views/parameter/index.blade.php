@extends('layouts.main')

@section('title')
    {{ __('Facility') }}
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
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Create Facility') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            {!! Form::open(['url' => route('parameters.store'), 'data-parsley-validate', 'files' => true, 'class' => 'create-form','data-pre-submit-function','data-success-function'=> "formSuccessFunction"]) !!}
                                @csrf

                                <div class="row">
                                    {{-- Facility Name --}}
                                    <div class="col-sm-12 col-md-6 col-lg-3 form-group mandatory">
                                        {{ Form::label('type', __('Facility Name'), ['class' => 'form-label text-center']) }}
                                        {{ Form::text('parameter', '', ['class' => 'form-control', 'placeholder' => trans('Facility Name'), 'data-parsley-required' => 'true']) }}
                                    </div>

                                    {{-- Type --}}
                                    <div class="col-sm-12 col-md-6 col-lg-3 form-group mandatory">
                                        {{ Form::label('type', __('Type'), ['class' => 'form-label text-center']) }}
                                        <select name="options" id="options" class="form-select form-control-sm" data-parsley-required=true>
                                            <option value="">{{ __('Select Type') }}</option>
                                            <option value="textbox">{{ __('Text Box') }}</option>
                                            <option value="textarea">{{ __('Text Area') }}</option>
                                            <option value="dropdown">{{ __('Dropdown') }}</option>
                                            <option value="radiobutton">{{ __('Radio Button') }}</option>
                                            <option value="checkbox">{{ __('Checkbox') }}</option>
                                            <option value="file">{{ __('File') }}</option>
                                            <option value="number">{{ __('Number') }}</option>
                                        </select>
                                    </div>

                                    {{-- Image --}}
                                    <div class="col-sm-12 col-md-6 col-lg-3 form-group mandatory">
                                        {{ Form::label('image', __('Image'), ['class' => ' form-label text-center']) }}
                                        {{ Form::file('image', ['class' => 'form-control', 'data-parsley-required' => 'true', 'accept' => '.svg']) }}
                                        <span class="text-danger text-sm">{{__('Only SVG is allowed')}}</span>
                                    </div>

                                    {{-- Is Required --}}
                                    <div class="col-sm-12 col-md-6 col-lg-3">
                                        {{ Form::label('is_required', __('Is Required ?'), ['class' => 'col-form-label text-center']) }}
                                        <div class="form-check form-switch col-12">
                                            {{ Form::checkbox('is_required', '1', false, ['class' => 'form-check-input', 'id' => 'is-required']) }}
                                        </div>
                                    </div>

                                    {{-- Options --}}
                                    <input type="hidden" name="optionvalues" id="optionvalues">
                                    <div class="row pt-5" id="elements"> </div>

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

    @if (has_permissions('read', 'facility'))
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">

                            <table class="table table-striped"
                                id="table_list" data-toggle="table" data-url="{{ url('parameter-list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                data-responsive="true" data-sort-name="id" data-sort-order="desc"
                                data-pagination-successively-size="3" data-query-params="queryParams">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                        <th scope="col" data-field="name" data-sortable="true">{{ __('Name') }}</th>
                                        <th scope="col" data-field="image" data-sortable="false" data-formatter="imageFormatter">{{ __('Image') }}</th>
                                        <th scope="col" data-field="type_of_parameter"> {{ __('Type') }}</th>
                                        <th scope="col" data-field="is_required" data-formatter="yesNoStatusFormatter"> {{ __('Is Required ?') }}</th>
                                        <th scope="col" data-field="value" data-sortable="true">{{ __('Value') }}</th>
                                        @if (has_permissions('update', 'facility'))
                                            <th scope="col" data-field="operate" data-sortable="false" data-events="parameterEvents">{{ __('Action') }} </th>
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
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">{{ __('Edit Facility') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('parameter-update') }}" class="form-horizontal" enctype="multipart/form-data" method="POST" data-parsley-validate>
                        {{ csrf_field() }}
                        <input type="hidden" id="edit_id" name="edit_id">

                        {{-- Edit Name --}}
                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="form-group mandatory">
                                    <label for="edit-name" class="form-label col-12">{{ __('Name') }}</label>
                                    <input type="text" id="edit-name" class="form-control col-12" placeholder="" name="edit_name" data-parsley-required="true">
                                </div>
                            </div>
                        </div>
                        {{-- Edit Image --}}
                        <div class="row">
                            {{ Form::label('image', __('Image'), ['class' => 'col-sm-12 col-form-label']) }}
                            <div class="col-md-12 col-12">
                                <input accept="image/svg+xml" name='image' type='file' id="edit_image" class="filepond" />
                            </div>
                            <div class="col-md-12 col-12 text-center edit-image-preview-div" style="display: none;">
                                <img id="edit-image-preview" height="100" width="110" />
                            </div>
                        </div>

                        {{-- Is Required --}}
                        <div class="col-12">
                            {{ Form::label('edit-is-required', __('Is Required ?'), ['class' => 'col-form-label text-center']) }}
                            <div class="form-check form-switch col-12">
                                {{ Form::checkbox('edit_is_required', '1', false, ['class' => 'form-check-input', 'id' => 'edit-is-required']) }}
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="edit_btn_submit">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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
        window.parameterEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit_id").val(row.id);
                $("#edit-name").val(row.name);
                $("#edit-image-preview").attr('src', row.image);
                if(row.image != null){
                    $(".edit-image-preview-div").show();
                }else{
                    $(".edit-image-preview-div").hide();
                }
                if(row.is_required){
                    $("#edit-is-required").prop('checked', true);
                }else{
                    $("#edit-is-required").prop('checked', false);
                }
            }
        }
        window.onload = function() {
            $('#add_options').hide();
            $('#edit_opt').hide();

        }


        $('#options').on('change', function() {

            selected_option = $('#options').val();
            if (selected_option == "radiobutton" || selected_option == "dropdown" ||
                selected_option == "checkbox") {
                $('#elements').empty();

                $('#add_options').show();

                $('#elements').append(
                    ' <div class="card" style="width:15rem;" id="op">' +
                    '<div class="row">' +
                    ' <div class="col-6">' +
                    ' <input type="text" class="form-control opt" name="opt[]" data-parsley-pattern="^[^,]*$" data-parsley-required="true">' +
                    '      </div>' +
                    ' <div class="col-1">' +

                    ' <button type="button" class="btn btn-primary me-1 mb-1 mt-0" id="btn1" disabled> x</button>' +
                    '</div>' +
                    ' </div>' +
                    '</div>' +
                    ' <div class="card" style="width: 15rem;" id="op">' +
                    '<div class="row">' +
                    ' <div class="col-6">' +
                    ' <lable class="form-lable" name="">Click to Add More   values </lable>' +
                    '      </div>' +
                    ' <div class="col-1">' +

                    ' <button type="button" class="btn btn-primary me-1 mb-1 mb-0" id="button-addon2"> +</button>' +
                    '</div>' +
                    ' </div>' +
                    '</div>'

                );
                $('#button-addon2').click(function() {
                    console.log("on");

                    newRowAdd =

                        ' <div class="card" style="width:15rem;" id="op">' +
                        '<div class="row">' +
                        ' <div class="col-6">' +
                        ' <input type="text" class="form-control opt" name="opt[]" data-parsley-pattern="^[^,]*$">' +
                        '      </div>' +
                        ' <div class="col-1">' +

                        ' <button type="button" class="btn btn-primary me-1 mb-1 mt-0" id="btn1"> x</button>' +
                        '</div>' +
                        ' </div>' +
                        '</div>';

                    $('#elements').append(

                        newRowAdd

                    );
                });
                $("body").on("click", "#btn1", function() {
                    $(this).parents("#op").remove();
                })

            } else {

                $('#elements').empty();

            }

        });

        sum = [];
        function preSubmitFunction() {
            $('#elements :input').each(function() {
                sum.push($(this).val().trimEnd());
            });
            $('#optionvalues').val(sum);
        }

        function formSuccessFunction(response) {
            if(!response.error){
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }




        function setValue(id) {
            $('#edit_options').val($("#" + id).parents('tr:first').find('td:nth-child(4)').text()).trigger('change');
            if ($('#svg_clr').val() == 1) {
                src = ($("#" + id).parents('tr:first').find('td:nth-child(3)').find($('.svg-img'))).attr('src');
            } else {
                src = ($("#" + id).parents('tr:first').find('td:nth-child(3)').find($('.image-popup-no-margins'))).attr('href');
            }
            $('#blah').attr('src', src);

            // $('#image').attr('src', src);

            if ($('#edit_options').val() == "checkbox" || $('#edit_options').val() == "radiobutton" || $('#edit_options') .val() == "dropdown") {
                val_str = ($("#" + id).parents('tr:first').find('td:nth-child(6)').text());
                arr = val_str.split(",");
                $('#edit_elements').empty();
                $.each(arr, function(key, value) {




                    newRowAdd =

                        ' <div class="card" style="width:15rem;" id="edit_op">' +
                        '<div class="row">' +
                        ' <div class="col-6">' +
                        ' <input type="text" class="form-control opt" name="edit_opt[]" id="first_value" value="' +
                        value +
                        '" data-parsley-required="true">' +
                        '      </div>' +
                        ' <div class="col-1">' +

                        '<button type="button" class="btn btn-primary me-1 mb-1 mt-0 ' + key + '" id="btn2" ' + (
                            key == 0 ?
                            'disabled' : '') + '> x</button>' +
                        '</div>' +
                        ' </div>' +
                        '</div>';

                    $('#edit_elements').append(

                        newRowAdd

                    );
                });
            }
            $('#edit_image').click(function() {

                $('#blah').hide();


            });
            if ($('#first_value').val() == 'null') {
                $('#first_value').val('');
            }
        }
    </script>
@endsection

