<script>
    window.ComplihanceConfig = {
        apiBaseUrl: @json(url(config('complihance.routes.api_prefix', 'complihance/api'))),
        csrfToken: @json(csrf_token()),
        blockedContent: @json(config('complihance.blocked_content')),
    };
</script>

{!! $assets !!}
