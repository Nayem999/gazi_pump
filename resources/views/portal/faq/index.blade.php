@extends('layouts.portal')

@section('title', 'Frequently Asked Questions')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Frequently Asked Questions</h1>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @forelse ($faqs as $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h2>
                            <div id="faq{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">{{ $faq->answer }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No FAQs available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
