<script>
    window.ComplihanceConfig = {
        apiBaseUrl: @json(url(config('complihance.routes.api_prefix', 'complihance/api'))),
        csrfToken: @json(csrf_token()),
        blockedContent: @json(config('complihance.blocked_content')),
        afterRevokeRedirectUrl: @json(url(config('complihance.after_revoke_redirect_url', '/'))),
    };
</script>

{!! $assets !!}
