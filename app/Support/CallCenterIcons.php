<?php

namespace App\Support;

/**
 * Icones SVG (trait 24x24) de l'espace call center. Elles sont declarees une seule fois dans la page
 * (sprite de <symbol>) puis referencees par <use> : une liste de 100 lignes affiche plusieurs centaines
 * d'icones, les recopier en entier alourdissait fortement le HTML.
 */
class CallCenterIcons
{
    public const PATHS = [
        'dashboard' => '<rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/>',
        'users' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/>',
        'professionnels' => '<rect x="3" y="7.5" width="18" height="12.5" rx="2.5"/><path d="M8.5 7.5V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5M3 13h18"/>',
        'vehicules' => '<path d="M5 16.5h14M6.5 16.5V19M17.5 16.5V19M4 16.5v-4l2-5.2A2 2 0 0 1 7.9 6h8.2a2 2 0 0 1 1.9 1.3l2 5.2v4"/><path d="M4 12.5h16"/><circle cx="7.5" cy="14.5" r=".6"/><circle cx="16.5" cy="14.5" r=".6"/>',
        'station-services' => '<path d="M5 20V5.5A1.5 1.5 0 0 1 6.5 4h6A1.5 1.5 0 0 1 14 5.5V20M3.5 20h12M5 10h9"/><path d="M14 8.5l3 2.5v6.5a1.5 1.5 0 0 0 3 0V9l-2.5-2.5"/>',
        'station-de-lavages' => '<path d="M12 3.5s6 6.4 6 10.5a6 6 0 0 1-12 0c0-4.1 6-10.5 6-10.5z"/>',
        'annonces' => '<path d="M4 10v4a1 1 0 0 0 1 1h2l6 4V5L7 9H5a1 1 0 0 0-1 1z"/><path d="M17 9a4 4 0 0 1 0 6"/>',
        'annonce-concessionnaires' => '<path d="M3.5 12.6V4.5a1 1 0 0 1 1-1h8.1a1 1 0 0 1 .7.3l7.4 7.4a1 1 0 0 1 0 1.4l-8.1 8.1a1 1 0 0 1-1.4 0l-7.4-7.4a1 1 0 0 1-.3-.7z"/><circle cx="8" cy="8" r="1.5"/>',
        'annonce-etablissements' => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 9h8M8 12.5h8M8 16h5"/>',
        'concessionnaires' => '<path d="M4 20.5V9l8-5 8 5v11.5"/><path d="M9 20.5v-6h6v6M2.5 20.5h19"/>',
        'etablissements' => '<path d="M4 9.5V20h16V9.5"/><path d="M3 9.5l1.5-5h15l1.5 5a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0z"/><path d="M10 20v-5h4v5"/>',
        'autodocs' => '<path d="M14 3.5H7a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8.5z"/><path d="M14 3.5v5h5M9 13h6M9 16.5h4"/>',
        'notification-send' => '<path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15z"/><path d="M10 20.5a2 2 0 0 0 4 0"/>',
        'notification-programmes' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'logout' => '<path d="M14 4.5h3.5a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H14"/><path d="M10 16l-4-4 4-4M6 12h9"/>',
        'alertes' => '<path d="M10.3 4.3L2.9 17.5a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0z"/><path d="M12 9.5v4M12 17h.01"/>',
        'phone' => '<path d="M5.5 3.5h3l1.5 4-2 1.3a11 11 0 0 0 7.2 7.2l1.3-2 4 1.5v3a2 2 0 0 1-2 2A16.5 16.5 0 0 1 3.5 5.5a2 2 0 0 1 2-2z"/>',
        'whatsapp' => '<path d="M4 20l1.2-3.9A8.5 8.5 0 1 1 8 19z"/><path d="M9 9.2c0 3 2.8 5.8 5.8 5.8l1.2-1.4-1.8-1-1 .8a4.6 4.6 0 0 1-2.6-2.6l.8-1-1-1.8z"/>',
        'copy' => '<rect x="8.5" y="8.5" width="11" height="11" rx="2.5"/><path d="M15.5 8.5V6.5a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h2"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'chevron' => '<path d="M9.5 6l6 6-6 6"/>',
        'back' => '<path d="M14.5 6l-6 6 6 6"/>',
        'file' => '<path d="M14 3.5H7a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8.5z"/><path d="M14 3.5v5h5"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'empty' => '<rect x="3.5" y="5" width="17" height="14" rx="3"/><path d="M3.5 13h5l1.5 2.5h4l1.5-2.5h5"/>',
    ];

    public static function name(?string $name): string
    {
        return array_key_exists((string) $name, self::PATHS) ? (string) $name : 'dashboard';
    }
}
