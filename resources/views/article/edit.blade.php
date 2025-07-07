@extends('layouts.main')

@section('title')
    {{ __('Update Article') }}
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
                            {{ __('Update') }}
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
                <div class="card article_form">
                    <div class="card-header add_article_header">
                        {{ __('Update Article') }}
                    </div>
                    <hr>
                    {!! Form::open([ 'route' => ['article.update', $id], 'data-parsley-validate', 'files' => true, 'method' => 'PATCH', ]) !!}
                    <div class="card-body">
                        <div class="row">

                            {{-- Title --}}
                            <div class="col-sm-12 col-md-12 col-lg-6  form-group mandatory">
                                {{ Form::label('title', __('Title'), ['class' => 'form-label col-12']) }}
                                {{ Form::text('title', $list->title, [ 'class' => 'form-control ', 'placeholder' => trans('Title'), 'data-parsley-required' => 'true', 'id' => 'title', ]) }}
                            </div>

                            {{-- Slug --}}
                            <div class="col-sm-12 col-md-12 col-lg-6  form-group">
                                {{ Form::label('slug', __('Slug'), ['class' => 'form-label col-12']) }}
                                {{ Form::text('slug', $list->slug_id, [ 'class' => 'form-control ', 'placeholder' => __('Slug'), 'id' => 'slug' ]) }}
                                <small class="text-danger text-sm">{{ __("Only Small English Characters, Numbers And Hypens Allowed") }}</small>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-12 col-12 form-group mandatory">
                                {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                                <select name="category" class="form-select form-control-sm" data-parsley-minSelect='1' required>
                                    <option value="0"> General </option>
                                    @foreach ($category as $row)
                                        <option value="{{ $row->id }}" data-parametertypes='{{ $row->parameter_types }}' {{ $row->id == $list->category_id ? 'selected' : '' }}>
                                            {{ $row->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Image --}}
                            <div class="col-md-12 col-sm-12 form-group mandatory">
                                {{ Form::label('image', __('Image'), ['class' => 'col-12 form-label']) }}
                                <input accept="image/*" name='image' type='file' class="filepond" id="edit_image" />
                                <div class="edit_article_img">
                                    <img src="{{ $list->image }}" alt="" class="edit_img" height="300px" width="500px">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="col-md-12 col-sm-12 form-group mandatory">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label col-12']) }}
                                {{ Form::textarea('description', $list->description, [ 'class' => 'form-control ', 'id' => 'tinymce_editor', 'data-parsley-required' => 'true', ]) }}
                            </div>

                            {{-- Meta title --}}
                            <div class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}"> {{ Form::label('title', __('Meta Title'), ['class' => 'form-label text-center']) }}
                                <input type="text" name="edit_meta_title" class="form-control" id="edit_meta_title" oninput="getWordCount('edit_meta_title','edit_meta_title_count','19.9px arial')" placeholder="{{ __('Meta Title') }}" value=" {{ $list->meta_title }}" {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>
                                <h6 id="edit_meta_title_count">0</h6>
                            </div>

                            {{-- Meta Keywords --}}
                            <div class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}">
                                {{ Form::label('title', __('Meta Keywords'), ['class' => 'form-label text-center']) }}
                                <input type="text" name="meta_keywords" class="form-control" id="meta_keywords" placeholder="{{ __('Meta Keywords') }}" value=" {{ $list->meta_keywords }}" {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>
                            </div>

                            {{-- Meta Description --}}
                            <div class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}">
                                {{ Form::label('description', __('Meta Description'), ['class' => 'form-label text-center']) }}
                                <textarea id="edit_meta_description" name="edit_meta_description" class="form-control" oninput="getWordCount('edit_meta_description','edit_meta_description_count','12.9px arial')" {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>{{ $list->meta_description }}</textarea>
                                <h6 id="edit_meta_description_count">0</h6>
                            </div>
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

                <div class="card edit_recent_articles">
                    <div class="card-header add_article_header">
                        {{ __('Recent Articles') }}
                    </div>
                    <hr>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($recent_articles as $row)
                                <div class="col-md-12 d-flex recent_articles">
                                    <img class="article_img" src="{{ $row->image != '' ? $row->image : url('assets/images/bg/Login_BG.jpg') }}" alt="">
                                    <div class="article_details">
                                        <div class="article_category">
                                            {{ $row->category ? $row->category->category : 'General' }}
                                        </div>
                                        <div class="article_title">
                                            {{ $row->title }}
                                        </div>
                                        <div class="article_description">
                                            @php
                                                echo Str::substr(strip_tags($row->description), 0, 180) . '...';
                                            @endphp
                                        </div>
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
            getWordCount("edit_meta_title", "edit_meta_title_count", "19.9px arial");
            getWordCount(
                "edit_meta_description",
                "edit_meta_description_count",
                "12.9px arial"
            );
            $('#edit_image').on('click', function() {
                $('.edit_img').hide();
            });
        });


        $("#title").on('keyup',function(e){
            let title = $(this).val();
            let slugElement = $("#slug");
            let id = "{{ $id }}";
            if(title){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('article.generate-slug') }}",
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        title: title,
                        id: id
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
