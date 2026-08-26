<?php

namespace App\Http\Controllers;

use App\Models\Forfait_usager;
use App\Models\ReductionCard;
use App\Models\ReductionCardHistory;
use App\Models\Super;
use App\Models\UserReductionCard;
use App\Services\ReductionCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReductionCardController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Cartes de réduction';
        $data['menu'] = 'reduction-cards';
        $data['user'] = Super::where('id', Auth::id())->first();
        $data['forfait_usagers'] = Forfait_usager::where('statut', 1)->orderBy('libelle')->get();
        $data['filters'] = [
            'forfait_usager_id' => $request->get('forfait_usager_id'),
            'statut' => $request->get('statut'),
        ];

        $cardsQuery = ReductionCard::with(['forfaitUsager', 'userCards', 'histories'])
            ->withCount(['userCards', 'histories'])
            ->orderByDesc('id');

        if (!empty($data['filters']['forfait_usager_id'])) {
            $cardsQuery->where('forfait_usager_id', $data['filters']['forfait_usager_id']);
        }

        if ($data['filters']['statut'] !== null && $data['filters']['statut'] !== '') {
            $cardsQuery->where('statut', $data['filters']['statut']);
        }

        $data['cards'] = $cardsQuery->paginate(15)->withQueryString();

        return view('reduction-cards.index', $data);
    }

    public function userCards(Request $request)
    {
        $data['title'] = 'Cartes attribuées';
        $data['menu'] = 'reduction-cards-user-cards';
        $data['user'] = Super::where('id', Auth::id())->first();
        $data['cards'] = ReductionCard::orderBy('name')->get(['id', 'name']);
        $data['forfait_usagers'] = Forfait_usager::where('statut', 1)->orderBy('libelle')->get();
        $data['filters'] = [
            'search' => trim((string) $request->get('search', '')),
            'reduction_card_id' => $request->get('reduction_card_id'),
            'forfait_usager_id' => $request->get('forfait_usager_id'),
            'statut' => $request->get('statut'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $query = UserReductionCard::with(['reductionCard.forfaitUsager', 'user', 'abonnementUsager'])
            ->orderByDesc('id');

        if ($data['filters']['search'] !== '') {
            $search = $data['filters']['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('card_code', 'like', '%' . $search . '%')
                    ->orWhere('qr_code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenoms', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');

                        foreach (['indicatif', 'mobile', 'telephone'] as $column) {
                            if (Schema::hasColumn('users', $column)) {
                                $userQuery->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
            });
        }

        if (!empty($data['filters']['reduction_card_id'])) {
            $query->where('reduction_card_id', $data['filters']['reduction_card_id']);
        }

        if (!empty($data['filters']['forfait_usager_id'])) {
            $query->where('forfait_usager_id', $data['filters']['forfait_usager_id']);
        }

        if ($data['filters']['statut'] !== null && $data['filters']['statut'] !== '') {
            $query->where('statut', $data['filters']['statut']);
        }

        if (!empty($data['filters']['date_from'])) {
            $query->whereDate('date_debut', '>=', $data['filters']['date_from']);
        }

        if (!empty($data['filters']['date_to'])) {
            $query->whereDate('date_fin', '<=', $data['filters']['date_to']);
        }

        $data['userCards'] = $query->paginate(25)->withQueryString();

        return view('reduction-cards.user-cards', $data);
    }

    public function histories(Request $request)
    {
        $data['title'] = 'Historique des réductions';
        $data['menu'] = 'reduction-cards-histories';
        $data['user'] = Super::where('id', Auth::id())->first();
        $data['cards'] = ReductionCard::orderBy('name')->get(['id', 'name']);
        $data['filters'] = [
            'search' => trim((string) $request->get('search', '')),
            'reduction_card_id' => $request->get('reduction_card_id'),
            'establishment_type' => $request->get('establishment_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $query = ReductionCardHistory::with(['reductionCard', 'user'])
            ->orderByDesc('used_at')
            ->orderByDesc('id');

        if ($data['filters']['search'] !== '') {
            $search = $data['filters']['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('notes', 'like', '%' . $search . '%')
                    ->orWhere('establishment_id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenoms', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($data['filters']['reduction_card_id'])) {
            $query->where('reduction_card_id', $data['filters']['reduction_card_id']);
        }

        if (!empty($data['filters']['establishment_type'])) {
            $query->where('establishment_type', $data['filters']['establishment_type']);
        }

        if (!empty($data['filters']['date_from'])) {
            $query->whereDate('used_at', '>=', $data['filters']['date_from']);
        }

        if (!empty($data['filters']['date_to'])) {
            $query->whereDate('used_at', '<=', $data['filters']['date_to']);
        }

        $data['histories'] = $query->paginate(25)->withQueryString();

        return view('reduction-cards.histories', $data);
    }

    public function toggleUserCardStatus($id)
    {
        $userCard = UserReductionCard::find($id);
        if (!$userCard) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Carte usager introuvable.');
            return back();
        }

        $userCard->statut = (int) $userCard->statut === 1 ? 0 : 1;
        $userCard->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', (int) $userCard->statut === 1 ? 'Carte usager activée.' : 'Carte usager désactivée.');

        return back();
    }

    public function store(Request $request, ReductionCardService $service)
    {
        $validated = $this->validatedCard($request);

        $card = DB::transaction(function () use ($validated, $request, $service) {
            $card = ReductionCard::create([
                'forfait_usager_id' => $validated['forfait_usager_id'],
                'name' => html_entity_decode($validated['name']),
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'description' => $validated['description'] ?? null,
                'statut' => $request->boolean('statut', true) ? 1 : 0,
                'created_by' => Auth::id(),
            ]);

            $service->syncForCard($card);

            return $card;
        });

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Carte de réduction créée avec succès. Cartes usagers générées: ' . $card->userCards()->count());

        return back();
    }

    public function update(Request $request, $id, ReductionCardService $service)
    {
        $card = ReductionCard::find($id);
        if (!$card) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Carte de réduction introuvable.');
            return back();
        }

        $validated = $this->validatedCard($request, $card->id);

        DB::transaction(function () use ($card, $validated, $request, $service) {
            $card->update([
                'forfait_usager_id' => $validated['forfait_usager_id'],
                'name' => html_entity_decode($validated['name']),
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'description' => $validated['description'] ?? null,
                'statut' => $request->boolean('statut') ? 1 : 0,
            ]);

            $service->syncForCard($card);
        });

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Carte de réduction mise à jour avec succès.');

        return back();
    }

    public function sync($id, ReductionCardService $service)
    {
        $card = ReductionCard::find($id);
        if (!$card) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Carte de réduction introuvable.');
            return back();
        }

        $count = $service->syncForCard($card);

        session()->flash('type', 'alert-success');
        session()->flash('message', $count . ' carte(s) usager synchronisée(s).');

        return back();
    }

    public function destroy($id)
    {
        $card = ReductionCard::withCount('histories')->find($id);
        if (!$card) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Carte de réduction introuvable.');
            return back();
        }

        if ($card->histories_count > 0) {
            $card->update(['statut' => 0]);
            session()->flash('type', 'alert-warning');
            session()->flash('message', 'Cette carte a déjà un historique. Elle a été désactivée.');
            return back();
        }

        $card->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Carte de réduction supprimée.');

        return back();
    }

    private function validatedCard(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'forfait_usager_id' => 'required|exists:forfait_usagers,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('reduction_cards', 'name')->ignore($ignoreId),
            ],
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                Rule::when($request->input('discount_type') === 'percentage', ['max:100']),
            ],
            'description' => 'nullable|string',
            'statut' => 'nullable|boolean',
        ]);
    }
}
