<footer class="bg-dark text-light-emphasis py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="text-white">{{ config('app.name') }}</h5>
                <p class="small text-white-50">Sales Force Automation &amp; customer engagement platform.</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('portal.promotions.index') }}" class="link-light link-opacity-75">Promotions</a></li>
                    <li><a href="{{ route('portal.faq.index') }}" class="link-light link-opacity-75">FAQ</a></li>
                    <li><a href="{{ route('portal.service-centers.index') }}" class="link-light link-opacity-75">Service Center</a></li>
                    <li><a href="{{ route('portal.brochures.index') }}" class="link-light link-opacity-75">Brochures</a></li>
                    <li><a href="{{ route('portal.warranty') }}" class="link-light link-opacity-75">Warranty Info</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white">Contact</h6>
                <p class="small text-white-50 mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
