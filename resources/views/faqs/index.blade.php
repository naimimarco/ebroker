@extends('layouts.main')

@section('title')
    {{ __('FAQ') }}
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
        {{-- Create FAQ Section --}}
        <div class="card add-category mt-3">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Create FAQ') }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        {!! Form::open(['url' => route('faqs.store'), 'data-parsley-validate', 'class' => 'create-form']) !!}
                        <div class=" row">

                            {{-- Question --}}
                            <div class="col-lg-12 col-xl-6 form-group mandatory">
                                {{ Form::label('question', __('Question'), ['class' => 'form-label text-center']) }}
                                {{ Form::textarea('question', '', [ 'class' => 'form-control', 'placeholder' => trans('Question'), 'data-parsley-required' => 'true', 'id' => 'question', 'rows' => 2]) }}
                            </div>

                            {{-- Answer --}}
                            <div class="col-lg-12 col-xl-6 form-group mandatory">
                                {{ Form::label('answer', __('Answer'), ['class' => 'form-label text-center']) }}
                                {{ Form::textarea('answer', '', [ 'class' => 'form-control', 'placeholder' => trans('Answer'), 'data-parsley-required' => 'true', 'id' => 'answer', 'rows' => 2]) }}
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
                        <table class="table table-striped"
                            id="table_list" data-toggle="table" data-url="{{ route('faqs.show',1) }}"
                            data-click-to-select="true" data-responsive="true" data-side-pagination="server"
                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                    <th scope="col" data-field="question" data-sortable="true">{{ __('Question') }}</th>
                                    <th scope="col" data-field="answer" data-sortable="true">{{ __('Answer') }}</th>
                                    <th scope="col" data-field="status" data-sortable="false" data-align="center" data-width="5%" data-formatter="enableDisableSwitchFormatter"> {{ __('Enable/Disable') }}</th>
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
                    <h6 class="modal-title" id="FaqEditModal">{{ __('Edit FAQ') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal edit-form" action="{{ url('faqs') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" id="edit-id" name="edit_id">
                        {{-- Question --}}
                        <div class="col-lg-12 form-group">
                            {{ Form::label('edit-question', __('Question'), ['class' => 'form-label text-center']) }}
                            {{ Form::textarea('edit_question', '', [ 'class' => 'form-control', 'placeholder' => trans('Question'), 'required' => true, 'id' => 'edit-question', 'rows' => 2]) }}
                        </div>

                        {{-- Answer --}}
                        <div class="col-lg-12 form-group">
                            {{ Form::label('edit-answer', __('Answer'), ['class' => 'form-label text-center']) }}
                            {{ Form::textarea('edit_answer', '', [ 'class' => 'form-control', 'placeholder' => trans('Answer'), 'required' => true, 'id' => 'edit-answer', 'rows' => 2]) }}
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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.6.6/dragula.min.js"
        integrity="sha512-MrA7WH8h42LMq8GWxQGmWjrtalBjrfIzCQ+i2EZA26cZ7OBiBd/Uct5S3NP9IBqKx5b+MMNH1PhzTsk6J9nPQQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src=https://bevacqua.github.io/dragula/dist/dragula.js></script>
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

        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {
                $("#edit-id").val(row.id);
                $("#edit-question").val(row.question);
                $("#edit-answer").val(row.answer);
            }
        }


    </script>
@endsection
