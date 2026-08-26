<div class="mb-3">
    <a href="{{ route('reduction-cards.index') }}" class="btn btn-sm {{ ($menu ?? '') === 'reduction-cards' ? 'btn-primary' : 'btn-outline-primary' }}">
        Cartes configurées
    </a>
    <a href="{{ route('reduction-cards.user-cards') }}" class="btn btn-sm {{ ($menu ?? '') === 'reduction-cards-user-cards' ? 'btn-primary' : 'btn-outline-primary' }}">
        Cartes usagers
    </a>
    <a href="{{ route('reduction-cards.histories') }}" class="btn btn-sm {{ ($menu ?? '') === 'reduction-cards-histories' ? 'btn-primary' : 'btn-outline-primary' }}">
        Historique
    </a>
</div>
