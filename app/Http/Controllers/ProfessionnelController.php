<?php

namespace App\Http\Controllers;

use App\Models\Professionnel;
use Illuminate\Http\Request;
use App\Models\Super;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfessionnelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] = 'Liste des professionnels';
        $data['menu'] = 'professionnel';
        $data['user'] = Super::where('id', auth()->user()->id)->first();

        $search = trim((string) request('search', ''));

        $query = Professionnel::with('etablissements:id,name,professionnel_id')->withCount('etablissements')->orderBy('id', 'desc');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nom', 'like', '%' . $search . '%')
                    ->orWhere('prenoms', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%');
            });
        }

        $data['professionnels'] = $query->paginate(25)->withQueryString();
        $data['filters'] = [
            'search' => $search,
        ];

        return view('professionnels.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'mobile' => 'required|string|max:255|unique:professionnels,mobile',
            'email' => 'nullable|email|max:255|unique:professionnels,email',
            'statut' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        if (empty($user)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $professionnel = new Professionnel();
        $professionnel->nom = $request->nom;
        $professionnel->prenoms = $request->prenoms;
        $professionnel->role = $request->role;
        $professionnel->mobile = $request->mobile;
        $professionnel->email = $request->email;
        $professionnel->created_by = $user->id;
        $professionnel->statut = $request->statut ?? 1;
        $professionnel->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Professionnel créé avec succès.');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Professionnel $professionnel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professionnel $professionnel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'mobile' => [
                'required',
                'string',
                'max:255',
                Rule::unique('professionnels', 'mobile')->ignore($id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('professionnels', 'email')->ignore($id),
            ],
            'statut' => 'nullable|boolean',
        ]);

        $professionnel = Professionnel::find($id);
        if (empty($professionnel)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Professionnel introuvable.');
            return back();
        }

        $professionnel->nom = $request->nom;
        $professionnel->prenoms = $request->prenoms;
        $professionnel->role = $request->role;
        $professionnel->mobile = $request->mobile;
        $professionnel->email = $request->email;
        $professionnel->statut = $request->statut ?? $professionnel->statut;
        $professionnel->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Professionnel mis à jour avec succès.');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $professionnel = Professionnel::withCount('etablissements')->find($id);
        if (empty($professionnel)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Professionnel introuvable.');
            return back();
        }

        if ($professionnel->etablissements_count > 0) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Impossible de supprimer ce professionnel car il est lié à un ou plusieurs établissements.');
            return back();
        }

        $professionnel->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Professionnel supprimé avec succès.');
        return back();
    }

    public function activer($id)
    {
        $professionnel = Professionnel::find($id);
        if (empty($professionnel)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Professionnel introuvable.');
            return back();
        }

        $professionnel->statut = 1;
        $professionnel->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Professionnel activé avec succès.');
        return back();
    }

    public function desactiver($id)
    {
        $professionnel = Professionnel::find($id);
        if (empty($professionnel)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Professionnel introuvable.');
            return back();
        }

        $professionnel->statut = 0;
        $professionnel->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Professionnel désactivé avec succès.');
        return back();
    }
}
