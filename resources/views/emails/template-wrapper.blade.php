@component('mail::layout')
{{-- Header --}}
@slot('head')
<style>
    /* Template-spezifische Styles */
    .template-content {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }

    .template-content h1,
    .template-content h2,
    .template-content h3 {
        color: #2d3748;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .template-content p {
        margin-bottom: 15px;
    }

    .template-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }

    .template-content table th,
    .template-content table td {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        text-align: left;
    }

    .template-content table th {
        background-color: #f7fafc;
        font-weight: bold;
    }
</style>
@endslot

{{-- Body --}}
<div class="template-content">
    {!! $content !!}
</div>

{{-- Footer --}}
@slot('footer')
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="content-cell" align="center">
            <p style="font-size: 12px; color: #b0adc5; margin: 0; text-align: center;">
                © {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.
            </p>
            <p style="font-size: 12px; color: #b0adc5; margin: 5px 0 0 0; text-align: center;">
                Bei Fragen wenden Sie sich gerne an uns.
            </p>
        </td>
    </tr>
</table>
@endslot
@endcomponent