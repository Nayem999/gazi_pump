@extends('layouts.portal')

@section('title', 'About Us')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">About {{ config('app.name') }}</h1>
        <div class="row">
            <div class="col-lg-8">
                <p>{{ config('app.name') }} is a leading provider of fuel pumps and dispensing equipment, serving dealers, retailers, and distributors nationwide through a dedicated sales force and an extensive service network.</p>
                <p>Our Sales Force Automation platform keeps our field team connected in real time — tracking visits, sales, and collections — so our dealer network always gets fast, reliable support.</p>
                <p>Whether you're looking for a new pump, replacement parts, or ongoing service, our team is ready to help.</p>
            </div>
        </div>
    </div>
@endsection
