@php
    $blockedContent = [
        'inline_consent' => config('complihance.blocked_content.inline_consent'),
        'placeholder' => [
            'title' => __('Blocked content'),
            'description' => __('This content requires :category consent.'),
            'button' => __('Accept and view'),
        ]
    ]
@endphp

<script>
    window.ComplihanceConfig = {
        apiBaseUrl: @json(url(config('complihance.routes.api_prefix', 'complihance/api'))),
        csrfToken: @json(csrf_token()),
        blockedContent: @json($blockedContent),
        afterRevokeRedirectUrl: @json(url(config('complihance.after_revoke_redirect_url', '/'))),
    };
</script>

{!! $assets !!}
