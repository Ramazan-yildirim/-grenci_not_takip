<?php

namespace App\Http\Controllers;

use App\Models\Ders;
use Illuminate\Http\Request;

class DersController extends Controller
{
    public function index(Request $request)
    {
        $query = Ders::with(['egitmen']);

        if ($search = $request->string('q')->trim("\"'")->toString()) {
            $query->where('ad', 'like', "%{$search}%");
        }

        if ($d = $request->string('d')->trim("\"'")->toString()) { // alias
            $query->where('ad', 'like', "%{$d}%");
        }

        if ($ad = $request->string('ad')->trim("\"'")->toString()) {
            $query->where('ad', 'like', "%{$ad}%");
        }

        if ($egitmenId = $request->input('egitmen_id')) {
            $query->where('egitmen_id', $egitmenId);
        }

        if ($sort = $request->string('sort')->toString()) {
            foreach (explode(',', $sort) as $field) {
                $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                $name = ltrim($field, '-');
                if (in_array($name, ['id','ad','egitmen_id'])) {
                    $query->orderBy($name, $direction);
                }
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'egitmen_id' => ['required', 'integer', 'exists:egitmenler,id'],
        ]);

        $ders = Ders::create($data);
        return response()->json($ders->load('egitmen'), 201);
    }

    public function show(Ders $ders)
    {
        return $ders->load(['egitmen', 'notlar']);
    }

    public function update(Request $request, Ders $ders)
    {
        $data = $request->validate([
            'ad' => ['sometimes', 'required', 'string', 'max:255'],
            'egitmen_id' => ['sometimes', 'required', 'integer', 'exists:egitmenler,id'],
        ]);

        $ders->update($data);
        return $ders->load('egitmen');
    }

    public function destroy(Ders $ders)
    {
        $ders->delete();
        return response()->noContent();
    }
}
