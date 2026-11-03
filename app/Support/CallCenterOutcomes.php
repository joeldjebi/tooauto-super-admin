<?php

namespace App\Support;

/**
 * Referentiel unique du journal des appels : cibles, canaux, motifs et resultats.
 * Les formulaires, la validation et les rapports lisent tous cette classe.
 */
class CallCenterOutcomes
{
    /**
     * Cibles appelables : type (= table) => libelle, expression du nom affiche,
     * et colonnes du telephone (mobile, indicatif, whatsapp ; null si absentes).
     */
    public const CIBLES = [
        'users' => ['label' => 'Usager', 'name' => "TRIM(CONCAT(COALESCE(nom, ''), ' ', COALESCE(prenoms, '')))", 'mobile' => 'mobile', 'indicatif' => 'indicatif', 'whatsapp' => 'is_whatsapp'],
        'professionnels' => ['label' => 'Professionnel', 'name' => "TRIM(CONCAT(COALESCE(nom, ''), ' ', COALESCE(prenoms, '')))", 'mobile' => 'mobile', 'indicatif' => null, 'whatsapp' => null],
        'etablissements' => ['label' => 'Etablissement', 'name' => 'name', 'mobile' => 'mobile', 'indicatif' => 'indicatif', 'whatsapp' => 'is_whatsapp'],
        'concessionnaires' => ['label' => 'Concessionnaire', 'name' => 'name', 'mobile' => 'contact', 'indicatif' => null, 'whatsapp' => 'is_whatsapp'],
        'station_services' => ['label' => 'Station service', 'name' => 'name', 'mobile' => 'mobile', 'indicatif' => null, 'whatsapp' => null],
        'station_de_lavages' => ['label' => 'Station de lavage', 'name' => 'name', 'mobile' => 'contact', 'indicatif' => null, 'whatsapp' => null],
    ];

    /** Menu de l'espace call center => type de cible. */
    public const MENU_CIBLES = [
        'call-center-users' => 'users',
        'call-center-alertes' => 'users',
        'call-center-professionnels' => 'professionnels',
        'call-center-etablissements' => 'etablissements',
        'call-center-concessionnaires' => 'concessionnaires',
        'call-center-station-services' => 'station_services',
        'call-center-station-de-lavages' => 'station_de_lavages',
    ];

    public const CANAUX = [
        'appel' => 'Appel',
        'whatsapp' => 'WhatsApp',
        'sms' => 'SMS',
    ];

    public const MOTIFS = [
        'relance_alerte' => "Relance d'alerte",
        'prospection' => 'Prospection',
        'inscription' => "Aide a l'inscription",
        'support' => 'Support / assistance',
        'satisfaction' => 'Satisfaction',
        'autre' => 'Autre',
    ];

    /**
     * Resultats d'un appel. "contact" = la personne a ete jointe (compte dans le taux de decroche).
     * "tone" = couleur du badge.
     */
    public const RESULTATS = [
        'pas_de_reponse' => ['label' => 'Pas de reponse', 'contact' => false, 'tone' => 'light'],
        'messagerie' => ['label' => 'Messagerie', 'contact' => false, 'tone' => 'light'],
        'injoignable' => ['label' => 'Injoignable / eteint', 'contact' => false, 'tone' => 'light'],
        'mauvais_numero' => ['label' => 'Mauvais numero', 'contact' => false, 'tone' => 'danger'],
        'a_rappeler' => ['label' => 'A rappeler', 'contact' => true, 'tone' => 'warning'],
        'interesse' => ['label' => 'Interesse', 'contact' => true, 'tone' => 'info'],
        'refus' => ['label' => 'Pas interesse / refus', 'contact' => true, 'tone' => 'danger'],
        'inscrit' => ['label' => 'Inscrit', 'contact' => true, 'tone' => 'primary'],
        'converti' => ['label' => 'Converti (client gagne)', 'contact' => true, 'tone' => 'success'],
        'alerte_renouvelee' => ['label' => 'Alerte renouvelee', 'contact' => true, 'tone' => 'success'],
        'informe' => ['label' => 'Informe / renseigne', 'contact' => true, 'tone' => 'info'],
    ];

    /** Resultats qui imposent une date de rappel. */
    public const RESULTATS_AVEC_RAPPEL = ['a_rappeler'];

    public static function resultatLabels(): array
    {
        return array_map(fn ($resultat) => $resultat['label'], self::RESULTATS);
    }

    public static function contactResultats(): array
    {
        return array_keys(array_filter(self::RESULTATS, fn ($resultat) => $resultat['contact']));
    }

    public static function label(string $group, ?string $key): string
    {
        return match ($group) {
            'cible' => self::CIBLES[$key]['label'] ?? (string) $key,
            'canal' => self::CANAUX[$key] ?? (string) $key,
            'motif' => self::MOTIFS[$key] ?? (string) $key,
            'resultat' => self::RESULTATS[$key]['label'] ?? (string) $key,
            default => (string) $key,
        };
    }

    public static function tone(?string $resultat): string
    {
        return self::RESULTATS[$resultat]['tone'] ?? 'light';
    }
}
