{{--
    resources/views/documentos_pdf/partials/cabecalho.blade.php

    Variáveis obrigatórias:
        $docLabel  (string)  — ex: 'Comprovante de Pagamento', 'Orçamento'
        $docNumero (string)  — ex: '000006', '1'

    Variáveis opcionais:
        $docData   (string|null) — ex: '13/03/2026 às 16:36'  (omite a linha se null)
--}}
<table style="width:100%; font-family: DejaVu Sans, sans-serif; font-size:12px; color:#333; border-collapse:collapse; margin-bottom:10px;">
    <tr>
        {{-- Logo --}}
        <td style="width:4rem; vertical-align:middle;">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width:70px;">
        </td>

        {{-- Dados da empresa --}}
        <td style="padding:5px; border:1px solid #ccc; background-color:#f9f9f9; vertical-align:middle;">
            <div style="text-align:center;">
                <p style="margin:2px 0; font-size:11px; line-height:1.5;">
                    <strong>ACAV</strong> - Comércio de Acessórios LTDA<br/>
                    R. São Luís do Paraitinga, 1338 - Jardim do Trevo - Campinas - SP - CEP: 13030-105<br/>
                    (19) 3273-3783 &nbsp; (19) 3274-1717 &nbsp;
                    contato@acavacessorios.com.br &nbsp;|&nbsp; www.acavacessorios.com.br
                </p>
            </div>
        </td>

        {{-- Número e tipo do documento --}}
        <td style="width:6rem; text-align:center; padding-left:8px; vertical-align:middle;">
            <div style="font-size:8px; color:#888; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;">
                {{ $docLabel }}
            </div>
            <div style="font-size:18px; font-weight:bold; color:#1e3a5f; line-height:1.1;">
                #{{ $docNumero }}
            </div>
            @if(!empty($docData))
                <div style="font-size:8px; color:#666; margin-top:3px;">
                    {{ $docData }}
                </div>
            @endif
        </td>
    </tr>
</table>
