@extends('layouts.dashboard')
@section('title', 'Page Builder')

@section('content')
@include('shared.page-builder-index', [
    'indexUrl' => route('dashboard.page-builder.index'),
    'editRouteName' => 'dashboard.page-builder.edit',
])
@endsection
