<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * One-click demo-data generators for presentations. Each runs the relevant
 * idempotent seeder (deletes only its own demo rows, keeps real data).
 */
class DemoController extends Controller
{
    public function equipment(): RedirectResponse
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\EquipmentCategorySeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\EquipmentProductSeeder', '--force' => true]);

        return back()->with('success', 'কৃষি সরঞ্জাম ডেমো ডেটা তৈরি হয়েছে (৬০+ পণ্য)।');
    }

    public function news(): RedirectResponse
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AgricultureNewsSeeder', '--force' => true]);

        return back()->with('success', 'কৃষি সংবাদ ডেমো ডেটা তৈরি হয়েছে (৫০+ সংবাদ)।');
    }
}
