@extends('layouts.main')

@section('title')
    {{ __('Add Article') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('article.index') }}" id="subURL">{{ __('View Article') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Add') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-md-7 col-sm-12">
                <div class="card">
                    <div class="card-header add_article_header">
                        {{ __('New Article') }}
                    </div>
                    <hr>
                    {!! Form::open(['route' => 'article.store', 'data-parsley-validate', 'files' => true]) !!}
                    <div class="card-body">
                        <div class="row">
                            {{-- Title --}}
                            <div class="col-sm-12 col-md-12 col-lg-6  form-group mandatory">
                                {{ Form::label('title', __('Title'), ['class' => 'form-label col-12']) }}
                                {{ Form::text('title', '', [ 'class' => 'form-control ', 'placeholder' => 'Title', 'data-parsley-required' => 'true', 'id' => 'title', ]) }}
                            </div>

                            {{-- Slug --}}
                            <div class="col-sm-12 col-md-12 col-lg-6  form-group">
                                {{ Form::label('slug', __('Slug'), ['class' => 'form-label col-12']) }}
                                {{ Form::text('slug', '', [ 'class' => 'form-control ', 'placeholder' => __('Slug'), 'id' => 'slug' ]) }}
                                <small class="text-danger text-sm">{{ __("Only Small English Characters, Numbers And Hypens Allowed") }}</small>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-12 col-12 form-group mandatory">
                                {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                                <select name="category" class="form-select form-control-sm" data-parsley-minSelect='1' required>
                                    <option value="0"> General </option>
                                    @foreach ($category as $row)
                                        <option value="{{ $row->id }}"
                                            data-parametertypes='{{ $row->parameter_types }}'>
                                            {{ $row->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Image --}}
                            <div class="col-md-12 col-sm-12 form-group mandatory">
                                {{ Form::label('image', __('Image'), ['class' => 'col-12 form-label']) }}
                                <input accept="image/jpg,image/png,image/jpeg" name='image' type='file' class="form-control filepond" id="edit_image" required />
                            </div>
                        </div>
                        {{-- Description --}}
                        <div class="row  mt-4">
                            <div class="col-md-12 col-sm-12 form-group mandatory">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label col-12']) }}
                                {{ Form::textarea('description', '', [
                                    'class' => 'form-control ',
                                    'id' => 'tinymce_editor',
                                    'data-parsley-required' => 'true',
                                ]) }}
                            </div>
                        </div>
                        {{-- Meta Title --}}
                        <div class="col-md-12 col-sm-12 form-group">
                            {{ Form::label('title', __('Meta Title'), ['class' => 'form-label text-center']) }}
                            <input type="text" name="meta_title" class="form-control" id="meta_title" oninput="getWordCount('meta_title','meta_title_count','19.9px arial')" placeholder="{{ __('Meta title') }}">
                            <h6 id="meta_title_count">0</h6>
                        </div>
                        {{-- Meta Keywords --}}
                        <div class="col-md-12 col-sm-12 form-group">
                            {{ Form::label('title', __('Meta Keywords'), ['class' => 'form-label text-center']) }}
                            <input type="text" name="meta_keywords" class="form-control" id="meta_keywords" placeolder="{{ __('Meta Keywords') }}">
                        </div>
                        {{-- Meta Description --}}
                        <div class="col-md-12 col-sm-12 form-group">
                            {{ Form::label('description', __('Meta Description'), ['class' => 'form-label text-center']) }}
                            <textarea id="meta_description" name="meta_description" class="form-control" oninput="getWordCount('meta_description','meta_description_count','12.9px arial')"></textarea>
                            <h6 id="meta_description_count">0</h6>
                        </div>
                        {{-- Save Button --}}
                        <div class="card-footer">
                            <div class="col-12 d-flex justify-content-end">
                                {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1']) }}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-sm-12">

                <div class="card">
                    <div class="card-header add_article_header">
                        {{ __('Recent Articles') }}
                    </div>
                    <hr>

                    <div class="card-body">

                        <div class="row">

                            @foreach ($recent_articles as $row)
                                <div class="col-md-12 d-flex recent_articles">

                                    {{-- Article Image --}}
                                    <img class="article_img" src="{{ $row->image != '' ? $row->image : url('assets/images/bg/Login_BG.jpg') }}" alt="">

                                    {{-- Article Details --}}
                                    <div class="article_details">

                                        {{-- Article Category --}}
                                        <div class="article_category">
                                            {{ $row->category ? $row->category->category : 'General' }}
                                        </div>

                                        {{-- Article Title --}}
                                        <div class="article_title">
                                            {{ $row->title }}
                                        </div>

                                        {{-- Article Description --}}
                                        <div class="article_description">
                                            @php
                                                echo Str::substr(strip_tags($row->description), 0, 180) . '...';
                                            @endphp
                                        </div>

                                        {{-- Article Date --}}
                                        <div class="article_date">
                                            {{ date('d M Y', strtotime($row->created_at)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            getWordCount("meta_title", "meta_title_count", "19.9px arial");
            getWordCount("meta_description", "meta_description_count", "12.9px arial");
        });

        $("#title").on('keyup',function(e){
            let title = $(this).val();
            let slugElement = $("#slug");
            if(title){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('article.generate-slug') }}",
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        title: title
                    },
                    beforeSend: function() {
                        slugElement.attr('readonly', true).val('Please wait....')
                    },
                    success: function(response) {
                        if(!response.error){
                            if(response.data){
                                slugElement.removeAttr('readonly').val(response.data);
                            }else{
                                slugElement.removeAttr('readonly').val("")
                            }
                        }
                    }
                });
            }else{
                slugElement.removeAttr('readonly', true).val("")
            }
        });
    </script>
@endsection
