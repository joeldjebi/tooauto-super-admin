<svg width="0" height="0" style="position: absolute;" aria-hidden="true" focusable="false">
    @foreach(\App\Support\CallCenterIcons::PATHS as $iconName => $iconPaths)
        <symbol id="cc-i-{{ $iconName }}" viewBox="0 0 24 24">{!! $iconPaths !!}</symbol>
    @endforeach
</svg>
