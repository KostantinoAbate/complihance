@if(config('complihance.consent_mode.enabled', true))
    <script>
        window.dataLayer = window.dataLayer || [];

        window.gtag = window.gtag || function () {
            window.dataLayer.push(arguments);
        };

        window.gtag('consent', 'default', @json($defaultConsentMode));

        @if(! empty($currentConsentMode))
            window.gtag('consent', 'update', @json($currentConsentMode));
        @endif
    </script>
@endif
