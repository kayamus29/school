@php
    $isValidHex = function ($color, $default) {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $default;
    };

    $lightenHex = function ($hex, $percent) {
        $hex = ltrim($hex, '#');
        $percent = max(0, min(1, $percent));

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = (int) round($r + (255 - $r) * $percent);
        $g = (int) round($g + (255 - $g) * $percent);
        $b = (int) round($b + (255 - $b) * $percent);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    };

    $primaryColor = $isValidHex($site_setting->primary_color ?? null, '#0d2545');
    $secondaryColor = $isValidHex($site_setting->secondary_color ?? null, '#c8962e');
    if (strtolower($secondaryColor) === '#ffffff') {
        $secondaryColor = '#c8962e';
    }

    $primarySoft = $lightenHex($primaryColor, 0.85);
    $secondarySoft = $lightenHex($secondaryColor, 0.35);
    $pageTint = $lightenHex($primaryColor, 0.94);
    $cardTint = $lightenHex($secondaryColor, 0.92);
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

    :root {
        --report-navy: {{ $primaryColor }};
        --report-gold: {{ $secondaryColor }};
        --report-gold-light: {{ $secondarySoft }};
        --report-cream: {{ $pageTint }};
        --report-white: #ffffff;
        --report-gray-light: {{ $cardTint }};
        --report-gray-mid: {{ $primarySoft }};
        --report-gray-text: #6b6453;
        --report-green: #2a7d4f;
        --report-red: #b83232;
        --report-amber: #c87b2e;
    }

    .report-view {
        font-family: 'DM Sans', sans-serif;
        color: var(--report-navy);
    }

    .report-page {
        max-width: 980px;
        margin: 0 auto;
        background: var(--report-white);
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 12px 60px rgba(13, 37, 69, 0.18);
    }

    .report-header {
        background: var(--report-navy);
        padding: 36px 48px 28px;
        display: flex;
        align-items: center;
        gap: 28px;
        position: relative;
        overflow: hidden;
    }

    .report-header::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(200, 150, 46, 0.08);
        pointer-events: none;
    }

    .report-logo-wrap {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: var(--report-white);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 3px solid var(--report-gold);
        overflow: hidden;
        color: var(--report-navy);
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
    }

    .report-logo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .report-school-info {
        flex: 1;
        min-width: 0;
    }

    .report-school-name {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--report-white);
        line-height: 1.2;
    }

    .report-school-tagline {
        color: var(--report-gold-light);
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 4px;
        font-weight: 600;
    }

    .report-school-contact {
        color: rgba(255, 255, 255, 0.58);
        font-size: 11.5px;
        margin-top: 8px;
        line-height: 1.7;
    }

    .report-badge-box {
        text-align: right;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .report-badge-label {
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--report-gold);
        font-weight: 700;
        display: block;
    }

    .report-badge-term {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        color: var(--report-white);
        font-weight: 600;
        display: block;
        margin-top: 2px;
    }

    .report-badge-session {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.55);
        display: block;
        margin-top: 2px;
    }

    .report-gold-rule {
        height: 4px;
        background: linear-gradient(90deg, var(--report-gold) 0%, var(--report-gold-light) 50%, var(--report-gold) 100%);
    }

    .report-student-banner {
        background: var(--report-gray-light);
        padding: 24px 48px;
        display: flex;
        align-items: center;
        gap: 24px;
        border-bottom: 1px solid var(--report-gray-mid);
    }

    .report-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--report-navy);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        color: var(--report-gold-light);
        font-weight: 700;
        flex-shrink: 0;
        border: 3px solid var(--report-gold);
        overflow: hidden;
    }

    .report-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .report-student-details {
        flex: 1;
        min-width: 0;
    }

    .report-student-name {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--report-navy);
    }

    .report-student-meta {
        display: flex;
        gap: 22px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .report-student-meta span {
        font-size: 12px;
        color: var(--report-gray-text);
    }

    .report-student-meta strong {
        color: var(--report-navy);
        font-weight: 600;
    }

    .report-summary-pills {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }

    .report-pill {
        text-align: center;
        background: var(--report-white);
        border-radius: 8px;
        padding: 10px 16px;
        border: 1px solid var(--report-gray-mid);
        min-width: 86px;
    }

    .report-pill-value {
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        font-weight: 700;
        display: block;
    }

    .report-pill-label {
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--report-gray-text);
        display: block;
        margin-top: 2px;
        font-weight: 700;
    }

    .report-pill.avg .report-pill-value { color: var(--report-gold); }
    .report-pill.pass .report-pill-value { color: var(--report-green); }
    .report-pill.fail .report-pill-value { color: var(--report-red); }

    .report-body {
        padding: 36px 48px;
        background: var(--report-cream);
    }

    .report-section-title {
        font-family: 'Playfair Display', serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--report-navy);
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--report-gold);
    }

    .report-table-wrap,
    .report-block,
    .report-comment-box,
    .report-info-banner {
        background: var(--report-white);
        border: 1px solid var(--report-gray-mid);
        border-radius: 10px;
    }

    .report-info-banner {
        padding: 18px 20px;
        margin-bottom: 24px;
        color: var(--report-gray-text);
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .report-table thead tr {
        background: var(--report-navy);
        color: var(--report-white);
    }

    .report-table thead th {
        padding: 11px 14px;
        text-align: left;
        font-size: 10.5px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .report-table tbody tr {
        border-bottom: 1px solid var(--report-gray-mid);
    }

    .report-table tbody tr:last-child {
        border-bottom: none;
    }

    .report-table tbody td {
        padding: 11px 14px;
        vertical-align: middle;
    }

    .report-score-cell {
        font-weight: 700;
        font-size: 14px;
    }

    .report-progress {
        width: 100px;
        height: 6px;
        background: var(--report-gray-mid);
        border-radius: 3px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }

    .report-progress-fill {
        height: 100%;
        border-radius: 3px;
    }

    .report-grade-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .report-grade-a { background: #e6f4ec; color: #2a7d4f; }
    .report-grade-b { background: #e8f0fb; color: #2a5abc; }
    .report-grade-c { background: #fdf4e3; color: #9b6b00; }
    .report-grade-d { background: #fff0e0; color: #c87b2e; }
    .report-grade-f { background: #fde8e8; color: #b83232; }

    .report-tag {
        font-size: 9px;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 1px 6px;
        border-radius: 3px;
        margin-left: 6px;
        font-weight: 700;
        vertical-align: middle;
    }

    .report-tag-core {
        background: var(--report-navy);
        color: var(--report-gold-light);
    }

    .report-tag-elective,
    .report-tag-general,
    .report-tag-optional {
        background: var(--report-gray-mid);
        color: var(--report-navy);
    }

    .report-attendance-row,
    .report-comments-grid {
        display: grid;
        gap: 16px;
    }

    .report-attendance-row {
        grid-template-columns: repeat(4, 1fr);
        margin-bottom: 28px;
    }

    .report-att-box,
    .report-psycho-item {
        background: var(--report-white);
        border-radius: 10px;
        padding: 16px 18px;
        border: 1px solid var(--report-gray-mid);
        text-align: center;
    }

    .report-att-num {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--report-navy);
        display: block;
    }

    .report-att-lbl {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--report-gray-text);
        font-weight: 700;
        margin-top: 4px;
        display: block;
    }

    .report-psycho-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .report-psycho-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--report-gray-text);
        font-weight: 700;
        margin-bottom: 8px;
    }

    .report-stars {
        display: flex;
        justify-content: center;
        gap: 4px;
        font-size: 16px;
        color: var(--report-gold);
    }

    .report-stars .empty {
        color: var(--report-gray-mid);
    }

    .report-rating-key {
        display: flex;
        gap: 16px;
        font-size: 11px;
        color: var(--report-gray-text);
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .report-comments-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .report-comment-box {
        padding: 18px 20px;
    }

    .report-comment-label {
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--report-gold);
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }

    .report-comment-box p {
        font-size: 13px;
        color: var(--report-navy);
        line-height: 1.7;
        margin-bottom: 0;
    }

    .report-news-card,
    .report-next-term-card {
        background: var(--report-white);
        border: 1px solid var(--report-gray-mid);
        border-radius: 10px;
        padding: 18px 20px;
        box-shadow: 0 4px 24px rgba(13, 37, 69, 0.07);
        margin-bottom: 18px;
    }

    .report-news-kicker,
    .report-next-term-label {
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--report-gold);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .report-news-title {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        color: var(--report-navy);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .report-news-content,
    .report-next-term-note {
        font-size: 13px;
        line-height: 1.8;
        color: var(--report-navy);
    }

    .report-news-link {
        color: var(--report-navy);
        font-weight: 700;
        text-decoration: none;
        border-bottom: 1px solid var(--report-gold);
    }

    .report-next-term-line {
        font-size: 14px;
        color: var(--report-navy);
        font-weight: 600;
    }

    .report-next-term-subline {
        font-size: 12px;
        color: var(--report-gray-text);
        margin-top: 4px;
    }

    .report-next-term-note {
        margin-top: 10px;
    }

    .report-footer {
        background: var(--report-navy);
        padding: 18px 48px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .report-footer-left {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.45);
        line-height: 1.7;
    }

    .report-footer-stamp {
        font-family: 'Playfair Display', serif;
        font-size: 12px;
        color: var(--report-gold);
        border: 1.5px solid var(--report-gold);
        padding: 5px 14px;
        border-radius: 2px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .report-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 18px;
    }

    .report-term-card {
        margin-bottom: 28px;
        padding: 20px;
    }

    .report-term-title {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        margin-bottom: 16px;
        color: var(--report-navy);
    }

    .report-hidden-print {
        display: block;
    }

    @media print {
        @page {
            size: auto;
            margin: 10mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: #fff !important;
            padding: 0 !important;
        }

        body * {
            visibility: hidden !important;
        }

        .no-print,
        .report-hidden-print,
        .col-xs-12.col-sm-12.col-md-3.col-lg-2,
        .col-xs-12.col-sm-12.col-md-9.col-lg-10 > .d-sm-flex,
        .col-xs-12.col-sm-12.col-md-9.col-lg-10 > .row > .col > .d-flex.align-items-center.justify-content-between.mb-4 {
            display: none !important;
        }

        .report-page,
        .report-page * {
            visibility: visible !important;
        }

        .report-page {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
            border-radius: 0;
            max-width: 100%;
        }

        .report-body {
            background: var(--report-cream) !important;
        }

        .report-header,
        .report-footer,
        .report-gold-rule,
        .report-student-banner,
        .report-news-card,
        .report-next-term-card,
        .report-table thead tr,
        .report-pill,
        .report-att-box,
        .report-psycho-item,
        .report-comment-box,
        .report-block,
        .report-table-wrap,
        .report-info-banner {
            break-inside: avoid;
        }
    }

    @media (max-width: 900px) {
        .report-header,
        .report-student-banner,
        .report-body,
        .report-footer {
            padding-left: 20px;
            padding-right: 20px;
        }

        .report-header,
        .report-student-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .report-badge-box,
        .report-summary-pills {
            width: 100%;
            justify-content: flex-start;
        }

        .report-attendance-row,
        .report-comments-grid,
        .report-psycho-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .report-attendance-row,
        .report-comments-grid,
        .report-psycho-grid {
            grid-template-columns: 1fr;
        }

        .report-summary-pills {
            flex-wrap: wrap;
        }
    }
</style>
