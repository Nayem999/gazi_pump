@extends('layouts.admin')

@section('title', 'Edit Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.update', $attendance) }}">
                @include('attendance._form')
            </form>
        </div>
    </div>
@endsection
