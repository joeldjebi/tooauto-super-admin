@extends('layouts.header')

@section('content')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('index-candidat') }}">Candidatures</a>
    </li>
    <li class="breadcrumb-item active">Statistiques</li>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        
        <div class="card text-left">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Statistiques des candidatures</h4>
                    <a href="{{ route('index-candidat') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
                
                <!-- Statistiques générales -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $total_candidats }}</h4>
                                        <p class="mb-0">Total candidatures</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $candidats_ce_mois }}</h4>
                                        <p class="mb-0">Ce mois</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $candidats_cette_semaine }}</h4>
                                        <p class="mb-0">Cette semaine</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fa fa-clock-o fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Top 5 des offres -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa fa-trophy"></i> Top 5 des offres les plus demandées</h5>
                            </div>
                            <div class="card-body">
                                @if($top_offres->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Offre</th>
                                                    <th>Type</th>
                                                    <th>Ville</th>
                                                    <th>Candidatures</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($top_offres as $index => $offre)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            @if($offre->offre_emploi)
                                                                {{ Str::limit($offre->offre_emploi->description, 30) }}
                                                            @else
                                                                <span class="text-muted">Offre supprimée</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($offre->offre_emploi && $offre->offre_emploi->type_offre)
                                                                <span class="badge badge-primary">
                                                                    {{ $offre->offre_emploi->type_offre->libelle }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($offre->offre_emploi && $offre->offre_emploi->ville)
                                                                <span class="badge badge-secondary">
                                                                    {{ $offre->offre_emploi->ville->libelle }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success">{{ $offre->total }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fa fa-chart-bar fa-2x mb-2"></i>
                                        <p>Aucune donnée disponible</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Évolution des candidatures -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa fa-line-chart"></i> Évolution des candidatures (6 derniers mois)</h5>
                            </div>
                            <div class="card-body">
                                @if($candidats_par_mois->isNotEmpty())
                                    <canvas id="candidatsChart" width="400" height="200"></canvas>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fa fa-chart-line fa-2x mb-2"></i>
                                        <p>Aucune donnée disponible</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@if($candidats_par_mois->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const ctx = document.getElementById('candidatsChart').getContext('2d');
    
    const mois = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    
    const data = @json($candidats_par_mois);
    const labels = data.map(item => mois[item.mois - 1] + ' ' + item.annee);
    const values = data.map(item => item.total);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre de candidatures',
                data: values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endif
@endsection

