<?php

namespace App\Http\Controllers;

use App\Models\Egitmen;
use Illuminate\Http\Request;

class EgitmenController extends Controller
{
    public function index()
    {
        return Egitmen::withCount('dersler')->paginate();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
        ]);

        $egitmen = Egitmen::create($data);
        return response()->json($egitmen, 201);
    }

    public function show(Egitmen $egitmen)
    {
        return $egitmen->load('dersler');
    }

    public function update(Request $request, Egitmen $egitmen)
    {
        $data = $request->validate([
            'ad_soyad' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $egitmen->update($data);
        return $egitmen;
    }

    public function destroy(Egitmen $egitmen)
    {
        $egitmen->delete();
        return response()->noContent();
    }
}
