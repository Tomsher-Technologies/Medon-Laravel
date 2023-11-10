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
                                value="{{ $career->name }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{ translate('Email') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ translate('Email') }}" id="email" name="email"
                                class="form-control" value="{{ $career->email }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">{{ translate('Phone') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ translate('Phone') }}" id="mobile" name="mobile"
                                class="form-control" value="{{ $career->phone_number }}" disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Qualification</label>
                        <div class="col-sm-9">
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                value="{{ $career->qualification }}" disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Current Work Status</label>
                        <div class="col-sm-9">
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                value="{{ $career->current_status }}" disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Gender</label>
                        <div class="col-sm-9">
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                value="{{ $career->gender }}" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Years of Experience</label>
                        <div class="col-sm-9">
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                value="{{ $career->years_of_experience }}" disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">Resume</label>
                        <div class="col-sm-9">
                            <a target="_new" href="{{ URL::to('storage/' . $career->resume) }}" class="btn btn-primary">View</a>
                            <a target="_new" download href="{{ URL::to('storage/' . $career->resume) }}" class="btn btn-primary">Download</a>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
