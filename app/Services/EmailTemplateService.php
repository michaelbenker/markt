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
     * @deprecated Verwende stattdessen MailService::send()
     */
    public function sendTemplatedEmail(string $templateKey, string $toEmail, array $variables = [], ?string $toName = null): bool
    {
        // Weiterleitung an den neuen MailService
        $mailService = new MailService();
        return $mailService->send($templateKey, $toEmail, $variables, $toName);
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

    /**
     * Rendert ein Template mit Fallback auf Dateisystem-Templates
     */
    public function renderTemplate(string $key, array $variables = []): array
    {
        // 1. Versuche Datenbank-Template
        $template = EmailTemplate::getByKey($key);

        $rendered = $template->render($variables);
        return [
            'hasTemplate' => true,
            'source' => 'database',
            'subject' => $rendered['subject'],
            'content' => $rendered['content']
        ];

        // 3. Kein Template gefunden
        throw new \Exception("E-Mail-Template '{$key}' weder in Datenbank noch als Blade-Template gefunden");
    }

    private function getAusstellerAbsageTemplate(): string
    {
        return '# Absage für Ihre Standanfrage

Sehr geehrte Damen und Herren,

vielen Dank für Ihr Interesse an unserem Markt **{{markt_name}}**.

Leider können wir Ihr Angebot nicht berücksichtigen.

Aufgrund der begrenzten Platzkapazität und der hohen Nachfrage können wir Ihnen leider keinen Stand anbieten. Wir bedauern diese Entscheidung sehr.

> **Ihre Anfrage im Überblick:**  
> **Markt:** {{markt_name}}  
> **Termin:** {{termin}}
> **Eingereicht am:** {{eingereicht_am}}  
> **Firma:** {{firma}}  
> **Warenangebot:** {{warenangebot}}

Wir möchten Sie gerne über zukünftige Märkte informieren und laden Sie herzlich ein, sich auch in Zukunft bei uns zu bewerben.

Sollten Sie Fragen haben, stehen wir Ihnen gerne zur Verfügung.

Wir wünschen Ihnen alles Gute und hoffen auf eine zukünftige Zusammenarbeit.

Mit freundlichen Grüßen  
Ihr Markt-Team';
    }

    private function getAusstellerBestaetigungTemplate(): string
    {
        return '# Bestätigung Ihrer Anmeldung

Sehr geehrte{{aussteller_name}},

wir freuen uns, Ihnen mitteilen zu können, dass Ihre Anmeldung für den Markt **{{markt_name}}** erfolgreich war!

> **Ihre Buchungsdetails:**
> 
> **Markt:** {{markt_name}}  
> **Termin:** {{termine}}  
> **Standplatz:** {{standplatz}}

Weitere Informationen und Unterlagen finden Sie im Anhang.

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen  
Ihr Markt-Team';
    }

    private function getRechnungVersandTemplate(): string
    {
        return '# Ihre Rechnung {{rechnung_nummer}}

Sehr geehrte{{aussteller_name}},

anbei erhalten Sie Ihre Rechnung für den Markt **{{markt_name}}**.

> **Rechnungsdetails:**
> 
> **Rechnungsnummer:** {{rechnung_nummer}}  
> **Betrag:** {{betrag}}  
> **Markt:** {{markt_name}}

Die Rechnung finden Sie als PDF-Anhang.

Bei Fragen zur Rechnung stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen  
Ihr Markt-Team';
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
