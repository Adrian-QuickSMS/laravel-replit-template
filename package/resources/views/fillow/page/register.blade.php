@extends('layouts.fullwidth')
@section('content')
<div class="col-lg-5 col-md-6">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('index') }}"><img class="logo-auth" src="{{ asset('images/logo-full.png') }}" alt=""></a>
            </div>
            <h4 class="text-center mb-4">Sign up your account</h4>
            <form action="{{ url('index') }}">
                <div class="form-group mb-4">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" placeholder="Enter username" id="username">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" placeholder="hello@example.com" id="email">
                </div>
                <div class="mb-sm-4 mb-3 position-relative">
                    <label class="form-label" for="dlab-password">Password</label>
                    <input type="password" id="dlab-password" class="form-control" value="123456">
                    <span class="show-pass eye">
                        <i class="fa fa-eye-slash"></i>
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block">Sign up</button>
                </div>
            </form>
            <div class="new-account mt-3">
                <p>Already have an account? <a class="text-primary" href="{{ url('page-login') }}">Sign in</a></p>
            </div>
        </div>
    </div>
</div>
@endsection