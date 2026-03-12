@php
    $renderedNewsletter = '';
    if (!empty($endTermUpdate->content_body)) {
        $renderedNewsletter = $endTermUpdate->content_format === 'html'
            ? Purify::clean($endTermUpdate->content_body)
            : nl2br(e($endTermUpdate->content_body));
    }
@endphp

@if(
    $endTermUpdate &&
    (
        !empty($endTermUpdate->title) ||
        !empty($endTermUpdate->content_body) ||
        !empty($endTermUpdate->newsletter_url) ||
        !empty($endTermUpdate->next_resumption_date) ||
        !empty($endTermUpdate->fee_deadline) ||
        !empty($endTermUpdate->resumption_note)
    )
)
    <div class="report-section-title">End of Term</div>

    <div class="report-news-card">
        <div class="report-news-kicker">End of Term Update</div>
        <div class="report-news-title">{{ $endTermUpdate->title ?: ($semester->semester_name . ' Newsletter') }}</div>
        @if($renderedNewsletter)
            <div class="report-news-content">{!! $renderedNewsletter !!}</div>
        @endif
        @if(!empty($endTermUpdate->newsletter_url))
            <div class="mt-3">
                <a class="report-news-link" href="{{ $endTermUpdate->newsletter_url }}" target="_blank" rel="noopener">
                    Open Full Newsletter
                </a>
            </div>
        @endif
    </div>

    @if(!empty($endTermUpdate->next_resumption_date) || !empty($endTermUpdate->fee_deadline) || !empty($endTermUpdate->resumption_note))
        <div class="report-next-term-card">
            <div class="report-next-term-label">{{ $endTermUpdate->next_term_label ?: 'Next Term' }}</div>
            @if(!empty($endTermUpdate->next_resumption_date))
                <div class="report-next-term-line">
                    Resumption date: <strong>{{ $endTermUpdate->next_resumption_date->format('l, d F Y') }}</strong>
                </div>
            @endif
            @if(!empty($endTermUpdate->fee_deadline))
                <div class="report-next-term-subline">
                    Fees deadline: {{ $endTermUpdate->fee_deadline->format('l, d F Y') }}
                </div>
            @endif
            @if(!empty($endTermUpdate->resumption_note))
                <div class="report-next-term-note">{{ nl2br(e($endTermUpdate->resumption_note)) }}</div>
            @endif
        </div>
    @endif
@endif
