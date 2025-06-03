<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    <p style="text-align: left; font-size: 0.7em;">
                        <b>{{ $stammdaten['allgemein']['name'] }}</b> · {{ $stammdaten['allgemein']['abteilung'] }} · {{ $stammdaten['ansprechpartner']['name'] }}
                        <br>{{ $stammdaten['allgemein']['strasse'] }} · {{ $stammdaten['allgemein']['plz'] }} {{ $stammdaten['allgemein']['ort'] }} · Tel. <a href="tel:{{ preg_replace('/\D+/', '', $stammdaten['ansprechpartner']['telefon']) }}">{{ $stammdaten['ansprechpartner']['telefon'] }}</a>
                        <br>E-Mail: <a href="mailto:{{ $stammdaten['ansprechpartner']['email'] }}">{{ $stammdaten['ansprechpartner']['email'] }}</a> · <a href="{{ $stammdaten['allgemein']['web'] }}">{{ $stammdaten['allgemein']['web'] }}</a>
                        <br>Werkleitung: {{ $stammdaten['allgemein']['leitung'] }} · Sitz: {{ $stammdaten['allgemein']['sitz'] }}; Registergericht: {{ $stammdaten['allgemein']['registergericht'] }}
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>