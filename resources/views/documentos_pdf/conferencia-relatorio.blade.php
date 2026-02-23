<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        /* ─── Reset / Base ──────────────────────────────────────────────── */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2, h3, h4 {
            margin: 4px 0;
            font-weight: 600;
        }

        p { margin: 2px 0; }

        /* ─── Cabeçalho ─────────────────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10.5px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 4px 6px;
        }

        .header-empresa {
            border: 1px solid #ccc;
            background: #f9f9f9;
            text-align: center;
            line-height: 1.4;
        }

        /* ─── Título do relatório ───────────────────────────────────────── */
        .titulo-relatorio {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .subtitulo {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 14px;
        }

        /* ─── Dados do orçamento ────────────────────────────────────────── */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #aaa;
            border-radius: 3px;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .info-box td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-box .label {
            font-weight: bold;
            color: #000;
            width: 12%;
            white-space: nowrap;
            background: #f2f2f2;
        }

        .info-box .value {
            color: #444;
        }

        .info-box tr:nth-child(even) td.label {
            background: #ebebeb;
        }

        /* ─── Card de conferência ───────────────────────────────────────── */
        .conf-card {
            border: 1.5px solid #555;
            border-radius: 4px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .conf-card-header {
            background: #444;
            color: #fff;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: bold;
        }

        .conf-card-header span {
            font-weight: normal;
            font-size: 10px;
            color: #ddd;
        }

        .conf-card-body {
            padding: 6px 8px;
        }

        .conf-meta {
            font-size: 9.5px;
            color: #555;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }

        /* ─── Embalagem ─────────────────────────────────────────────────── */
        .embalagem-box {
            background: #f0f7ff;
            border: 1px solid #b8d4f0;
            border-radius: 3px;
            padding: 5px 8px;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .embalagem-box strong {
            color: #1a56a0;
        }

        /* ─── Tabela de itens ───────────────────────────────────────────── */
        .itens-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 8px;
        }

        .itens-table th {
            background: #e8e8e8;
            border: 1px solid #bbb;
            padding: 4px 5px;
            text-align: center;
            font-weight: bold;
        }

        .itens-table td {
            border: 1px solid #ccc;
            padding: 4px 5px;
            vertical-align: top;
        }

        .itens-table tr:nth-child(even) td {
            background: #fafafa;
        }

        .status-ok {
            color: #166534;
            font-weight: bold;
        }

        .status-divergente {
            color: #991b1b;
            font-weight: bold;
        }

        .status-pendente {
            color: #92400e;
            font-weight: bold;
        }

        .motivo-text {
            font-size: 9px;
            color: #b45309;
            font-style: italic;
            display: block;
            margin-top: 2px;
        }

        /* ─── Fotos ─────────────────────────────────────────────────────── */
        .fotos-section {
            margin-top: 4px;
        }

        .fotos-section p {
            font-size: 9.5px;
            font-weight: bold;
            color: #444;
            margin-bottom: 4px;
        }

        .fotos-grid {
            /* Simula grid com tabela para DomPDF */
        }

        .fotos-grid table {
            border-collapse: collapse;
            width: 100%;
        }

        .fotos-grid td {
            border: none;
            padding: 3px;
            vertical-align: top;
            width: 25%;
            text-align: center;
        }

        .foto-thumb {
            width: 110px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .foto-legenda {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
            display: block;
            word-break: break-word;
        }

        /* ─── Totalizador final ─────────────────────────────────────────── */
        .resumo-final {
            border: 1.5px solid #333;
            border-radius: 4px;
            padding: 8px 10px;
            margin-top: 16px;
            font-size: 10px;
            background: #f9f9f9;
        }

        .resumo-final h3 {
            font-size: 12px;
            margin-bottom: 6px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .resumo-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .resumo-table td {
            padding: 3px 5px;
            border-bottom: 1px solid #eee;
        }

        .resumo-table .r-label { font-weight: bold; width: 55%; }
        .resumo-table .r-val   { text-align: center; }

        .badge-ok   { color: #166534; font-weight: bold; }
        .badge-div  { color: #991b1b; font-weight: bold; }
        .badge-pend { color: #92400e; font-weight: bold; }

        /* ─── Rodapé ────────────────────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }

        /* ─── Evitar quebras dentro de blocos ──────────────────────────── */
        table, tr, td { page-break-inside: avoid; }
        .conf-card     { page-break-inside: avoid; }
    </style>
</head>

<body>

    {{-- ═══ CABEÇALHO (idêntico ao orçamento) ════════════════════════════════ --}}
    <table class="header-table">
        <tr>
            <td style="width:4rem;">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width:70px;">
            </td>
            <td class="header-empresa">
                <p>
                    <strong>ACAV</strong> - Comércio de Acessórios LTDA<br /><br />
                    R. São Luís do Paraitinga, 1338 - Jardim do Trevo - Campinas - SP - CEP: 13030-105<br />
                    (19) 3273-3783 (19) 3274-1717 &nbsp;
                    <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="ec8f8382988d9883ac8d8f8d9a8d8f899f9f839e85839fc28f8381c28e9e">[email&#160;protected]</a> &nbsp;|&nbsp; www.acavacessorios.com.br
                </p>
            </td>
            <td style="width:4rem;"></td>
        </tr>
    </table>

    {{-- ═══ TÍTULO ════════════════════════════════════════════════════════════ --}}
    <div class="titulo-relatorio">
        Relatório de Conferência — Orçamento nº {{ $orcamento->id }}
        @if ($orcamento->versao > 1)
            (Rev. {{ $orcamento->versao }})
        @endif
    </div>
    <div class="subtitulo">
        Gerado em {{ $geradoEm }} &nbsp;•&nbsp;
        Total de conferências: {{ $conferencias->count() }}
    </div>

    {{-- ═══ DADOS DO ORÇAMENTO ════════════════════════════════════════════════ --}}
    <table class="info-box">
        <tr>
            <td class="label">Cliente:</td>
            <td class="value">{{ optional($orcamento->cliente)->nome ?? '—' }}</td>
            <td class="label">Telefone:</td>
            <td class="value">{{ optional($orcamento->cliente)->telefone ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Obra:</td>
            <td class="value" colspan="3">{{ $orcamento->obra ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Status orçamento:</td>
            <td class="value">{{ $orcamento->status ?? '—' }}</td>
            <td class="label">Workflow:</td>
            <td class="value">{{ $orcamento->workflow_status ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Vendedor:</td>
            <td class="value">{{ optional($orcamento->vendedor)->name ?? '—' }}</td>
            <td class="label">Validade:</td>
            <td class="value">
                {{ $orcamento->validade
                    ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y')
                    : '—' }}
            </td>
        </tr>
    </table>

    {{-- ═══ CONFERÊNCIAS ══════════════════════════════════════════════════════ --}}
    @foreach ($conferencias as $conf)
        @php
            $totalItens = $conf->itens->count();
            $totalOk    = $conf->itens->filter(fn($i) => $i->conferido_por_id && $i->status === 'ok')->count();
            $totalDiv   = $conf->itens->where('status', 'divergente')->count();
            $totalPend  = $conf->itens->whereNull('conferido_por_id')->count();
            $totalFotos = $conf->itens->sum(fn($i) => $i->fotos->count());
        @endphp

        <div class="conf-card">

            {{-- Cabeçalho do card --}}
            <div class="conf-card-header">
                Conferência #{{ $conf->id }}
                &nbsp;—&nbsp;
                Status:
                @if ($conf->status === 'concluida')
                    ✔ Concluída
                @elseif ($conf->status === 'em_conferencia')
                    ⏳ Em andamento
                @else
                    {{ $conf->status }}
                @endif
                <span style="float:right;">
                    Lote de Separação #{{ $conf->picking_batch_id }}
                </span>
            </div>

            <div class="conf-card-body">

                {{-- Meta --}}
                <div class="conf-meta">
                    @if ($conf->conferente)
                        Conferente: <strong>{{ $conf->conferente->name }}</strong> &nbsp;•&nbsp;
                    @endif
                    Início: {{ optional($conf->started_at)->format('d/m/Y H:i') ?? '—' }}
                    @if ($conf->finished_at)
                        &nbsp;•&nbsp; Conclusão: {{ $conf->finished_at->format('d/m/Y H:i') }}
                    @endif
                    &nbsp;•&nbsp;
                    Itens: {{ $totalItens }}
                    &nbsp;|&nbsp;
                    <span class="badge-ok">OK: {{ $totalOk }}</span>
                    &nbsp;|&nbsp;
                    <span class="badge-div">Divergentes: {{ $totalDiv }}</span>
                    @if ($totalPend > 0)
                        &nbsp;|&nbsp;
                        <span class="badge-pend">Pendentes: {{ $totalPend }}</span>
                    @endif
                    @if ($totalFotos > 0)
                        &nbsp;|&nbsp; 📷 {{ $totalFotos }} foto(s)
                    @endif
                </div>

                {{-- Embalagem --}}
                @if ($conf->qtd_caixas || $conf->qtd_sacos || $conf->qtd_sacolas || $conf->outros_embalagem)
                    <div class="embalagem-box">
                        <strong>📦 Embalagem:</strong>&nbsp;
                        @php $emb = []; @endphp
                        @if ($conf->qtd_caixas)   @php $emb[] = $conf->qtd_caixas . ' caixa(s)'; @endphp @endif
                        @if ($conf->qtd_sacos)    @php $emb[] = $conf->qtd_sacos  . ' saco(s)';  @endphp @endif
                        @if ($conf->qtd_sacolas)  @php $emb[] = $conf->qtd_sacolas. ' sacola(s)';@endphp @endif
                        @if ($conf->outros_embalagem) @php $emb[] = 'Outros: ' . $conf->outros_embalagem; @endphp @endif
                        {{ implode(' • ', $emb) }}
                    </div>
                @else
                    <div class="embalagem-box" style="background:#fff8e1; border-color:#f6c90e;">
                        <strong>📦 Embalagem:</strong>&nbsp;
                        <span style="color:#92400e;">Não informada</span>
                    </div>
                @endif

                {{-- Tabela de itens --}}
                <table class="itens-table">
                    <thead>
                        <tr>
                            <th style="width:30%;">Produto</th>
                            <th style="width:10%;">Separada</th>
                            <th style="width:10%;">Conferida</th>
                            <th style="width:10%;">Divergência</th>
                            <th style="width:12%;">Status</th>
                            <th style="width:28%;">Motivo / Conferente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conf->itens as $item)
                            <tr>
                                <td>
                                    {{ $item->produto->nome ?? '—' }}
                                    @if ($item->produto->sku ?? null)
                                        <br /><span style="font-size:8.5px; color:#888;">SKU: {{ $item->produto->sku }}</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    {{ rtrim(rtrim(number_format($item->qty_separada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td style="text-align:center;">
                                    {{ rtrim(rtrim(number_format($item->qty_conferida, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td style="text-align:center;">
                                    @if (abs($item->divergencia) >= 0.001)
                                        <span class="status-divergente">
                                            {{ rtrim(rtrim(number_format($item->divergencia, 3, ',', '.'), '0'), ',') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @php
                                        // Se o item nunca foi conferido, força exibição como pendente
                                        // independente do valor salvo no banco
                                        $statusExibir = $item->conferido_por_id
                                            ? $item->status
                                            : 'pendente';
                                    @endphp
                                    @if ($statusExibir === 'ok')
                                        <span class="status-ok">✔ OK</span>
                                    @elseif ($statusExibir === 'divergente')
                                        <span class="status-divergente">⚠ Divergente</span>
                                    @else
                                        <span class="status-pendente">⏳ Não conferido</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->motivo_divergencia)
                                        <span class="motivo-text">{{ $item->motivo_divergencia }}</span>
                                    @endif
                                    @if ($item->conferidoPor)
                                        <span style="font-size:8.5px; color:#555;">
                                            Por: {{ $item->conferidoPor->name }}
                                            @if ($item->conferido_em)
                                                em {{ $item->conferido_em->format('d/m H:i') }}
                                            @endif
                                        </span>
                                    @endif
                                    @if (!$item->motivo_divergencia && !$item->conferidoPor)
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- ── FOTOS POR ITEM ──────────────────────────────────────── --}}
                @foreach ($conf->itens as $item)
                    @if ($item->fotos->isNotEmpty())
                        <div class="fotos-section" style="margin-top: 8px; page-break-inside: avoid;">
                            <p>📷 Fotos — {{ $item->produto->nome ?? 'Item #'.$item->id }}
                               ({{ $item->fotos->count() }} foto{{ $item->fotos->count() > 1 ? 's' : '' }})
                            </p>

                            {{-- Grid de fotos: 4 por linha usando tabela --}}
                            @php
                                $chunks = $item->fotos->chunk(4);
                            @endphp
                            @foreach ($chunks as $chunk)
                                <table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
                                    <tr>
                                        @foreach ($chunk as $foto)
                                            <td style="width:25%; text-align:center; padding:3px; vertical-align:top; border:none;">
                                                @if ($foto->base64)
                                                    <img src="{{ $foto->base64 }}"
                                                         style="width:110px; height:90px; object-fit:cover;
                                                                border:1px solid #ccc; border-radius:3px;" />
                                                @else
                                                    <div style="width:110px; height:90px; border:1px dashed #ccc;
                                                                border-radius:3px; background:#f5f5f5;
                                                                display:flex; align-items:center; justify-content:center;">
                                                        <span style="font-size:8px; color:#aaa;">Foto indisponível</span>
                                                    </div>
                                                @endif
                                                @if ($foto->legenda)
                                                    <span style="font-size:8px; color:#666; display:block; margin-top:2px;">
                                                        {{ $foto->legenda }}
                                                    </span>
                                                @endif
                                                @if ($foto->enviadoPor ?? null)
                                                    <span style="font-size:7.5px; color:#999; display:block;">
                                                        {{ $foto->enviadoPor->name ?? '' }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Preenche células vazias para completar a linha --}}
                                        @for ($i = $chunk->count(); $i < 4; $i++)
                                            <td style="width:25%; border:none;"></td>
                                        @endfor
                                    </tr>
                                </table>
                            @endforeach
                        </div>
                    @endif
                @endforeach

            </div>{{-- /conf-card-body --}}
        </div>{{-- /conf-card --}}
    @endforeach

    {{-- ═══ RESUMO FINAL (todas as conferências) ══════════════════════════════ --}}
    @php
        $todosItens  = $conferencias->flatMap(fn($c) => $c->itens);
        $sumFotos    = $todosItens->sum(fn($i) => $i->fotos->count());
        $confConcl   = $conferencias->where('status', 'concluida')->count();
        $confAndando = $conferencias->whereNotIn('status', ['concluida','cancelada'])->count();

        /*
         * O resumo deve refletir o estado FINAL de cada produto do orçamento,
         * considerando a última vez que cada produto foi conferido em qualquer conferência.
         *
         * Lógica: para cada produto_id único, pega o registro mais recente
         * (maior id) entre todas as conferências e avalia seu status real.
         */
        $ultimaOcorrenciaPorProduto = $todosItens
            ->sortByDesc('id')                      // mais recente primeiro
            ->unique('produto_id');                  // mantém só a primeira (= mais recente) de cada produto

        $sumOk    = $ultimaOcorrenciaPorProduto
            ->filter(fn($i) => $i->conferido_por_id && $i->status === 'ok')
            ->count();

        $sumDiv   = $ultimaOcorrenciaPorProduto
            ->filter(fn($i) => $i->status === 'divergente')
            ->count();

        // Pendente = produto que, na sua ocorrência mais recente, nunca foi conferido
        $sumPend  = $ultimaOcorrenciaPorProduto
            ->filter(fn($i) => is_null($i->conferido_por_id))
            ->count();

        $totalProdutosUnicos = $ultimaOcorrenciaPorProduto->count();
    @endphp

    <div class="resumo-final">
        <h3>Resumo Geral — Todas as Conferências</h3>
        <table class="resumo-table">
            <tr>
                <td class="r-label">Total de conferências</td>
                <td class="r-val">{{ $conferencias->count() }}</td>
                <td class="r-label">Conferências realizadas</td>
                <td class="r-val">{{ $confConcl }}</td>
            </tr>
            <tr>
                <td class="r-label">Em andamento</td>
                <td class="r-val">{{ $confAndando }}</td>
                <td class="r-label">Produtos distintos no orçamento</td>
                <td class="r-val">{{ $totalProdutosUnicos }}</td>
            </tr>
            <tr>
                <td class="r-label"><span class="badge-ok">Produtos OK (última conferência)</span></td>
                <td class="r-val"><span class="badge-ok">{{ $sumOk }}</span></td>
                <td class="r-label"><span class="badge-div">Produtos com divergência</span></td>
                <td class="r-val"><span class="badge-div">{{ $sumDiv }}</span></td>
            </tr>
            @if ($sumPend > 0)
                <tr>
                    <td class="r-label"><span class="badge-pend">Produtos não conferidos</span></td>
                    <td class="r-val"><span class="badge-pend">{{ $sumPend }}</span></td>
                    <td class="r-label"></td>
                    <td class="r-val"></td>
                </tr>
            @endif
            <tr>
                <td class="r-label">Total de fotos registradas</td>
                <td class="r-val">{{ $sumFotos }}</td>
                <td class="r-label"></td>
                <td class="r-val"></td>
            </tr>
        </table>
    </div>

    {{-- ═══ RODAPÉ ═════════════════════════════════════════════════════════════ --}}
    <div class="footer">
        Relatório de Conferência — Orçamento nº {{ $orcamento->id }} &nbsp;•&nbsp;
        Gerado em {{ $geradoEm }}&nbsp;•&nbsp;
        © {{ date('Y') }} {{ config('app.name') }} — Todos os direitos reservados.
    </div>

</body>
</html>