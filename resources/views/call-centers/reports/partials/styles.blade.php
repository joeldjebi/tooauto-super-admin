{{-- Styles des rapports (superviseur) et de « Mon activite » (agent). --}}
    <style>
        .cc-r-filters { align-items: end; display: grid; gap: 12px 14px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .cc-r-filters .btn { height: 38px; }
        .cc-r-tiles { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); margin: 20px 0; }
        .cc-r-tile { background: var(--cc-surface); border-radius: var(--cc-radius-lg); box-shadow: var(--cc-shadow); padding: 16px 18px; }
        .cc-r-tile-label { color: var(--cc-label-2); font-size: 13px; font-weight: 500; }
        .cc-r-tile-value { display: block; font-size: 32px; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; line-height: 1.15; margin-top: 6px; }
        .cc-r-tile-hint { color: var(--cc-label-3); display: block; font-size: 12.5px; margin-top: 2px; }
        .cc-r-tile.is-alert .cc-r-tile-value { color: var(--cc-red); }
        .cc-r-tile.is-good .cc-r-tile-value { color: var(--cc-green); }
        .cc-r-two { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); margin-bottom: 20px; }
        .cc-r-two > .cc-card { margin: 0 !important; }

        /* Histogramme des appels par jour : une seule serie, barres fines arrondies en haut. */
        .cc-r-chart { position: relative; }
        .cc-r-plot { align-items: flex-end; border-bottom: 1px solid var(--cc-separator); display: flex; gap: 2px; height: 180px; padding-top: 8px; }
        .cc-r-col { align-items: flex-end; display: flex; flex: 1 1 0; height: 100%; justify-content: center; min-width: 0; position: relative; }
        .cc-r-bar { background: var(--cc-blue); border-radius: 4px 4px 0 0; max-width: 28px; min-height: 0; width: 70%; }
        .cc-r-col.is-empty .cc-r-bar { background: transparent; }
        .cc-r-col:hover .cc-r-bar { background: var(--cc-blue-hover); }
        .cc-r-col:hover { background: var(--cc-fill); border-radius: 6px 6px 0 0; }
        .cc-r-tip { background: var(--cc-label); border-radius: 8px; bottom: calc(100% + 6px); color: var(--cc-surface); display: none; font-size: 12px; left: 50%; padding: 6px 9px; pointer-events: none; position: absolute; transform: translateX(-50%); white-space: nowrap; z-index: 5; }
        .cc-r-col:hover .cc-r-tip, .cc-r-col:focus .cc-r-tip { display: block; }
        .cc-r-axis { color: var(--cc-label-3); display: flex; font-size: 11px; gap: 2px; margin-top: 6px; }
        .cc-r-axis span { flex: 1 1 0; min-width: 0; overflow: hidden; text-align: center; white-space: nowrap; }
        .cc-r-ymax { color: var(--cc-label-3); font-size: 11px; position: absolute; right: 0; top: -10px; }

        /* Barres horizontales (entonnoir, repartitions) : une seule teinte, valeurs en texte. */
        .cc-r-hbars { display: grid; gap: 10px; }
        .cc-r-hbar-head { display: flex; font-size: 13.5px; gap: 10px; justify-content: space-between; margin-bottom: 4px; }
        .cc-r-hbar-head strong { font-variant-numeric: tabular-nums; }
        .cc-r-hbar-track { background: var(--cc-fill); border-radius: 4px; height: 8px; overflow: hidden; }
        .cc-r-hbar-fill { background: var(--cc-blue); border-radius: 4px; height: 100%; }
        .cc-r-kv { display: grid; gap: 8px; }
        .cc-r-kv > div { align-items: baseline; border-bottom: 1px solid var(--cc-separator); display: flex; font-size: 14px; justify-content: space-between; padding-bottom: 8px; }
        .cc-r-kv > div:last-child { border-bottom: 0; padding-bottom: 0; }
        .cc-r-kv strong { font-variant-numeric: tabular-nums; }
        .cc-r-note { color: var(--cc-label-3); font-size: 12.5px; margin-top: 12px; }
        .cc-r-table td, .cc-r-table th { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .cc-r-table td.cc-r-num, .cc-r-table th.cc-r-num { text-align: right; }
        .cc-r-sub { color: var(--cc-label-3); font-size: 12px; }
        .cc-r-section-title { font-size: 13px; font-weight: 600; margin: 18px 0 10px; }
    </style>
