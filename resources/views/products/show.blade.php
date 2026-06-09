@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body">
            <h2 class="card-title">{{ $product->name }}</h2>
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="mt-4 mb-4 rounded-lg object-cover w-full h-64" />
            @endif
            <p>Category: {{ $product->category->name ?? '-' }}</p>
            <p>Price: Rp {{ number_format($product->price, 2, ',', '.') }}</p>
            <p>Status: {{ $product->is_available ? 'Available' : 'Unavailable' }}</p>

            <div class="mt-4">
                <a href="{{ route('products.edit', $product) }}" class="btn">Edit</a>
                <a href="{{ route('products.index') }}" class="btn btn-ghost">Back</a>
            </div>
        </div>
    </div>
@endsection
