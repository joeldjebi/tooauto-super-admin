<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Etablissement;
use App\Models\Super;
use App\Models\Type_etablissement;
use App\Models\User;
use App\Models\Professionnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexService()
    {
        $data['title'] ='Liste des services';
        $data['menu'] ='service';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        $data["services"] = Service::orderBy('id', 'desc')
        ->with('etablissement', 'professionnel')
        ->get();

        // dd($data["services"]);
        
        return view('services.index',$data);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }
}