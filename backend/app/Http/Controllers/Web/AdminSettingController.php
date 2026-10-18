<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminSettingController extends Controller
{
    private function getShop(): Shop
    {
        return Auth::user()->shop ?? Shop::first();
    }

    public function index()
    {
        $shop = $this->getShop();
        return view('admin.settings.index', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = $this->getShop();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:10',
            'currency_position' => 'required|in:prefix,suffix',
            'tax_number' => 'nullable|string|max:50',
            'primary_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'thermal_paper_size' => 'required|in:mm58,mm80',
            'receipt_header' => 'nullable|string|max:1000',
            'receipt_footer' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'logo_url' => 'nullable|string|max:500',
        ]);

        // Handle direct logo file upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_url'] = asset('storage/' . $path);
        } elseif ($request->filled('logo_url')) {
            $validated['logo_url'] = $request->input('logo_url');
        }

        // Unset file instance before model update
        unset($validated['logo']);

        $shop->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', "Store settings for '{$shop->name}' have been saved successfully.");
    }
}
