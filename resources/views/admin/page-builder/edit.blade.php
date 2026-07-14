@extends('layouts.admin')
@section('title', 'Page Builder — ' . $product->title)

@section('content')
@include('shared.page-builder', [
    'backUrl' => route('admin.page-builder.index'),
    'saveUrl' => route('admin.page-builder.update', $product),
    'uploadUrl' => route('admin.page-builder.upload-image', $product),
])
@endsection
