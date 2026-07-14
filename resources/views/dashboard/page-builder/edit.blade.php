@extends('layouts.dashboard')
@section('title', 'Page Builder — ' . $product->title)

@section('content')
@include('shared.page-builder', [
    'backUrl' => route('dashboard.page-builder.index'),
    'saveUrl' => route('dashboard.page-builder.update', $product),
    'uploadUrl' => route('dashboard.page-builder.upload-image', $product),
])
@endsection
