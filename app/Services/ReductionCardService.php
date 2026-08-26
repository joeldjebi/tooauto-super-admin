<?php

namespace App\Services;

use App\Models\Abonnement_usager;
use App\Models\ReductionCard;
use App\Models\ReductionCardHistory;
use App\Models\UserReductionCard;
use Illuminate\Support\Str;

class ReductionCardService
{
    public function syncForAbonnement(Abonnement_usager $abonnement): int
    {
        if ((int) ($abonnement->statut ?? 0) !== 1) {
            return 0;
        }

        $cards = ReductionCard::where('forfait_usager_id', $abonnement->forfait_id)
            ->where('statut', 1)
            ->get();

        $count = 0;
        foreach ($cards as $card) {
            $this->assignCard($card, $abonnement);
            $count++;
        }

        return $count;
    }

    public function syncForCard(ReductionCard $card): int
    {
        if ((int) $card->statut !== 1) {
            UserReductionCard::where('reduction_card_id', $card->id)->update(['statut' => 0]);
            return 0;
        }

        $abonnements = Abonnement_usager::where('forfait_id', $card->forfait_usager_id)
            ->where('statut', 1)
            ->get();

        $count = 0;
        foreach ($abonnements as $abonnement) {
            $this->assignCard($card, $abonnement);
            $count++;
        }

        return $count;
    }

    public function assignCard(ReductionCard $card, Abonnement_usager $abonnement): UserReductionCard
    {
        $payload = [
            'user_id' => $abonnement->user_id,
            'forfait_usager_id' => $abonnement->forfait_id,
            'date_debut' => $abonnement->date_debut,
            'date_fin' => $abonnement->date_fin,
            'statut' => 1,
        ];

        $userCard = UserReductionCard::firstOrNew([
            'reduction_card_id' => $card->id,
            'abonnement_usager_id' => $abonnement->id,
        ]);

        if (!$userCard->exists) {
            $payload['card_code'] = $this->generateUniqueCode();
            $payload['qr_code'] = $this->generateUniqueQrCode();
        }

        $userCard->fill($payload);
        $userCard->save();

        return $userCard;
    }

    public function recordUsage(UserReductionCard $userCard, array $payload): ReductionCardHistory
    {
        $card = $userCard->reductionCard;
        $initial = (float) ($payload['montant_initial'] ?? 0);
        $discountAmount = $card->discount_type === 'percentage'
            ? ($initial * (float) $card->discount_value / 100)
            : (float) $card->discount_value;
        $discountAmount = min($discountAmount, $initial);

        return ReductionCardHistory::create([
            'user_reduction_card_id' => $userCard->id,
            'reduction_card_id' => $card->id,
            'user_id' => $userCard->user_id,
            'abonnement_usager_id' => $userCard->abonnement_usager_id,
            'forfait_usager_id' => $userCard->forfait_usager_id,
            'discount_type' => $card->discount_type,
            'discount_value' => $card->discount_value,
            'montant_initial' => $initial,
            'montant_reduction' => $discountAmount,
            'montant_final' => max(0, $initial - $discountAmount),
            'applied_by_id' => $payload['applied_by_id'] ?? null,
            'establishment_type' => $payload['establishment_type'],
            'establishment_id' => $payload['establishment_id'],
            'notes' => $payload['notes'] ?? null,
            'used_at' => $payload['used_at'] ?? now(),
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'RC-' . now()->format('ym') . '-' . Str::upper(Str::random(8));
        } while (UserReductionCard::where('card_code', $code)->exists());

        return $code;
    }

    private function generateUniqueQrCode(): string
    {
        do {
            $code = 'TOOAUTO-REDUCTION-' . Str::upper(Str::random(18));
        } while (UserReductionCard::where('qr_code', $code)->exists());

        return $code;
    }
}
