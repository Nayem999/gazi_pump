@extends('layouts.portal')

@section('title', 'Warranty Information')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Warranty Information</h1>
        <div class="row">
            <div class="col-lg-8">
                <p>All {{ config('app.name') }} pumps and equipment are covered by a standard manufacturer's warranty against defects in materials and workmanship from the date of purchase.</p>
                <p>For warranty claims, please bring your proof of purchase to your nearest <a href="{{ route('portal.service-centers.index') }}">service center</a>, or reach out via our <a href="{{ route('portal.contact') }}">contact page</a> and our team will guide you through the process.</p>
                <p>Warranty coverage does not include damage from misuse, unauthorized modification, or normal wear and tear.</p>
            </div>
        </div>
    </div>
@endsection
