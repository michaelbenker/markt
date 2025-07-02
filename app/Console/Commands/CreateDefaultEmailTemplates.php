<?php

namespace App\Console\Commands;

use App\Services\EmailTemplateService;
use Illuminate\Console\Command;

class CreateDefaultEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-templates:create-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt die Standard-E-Mail-Templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new EmailTemplateService();

        $this->info('Erstelle Standard-E-Mail-Templates...');

        try {
            $service->createDefaultTemplates();
            $this->info('✅ Standard-E-Mail-Templates wurden erfolgreich erstellt!');
        } catch (\Exception $e) {
            $this->error('❌ Fehler beim Erstellen der Templates: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
