<?php

namespace App\Http\Controllers\Admin;

use App\Models\QualitySetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QualitySettingController extends Controller
{
    public function index()
    {
        $settings = QualitySetting::all();
        return view('admin.quality-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.red_threshold' => 'required|integer|min:0',
            'settings.*.yellow_threshold' => 'required|integer|min:0',
            'settings.*.green_threshold' => 'required|integer|min:0',
        ]);

        foreach ($request->settings as $id => $data) {
            QualitySetting::where('id', $id)->update($data);
        }

        return redirect()->back()->with('success', 'Quality settings updated successfully.');
    }
}
