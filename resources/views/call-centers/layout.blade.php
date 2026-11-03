<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Call Center' }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="/dist-assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/dist-assets/css/themes/lite-purple.min.css" rel="stylesheet">
    <style>
        :root {
            --cc-font: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", "Helvetica Neue", Arial, sans-serif;
            --cc-bg: #f5f5f7;
            --cc-surface: #ffffff;
            --cc-surface-2: #fbfbfd;
            --cc-fill: rgba(118, 118, 128, .12);
            --cc-fill-2: rgba(118, 118, 128, .08);
            --cc-separator: rgba(60, 60, 67, .12);
            --cc-label: #1d1d1f;
            --cc-label-2: #6e6e73;
            --cc-label-3: #86868b;
            --cc-blue: #0071e3;
            --cc-blue-hover: #0077ed;
            --cc-blue-tint: rgba(0, 113, 227, .1);
            --cc-green: #248a3d;
            --cc-green-tint: rgba(52, 199, 89, .14);
            --cc-orange: #c93400;
            --cc-orange-tint: rgba(255, 149, 0, .15);
            --cc-red: #d70015;
            --cc-red-tint: rgba(255, 59, 48, .12);
            --cc-teal: #0071a4;
            --cc-teal-tint: rgba(50, 173, 230, .14);
            --cc-radius-lg: 18px;
            --cc-radius: 12px;
            --cc-radius-sm: 9px;
            --cc-shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 8px 24px rgba(0, 0, 0, .04);
            --cc-shadow-pop: 0 4px 12px rgba(0, 0, 0, .08), 0 24px 48px rgba(0, 0, 0, .12);
            --cc-ease: cubic-bezier(.25, .1, .25, 1);
        }

        /* ---------- Base ---------- */
        body.cc-app {
            background: var(--cc-bg);
            color: var(--cc-label);
            font-family: var(--cc-font);
            font-size: 15px;
            letter-spacing: -.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .cc-app h1, .cc-app h2, .cc-app h3, .cc-app h4, .cc-app h5, .cc-app h6 {
            color: var(--cc-label);
            font-family: var(--cc-font);
            letter-spacing: -.02em;
        }
        .cc-app a {
            color: var(--cc-blue);
        }
        .cc-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* ---------- Shell ---------- */
        .cc-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 268px 1fr;
        }
        .cc-sidebar {
            background: rgba(246, 246, 248, .78);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            backdrop-filter: saturate(180%) blur(20px);
            border-right: 1px solid var(--cc-separator);
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 20px 12px;
            position: sticky;
            top: 0;
        }
        .cc-brand {
            align-items: center;
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
            padding: 6px 10px 18px;
            border-bottom: 1px solid var(--cc-separator);
        }
        .cc-brand-mark {
            align-items: center;
            background: linear-gradient(160deg, #2f8cff 0%, #0058d1 100%);
            border-radius: 11px;
            box-shadow: 0 2px 6px rgba(0, 88, 209, .3);
            color: #fff;
            display: flex;
            flex-shrink: 0;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .cc-brand h2 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }
        .cc-brand p {
            color: var(--cc-label-2);
            font-size: 12.5px;
            margin: 2px 0 0;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .cc-nav-label {
            color: var(--cc-label-3);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .04em;
            padding: 0 10px 6px;
            text-transform: uppercase;
        }
        .cc-nav {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 2px;
            margin: 0 -4px;
            overflow-y: auto;
            padding: 0 4px;
        }
        .cc-nav a {
            align-items: center;
            border-radius: var(--cc-radius-sm);
            color: var(--cc-label) !important;
            display: flex;
            font-size: 14px;
            font-weight: 500;
            gap: 10px;
            padding: 6px 10px;
            text-decoration: none;
            transition: background .15s var(--cc-ease);
            white-space: nowrap;
        }
        .cc-nav a:hover {
            background: var(--cc-fill-2);
        }
        .cc-nav a.active {
            background: var(--cc-blue);
            color: #fff !important;
        }
        .cc-nav a.active .cc-tile {
            background: rgba(255, 255, 255, .22) !important;
            box-shadow: none;
        }
        .cc-tile {
            align-items: center;
            border-radius: 7px;
            box-shadow: inset 0 0 0 .5px rgba(0, 0, 0, .06);
            color: #fff;
            display: inline-flex;
            flex-shrink: 0;
            height: 26px;
            justify-content: center;
            width: 26px;
        }
        .cc-tile .cc-icon {
            width: 16px;
            height: 16px;
        }
        .cc-tile-lg {
            border-radius: 12px;
            height: 44px;
            width: 44px;
        }
        .cc-tile-lg .cc-icon {
            width: 24px;
            height: 24px;
        }
        .cc-tone-blue { background: linear-gradient(160deg, #3b9bff, #0a6cf0); }
        .cc-tone-indigo { background: linear-gradient(160deg, #7b7bff, #4c4ce0); }
        .cc-tone-purple { background: linear-gradient(160deg, #c77dff, #9645e0); }
        .cc-tone-pink { background: linear-gradient(160deg, #ff6b9a, #e5336b); }
        .cc-tone-red { background: linear-gradient(160deg, #ff6b61, #e5352b); }
        .cc-tone-orange { background: linear-gradient(160deg, #ffad33, #f57f00); }
        .cc-tone-yellow { background: linear-gradient(160deg, #ffd23f, #f2b200); }
        .cc-tone-green { background: linear-gradient(160deg, #4cd964, #26b043); }
        .cc-tone-teal { background: linear-gradient(160deg, #5ac8fa, #1f9fd6); }
        .cc-tone-gray { background: linear-gradient(160deg, #a1a1a6, #7c7c80); }
        .cc-mobile-only {
            display: none;
        }
        .cc-sidebar-footer {
            border-top: 1px solid var(--cc-separator);
            margin-top: 12px;
            padding-top: 12px;
        }

        .cc-main {
            margin: 0 auto;
            max-width: 1440px;
            min-width: 0;
            padding: 40px 44px 56px;
            width: 100%;
        }
        .cc-topbar {
            align-items: flex-end;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .cc-eyebrow {
            color: var(--cc-label-2);
            font-size: 13px;
            font-weight: 500;
            margin: 0 0 4px;
        }
        .cc-topbar h1 {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -.028em;
            line-height: 1.1;
            margin: 0;
        }
        .cc-topbar p.cc-lead {
            color: var(--cc-label-2);
            font-size: 15px;
            margin: 6px 0 0;
        }

        /* ---------- Cards ---------- */
        .cc-card {
            background: var(--cc-surface);
            border: 0;
            border-radius: var(--cc-radius-lg);
            box-shadow: var(--cc-shadow);
            min-width: 0;
            padding: 24px;
        }
        .cc-card + .cc-card {
            margin-top: 20px;
        }
        .cc-card-header {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .cc-card-title {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -.02em;
            margin: 0;
        }
        .cc-card-subtitle {
            color: var(--cc-label-2);
            font-size: 14px;
            margin: 4px 0 0;
        }
        .cc-section-title {
            color: var(--cc-label);
            font-size: 17px;
            font-weight: 600;
            margin: 0 0 14px;
        }
        .cc-header-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .cc-perpage {
            align-items: center;
            display: inline-flex;
            gap: 8px;
            margin: 0;
        }
        .cc-perpage label {
            color: var(--cc-label-2);
            font-size: 13px;
            margin: 0;
        }
        .cc-app .cc-perpage .form-control {
            border-radius: 980px;
            height: 32px;
            padding: 4px 30px 4px 12px;
            width: auto;
        }
        .cc-count {
            background: var(--cc-fill-2);
            border-radius: 980px;
            color: var(--cc-label-2);
            font-size: 13px;
            font-weight: 500;
            padding: 5px 12px;
            white-space: nowrap;
        }

        /* ---------- Buttons (also restyles HTML generated by controllers) ---------- */
        .cc-app .btn {
            align-items: center;
            border: 0;
            border-radius: 980px;
            box-shadow: none !important;
            display: inline-flex;
            font-family: var(--cc-font);
            font-size: 14px;
            font-weight: 500;
            gap: 6px;
            justify-content: center;
            letter-spacing: -.01em;
            line-height: 1.2;
            padding: 9px 18px;
            transition: background .15s var(--cc-ease), color .15s var(--cc-ease), transform .1s var(--cc-ease);
        }
        .cc-app .btn:active {
            transform: scale(.97);
        }
        .cc-app .btn:focus-visible {
            outline: 0;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .3) !important;
        }
        .cc-app .btn-sm {
            font-size: 13px;
            padding: 6px 13px;
        }
        .cc-app .btn .cc-icon {
            width: 16px;
            height: 16px;
        }
        .cc-app .btn-primary {
            background: var(--cc-blue);
            color: #fff;
        }
        .cc-app .btn-primary:hover {
            background: var(--cc-blue-hover);
            color: #fff;
        }
        .cc-app .btn-outline-primary,
        .cc-app .btn-outline-info {
            background: var(--cc-blue-tint);
            color: var(--cc-blue);
        }
        .cc-app .btn-outline-info {
            background: var(--cc-teal-tint);
            color: var(--cc-teal);
        }
        .cc-app .btn-outline-success {
            background: var(--cc-green-tint);
            color: var(--cc-green);
        }
        .cc-app .btn-outline-warning {
            background: var(--cc-orange-tint);
            color: var(--cc-orange);
        }
        .cc-app .btn-outline-danger {
            background: var(--cc-red-tint);
            color: var(--cc-red);
        }
        .cc-app .btn-outline-secondary {
            background: var(--cc-fill);
            color: var(--cc-label);
        }
        .cc-app [class*="btn-outline-"]:hover {
            filter: brightness(.96) saturate(1.2);
        }
        .cc-app .btn-outline-primary:hover { background: rgba(0, 113, 227, .16); color: var(--cc-blue); }
        .cc-app .btn-outline-info:hover { background: rgba(50, 173, 230, .22); color: var(--cc-teal); }
        .cc-app .btn-outline-success:hover { background: rgba(52, 199, 89, .22); color: var(--cc-green); }
        .cc-app .btn-outline-warning:hover { background: rgba(255, 149, 0, .22); color: var(--cc-orange); }
        .cc-app .btn-outline-danger:hover { background: rgba(255, 59, 48, .18); color: var(--cc-red); }
        .cc-app .btn-outline-secondary:hover { background: rgba(118, 118, 128, .18); color: var(--cc-label); }
        .cc-app .btn-plain {
            background: transparent;
            color: var(--cc-blue);
            padding-left: 6px;
            padding-right: 10px;
        }
        .cc-app .btn-plain:hover {
            background: var(--cc-blue-tint);
            color: var(--cc-blue);
        }

        /* ---------- Badges ---------- */
        .cc-app .badge {
            border: 0 !important;
            border-radius: 980px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0;
            padding: 4px 10px;
        }
        .cc-app .badge-success { background: var(--cc-green-tint); color: var(--cc-green); }
        .cc-app .badge-info,
        .cc-app .badge-primary { background: var(--cc-blue-tint); color: var(--cc-blue); }
        .cc-app .badge-light { background: var(--cc-fill); color: var(--cc-label-2); }
        .cc-app .badge-warning { background: var(--cc-orange-tint); color: var(--cc-orange); }
        .cc-app .badge-danger { background: var(--cc-red-tint); color: var(--cc-red); }
        .cc-dot {
            border-radius: 50%;
            display: inline-block;
            height: 7px;
            margin-right: 6px;
            vertical-align: 1px;
            width: 7px;
            background: currentColor;
        }

        /* ---------- Forms ---------- */
        .cc-app .form-control {
            background: var(--cc-surface);
            border: 1px solid rgba(0, 0, 0, .14);
            border-radius: var(--cc-radius-sm);
            color: var(--cc-label);
            font-family: var(--cc-font);
            font-size: 15px;
            height: 42px;
            padding: 8px 12px;
            transition: border-color .15s var(--cc-ease), box-shadow .15s var(--cc-ease);
        }
        .cc-app textarea.form-control {
            height: auto;
        }
        .cc-app .form-control-sm {
            font-size: 14px;
        }
        .cc-app .form-control::placeholder {
            color: var(--cc-label-3);
        }
        .cc-app .form-control:focus {
            border-color: var(--cc-blue);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .18);
            outline: 0;
        }
        .cc-app select.form-control {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236e6e73' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M8 10l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 18px;
            padding-right: 34px;
        }
        .cc-field-label {
            color: var(--cc-label-2);
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        /* ---------- Alerts ---------- */
        .cc-app .alert {
            border: 0;
            border-radius: var(--cc-radius);
            font-size: 14px;
            padding: 13px 16px;
        }
        .cc-app .alert-success { background: var(--cc-green-tint); color: #1b6b30; }
        .cc-app .alert-danger { background: var(--cc-red-tint); color: #a3000f; }
        .cc-app .alert-warning { background: var(--cc-orange-tint); color: #9a4a00; }
        .cc-app .alert-info { background: var(--cc-blue-tint); color: #00509e; }
        .cc-app .alert-light { background: var(--cc-fill-2); color: var(--cc-label-2); }

        /* ---------- Filters ---------- */
        .cc-filter-panel {
            background: var(--cc-surface-2);
            border: 1px solid var(--cc-separator);
            border-radius: var(--cc-radius);
            margin-bottom: 20px;
            padding: 18px 18px 14px;
        }
        .cc-filter-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .cc-filter-head > span {
            align-items: center;
            color: var(--cc-label);
            display: inline-flex;
            font-size: 15px;
            font-weight: 600;
            gap: 8px;
        }
        .cc-filter-head a {
            font-size: 13px;
            font-weight: 500;
        }
        .cc-filter-count {
            background: var(--cc-blue);
            border-radius: 980px;
            color: #fff;
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 8px;
        }
        .cc-app .form-control.cc-input-search {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2386868b' stroke-width='2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='6.5'/%3E%3Cpath d='M20 20l-4.2-4.2'/%3E%3C/svg%3E");
            background-position: 11px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-left: 34px;
        }
        .cc-filter-grid {
            display: grid;
            gap: 14px 16px;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        }
        .cc-filter-divider {
            align-items: center;
            color: var(--cc-label-2);
            display: flex;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            margin: 18px 0 12px;
        }
        .cc-filter-divider::after {
            background: var(--cc-separator);
            content: "";
            flex: 1;
            height: 1px;
        }
        .cc-filter-divider .cc-icon {
            width: 16px;
            height: 16px;
        }
        .cc-filter-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }
        .cc-filter-chip {
            align-items: center;
            background: var(--cc-blue-tint);
            border-radius: 980px;
            color: var(--cc-blue);
            display: inline-flex;
            font-size: 13px;
            font-weight: 500;
            gap: 6px;
            margin-left: auto;
            padding: 5px 12px;
        }
        .cc-filter-chip .cc-icon {
            width: 15px;
            height: 15px;
        }

        /* ---------- Tables ---------- */
        .cc-table-wrap {
            border: 1px solid var(--cc-separator);
            border-radius: var(--cc-radius);
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: rgba(0, 0, 0, .25) transparent;
            scrollbar-width: thin;
        }
        .cc-table-wrap::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .cc-table-wrap::-webkit-scrollbar-track {
            background: transparent;
        }
        .cc-table-wrap::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, .2);
            border-radius: 999px;
        }
        .cc-table {
            margin-bottom: 0;
            min-width: 100%;
            width: max-content;
        }
        .cc-app .cc-table th,
        .cc-app .cc-table td {
            border-color: var(--cc-separator);
        }
        .cc-table thead th {
            background: var(--cc-surface-2);
            border-bottom: 1px solid var(--cc-separator) !important;
            border-top: 0 !important;
            color: var(--cc-label-2);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .01em;
            padding: 12px 16px;
            position: sticky;
            top: 0;
            white-space: nowrap;
            z-index: 2;
        }
        .cc-table td {
            color: var(--cc-label);
            font-size: 14px;
            max-width: 260px;
            padding: 13px 16px;
            vertical-align: middle;
            white-space: nowrap;
        }
        .cc-table td:not(.cc-table-actions) {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cc-table tbody td {
            background: var(--cc-surface);
            transition: background .12s var(--cc-ease);
        }
        .cc-table tbody td:first-child {
            font-weight: 600;
        }
        .cc-app .cc-table.table-hover tbody tr:hover,
        .cc-app .cc-table.table-hover tbody tr:hover > td {
            background: #f5f9ff;
        }
        .cc-table-actions {
            min-width: 140px;
            max-width: 340px;
        }
        /* Colonne actions toujours visible a droite quand le tableau defile */
        .cc-table .cc-table-actions {
            box-shadow: inset 1px 0 0 var(--cc-separator);
            position: sticky;
            right: 0;
        }
        .cc-table thead th.cc-table-actions {
            z-index: 3;
        }
        .cc-muted {
            color: var(--cc-label-3);
        }
        .cc-follow-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-width: 130px;
        }

        /* Telephone cliquable */
        .cc-phone {
            align-items: center;
            display: inline-flex;
            gap: 4px;
            white-space: nowrap;
        }
        .cc-phone-call {
            align-items: center;
            background: var(--cc-green-tint);
            border-radius: 980px;
            color: var(--cc-green) !important;
            display: inline-flex;
            font-size: 13.5px;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            gap: 6px;
            padding: 5px 11px 5px 9px;
            text-decoration: none !important;
            transition: background .15s var(--cc-ease);
        }
        .cc-phone-call:hover {
            background: rgba(52, 199, 89, .24);
        }
        .cc-phone-call .cc-icon {
            height: 15px;
            width: 15px;
        }
        .cc-phone-btn {
            align-items: center;
            background: var(--cc-fill-2);
            border: 0;
            border-radius: 50%;
            color: var(--cc-label-2) !important;
            cursor: pointer;
            display: inline-flex;
            height: 28px;
            justify-content: center;
            padding: 0;
            transition: background .15s var(--cc-ease), color .15s var(--cc-ease);
            width: 28px;
        }
        .cc-phone-btn:hover {
            background: var(--cc-fill);
            color: var(--cc-label) !important;
        }
        .cc-phone-btn .cc-icon {
            height: 15px;
            width: 15px;
        }
        .cc-phone-btn.cc-phone-wa {
            color: #1f9d55 !important;
        }
        .cc-phone-btn.is-copied {
            background: var(--cc-green-tint);
            color: var(--cc-green) !important;
        }
        .cc-phone-lg .cc-phone-call {
            font-size: 16px;
            padding: 8px 16px 8px 13px;
        }
        .cc-phone-lg .cc-phone-btn {
            height: 36px;
            width: 36px;
        }

        /* Fiche contact */
        .cc-contact {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 16px 24px;
            justify-content: space-between;
        }
        .cc-contact-id {
            align-items: center;
            display: flex;
            gap: 16px;
            min-width: 0;
        }
        .cc-contact-avatar {
            align-items: center;
            background: linear-gradient(160deg, #3b9bff, #0a6cf0);
            border-radius: 50%;
            color: #fff;
            display: flex;
            flex-shrink: 0;
            font-size: 19px;
            font-weight: 600;
            height: 56px;
            justify-content: center;
            width: 56px;
        }
        .cc-contact-meta {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            font-size: 13.5px;
            gap: 8px;
            margin-top: 6px;
        }
        .cc-contact-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Echeances des alertes */
        .cc-due,
        .cc-due-date {
            align-items: center;
            border-radius: 980px;
            display: inline-flex;
            font-size: 12.5px;
            font-weight: 600;
            gap: 5px;
            padding: 4px 10px;
            white-space: nowrap;
        }
        .cc-due .cc-dot {
            margin-right: 0;
        }
        .cc-due-date {
            font-size: 13.5px;
            font-variant-numeric: tabular-nums;
        }
        .cc-due-date .cc-icon {
            height: 14px;
            width: 14px;
        }
        .cc-due-expired { background: var(--cc-red-tint); color: var(--cc-red); }
        .cc-due-today { background: #ff3b30; color: #fff; }
        .cc-due-urgent { background: var(--cc-orange-tint); color: var(--cc-orange); }
        .cc-due-soon { background: rgba(255, 204, 0, .2); color: #8a6100; }
        .cc-due-ok { background: var(--cc-green-tint); color: var(--cc-green); }
        .cc-table td.cc-td-full {
            max-width: none;
            overflow: visible;
        }
        .cc-table tbody tr.cc-row-expired > td:first-child {
            box-shadow: inset 3px 0 0 #ff3b30;
        }
        .cc-table tbody tr.cc-row-urgent > td:first-child {
            box-shadow: inset 3px 0 0 #ff9500;
        }

        /* Actions popover */
        .cc-actions-dropdown {
            display: inline-block;
            position: relative;
        }
        .cc-actions-dropdown summary {
            cursor: pointer;
            list-style: none;
        }
        .cc-actions-dropdown summary::-webkit-details-marker {
            display: none;
        }
        .cc-actions-toggle .cc-icon {
            transform: rotate(90deg);
            transition: transform .2s var(--cc-ease);
        }
        .cc-actions-dropdown[open] .cc-actions-toggle .cc-icon {
            transform: rotate(-90deg);
        }
        .cc-actions-dropdown[open] .cc-actions-toggle {
            background: var(--cc-blue);
            color: #fff;
        }
        .cc-actions-menu {
            animation: cc-pop .18s var(--cc-ease);
            background: rgba(255, 255, 255, .92);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            backdrop-filter: saturate(180%) blur(20px);
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 14px;
            box-shadow: var(--cc-shadow-pop);
            margin-top: 8px;
            padding: 12px;
            position: absolute;
            right: 0;
            top: 100%;
            width: 300px;
            z-index: 20;
        }
        @keyframes cc-pop {
            from { opacity: 0; transform: translateY(-4px) scale(.98); }
            to { opacity: 1; transform: none; }
        }
        .cc-menu-label {
            color: var(--cc-label-3);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .04em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .cc-action-links,
        .cc-action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .cc-action-links .mr-1 { margin-right: 0 !important; }
        .cc-action-links .mb-1 { margin-bottom: 0 !important; }
        .cc-call-panel {
            border-top: 1px solid var(--cc-separator);
            margin-top: 12px;
            padding-top: 12px;
        }
        .cc-call-panel-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .cc-note-preview {
            background: var(--cc-fill-2);
            border-radius: var(--cc-radius-sm);
            color: var(--cc-label-2);
            font-size: 13px;
            margin-top: 10px;
            padding: 9px 11px;
            white-space: normal;
        }
        .cc-note-editor {
            margin-top: 10px;
            min-width: 0;
        }

        /* ---------- Empty state ---------- */
        .cc-empty-state {
            align-items: center;
            color: var(--cc-label-2);
            display: flex;
            flex-direction: column;
            font-size: 15px;
            gap: 10px;
            padding: 48px 20px;
            text-align: center;
        }
        .cc-empty-state .cc-icon {
            color: var(--cc-label-3);
            height: 40px;
            width: 40px;
        }
        .cc-empty-state strong {
            color: var(--cc-label);
            font-size: 17px;
            font-weight: 600;
        }

        /* ---------- Grouped list (iOS Settings style) ---------- */
        .cc-grouped {
            background: var(--cc-surface);
            border-radius: var(--cc-radius);
            box-shadow: inset 0 0 0 1px var(--cc-separator);
            margin: 0;
            overflow: hidden;
        }
        .cc-grouped-row {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            min-height: 46px;
            padding: 11px 16px;
            position: relative;
        }
        .cc-grouped-row + .cc-grouped-row::before {
            background: var(--cc-separator);
            content: "";
            height: 1px;
            left: 16px;
            position: absolute;
            right: 0;
            top: 0;
        }
        .cc-grouped-row dt {
            color: var(--cc-label-2);
            font-size: 14px;
            font-weight: 400;
            margin: 0;
        }
        .cc-grouped-row dd {
            color: var(--cc-label);
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            overflow-wrap: anywhere;
            text-align: right;
        }

        /* ---------- Media gallery ---------- */
        .cc-gallery {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
        .cc-media {
            background: var(--cc-surface);
            border-radius: var(--cc-radius);
            box-shadow: inset 0 0 0 1px var(--cc-separator);
            color: var(--cc-label) !important;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            text-decoration: none !important;
            transition: transform .25s var(--cc-ease), box-shadow .25s var(--cc-ease);
        }
        .cc-media:hover {
            box-shadow: var(--cc-shadow-pop);
            transform: translateY(-3px);
        }
        .cc-media-thumb {
            align-items: center;
            aspect-ratio: 4 / 3;
            background: var(--cc-fill-2);
            color: var(--cc-label-3);
            display: flex;
            justify-content: center;
            overflow: hidden;
        }
        .cc-media-thumb img {
            height: 100%;
            object-fit: cover;
            transition: transform .4s var(--cc-ease);
            width: 100%;
        }
        .cc-media:hover .cc-media-thumb img {
            transform: scale(1.04);
        }
        .cc-media-thumb .cc-icon {
            height: 44px;
            width: 44px;
        }
        .cc-media-body {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            padding: 12px 14px;
        }
        .cc-media-body span {
            font-size: 14px;
            font-weight: 500;
        }
        .cc-media-body small {
            color: var(--cc-label-3);
            display: block;
            font-size: 12px;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .cc-media-body .cc-open {
            color: var(--cc-blue);
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
        }

        /* ---------- Pagination ---------- */
        .cc-pager {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            margin-top: 18px;
        }
        .cc-pager p {
            color: var(--cc-label-2);
            font-size: 13px;
            margin: 0;
        }
        .cc-app .pagination {
            gap: 4px;
            margin: 0;
        }
        .cc-app .page-link {
            background: transparent;
            border: 0;
            border-radius: 980px !important;
            color: var(--cc-label);
            font-size: 14px;
            font-weight: 500;
            min-width: 34px;
            padding: 7px 11px;
            text-align: center;
        }
        .cc-app .page-link:hover {
            background: var(--cc-fill-2);
            color: var(--cc-label);
        }
        .cc-app .page-item.active .page-link {
            background: var(--cc-label);
            color: #fff;
        }
        .cc-app .page-item.disabled .page-link {
            background: transparent;
            color: var(--cc-label-3);
        }

        /* ---------- Composants Bootstrap (pages partagees : notifications) ---------- */
        .cc-app {
            accent-color: var(--cc-blue);
        }
        .cc-app .text-muted {
            color: var(--cc-label-2) !important;
        }
        .cc-app .card {
            background: var(--cc-surface);
            border: 0;
            border-radius: var(--cc-radius-lg);
            box-shadow: var(--cc-shadow);
        }
        .cc-app .card.border-danger {
            box-shadow: 0 0 0 1.5px rgba(255, 59, 48, .45), var(--cc-shadow);
        }
        .cc-app .card.border-success {
            box-shadow: 0 0 0 1.5px rgba(52, 199, 89, .45), var(--cc-shadow);
        }
        .cc-app .card-body {
            padding: 24px;
        }
        .cc-app .card-title {
            font-size: 19px;
            font-weight: 600;
            letter-spacing: -.02em;
        }
        .cc-app .form-group > label,
        .cc-app .form-row label {
            color: var(--cc-label-2);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .cc-app .form-text,
        .cc-app small.text-muted {
            font-size: 12.5px;
        }
        .cc-app .form-control:disabled {
            background-color: var(--cc-fill-2);
            color: var(--cc-label-3);
        }
        .cc-app input[type="file"].form-control {
            height: auto;
            padding: 8px 10px;
        }
        .cc-app input[type="file"]::file-selector-button {
            background: var(--cc-fill);
            border: 0;
            border-radius: 980px;
            color: var(--cc-label);
            cursor: pointer;
            font: 500 13px var(--cc-font);
            margin-right: 12px;
            padding: 6px 14px;
        }
        .cc-app .form-control-sm {
            height: 34px;
            padding: 5px 10px;
        }
        .cc-app textarea.form-control-sm {
            height: auto;
        }
        .cc-app .btn-block {
            display: flex;
            width: 100%;
        }
        .cc-app .btn-success { background: #30b14c; color: #fff; }
        .cc-app .btn-success:hover { background: #2aa043; color: #fff; }
        .cc-app .btn-warning { background: #ff9500; color: #fff; }
        .cc-app .btn-warning:hover { background: #f08a00; color: #fff; }
        .cc-app .btn-danger { background: #ff3b30; color: #fff; }
        .cc-app .btn-info { background: var(--cc-teal-tint); color: var(--cc-teal); }
        .cc-app .btn-light { background: var(--cc-fill); color: var(--cc-label); }
        .cc-app .btn-light:hover { background: rgba(118, 118, 128, .18); color: var(--cc-label); }
        .cc-app .btn:disabled,
        .cc-app .btn.disabled {
            opacity: .45;
        }
        .cc-app .dropdown-toggle.text-left {
            justify-content: space-between;
        }
        .cc-app .dropdown-toggle::after {
            border: 0;
            content: "";
            height: 14px;
            margin-left: 8px;
            width: 14px;
            background: currentColor;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M7 10l5 5 5-5'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M7 10l5 5 5-5'/%3E%3C/svg%3E") center / contain no-repeat;
        }
        .cc-app .dropdown-menu {
            background: rgba(255, 255, 255, .96);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            backdrop-filter: saturate(180%) blur(20px);
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 14px;
            box-shadow: var(--cc-shadow-pop);
        }
        .cc-app .dropdown-item {
            border-radius: 8px;
            font-size: 14px;
            padding: 7px 10px;
        }
        .cc-app .dropdown-item:hover,
        .cc-app .dropdown-item:focus {
            background: var(--cc-fill-2);
            color: var(--cc-label);
        }
        .cc-app .badge-secondary { background: var(--cc-fill); color: var(--cc-label-2); }
        .cc-app .badge-dark { background: rgba(29, 29, 31, .85); color: #fff; }
        .cc-app code {
            background: var(--cc-fill-2);
            border-radius: 6px;
            color: var(--cc-label-2);
            font-size: 12.5px;
            padding: 2px 6px;
        }
        .cc-app .text-danger { color: var(--cc-red) !important; }
        .cc-app .text-success { color: var(--cc-green) !important; }

        .cc-app .table-responsive {
            border: 1px solid var(--cc-separator);
            border-radius: var(--cc-radius);
            margin-bottom: 4px;
        }
        .cc-app .table-responsive > .table {
            margin-bottom: 0;
        }
        .cc-app .table {
            color: var(--cc-label);
            font-size: 14px;
        }
        .cc-app .table-bordered,
        .cc-app .table-bordered th,
        .cc-app .table-bordered td {
            border-left: 0;
            border-right: 0;
        }
        .cc-app .table-responsive .table-bordered {
            border: 0;
        }
        .cc-app .table th,
        .cc-app .table td {
            border-color: var(--cc-separator);
            padding: 12px 16px;
            vertical-align: middle;
        }
        .cc-app .table tbody tr:first-child td {
            border-top: 0;
        }
        .cc-app .table-sm th,
        .cc-app .table-sm td {
            padding: 9px 12px;
        }
        .cc-app .table thead th,
        .cc-app .table .thead-light th {
            background: var(--cc-surface-2);
            border-bottom: 1px solid var(--cc-separator);
            border-top: 0;
            color: var(--cc-label-2);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .01em;
            position: sticky;
            top: 0;
            white-space: nowrap;
            z-index: 2;
        }
        .cc-app .table thead tr + tr th {
            font-weight: 400;
            padding-bottom: 10px;
            padding-top: 0;
            position: static;
        }
        .cc-app .table-striped tbody tr:nth-of-type(odd) {
            background: transparent;
        }
        .cc-app .table tbody tr {
            transition: background .12s var(--cc-ease);
        }
        .cc-app .table tbody tr:hover {
            background: #f5f9ff;
        }
        .cc-app .table .table-danger,
        .cc-app .table .table-danger > td {
            background: rgba(255, 59, 48, .07);
        }
        .cc-app .table code {
            white-space: nowrap;
            word-break: normal;
        }
        .cc-app .table td .badge {
            margin: 2px 0;
        }
        .cc-app .table td .d-inline,
        .cc-app .table td > .btn + .btn {
            margin-left: 4px;
        }
        .cc-app .table .bg-light {
            background: var(--cc-surface-2) !important;
        }

        /* Segmented control */
        .cc-segmented {
            background: var(--cc-fill);
            border-radius: 10px;
            display: inline-flex;
            gap: 2px;
            max-width: 100%;
            overflow-x: auto;
            padding: 3px;
        }
        .cc-segmented a {
            border-radius: 8px;
            color: var(--cc-label) !important;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 18px;
            text-decoration: none !important;
            transition: background .15s var(--cc-ease), box-shadow .15s var(--cc-ease);
            white-space: nowrap;
        }
        .cc-segmented a:hover {
            background: rgba(255, 255, 255, .5);
        }
        .cc-segmented a.active {
            background: var(--cc-surface);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1), 0 1px 1px rgba(0, 0, 0, .04);
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 900px) {
            .cc-shell {
                display: block;
            }
            .cc-sidebar {
                border-bottom: 1px solid var(--cc-separator);
                border-right: 0;
                height: auto;
                padding: 12px 16px 10px;
                z-index: 30;
            }
            .cc-brand {
                border-bottom: 0;
                margin-bottom: 8px;
                padding: 0;
            }
            .cc-brand-mark {
                height: 32px;
                width: 32px;
                border-radius: 9px;
            }
            .cc-nav-label,
            .cc-sidebar-footer {
                display: none;
            }
            .cc-mobile-only {
                display: block;
            }
            .cc-nav {
                flex-direction: row;
                margin: 0 -16px;
                overflow-x: auto;
                padding: 2px 16px 4px;
                scrollbar-width: none;
            }
            .cc-nav::-webkit-scrollbar {
                display: none;
            }
            .cc-nav a {
                background: var(--cc-fill-2);
                border-radius: 980px;
                padding: 5px 12px 5px 5px;
            }
            .cc-nav .cc-tile {
                border-radius: 50%;
                height: 24px;
                width: 24px;
            }
            .cc-main {
                padding: 22px 16px 40px;
            }
            .cc-topbar {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 20px;
            }
            .cc-topbar h1 {
                font-size: 28px;
            }
            .cc-card {
                border-radius: 16px;
                padding: 18px;
            }
            .cc-card-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .cc-filter-chip {
                margin-left: 0;
            }
            .cc-table-wrap {
                border: 0;
                max-height: none;
                overflow: visible;
            }
            .cc-table .cc-table-actions {
                box-shadow: none;
                position: static;
            }
            .cc-app .card-body {
                padding: 18px;
            }
            .cc-table {
                display: block;
                min-width: 0;
                width: 100%;
            }
            .cc-table thead {
                display: none;
            }
            .cc-table tbody,
            .cc-table tr,
            .cc-table td {
                display: block;
                width: 100%;
            }
            .cc-table tbody tr {
                background: var(--cc-surface);
                border-radius: var(--cc-radius);
                box-shadow: inset 0 0 0 1px var(--cc-separator);
                margin-bottom: 12px;
                padding: 4px 0;
            }
            .cc-table td {
                align-items: center;
                border-top: 0 !important;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                max-width: none;
                padding: 9px 14px;
                position: relative;
                white-space: normal;
            }
            .cc-table td + td::after {
                background: var(--cc-separator);
                content: "";
                height: 1px;
                left: 14px;
                position: absolute;
                right: 0;
                top: 0;
            }
            .cc-table td::before {
                color: var(--cc-label-2);
                content: attr(data-label);
                flex: 0 0 38%;
                font-size: 13px;
                font-weight: 400;
            }
            .cc-table td > * {
                max-width: 62%;
            }
            .cc-table-actions {
                max-width: none;
                min-width: 0;
            }
            .cc-actions-dropdown,
            .cc-actions-toggle {
                width: 100%;
            }
            .cc-actions-menu {
                position: static;
                width: 100%;
            }
            .cc-follow-badges {
                justify-content: flex-end;
                min-width: 0;
            }
            .cc-grouped-row {
                align-items: flex-start;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .cc-app *, .cc-app *::before, .cc-app *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body class="cc-app">
    @php
        $callCenterMenu = [
            ['key' => 'call-center-dashboard', 'label' => 'Dashboard', 'route' => route('call-center.dashboard'), 'icon' => 'dashboard', 'tone' => 'gray'],
            ['key' => 'call-center-alertes', 'label' => 'Alertes', 'route' => route('call-center.alertes'), 'icon' => 'alertes', 'tone' => 'red'],
            ['key' => 'call-center-users', 'label' => 'Users', 'route' => route('call-center.users'), 'icon' => 'users', 'tone' => 'blue'],
            ['key' => 'call-center-professionnels', 'label' => 'Professionnels', 'route' => route('call-center.professionnels'), 'icon' => 'professionnels', 'tone' => 'indigo'],
            ['key' => 'call-center-vehicules', 'label' => 'Vehicules', 'route' => route('call-center.vehicules'), 'icon' => 'vehicules', 'tone' => 'teal'],
            ['key' => 'call-center-station-services', 'label' => 'Station services', 'route' => route('call-center.station-services'), 'icon' => 'station-services', 'tone' => 'orange'],
            ['key' => 'call-center-station-de-lavages', 'label' => 'Station de lavages', 'route' => route('call-center.station-de-lavages'), 'icon' => 'station-de-lavages', 'tone' => 'teal'],
            ['key' => 'call-center-annonces', 'label' => 'Annonces', 'route' => route('call-center.annonces'), 'icon' => 'annonces', 'tone' => 'pink'],
            ['key' => 'call-center-annonce-concessionnaires', 'label' => 'Annonce concessionnaires', 'route' => route('call-center.annonce-concessionnaires'), 'icon' => 'annonce-concessionnaires', 'tone' => 'purple'],
            ['key' => 'call-center-annonce-etablissements', 'label' => 'Annonce etablissements', 'route' => route('call-center.annonce-etablissements'), 'icon' => 'annonce-etablissements', 'tone' => 'red'],
            ['key' => 'call-center-concessionnaires', 'label' => 'Concessionnaires', 'route' => route('call-center.concessionnaires'), 'icon' => 'concessionnaires', 'tone' => 'indigo'],
            ['key' => 'call-center-etablissements', 'label' => 'Etablissements', 'route' => route('call-center.etablissements'), 'icon' => 'etablissements', 'tone' => 'green'],
            ['key' => 'call-center-autodocs', 'label' => 'Autodocs', 'route' => route('call-center.autodocs'), 'icon' => 'autodocs', 'tone' => 'yellow'],
            ['key' => 'call-center-notification-send', 'label' => 'Ciblage notif', 'route' => route('call-center.notification-send.index'), 'icon' => 'notification-send', 'tone' => 'red'],
            ['key' => 'call-center-notification-programmes', 'label' => 'Notifs programmees', 'route' => route('call-center.notification-send.programmes'), 'icon' => 'notification-programmes', 'tone' => 'orange'],
        ];
        $currentMenu = collect($callCenterMenu)->firstWhere('key', $menu ?? '');
        // Lu depuis le guard : certaines vues bouclent sur une variable $user qui ecrase celle du controleur.
        $callCenterUser = auth('call_center')->user() ?? ($user ?? null);
        $userName = $callCenterUser->full_name ?? $callCenterUser->email ?? 'Utilisateur';
    @endphp

    <div class="cc-shell">
        <aside class="cc-sidebar">
            <div class="cc-brand">
                <span class="cc-brand-mark">
                    @include('call-centers.partials.icon', ['name' => 'users'])
                </span>
                <div>
                    <h2>Call Center</h2>
                    <p title="{{ $userName }}">{{ $userName }}</p>
                </div>
            </div>

            <div class="cc-nav-label">Navigation</div>
            <nav class="cc-nav">
                @foreach ($callCenterMenu as $item)
                    <a href="{{ $item['route'] }}" class="{{ ($menu ?? '') === $item['key'] ? 'active' : '' }}">
                        <span class="cc-tile cc-tone-{{ $item['tone'] }}">
                            @include('call-centers.partials.icon', ['name' => $item['icon']])
                        </span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="cc-sidebar-footer">
                <form action="{{ route('call-center.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        @include('call-centers.partials.icon', ['name' => 'logout'])
                        Deconnexion
                    </button>
                </form>
            </div>
        </aside>

        <main class="cc-main">
            <header class="cc-topbar">
                <div>
                    <p class="cc-eyebrow">Espace Call Center{{ $currentMenu && $currentMenu['key'] !== 'call-center-dashboard' ? ' · ' . $currentMenu['label'] : '' }}</p>
                    <h1>{{ $title ?? 'Call Center' }}</h1>
                    @hasSection('lead')
                        <p class="cc-lead">@yield('lead')</p>
                    @endif
                </div>

                <div class="cc-header-actions">
                    @yield('header-actions')
                    <form action="{{ route('call-center.logout') }}" method="POST" class="cc-mobile-only">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            @include('call-centers.partials.icon', ['name' => 'logout'])
                            Deconnexion
                        </button>
                    </form>
                </div>
            </header>

            @if(session()->has('message'))
                <div class="alert {{ session()->get('type') ?? 'alert-info' }}">
                    {{ session()->get('message') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="/dist-assets/js/plugins/jquery-3.3.1.min.js"></script>
    <script src="/dist-assets/js/plugins/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var desktop = window.matchMedia('(min-width: 901px)');

            // Le conteneur du tableau scrolle horizontalement et couperait le menu : on le sort en position fixe.
            function place(dropdown) {
                var menu = dropdown.querySelector('.cc-actions-menu');
                if (!menu) return;
                if (!desktop.matches) {
                    menu.style.cssText = '';
                    return;
                }
                var rect = dropdown.querySelector('summary').getBoundingClientRect();
                var width = menu.offsetWidth;
                var left = Math.max(12, Math.min(rect.right - width, window.innerWidth - width - 12));
                var top = rect.bottom + 8;
                if (top + menu.offsetHeight > window.innerHeight - 12) {
                    top = Math.max(12, rect.top - menu.offsetHeight - 8);
                }
                menu.style.cssText = 'position:fixed;margin:0;right:auto;top:' + top + 'px;left:' + left + 'px;';
            }

            function closeAll(except) {
                document.querySelectorAll('.cc-actions-dropdown[open]').forEach(function (dropdown) {
                    if (dropdown !== except) dropdown.removeAttribute('open');
                });
            }

            document.addEventListener('toggle', function (event) {
                var dropdown = event.target;
                if (!dropdown.classList || !dropdown.classList.contains('cc-actions-dropdown')) return;
                // La cellule actions est sticky : on la passe au-dessus des lignes suivantes tant que le menu est ouvert.
                var cell = dropdown.closest('td');
                if (cell) cell.style.zIndex = dropdown.open ? '30' : '';
                if (!dropdown.open) return;
                closeAll(dropdown);
                place(dropdown);
            }, true);

            document.addEventListener('click', function (event) {
                if (!event.target.closest('.cc-actions-dropdown')) closeAll();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeAll();
            });

            ['scroll', 'resize'].forEach(function (name) {
                window.addEventListener(name, function () {
                    document.querySelectorAll('.cc-actions-dropdown[open]').forEach(place);
                }, true);
            });

            // Copie d'un numero de telephone dans le presse-papiers.
            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-cc-copy]');
                if (!button || !navigator.clipboard) return;
                navigator.clipboard.writeText(button.getAttribute('data-cc-copy')).then(function () {
                    button.classList.add('is-copied');
                    button.setAttribute('title', 'Copie !');
                    setTimeout(function () {
                        button.classList.remove('is-copied');
                        button.setAttribute('title', 'Copier le numero');
                    }, 1500);
                });
            });

            // Le bloc commentaire change la hauteur du menu : on le repositionne.
            document.addEventListener('click', function (event) {
                var dropdown = event.target.closest('.cc-actions-dropdown[open]');
                if (dropdown) setTimeout(function () { place(dropdown); }, 0);
            });
        })();
    </script>
</body>
</html>
