<?php

namespace Database\Seeders;

use App\Models\VoiceTemplate;
use App\Services\ProtiddhoniVoiceService;
use Illuminate\Database\Seeder;

class VoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProtiddhoniVoiceService::DEFAULTS as $type => $tpl) {
            VoiceTemplate::firstOrCreate(['type' => $type], [
                'title' => VoiceTemplate::TYPES[$type] ?? $type,
                'start_text' => $tpl['start_text'],
                'question_text' => $tpl['question_text'],
                'end_text' => $tpl['end_text'],
                'dtmf_options' => $tpl['dtmf_options'],
                'status' => true,
            ]);
        }
    }
}
