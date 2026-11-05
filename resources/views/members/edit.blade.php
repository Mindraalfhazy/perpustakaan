@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form action="{{ route('admin.members.update', $member) }}" method="POST">
                @csrf
                @method('PUT')
                @include('members._form')
            </form>
        </div>
    </div>
@endsection
