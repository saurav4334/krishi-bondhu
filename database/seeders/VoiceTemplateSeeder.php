<?php

namespace Database\Seeders;

use App\Models\VoiceTemplate;
use App\Services\ProtiddhoniVoiceService;
use Illuminate\Database\Seeder;

/**
 * Seeds the canonical default voice templates (one per feature type).
 *
 * Uses updateOrCreate so re-running refreshes the default texts/DTMF to the
 * canonical set, while preserving any per-template voice_type / language_code
 * an admin may have chosen (those are not in the update payload).
 *
 * Templates for retired feature-type slugs are removed so the registry stays
 * in sync with VoiceTemplate::TYPES.
 */
class VoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $valid = array_keys(ProtiddhoniVoiceService::DEFAULTS);

        foreach (ProtiddhoniVoiceService::DEFAULTS as $type => $tpl) {
            VoiceTemplate::updateOrCreate(['type' => $type], [
                'title' => $tpl['title'] ?? (VoiceTemplate::TYPES[$type] ?? $type),
                'start_text' => $tpl['start_text'],
                'question_text' => $tpl['question_text'],
                'end_text' => $tpl['end_text'],
                'dtmf_options' => $tpl['dtmf_options'],
                'status' => true,
            ]);
        }

        // Drop templates whose feature type is no longer registered.
        VoiceTemplate::whereNotIn('type', $valid)->delete();
    }
}
