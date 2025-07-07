@extends('layouts.main')

@section('title')
    {{ __('Calculator') }}
@endsection

@section('page-title')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="divider">
                    <div class="divider-text">
                        <h4>{{ __('Calculator') }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            {!! Form::open(['files' => true]) !!}
                            <div class="form-group row">
                                {{ Form::label('from_options', trans('Type'), ['class' => 'col-sm-2 col-form-label text-center']) }}
                                <div class="col-sm-4">
                                    <select name="from_options" id="from_options" class="form-select form-control-sm">
                                        <option value=""> {{ __('Select Option') }} </option>
                                        <option value="Square Feet">{{ __('Square Feet') }}</option>
                                        <option value="Square Meter">{{ __('Square Meter') }}</option>
                                        <option value="Acre">{{ __('Acre') }}</option>
                                        <option value="Hectare">{{ __('Hectare') }}</option>
                                        <option value="Gaj">{{ __('Gaj') }}</option>
                                        <option value="Bigha">{{ __('Bigha') }}</option>
                                        <option value="Cent">{{ __('Cent') }}</option>
                                        <option value="Katha">{{ __('Katha') }}</option>
                                        <option value="Guntha">{{ __('Guntha') }}</option>
                                    </select>

                                </div>

                                {{ Form::label('num_of_unit', trans('Number Of Unit'), ['class' => 'col-sm-2 col-form-label text-center']) }}
                                <div class="col-sm-4">
                                    {{ Form::number('NumberOfUnits', '', ['class' => 'form-control', 'placeholder' => trans('Number Of Unit'), 'id' => 'num_of_unit', 'required' => true]) }}
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                {{ Form::label('to_options', trans('Type'), ['class' => 'col-sm-2 col-form-label text-center']) }}
                                <div class="col-sm-4">
                                    <select name="to_options" id="to_options" class="form-select form-control-sm">
                                        <option value=""> {{ __('Select Option') }} </option>
                                        <option value="Square Feet">{{ __('Square Feet') }}</option>
                                        <option value="Square Meter">{{ __('Square Meter') }}</option>
                                        <option value="Acre">{{ __('Acre') }}</option>
                                        <option value="Hectare">{{ __('Hectare') }}</option>
                                        <option value="Gaj">{{ __('Gaj') }}</option>
                                        <option value="Bigha">{{ __('Bigha') }}</option>
                                        <option value="Cent">{{ __('Cent') }}</option>
                                        <option value="Katha">{{ __('Katha') }}</option>
                                        <option value="Guntha">{{ __('Guntha') }}</option>
                                    </select>
                                </div>

                                {{ Form::label('converted_figure', trans('Converted Figure'), ['class' => 'col-sm-2 col-form-label text-center']) }}
                                <div class="col-sm-4">
                                    {{ Form::text('Converted Figure', '', ['class' => 'form-control', 'placeholder' => trans('Converted Figure'), 'id' => 'converted_figure', 'readonly' => true]) }}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>



    </section>
@section('script')
    <script>
        $('#to_options,#num_of_unit').on('change', function() {

            converted_from = $('#from_options').val();
            convert_to = $('#to_options').val();
            no_of_unit = $('#num_of_unit').val();
            converted_figure = $('#converted_figure');




            //Square Feet <----------->Square Meter

            if (converted_from == "Square Feet" && convert_to == "Square Meter") {
                ans = (no_of_unit * 0.092903);
                converted_figure.val(ans);
            }
            if (converted_from == "Square Meter" && convert_to == "Square Feet") {
                ans = (no_of_unit * 10.763915);
                converted_figure.val(ans);
            }

            //Square Feet <----------->Acre
            if (converted_from == "Square Feet" && convert_to == "Acre") {
                ans = (no_of_unit * 0.00002295);
                converted_figure.val(ans);
            }
            if (converted_from == "Acre" && convert_to == "Square Feet") {
                ans = (no_of_unit * 43560.057264);
                converted_figure.val(ans);
            }

            //Square Feet <----------->Hectare

            if (converted_from == "Square Feet" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.000009);
                converted_figure.val(ans);
            }
            if (converted_from == "Hectare" && convert_to == "Square Feet") {
                ans = (no_of_unit * 107639.150512);
                converted_figure.val(ans);
            }
            //Square Feet <----------->Gaj

            if (converted_from == "Square Feet" && convert_to == "Gaj") {
                ans = (no_of_unit * 0.112188);
                converted_figure.val(ans);
            }
            if (converted_from == "Gaj" && convert_to == "Square Feet") {
                ans = (no_of_unit * 8.913598);
                converted_figure.val(ans);
            }

            //Square Feet <----------->Bigha

            if (converted_from == "Square Feet" && convert_to == "Bigha") {
                ans = (no_of_unit * 0.000037);
                converted_figure.val(ans);
            }
            if (converted_from == "Bigha" && convert_to == "Square Feet") {
                ans = (no_of_unit * 27000.010764);
                converted_figure.val(ans);
            }
            //Square Feet <----------->Cent

            if (converted_from == "Square Feet" && convert_to == "Cent") {
                ans = (no_of_unit * 0.002296);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Square Feet") {
                ans = (no_of_unit * 435.508003);
                converted_figure.val(ans);
            }
            //Square Feet <----------->Katha

            if (converted_from == "Square Feet" && convert_to == "Katha") {
                ans = (no_of_unit * 0.000735);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Square Feet") {
                ans = (no_of_unit * 1361.000614);
                converted_figure.val(ans);
            }
            //Square Feet <----------->Guntha

            if (converted_from == "Square Feet" && convert_to == "Guntha") {
                ans = (no_of_unit * 0.0009182);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Square Feet") {
                ans = (no_of_unit * 1089.000463);
                converted_figure.val(ans);
            }


            //Square Meter <----------->Acre

            if (converted_from == "Square Meter" && convert_to == "Acre") {
                ans = (no_of_unit * 0.00024677419354838707);
                converted_figure.val(ans);
            }
            if (converted_from == "Acre" && convert_to == "Square Meter") {
                ans = (no_of_unit * 4046.860000);
                converted_figure.val(ans);
            }

            //Square Meter <----------->Hectare

            if (converted_from == "Square Meter" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.000100);
                converted_figure.val(ans);
            }
            if (converted_from == "Hectare" && convert_to == "Square Meter") {
                ans = (no_of_unit * 10000.000000);
                converted_figure.val(ans);
            }
            //Square Meter <----------->gaj

            if (converted_from == "Square Meter" && convert_to == "gaj") {
                ans = (no_of_unit * 1.207584);
                converted_figure.val(ans);
            }
            if (converted_from == "gaj" && convert_to == "Square Meter") {
                ans = (no_of_unit * 0.828100);
                converted_figure.val(ans);
            }
            //Square Meter <----------->Bigha

            if (converted_from == "Square Meter" && convert_to == "Bigha") {
                ans = (no_of_unit * 0.000399);
                converted_figure.val(ans);
            }
            if (converted_from == "Bigha" && convert_to == "Square Meter") {
                ans = (no_of_unit * 2508.382000);
                converted_figure.val(ans);
            }

            //Square Meter <----------->Cent

            if (converted_from == "Square Meter" && convert_to == "Cent") {
                ans = (no_of_unit * 0.024688172043010752);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Square Meter") {
                ans = (no_of_unit * 40.460000);
                converted_figure.val(ans);
            }

            //Square Meter <----------->Katha

            if (converted_from == "Square Meter" && convert_to == "Katha") {
                ans = (no_of_unit * 0.007909);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Square Meter") {
                ans = (no_of_unit * 126.441040);
                converted_figure.val(ans);
            }

            //Square Meter <----------->Guntha

            if (converted_from == "Square Meter" && convert_to == "Guntha") {
                ans = (no_of_unit * 0.009884);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Square Meter") {
                ans = (no_of_unit * 101.171410);
                converted_figure.val(ans);
            }


            //Acre <----------->Hectare

            if (converted_from == "Acre" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.404686);
                converted_figure.val(ans);
            }
            if (converted_from == "Hectare" && convert_to == "Acre") {
                ans = (no_of_unit * 2.4710538146717);
                converted_figure.val(ans);
            }


            //Acre <----------->gaj

            if (converted_from == "Acre" && convert_to == "gaj") {
                ans = (no_of_unit * 4886.921869);
                converted_figure.val(ans);
            }
            if (converted_from == "gaj" && convert_to == "Acre") {
                ans = (no_of_unit * 0.000205);
                converted_figure.val(ans);
            }

            //Acre <----------->Bigha

            if (converted_from == "Acre" && convert_to == "Bigha") {
                ans = (no_of_unit * 1.613335);
                converted_figure.val(ans);
            }
            if (converted_from == "Bigha" && convert_to == "Acre") {
                ans = (no_of_unit * 0.619834);
                converted_figure.val(ans);
            }

            //Acre <----------->Cent

            if (converted_from == "Acre" && convert_to == "Cent") {
                ans = (no_of_unit * 100.021256);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Acre") {
                ans = (no_of_unit * 0.009998);
                converted_figure.val(ans);
            }
            //Acre <----------->Katha

            if (converted_from == "Acre" && convert_to == "Katha") {
                ans = (no_of_unit * 32.005906);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Acre") {
                ans = (no_of_unit * 0.031244);
                converted_figure.val(ans);
            }
            //Acre <----------->Guntha

            if (converted_from == "Acre" && convert_to == "Guntha") {
                ans = (no_of_unit * 40.000036);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Acre") {
                ans = (no_of_unit * 0.025000);
                converted_figure.val(ans);
            }
            //Hectare <----------->gaj

            if (converted_from == "Hectare" && convert_to == "gaj") {
                ans = (no_of_unit * 12075.836252);
                converted_figure.val(ans);
            }
            if (converted_from == "gaj" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.000083);
                converted_figure.val(ans);
            }

            //Hectare <----------->Bigha

            if (converted_from == "Hectare" && convert_to == "Bigha") {
                ans = (no_of_unit * 3.986634);
                converted_figure.val(ans);
            }
            if (converted_from == "Bigha" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.250838);
                converted_figure.val(ans);
            }
            //Hectare <----------->Cent

            if (converted_from == "Hectare" && convert_to == "Cent") {
                ans = (no_of_unit * 247.157687);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.004046);
                converted_figure.val(ans);
            }


            //Hectare <----------->Katha

            if (converted_from == "Hectare" && convert_to == "Katha") {
                ans = (no_of_unit * 79.088245);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.012644);
                converted_figure.val(ans);
            }

            //Hectare <----------->Guntha

            if (converted_from == "Hectare" && convert_to == "Guntha") {
                ans = (no_of_unit * 98.842153);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Hectare") {
                ans = (no_of_unit * 0.010117);
                converted_figure.val(ans);
            }


            //Gaj <----------->Bigha

            if (converted_from == "Gaj" && convert_to == "Bigha") {
                ans = (no_of_unit * 0.000330);
                converted_figure.val(ans);
            }
            if (converted_from == "Bigha" && convert_to == "Gaj") {
                ans = (no_of_unit * 3029.081029);
                converted_figure.val(ans);
            }


            //Gaj <----------->Cent

            if (converted_from == "Gaj" && convert_to == "Cent") {
                ans = (no_of_unit * 0.020467);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Gaj") {
                ans = (no_of_unit * 48.858833);
                converted_figure.val(ans);
            }

            //Gaj <----------->Katha

            if (converted_from == "Gaj" && convert_to == "Katha") {
                ans = (no_of_unit * 0.006549);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Gaj") {
                ans = (no_of_unit * 152.688129);
                converted_figure.val(ans);
            }


            //Gaj <----------->Guntha

            if (converted_from == "Gaj" && convert_to == "Guntha") {
                ans = (no_of_unit * 0.008185);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Gaj") {
                ans = (no_of_unit * 122.172938);
                converted_figure.val(ans);
            }

            //Bigha <----------->Cent

            if (converted_from == "Bigha" && convert_to == "Cent") {
                ans = (no_of_unit * 61.996589);
                converted_figure.val(ans);
            }
            if (converted_from == "Cent" && convert_to == "Bigha") {
                ans = (no_of_unit * 0.016130);
                converted_figure.val(ans);
            }
            //Bigha <----------->Katha

            if (converted_from == "Bigha" && convert_to == "Katha") {
                ans = (no_of_unit * 19.838353);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Bigha") {
                ans = (no_of_unit * 0.050407);
                converted_figure.val(ans);
            }
            //Cent <----------->Katha

            if (converted_from == "Cent" && convert_to == "Katha") {
                ans = (no_of_unit * 0.319991);
                converted_figure.val(ans);
            }
            if (converted_from == "Katha" && convert_to == "Cent") {
                ans = (no_of_unit * 3.125087);
                converted_figure.val(ans);
            }
            //Cent <----------->Guntha

            if (converted_from == "Cent" && convert_to == "Guntha") {
                ans = (no_of_unit * 0.399915);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Cent") {
                ans = (no_of_unit * 2.500529);
                converted_figure.val(ans);
            }
            //Katha <----------->Guntha

            if (converted_from == "Katha" && convert_to == "Guntha") {
                ans = (no_of_unit * 1.249770);
                converted_figure.val(ans);
            }
            if (converted_from == "Guntha" && convert_to == "Katha") {
                ans = (no_of_unit * 0.800147);
                converted_figure.val(ans);
            }

        });
    </script>
@endsection
@endsection
