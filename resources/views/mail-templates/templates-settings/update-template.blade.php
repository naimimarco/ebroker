@extends('layouts.main')

@section('title')
    {{ __('Email Templates') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="text-end m-2">
            <a href="{{ route('email-templates.index') }}" class="btn btn-primary">{{ __('Back') }}</a>
        </div>
        <div class="page-header">
            <h3 class="page-title">
                {{ __('Email Templates') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <form class="create-form" action="{{ route('email-templates.store') }}" method="POST" data-success-function="formSuccessFunction">
                        <div class="card-body">
                            @csrf
                            {!! Form::hidden('type', $data['type']) !!}
                            <div class="form-group">
                                <label>{{ trans($data['title']) }} <span class="text-danger">*</span></label>
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea class="tinymce_editor" name="data" class="form-control email-template col-md-7 col-xs-12">{{ $data['template'] }}</textarea>
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    @foreach ($data['required_fields'] as $field)
                                        @if ($field['is_condition'])
                                            <a data-value="{{ "{".$field['name']."}" }}|{{ "{end_".$field['name']."}" }}" class="btn btn-light btn_tag mt-2" data-is-condition="true">{{ "{".$field['name']."}" }}</a>
                                        @else
                                            <a data-value="{{ "{".$field['name']."}" }}" class="btn btn-light btn_tag mt-2" data-is-condition="false">{{ "{".$field['name']."}" }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 mt-2 d-flex justify-content-end">
                                <button class="btn btn-primary me-1 mb-1" type="submit" name="submit">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
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

        $('.btn_tag').click(function (e) {
            e.preventDefault();
            var value = `<b>${$(this).data('value')}</b>`;
            var isCondition = $(this).data('is-condition');

            if (tinymce.activeEditor) { // Check if editor is active
                if (isCondition) {
                    var values = value.split('|');

                    var combinedValue = values[0] + '<span id="cursorMarker"></span>' + values[1];

                    tinymce.activeEditor.insertContent(combinedValue);

                    // Move the cursor to the position between the split values
                    var editor = tinymce.activeEditor;
                    var marker = editor.getBody().querySelector('#cursorMarker');
                    if (marker) {
                        var range = document.createRange();
                        var selection = editor.selection;

                        range.setStartAfter(marker);
                        range.setEndAfter(marker);

                        selection.setRng(range);
                        marker.remove();
                    }
                } else {
                    tinymce.activeEditor.insertContent(value);
                }
            } else {
                alert('TinyMCE editor not active');
            }
        });



        function formSuccessFunction(response) {
            if(!response.error){
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }
        // Load
        window.onload = setTimeout(() => {
            $('.email-template').trigger('change');
        }, 500);
    </script>
@endsection
