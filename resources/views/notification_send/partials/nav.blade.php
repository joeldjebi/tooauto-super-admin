<div class="mb-3 d-flex flex-wrap align-items-center">
    <a href="{{ route($indexRoute) }}" class="btn btn-sm {{ in_array(($menu ?? ''), ['notification-send', 'call-center-notification-send'], true) ? 'btn-primary' : 'btn-outline-primary' }} mr-2 mb-2">
        Ciblage
    </a>
    <a href="{{ route($programmesRoute) }}" class="btn btn-sm {{ in_array(($menu ?? ''), ['notification-programmes', 'call-center-notification-programmes'], true) ? 'btn-primary' : 'btn-outline-primary' }} mr-2 mb-2">
        Notifs programmées
    </a>
    <a href="{{ route($logsRoute) }}" class="btn btn-sm btn-outline-info mb-2">
        Logs
    </a>
</div>
