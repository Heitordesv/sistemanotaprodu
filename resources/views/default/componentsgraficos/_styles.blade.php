<style>
    .dashboard-page {
        min-height: 100vh;
        padding: 1.5rem;
        background: #f7f8fa;
    }

    .dashboard-title {
        color: #101828;
        font-size: clamp(1.65rem, 2.2vw, 2.25rem);
        font-weight: 750;
        letter-spacing: -0.035em;
    }

    .dashboard-subtitle,
    .dashboard-section-heading p,
    .dashboard-chart-header p {
        color: #667085;
    }

    .dashboard-eyebrow {
        color: #175cd3;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .dashboard-live-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #12b76a;
        box-shadow: 0 0 0 4px rgba(18, 183, 106, .12);
    }

    .dashboard-filter-card,
    .dashboard-kpi-card,
    .dashboard-chart-card {
        border: 1px solid #eaecf0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .dashboard-filter-card {
        padding: 1rem;
    }

    .dashboard-label {
        display: block;
        margin-bottom: .45rem;
        color: #344054;
        font-size: .78rem;
        font-weight: 650;
    }

    .dashboard-input,
    .dashboard-location-select select {
        min-height: 42px;
        border-color: #d0d5dd;
        border-radius: 10px;
        box-shadow: none !important;
    }

    .dashboard-period-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    .dashboard-period-buttons .btn {
        border: 1px solid #d0d5dd;
        border-radius: 9px;
        background: #fff;
        color: #475467;
        font-weight: 600;
    }

    .dashboard-period-buttons .btn:hover,
    .dashboard-period-buttons .btn.active {
        border-color: #84adff;
        background: #eff4ff;
        color: #175cd3;
    }

    .dashboard-filter-button {
        min-height: 42px;
        border-radius: 10px;
        font-weight: 650;
    }

    .dashboard-kpi-card {
        position: relative;
        height: 100%;
        min-height: 172px;
        padding: 1.15rem;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .dashboard-kpi-card:hover {
        transform: translateY(-2px);
        border-color: #d0d5dd;
        box-shadow: 0 8px 22px rgba(16, 24, 40, .07);
    }

    .dashboard-kpi-card.kpi-primary {
        border-color: #b2ccff;
        background: linear-gradient(145deg, #f5f8ff 0%, #ffffff 72%);
    }

    .dashboard-kpi-card.kpi-danger-soft {
        border-color: #fecdca;
        background: linear-gradient(145deg, #fff6f5 0%, #ffffff 72%);
    }

    .dashboard-kpi-top {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-bottom: 1rem;
    }

    .dashboard-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eff4ff;
        color: #175cd3;
        font-size: 1.25rem;
    }

    .kpi-danger-soft .dashboard-kpi-icon {
        background: #fee4e2;
        color: #d92d20;
    }

    .dashboard-kpi-label {
        color: #475467;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .dashboard-kpi-value {
        margin-bottom: .85rem;
        color: #101828;
        font-size: clamp(1.45rem, 2vw, 1.9rem);
        font-weight: 750;
        letter-spacing: -.03em;
        overflow-wrap: anywhere;
    }

    .dashboard-kpi-meta {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        color: #667085;
        font-size: .78rem;
    }

    .dashboard-kpi-meta strong {
        color: #344054;
        font-weight: 650;
    }

    .dashboard-section-heading h5,
    .dashboard-chart-header h5 {
        color: #101828;
        font-weight: 700;
    }

    .dashboard-section-heading p,
    .dashboard-chart-header p {
        font-size: .86rem;
    }

    .dashboard-chart-card {
        height: 100%;
        padding: 1.1rem;
    }

    .dashboard-chart-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .5rem;
    }

    .dashboard-chart-header h5 {
        margin-bottom: .25rem;
    }

    .dashboard-chart-header p {
        margin-bottom: 0;
    }

    .dashboard-chart-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        flex: 0 0 auto;
        padding: .38rem .65rem;
        border-radius: 999px;
        background: #f2f4f7;
        color: #475467;
        font-size: .75rem;
        font-weight: 650;
    }

    .dashboard-chart {
        min-height: 280px;
    }

    .dashboard-chart-lg {
        min-height: 360px;
    }

    .dashboard-chart-xl {
        min-height: 410px;
    }

    .dashboard-chart-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
        margin-top: .25rem;
    }

    .dashboard-chart-summary > div {
        padding: .75rem;
        border-radius: 10px;
        background: #f9fafb;
        text-align: center;
    }

    .dashboard-chart-summary span {
        display: block;
        margin-bottom: .2rem;
        color: #667085;
        font-size: .75rem;
    }

    .dashboard-chart-summary strong {
        color: #101828;
        font-size: .95rem;
    }

    .dashboard-loading .dashboard-kpi-card,
    .dashboard-loading .dashboard-chart-card {
        opacity: .68;
        pointer-events: none;
    }

    .dashboard-loading .dashboard-live-dot {
        animation: dashboardPulse 1s ease-in-out infinite;
    }

    @keyframes dashboardPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .45; transform: scale(.8); }
    }

    @media (max-width: 767.98px) {
        .dashboard-page {
            padding: 1rem;
        }

        .dashboard-filter-card,
        .dashboard-chart-card {
            padding: .9rem;
        }

        .dashboard-kpi-card {
            min-height: 155px;
        }

        .dashboard-chart-header {
            flex-direction: column;
        }

        .dashboard-chart-badge {
            align-self: flex-start;
        }
    }
</style>