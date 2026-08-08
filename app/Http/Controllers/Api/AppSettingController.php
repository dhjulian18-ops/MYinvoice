<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * Get settings (Public / App Client).
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => AppSetting::getAllKeyValue(),
        ]);
    }

    /**
     * Get all settings for Admin Dashboard.
     */
    public function adminIndex()
    {
        return response()->json([
            'success' => true,
            'data'    => AppSetting::getAllKeyValue(),
        ]);
    }

    /**
     * Update settings (Admin only).
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'support_name',
            'support_subtitle',
            'app_name',
            'contact_email',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                AppSetting::setValue($key, trim($value));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil diperbarui.',
            'data'    => AppSetting::getAllKeyValue(),
        ]);
    }
}
