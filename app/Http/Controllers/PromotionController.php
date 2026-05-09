<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    /**
     * Afficher la liste des promotions
     */
    public function index(Request $request)
    {
        $query = Promotion::with(['etablissement:id,name', 'createdBy:id,name']);

        // Filtres
        if ($request->filled('etablissement_filter')) {
            $query->where('etablissement_id', $request->etablissement_filter);
        }

        if ($request->filled('statut_filter')) {
            $query->where('statut', $request->statut_filter);
        }

        if ($request->filled('date_debut_filter')) {
            $query->where('date_debut', '>=', $request->date_debut_filter);
        }

        if ($request->filled('date_fin_filter')) {
            $query->where('date_fin', '<=', $request->date_fin_filter);
        }

        $promotions = $query->orderBy('id', 'desc')->paginate(15);

        // Récupérer la liste des établissements pour le filtre
        $etablissements = Etablissement::select('id', 'name')->orderBy('name')->get();

        return view('promotions.index', compact('promotions', 'etablissements'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $etablissements = Etablissement::select('id', 'name')->orderBy('name')->get();
        return view('promotions.create', compact('etablissements'));
    }

    /**
     * Enregistrer une nouvelle promotion
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:200',
            'mobile' => 'required|string|max:20',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/promotions', $imageName);
        }

        $promotion = Promotion::create([
            'libelle' => $request->libelle,
            'mobile' => $request->mobile,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'image' => $imageName ?? null,
            'description' => $request->description,
            'etablissement_id' => $request->etablissement_id,
            'created_by' => auth()->id(),
            'statut' => 1,
        ]);

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion créée avec succès.');
    }

    /**
     * Afficher une promotion spécifique
     */
    public function show($id)
    {
        $promotion = Promotion::with(['etablissement', 'createdBy'])->findOrFail($id);
        return view('promotions.show', compact('promotion'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $promotion = Promotion::findOrFail($id);
        $etablissements = Etablissement::select('id', 'name')->orderBy('name')->get();
        return view('promotions.edit', compact('promotion', 'etablissements'));
    }

    /**
     * Mettre à jour une promotion
     */
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:200',
            'mobile' => 'required|string|max:20',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($promotion->image) {
                Storage::delete('public/promotions/' . $promotion->image);
            }

            $image = $request->file('image');
            $imageName = 'image-' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/promotions', $imageName);
        }

        $promotion->update([
            'libelle' => $request->libelle,
            'mobile' => $request->mobile,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'image' => $imageName ?? $promotion->image,
            'description' => $request->description,
            'etablissement_id' => $request->etablissement_id,
        ]);

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion mise à jour avec succès.');
    }

    /**
     * Supprimer une promotion
     */
    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);

        // Supprimer l'image
        if ($promotion->image) {
            Storage::delete('public/promotions/' . $promotion->image);
        }

        $promotion->delete();

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une promotion
     */
    public function toggleStatus($id)
    {
        $promotion = Promotion::findOrFail($id);
        
        $promotion->update([
            'statut' => $promotion->statut ? 0 : 1
        ]);

        $status = $promotion->statut ? 'activée' : 'désactivée';
        
        return redirect()->route('promotions.index')
            ->with('success', "Promotion {$status} avec succès.");
    }

    /**
     * API - Récupérer toutes les promotions
     */
    public function getAllPromotions()
    {
        $promotions = Promotion::with(['etablissement:id,name,logo,mobile'])
            ->active()
            ->enCours()
            ->orderBy('id', 'desc')
            ->get();

        if ($promotions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune promotion active trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des promotions actives.',
            'promotions' => $promotions,
        ], 200);
    }

    /**
     * API - Récupérer les promotions d'un établissement
     */
    public function getPromotionsByEtablissement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $promotions = Promotion::where('etablissement_id', $request->etablissement_id)
            ->active()
            ->enCours()
            ->orderBy('id', 'desc')
            ->get();

        if ($promotions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune promotion active trouvée pour cet établissement.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des promotions de l\'établissement.',
            'promotions' => $promotions,
        ], 200);
    }
}
