@extends('layouts.fullwidth')
@section('content')
<div class="col-lg-5 col-md-6">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('index') }}"><img class="logo-auth" src="images/logo-full.png" alt=""></a>
            </div>
            <h4 class="text-center mb-4">Account Locked</h4>
            <form action="{{ url('index') }}">
                <div class="mb-sm-4 mb-3 position-relative">
                    <label class="form-label" for="dlab-password">Password</label>
                    <input type="password" id="dlab-password" class="form-control" value="123456">
                    <span class="show-pass eye">
                        <i class="fa fa-eye-slash"></i>
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block">Unlock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection