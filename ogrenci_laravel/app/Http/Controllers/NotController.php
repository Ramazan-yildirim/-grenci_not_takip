<?php

namespace App\Http\Controllers;

use App\Models\Not;
use Illuminate\Http\Request;

class NotController extends Controller
{
    public function index()
    {
        return Not::with(['ogrenci', 'ders'])->paginate();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ogrenci_id' => ['required', 'integer', 'exists:ogrenciler,id'],
            'ders_id' => ['required', 'integer', 'exists:dersler,id'],
            'not' => ['required', 'numeric'],
        ]);

        $not = Not::create($data);
        return response()->json($not->load(['ogrenci', 'ders']), 201);
    }

    public function show(Not $not)
    {
        return $not->load(['ogrenci', 'ders']);
    }

    public function update(Request $request, Not $not)
    {
        $data = $request->validate([
            'ogrenci_id' => ['sometimes', 'required', 'integer', 'exists:ogrenciler,id'],
            'ders_id' => ['sometimes', 'required', 'integer', 'exists:dersler,id'],
            'not' => ['sometimes', 'required', 'numeric'],
        ]);

        $not->update($data);
        return $not->load(['ogrenci', 'ders']);
    }

    public function destroy(Not $not)
    {
        $not->delete();
        return response()->noContent();
    }
}
