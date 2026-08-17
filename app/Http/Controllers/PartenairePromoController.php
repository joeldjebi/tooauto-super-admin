<?php

namespace App\Http\Controllers;

use App\Models\CodePromo;
use App\Models\Forfait_usager;
use App\Models\PartenairePromo;
use App\Models\Super;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartenairePromoController extends Controller
{
    public function index()
    {
        $data['title'] = 'Partenaires promo';
        $data['menu'] = 'partenaires-promo';
        $data['user'] = Super::where('id', Auth::user()->id)->first();
        $data['forfait_usagers'] = Forfait_usager::where('statut', 1)->orderBy('libelle')->get();
        $data['partenaires'] = PartenairePromo::with([
                'codePromo.forfaitUsager',
                'codePromo.utilisations',
            ])
            ->orderByDesc('id')
            ->get();

        return view('partenaires-promo.index', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string',
            'pourcentage' => 'required|numeric|min:1|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'usage_limit' => 'nullable|integer|min:1',
            'is_unlimited' => 'nullable|boolean',
            'one_use_per_user' => 'nullable|boolean',
            'forfait_usager_id' => 'nullable|exists:forfait_usagers,id',
            'statut' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $partenaire = PartenairePromo::create([
                'nom' => html_entity_decode($validated['nom']),
                'email' => $validated['email'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'adresse' => $validated['adresse'] ?? null,
                'statut' => $request->boolean('statut', true) ? 1 : 0,
                'created_by' => Auth::id(),
            ]);

            CodePromo::create([
                'partenaire_promo_id' => $partenaire->id,
                'forfait_usager_id' => $validated['forfait_usager_id'] ?? null,
                'code' => $this->generatePartnerPromoCode($partenaire->nom),
                'pourcentage' => $validated['pourcentage'],
                'date_debut' => $validated['date_debut'] ?? null,
                'date_fin' => $validated['date_fin'] ?? null,
                'usage_limit' => $request->boolean('is_unlimited') ? null : ($validated['usage_limit'] ?? null),
                'usage_count' => 0,
                'is_unlimited' => $request->boolean('is_unlimited') ? 1 : 0,
                'one_use_per_user' => $request->boolean('one_use_per_user', true) ? 1 : 0,
                'statut' => $request->boolean('statut', true) ? 1 : 0,
            ]);
        });

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Partenaire promo créé avec succès.');

        return back();
    }

    public function update(Request $request, $id)
    {
        $partenaire = PartenairePromo::with('codePromo')->find($id);
        if (!$partenaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Partenaire promo introuvable.');
            return back();
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string',
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('codes_promo', 'code')->ignore(optional($partenaire->codePromo)->id),
            ],
            'pourcentage' => 'required|numeric|min:1|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'usage_limit' => 'nullable|integer|min:1',
            'is_unlimited' => 'nullable|boolean',
            'one_use_per_user' => 'nullable|boolean',
            'forfait_usager_id' => 'nullable|exists:forfait_usagers,id',
            'statut' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($partenaire, $validated, $request) {
            $partenaire->update([
                'nom' => html_entity_decode($validated['nom']),
                'email' => $validated['email'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'adresse' => $validated['adresse'] ?? null,
                'statut' => $request->boolean('statut') ? 1 : 0,
            ]);

            $partenaire->codePromo()->updateOrCreate(
                ['partenaire_promo_id' => $partenaire->id],
                [
                    'forfait_usager_id' => $validated['forfait_usager_id'] ?? null,
                    'code' => Str::upper($validated['code']),
                    'pourcentage' => $validated['pourcentage'],
                    'date_debut' => $validated['date_debut'] ?? null,
                    'date_fin' => $validated['date_fin'] ?? null,
                    'usage_limit' => $request->boolean('is_unlimited') ? null : ($validated['usage_limit'] ?? null),
                    'is_unlimited' => $request->boolean('is_unlimited') ? 1 : 0,
                    'one_use_per_user' => $request->boolean('one_use_per_user') ? 1 : 0,
                    'statut' => $request->boolean('statut') ? 1 : 0,
                ]
            );
        });

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Partenaire promo mis à jour avec succès.');

        return back();
    }

    public function toggleStatus($id)
    {
        $partenaire = PartenairePromo::with('codePromo')->find($id);
        if (!$partenaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Partenaire promo introuvable.');
            return back();
        }

        $newStatus = (int) $partenaire->statut === 1 ? 0 : 1;
        $partenaire->statut = $newStatus;
        $partenaire->save();

        if ($partenaire->codePromo) {
            $partenaire->codePromo->statut = $newStatus;
            $partenaire->codePromo->save();
        }

        session()->flash('type', 'alert-success');
        session()->flash('message', $newStatus ? 'Partenaire promo activé.' : 'Partenaire promo désactivé.');

        return back();
    }

    public function destroy($id)
    {
        $partenaire = PartenairePromo::with('codePromo.utilisations')->find($id);
        if (!$partenaire) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Partenaire promo introuvable.');
            return back();
        }

        if ($partenaire->codePromo && $partenaire->codePromo->utilisations()->exists()) {
            session()->flash('type', 'alert-warning');
            session()->flash('message', 'Ce partenaire a déjà des utilisations de code promo. Désactive-le plutôt que de le supprimer.');
            return back();
        }

        $partenaire->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Partenaire promo supprimé avec succès.');

        return back();
    }

    private function generatePartnerPromoCode(string $partnerName): string
    {
        $prefix = $this->initials($partnerName);

        do {
            $code = $prefix . random_int(1000, 9999);
        } while (CodePromo::where('code', $code)->exists());

        return $code;
    }

    private function initials(string $name): string
    {
        $words = collect(preg_split('/\s+/', Str::ascii(trim($name))))
            ->filter()
            ->values();

        $letters = $words->take(2)->map(function ($word) {
            return Str::upper(Str::substr($word, 0, 1));
        })->implode('');

        return str_pad($letters ?: 'TA', 2, 'X');
    }
}
