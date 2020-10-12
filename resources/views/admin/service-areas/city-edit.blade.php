@extends('admin.layouts.admin-app')

@section('css')
<script src="{{ asset('lib/datatables/jquery.dataTables.css') }}"></script>
@endsection
@section('js')
<script>
    $(function(){
        'use strict'

        $('.form-layout .form-control').on('focusin', function(){
          $(this).closest('.form-group').addClass('form-group-active');
        });

        $('.form-layout .form-control').on('focusout', function(){
          $(this).closest('.form-group').removeClass('form-group-active');
        });

        // Select2
        $('#select2-a, #select2-b').select2({
          minimumResultsForSearch: Infinity
        });

        $('#select2-a').on('select2:opening', function (e) {
          $(this).closest('.form-group').addClass('form-group-active');
        });

        $('#select2-a').on('select2:closing', function (e) {
          $(this).closest('.form-group').removeClass('form-group-active');
        });

        getlgas() {
            var state = $("#state option:selected").value();
        }


      });
</script>
@endsection
@section('content')
<!-- ########## START: MAIN PANEL ########## -->
<div class="br-mainpanel">

    <div class="pd-x-20 pd-sm-x-30 pd-t-20 pd-sm-t-30" style="position:center; ">
        <h4 class="tx-gray-800 mg-b-5">Update Country</h4>
    </div>

    <div class="br-pagebody">
        <div class="br-section-wrapper">

            <div class="row mg-t-10">
                <div class="col-xl-12">
                    <form action="{{ route('admin.cities.update', $city) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="form-layout form-layout-4">
                            <h6 class="tx-gray-800 tx-uppercase tx-bold tx-14 mg-b-10">Select Country</h6>
                            <div class="row">
                                <label class="col-sm-4 form-control-label">Select Country* </label>
                                <div class="col-sm-8 mg-t-10 mg-b-10">
                                    <select class="form-control select2" data-placeholder="Select Country" name="country_id">
                                        <option label="Select Country"></option>
                                        @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @if($city->lga->state->country_id == $country->id) selected @endif>{{ $country->name }}</option>
                                        @endforeach
                                    </select> </div>
                            </div><!-- col-4 -->
                            <h6 class="tx-gray-800 tx-uppercase tx-bold tx-14 mg-b-10"></h6>
                            <div class="row">
                                <label class="col-sm-4 form-control-label">Select State* </label>
                                <div class="col-sm-8 mg-t-10 mg-b-10">
                                    <select class="form-control select2" data-placeholder="Select State" name="state_id" id="state">
                                        <option label="Choose State"></option>
                                        @foreach ($states as $state)
                                        <option value="{{ $state->id }}" @if($city->lga->state_id == $state->id) selected @endif>{{ $state->name }}</option>
                                        @endforeach
                                    </select> </div>
                            </div><!-- col-4 -->
                            <h6 class="tx-gray-800 tx-uppercase tx-bold tx-14 mg-b-10"></h6>
                            <div class="row">
                                <label class="col-sm-4 form-control-label">Select Lga* </label>
                                <div class="col-sm-8 mg-t-10 mg-b-10">
                                    <select class="form-control select2" data-placeholder="Select Lga" name="lga_id">
                                        <option label="Choose Lga"></option>
                                        @foreach ($lgas as $lga)
                                        <option value="{{ $lga->id }}" @if($city->lga_id == $lga->id) selected @endif>{{ $lga->name }}</option>
                                        @endforeach
                                    </select> </div>
                            </div><!-- col-4 -->
                            <h6 class="tx-gray-800 tx-uppercase tx-bold tx-14 mg-b-10"></h6>
                            <div class="row">
                                <label class="col-sm-4 form-control-label">Enter Cities: <span class="tx-danger">*</span></label>
                                <div class="col-sm-8 mg-t-10 mg-sm-t-0">
                                    <input type="text" class="form-control" placeholder="Enter Cities" name="name" value="{{ $city->name }}">
                                </div>
                            </div><!-- row -->



                            <div class="form-layout-footer mg-t-30">
                                <button class="btn btn-info" type="submit">Submit </button>
                                <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">Cancel</a>
                            </div><!-- form-layout-footer -->
                        </div><!-- form-layout -->
                    </form>
                </div><!-- col-6 -->

            </div>
        </div>

    </div><!-- br-mainpanel -->
    @endsection