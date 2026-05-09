@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="nav-icon i-Pen-2"></i>Modifier la visite technique
                </h4>
                <form action="{{ route('update-visite-technique', $visite->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="commune_id" class="form-label">Commune <span class="text-danger">*</span></label>
                                <select class="form-control @error('commune_id') is-invalid @enderror" 
                                       id="commune_id" name="commune_id" required>
                                    @foreach ($communes as $commune)
                                        <option value="{{ $commune->id }}" {{ $visite->commune_id == $commune->id ? 'selected' : '' }}>
                                            {{ $commune->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commune_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ $visite->email }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                  id="adresse" name="adresse" rows="2">{{ $visite->adresse }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="contacts" class="form-label">Contacts</label>
                        <input type="text" class="form-control @error('contacts') is-invalid @enderror" 
                               id="contacts" name="contacts" value="{{ $visite->contacts }}">
                        @error('contacts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse_map" class="form-label">Adresse Google Maps</label>
                        <input type="url" class="form-control @error('adresse_map') is-invalid @enderror" 
                               id="adresse_map" name="adresse_map" value="{{ $visite->adresse_map }}">
                        @error('adresse_map')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL complète de Google Maps</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF (max 10MB)</div>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               id="logo" name="logo" accept="image/*">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($visite->logo)
                        <img src="{{ asset('images/visite_technique/' . $visite->logo) }}" 
                             alt="Logo" 
                             class="rounded mt-2" 
                             width="100" 
                             height="100"
                             style="object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center mt-2" 
                                style="width: 100px; height: 100px;">
                                <i class="nav-icon i-Car text-white"></i>
                            </div>
                        @endif
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="nav-icon i-Save"></i>Modifier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
