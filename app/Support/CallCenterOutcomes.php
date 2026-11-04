<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        'etablissements' => ['label' => 'Établissement', 'name' => 'name', 'mobile' => 'mobile', 'indicatif' => 'indicatif', 'whatsapp' => 'is_whatsapp'],
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
        'inscription' => "Aide à l'inscription",
        'support' => 'Support / assistance',
        'satisfaction' => 'Satisfaction',
        'autre' => 'Autre',
    ];

    /**
     * Resultats d'un appel. "contact" = la personne a ete jointe (compte dans le taux de decroche).
     * "tone" = couleur du badge.
     */
    public const RESULTATS = [
        'pas_de_reponse' => ['label' => 'Pas de réponse', 'contact' => false, 'tone' => 'light'],
        'messagerie' => ['label' => 'Messagerie', 'contact' => false, 'tone' => 'light'],
        'injoignable' => ['label' => 'Injoignable / éteint', 'contact' => false, 'tone' => 'light'],
        'mauvais_numero' => ['label' => 'Mauvais numéro', 'contact' => false, 'tone' => 'danger'],
        'a_rappeler' => ['label' => 'À rappeler', 'contact' => true, 'tone' => 'warning'],
        'interesse' => ['label' => 'Intéressé', 'contact' => true, 'tone' => 'info'],
        'refus' => ['label' => 'Pas intéressé / refus', 'contact' => true, 'tone' => 'danger'],
        'inscrit' => ['label' => 'Inscrit', 'contact' => true, 'tone' => 'primary'],
        'converti' => ['label' => 'Converti (client gagné)', 'contact' => true, 'tone' => 'success'],
        'alerte_renouvelee' => ['label' => 'Alerte renouvelée', 'contact' => true, 'tone' => 'success'],
        'informe' => ['label' => 'Informé / renseigné', 'contact' => true, 'tone' => 'info'],
    ];

    /** Resultats qui imposent une date de rappel. */
    public const RESULTATS_AVEC_RAPPEL = ['a_rappeler'];

    /** Valeur du choix « Autre » : l'agent saisit un libelle qui devient une nouvelle option. */
    public const AUTRE = 'autre';

    /** Options ajoutees par les agents (table call_center_options), chargees une fois par requete. */
    private static ?array $customOptions = null;

    /**
     * Motifs proposes : liste fixe, puis motifs ajoutes par les agents, puis « Autre » en dernier.
     */
    public static function motifs(): array
    {
        $motifs = self::MOTIFS;
        unset($motifs[self::AUTRE]);

        foreach (self::customOptions('motif') as $option) {
            $motifs[$option->code] = $option->label;
        }

        $motifs[self::AUTRE] = self::MOTIFS[self::AUTRE];

        return $motifs;
    }

    /**
     * Resultats : liste fixe puis resultats ajoutes par les agents.
     */
    public static function resultats(): array
    {
        $resultats = self::RESULTATS;

        foreach (self::customOptions('resultat') as $option) {
            $resultats[$option->code] = ['label' => $option->label, 'contact' => (bool) $option->contact, 'tone' => 'secondary'];
        }

        return $resultats;
    }

    public static function resultatLabels(): array
    {
        return array_map(fn ($resultat) => $resultat['label'], self::resultats());
    }

    public static function contactResultats(): array
    {
        return array_keys(array_filter(self::resultats(), fn ($resultat) => $resultat['contact']));
    }

    /**
     * Enregistre le libelle saisi avec « Autre » comme nouvelle option et renvoie son code.
     * Si une option porte deja ce libelle (sans tenir compte de la casse), son code est reutilise.
     */
    public static function addOption(string $groupe, string $label, bool $contact = true, ?int $createdBy = null): string
    {
        $label = trim(preg_replace('/\s+/', ' ', $label));
        $existing = $groupe === 'motif' ? self::motifs() : self::resultatLabels();

        foreach ($existing as $code => $existingLabel) {
            if ($code !== self::AUTRE && mb_strtolower($existingLabel) === mb_strtolower($label)) {
                return $code;
            }
        }

        if (! SchemaCache::hasTable('call_center_options')) {
            throw ValidationException::withMessages([
                $groupe . '_autre' => "L'option « Autre » n'est pas encore disponible (table call_center_options manquante). Choisissez une option de la liste.",
            ]);
        }

        $base = rtrim(Str::limit(Str::slug($label, '_'), 34, ''), '_') ?: 'option';
        $code = $base;
        for ($i = 2; array_key_exists($code, $existing) || $code === self::AUTRE; $i++) {
            $code = $base . '_' . $i;
        }

        DB::table('call_center_options')->insert([
            'groupe' => $groupe,
            'code' => $code,
            'label' => $label,
            'contact' => $contact,
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        self::$customOptions = null;

        return $code;
    }

    private static function customOptions(string $groupe): array
    {
        if (self::$customOptions === null) {
            self::$customOptions = SchemaCache::hasTable('call_center_options')
                ? DB::table('call_center_options')->orderBy('label')->get(['groupe', 'code', 'label', 'contact'])->groupBy('groupe')->map->all()->all()
                : [];
        }

        return self::$customOptions[$groupe] ?? [];
    }

    public static function label(string $group, ?string $key): string
    {
        return match ($group) {
            'cible' => self::CIBLES[$key]['label'] ?? (string) $key,
            'canal' => self::CANAUX[$key] ?? (string) $key,
            'motif' => self::motifs()[$key] ?? (string) $key,
            'resultat' => self::resultats()[$key]['label'] ?? (string) $key,
            default => (string) $key,
        };
    }

    public static function tone(?string $resultat): string
    {
        return self::resultats()[$resultat]['tone'] ?? 'light';
    }
}
