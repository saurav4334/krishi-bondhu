<?php

namespace App\Http\Controllers;

use App\Models\MarketPrice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MarketPrice::query();

        if ($search = $request->input('search')) {
            $query->where('crop_name', 'like', "%{$search}%");
        }

        if (($district = $request->input('district')) && $district !== 'সকল') {
            $query->where(function ($q) use ($district) {
                $q->where('district', $district)->orWhere('district', 'সকল');
            });
        }

        $prices = $query->orderBy('crop_name')->get();

        return view('prices.index', [
            'prices' => $prices,
            'search' => $search,
            'district' => $district,
        ]);
    }
}
