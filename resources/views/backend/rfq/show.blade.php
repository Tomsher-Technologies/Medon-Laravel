@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6"></h5>
                </div>

                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">Name</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="Name" id="name" name="name" class="form-control"
                                value="{{ $rfq->name }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{ translate('Email') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ translate('Email') }}" id="email" name="email"
                                class="form-control" value="{{ $rfq->email }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">{{ translate('Phone') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ translate('Phone') }}" id="mobile" name="mobile"
                                class="form-control" value="{{ $rfq->phone_number }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Message</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" placeholder="{{ translate('Message') }}" disabled id="" cols="30" rows="10">{{ $rfq->message }}</textarea>
                        </div>
                    </div>



                </div>

            </div>
        </div>
    </div>
@endsection
