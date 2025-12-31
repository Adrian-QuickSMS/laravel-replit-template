@extends('layouts.default')

@push('styles')
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@section('sidebar')
    @include('elements.quicksms-sidebar')
@endsection
