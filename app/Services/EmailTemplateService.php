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
                'key' => 'anfrage_bestaetigung',
                'name' => 'Anfrage-Bestätigung',
                'subject' => 'Ihre Buchungsanfrage - {{markt_name}}',
                'description' => 'Automatische Bestätigung nach Eingang einer Anfrage',
                'content' => $this->getAnfrageBestaetigungTemplate(),
                'available_variables' => [
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'termine', 'description' => 'Gewünschte Termine'],
                    ['variable' => 'name', 'description' => 'Name des Anfragenden'],
                    ['variable' => 'email', 'description' => 'E-Mail des Anfragenden'],
                    ['variable' => 'warenangebot', 'description' => 'Beschreibung des Warenangebots'],
                    ['variable' => 'bemerkung', 'description' => 'Bemerkung zur Anfrage'],
                ],
            ],
            [
                'key' => 'anfrage_warteliste',
                'name' => 'Anfrage auf Warteliste',
                'subject' => 'Ihre Anmeldung - {{markt_name}}',
                'description' => 'E-Mail wenn eine Anfrage auf die Warteliste gesetzt wird',
                'content' => $this->getAnfrageWartelisteTemplate(),
                'available_variables' => [
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'anmeldefrist', 'description' => 'Datum der Anmeldefrist'],
                    ['variable' => 'name', 'description' => 'Name des Anfragenden'],
                ],
            ],
            [
                'key' => 'anfrage_aussteller_importiert',
                'name' => 'Aussteller in Datenbank aufgenommen',
                'subject' => 'Ihre Anfrage für {{markt_name}}',
                'description' => 'E-Mail wenn Aussteller ohne Buchung in Datenbank aufgenommen wird',
                'content' => $this->getAnfrageAusstellerImportiertTemplate(),
                'available_variables' => [
                    ['variable' => 'markt_name', 'description' => 'Name des Marktes'],
                    ['variable' => 'name', 'description' => 'Name des Anfragenden'],
                ],
            ],
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

    private function getAnfrageBestaetigungTemplate(): string
    {
        return '# Ihre Buchungsanfrage

Vielen Dank für Ihre Anfrage!

Wir haben Ihre Buchungsanfrage erhalten und werden uns in Kürze bei Ihnen melden.

> **Ihre Anfrage im Überblick:**  
> **Markt:** {{markt_name}}  
> **Termine:** {{termine}}  
> **Name:** {{name}}  
> **E-Mail:** {{email}}  
> **Warenangebot:** {{warenangebot}}

{{bemerkung}}

Bei Rückfragen antworten Sie einfach auf diese E-Mail.

Mit freundlichen Grüßen  
Ihr Markt-Team';
    }

    private function getAnfrageWartelisteTemplate(): string
    {
        return '# Ihre Anmeldung für {{markt_name}}

Sehr geehrte/r {{name}},

vielen Dank für Ihre Anmeldung!

Die Anmeldefrist endet am **{{anmeldefrist}}**.

Wir melden uns bei Ihnen, sobald die Entscheidung über die Teilnehmenden getroffen ist.

Bei Rückfragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen  
Ihr Markt-Team';
    }

    private function getAnfrageAusstellerImportiertTemplate(): string
    {
        return '# Ihre Anfrage für {{markt_name}}

Sehr geehrte/r {{name}},

vielen Dank für Ihr Interesse an unserem Markt **{{markt_name}}**.

Für diesen Markt können wir Ihnen derzeit leider keine Zusage erteilen.

Wir haben Ihre Daten jedoch in unsere Datenbank aufgenommen und werden Sie gerne bei zukünftigen Märkten berücksichtigen.

Sollten sich kurzfristig Änderungen ergeben oder Plätze frei werden, melden wir uns umgehend bei Ihnen.

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen  
Ihr Markt-Team';
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

Sehr geehrte/r {{aussteller_name}},

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

Sehr geehrte/r {{aussteller_name}},

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
