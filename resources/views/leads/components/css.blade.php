<style>
.leads-page {
    --leads-primary: #4e73df;
    --leads-bg: #f7f8fc;
    --leads-border: #e7eaf1;
    --leads-text: #343a40;
    background: var(--leads-bg);
    min-height: calc(100vh - 70px);
}

.leads-page .crm-shell,
.lead-details-page .crm-shell {
    border-radius: 16px;
    overflow: hidden;
}

.leads-page .summary-card {
    border: 1px solid var(--leads-border);
    border-radius: 14px;
    transition: transform .18s ease, box-shadow .18s ease;
}

.leads-page .summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
}

.leads-page .summary-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.leads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(285px, 1fr));
    gap: 16px;
}

.lead-card {
    background: #fff;
    border: 1px solid var(--leads-border);
    border-left-width: 4px;
    border-radius: 12px;
    overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.lead-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .07);
}

.lead-card.border-left-info { border-left-color: #0dcaf0; }
.lead-card.border-left-warning { border-left-color: #ffc107; }
.lead-card.border-left-primary { border-left-color: #0d6efd; }
.lead-card.border-left-success { border-left-color: #198754; }
.lead-card.border-left-danger { border-left-color: #dc3545; }
.lead-card.border-left-secondary { border-left-color: #6c757d; }

.lead-card-body {
    padding: 18px;
}

.lead-card-footer {
    padding: 12px 18px;
    border-top: 1px solid var(--leads-border);
    background: #fbfcfe;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.badge-origem {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: 100%;
    padding: 5px 9px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4052a4;
    font-size: .76rem;
    font-weight: 600;
}

#loading-import {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(3px);
}

.lead-details-page .timeline-item {
    position: relative;
    padding: 0 0 22px 28px;
    border-left: 2px solid #e9ecef;
    margin-left: 8px;
}

.lead-details-page .timeline-item:last-child {
    padding-bottom: 0;
}

.lead-details-page .timeline-dot {
    position: absolute;
    left: -7px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0d6efd;
    box-shadow: 0 0 0 4px #eaf2ff;
}

@media (max-width: 767.98px) {
    .leads-grid {
        grid-template-columns: 1fr;
    }

    .leads-page .crm-header-actions {
        width: 100%;
        margin-top: 12px;
    }

    .leads-page .crm-header-actions .btn {
        flex: 1 1 auto;
    }
}
</style>