@extends('layouts.admin')
@section('title', 'Page Builder')

@section('content')
@include('shared.page-builder-index', [
    'indexUrl' => route('admin.page-builder.index'),
    'editRouteName' => 'admin.page-builder.edit',
])
@endsection
