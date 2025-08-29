<?php

namespace App\Http\Controllers;

use App\Models\Ogrenci;
use Illuminate\Http\Request;

class OgrenciController extends Controller
{
    public function index(Request $request)
    {
        $query = Ogrenci::query();

        if ($search = $request->string('q')->trim("\"'")->toString()) {
            $query->where('ad_soyad', 'like', "%{$search}%");
        }

        if ($ad = $request->string('ad_soyad')->trim("\"'")->toString()) {
            $query->where('ad_soyad', 'like', "%{$ad}%");
        }

        // Sıralama: ?sort=ad_soyad,-id
        if ($sort = $request->string('sort')->toString()) {
            foreach (explode(',', $sort) as $field) {
                $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                $name = ltrim($field, '-');
                if (in_array($name, ['id','ad_soyad'])) {
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
            'ad_soyad' => ['required', 'string', 'max:255'],
        ]);

        $ogrenci = Ogrenci::create($data);
        return response()->json($ogrenci, 201);
    }

    public function show(Ogrenci $ogrenci)
    {
        return $ogrenci->load('notlar');
    }

    public function update(Request $request, Ogrenci $ogrenci)
    {
        $data = $request->validate([
            'ad_soyad' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $ogrenci->update($data);
        return $ogrenci;
    }

    public function destroy(Ogrenci $ogrenci)
    {
        $ogrenci->delete();
        return response()->noContent();
    }
}
