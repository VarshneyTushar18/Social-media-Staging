<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Website audit report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.45; margin: 28px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #0f172a; }
        .meta { font-size: 10px; color: #64748b; margin-bottom: 18px; }
        .score-box { float: right; text-align: right; margin-top: -8px; }
        .score { font-size: 28px; font-weight: bold; color: #4f46e5; }
        .score-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        .summary { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; margin: 12px 0 16px; border-radius: 4px; }
        h2 { font-size: 12px; margin: 16px 0 8px; }
        h2.ok { color: #047857; }
        h2.fix { color: #b45309; }
        ul { margin: 0; padding-left: 16px; }
        li { margin-bottom: 6px; }
        .fix-item { border: 1px solid #fcd34d; background: #fffbeb; padding: 8px 10px; margin-bottom: 8px; border-radius: 4px; }
        .fix-area { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #b45309; }
        .fix-issue { font-weight: bold; margin: 4px 0; }
        .fix-rec { color: #475569; }
    </style>
</head>
<body>
    <span class="score-box">
        <span class="score">{{ (int) ($result['score'] ?? 0) }}</span><br>
        <span class="score-label">{{ __('Score') }}</span>
    </span>

    <h1>{{ __('Website audit report') }}</h1>
    <p class="meta">
        <strong>{{ $result['target_label'] ?? '' }}</strong><br>
        {{ $appName }} · {{ __('Generated') }} {{ $generatedAt }}<br>
        {{ ! empty($result['used_ai']) ? __('AI review') : __('Checklist review') }}
    </p>

    @if (filled($result['summary'] ?? null))
        <div class="summary">{{ $result['summary'] }}</div>
    @endif

    @if (count($result['strengths'] ?? []) > 0)
        <h2 class="ok">{{ __('What is correct') }}</h2>
        <ul>
            @foreach ($result['strengths'] as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    @endif

    @if (count($result['fixes'] ?? []) > 0)
        <h2 class="fix">{{ __('What to correct') }}</h2>
        @foreach ($result['fixes'] as $fix)
            <div class="fix-item">
                <p class="fix-area">{{ $fix['area'] ?? __('General') }}</p>
                <p class="fix-issue">{{ $fix['issue'] ?? '' }}</p>
                @if (filled($fix['recommendation'] ?? null))
                    <p class="fix-rec">{{ $fix['recommendation'] }}</p>
                @endif
            </div>
        @endforeach
    @endif
</body>
</html>