<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;

class EmailTemplateService
{
    /**
     * Sendet eine E-Mail basierend auf einem Template
     */
    public function sendTemplatedEmail(string $templateKey, string $toEmail, array $variables = [], ?string $toName = null): bool
    {
        $template = EmailTemplate::getByKey($templateKey);

        if (!$template) {
            throw new \Exception("E-Mail-Template mit Key '{$templateKey}' nicht gefunden.");
        }

        $rendered = $template->render($variables);

        $mailable = new TemplatedMail($rendered['subject'], $rendered['content']);

        try {
            Mail::to($toEmail, $toName)->send($mailable);
            return true;
        } catch (\Exception $e) {
            Log::error("Fehler beim Senden der Template-E-Mail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Erstellt Standard-Templates für die Anwendung
     */
    public function createDefaultTemplates(): void
    {
        $templates = [
            [
                'key' => 'aussteller_absage',
                'name' => 'Aussteller-Absage',
                'subject' => 'Absage für Ihre Standanfrage - {{markt_name}}',
                'description' => 'E-Mail-Template für Absagen an Aussteller',
                'content' => $this->getAusstellerAbsageTemplate(),
                'available_variables' => [
                    ['variable' => 'aussteller_name', 'description' => 'Name des Ausstellers'],
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'eingereicht_am', 'description' => 'Datum der Einreichung'],
                    ['variable' => 'firma', 'description' => 'Firmenname'],
                    ['variable' => 'warenangebot', 'description' => 'Warenangebot des Ausstellers'],
                ],
            ],
            [
                'key' => 'aussteller_bestaetigung',
                'name' => 'Aussteller-Bestätigung',
                'subject' => 'Bestätigung Ihrer Anmeldung - {{markt_name}}',
                'description' => 'E-Mail-Template für Bestätigungen an Aussteller',
                'content' => $this->getAusstellerBestaetigungTemplate(),
                'available_variables' => [
                    ['variable' => 'aussteller_name', 'description' => 'Name des Ausstellers'],
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'termine', 'description' => 'Termine des Marktes'],
                    ['variable' => 'standplatz', 'description' => 'Zugewiesener Standplatz'],
                ],
            ],
            [
                'key' => 'rechnung_versand',
                'name' => 'Rechnung versenden',
                'subject' => 'Ihre Rechnung {{rechnung_nummer}} - {{markt_name}}',
                'description' => 'E-Mail-Template für Rechnungsversand',
                'content' => $this->getRechnungVersandTemplate(),
                'available_variables' => [
                    ['variable' => 'aussteller_name', 'description' => 'Name des Ausstellers'],
                    ['variable' => 'rechnung_nummer', 'description' => 'Rechnungsnummer'],
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'betrag', 'description' => 'Rechnungsbetrag'],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            EmailTemplate::updateOrCreate(
                ['key' => $templateData['key']],
                $templateData
            );
        }
    }

    private function getAusstellerAbsageTemplate(): string
    {
        return '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Absage für Ihre Standanfrage</h2>
    
    <p>Sehr geehrte Damen und Herren,</p>
    
    <p>vielen Dank für Ihr Interesse an unserem Markt <strong>{{markt_name}}</strong>.</p>
    
    <p>Leider können wir Ihr Angebot nicht berücksichtigen.</p>
    
    <p>Aufgrund der begrenzten Platzkapazität und der hohen Nachfrage können wir Ihnen leider keinen Stand anbieten. Wir bedauern diese Entscheidung sehr.</p>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
        <h3>Ihre Anfrage im Überblick:</h3>
        <p><strong>Markt:</strong> {{markt_name}}</p>
        <p><strong>Eingereicht am:</strong> {{eingereicht_am}}</p>
        <p><strong>Firma:</strong> {{firma}}</p>
        <p><strong>Warenangebot:</strong> {{warenangebot}}</p>
    </div>
    
    <p>Wir möchten Sie gerne über zukünftige Märkte informieren und laden Sie herzlich ein, sich auch in Zukunft bei uns zu bewerben.</p>
    
    <p>Sollten Sie Fragen haben, stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Wir wünschen Ihnen alles Gute und hoffen auf eine zukünftige Zusammenarbeit.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Markt-Team</p>
</div>';
    }

    private function getAusstellerBestaetigungTemplate(): string
    {
        return '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Bestätigung Ihrer Anmeldung</h2>
    
    <p>Sehr geehrte{{aussteller_name}},</p>
    
    <p>wir freuen uns, Ihnen mitteilen zu können, dass Ihre Anmeldung für den Markt <strong>{{markt_name}}</strong> erfolgreich war!</p>
    
    <div style="background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0;">
        <h3>Ihre Buchungsdetails:</h3>
        <p><strong>Markt:</strong> {{markt_name}}</p>
        <p><strong>Termine:</strong> {{termine}}</p>
        <p><strong>Standplatz:</strong> {{standplatz}}</p>
    </div>
    
    <p>Weitere Informationen und Unterlagen erhalten Sie in den kommenden Tagen.</p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Markt-Team</p>
</div>';
    }

    private function getRechnungVersandTemplate(): string
    {
        return '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Ihre Rechnung {{rechnung_nummer}}</h2>
    
    <p>Sehr geehrte{{aussteller_name}},</p>
    
    <p>anbei erhalten Sie Ihre Rechnung für den Markt <strong>{{markt_name}}</strong>.</p>
    
    <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
        <h3>Rechnungsdetails:</h3>
        <p><strong>Rechnungsnummer:</strong> {{rechnung_nummer}}</p>
        <p><strong>Betrag:</strong> {{betrag}}</p>
        <p><strong>Markt:</strong> {{markt_name}}</p>
    </div>
    
    <p>Die Rechnung finden Sie als PDF-Anhang.</p>
    
    <p>Bei Fragen zur Rechnung stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Markt-Team</p>
</div>';
    }
}

/**
 * Mailable-Klasse für Template-E-Mails
 */
class TemplatedMail extends Mailable
{
    public $subject;
    public $htmlContent;

    public function __construct(string $subject, string $content)
    {
        $this->subject = $subject;
        $this->htmlContent = $content;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->html($this->htmlContent);
    }
}
