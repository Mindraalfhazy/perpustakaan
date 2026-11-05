@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form action="{{ route('admin.members.store') }}" method="POST">
                @csrf
                @include('members._form')
            </form>
        </div>
    </div>
@endsection
