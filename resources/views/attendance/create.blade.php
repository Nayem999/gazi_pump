@extends('layouts.admin')

@section('title', 'Record Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.store') }}">
                @include('attendance._form')
            </form>
        </div>
    </div>
@endsection
