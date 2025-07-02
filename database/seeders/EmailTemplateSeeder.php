<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\EmailTemplateService;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emailTemplateService = new EmailTemplateService();
        $emailTemplateService->createDefaultTemplates();

        $this->command->info('E-Mail-Templates wurden erfolgreich erstellt/aktualisiert.');
    }
}
