@extends('backend.layouts.app')

@section('title', 'Marketplace Admin Dashboard')
@section('page-title', 'Marketplace Overview')

@section('extra-styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.06);
    }
    .stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg { width: 26px; height: 26px; }
    .stat-icon.green  { background: #ecfdf5; color: #10b981; }
    .stat-icon.amber  { background: #fffbebf; color: #f59e0b; }
    .stat-icon.blue   { background: #eff6ff; color: #3b82f6; }
    .stat-icon.purple { background: #faf5ff; color: #a855f7; }
    .stat-icon.rose   { background: #fff1f2; color: #f43f5e; }

    .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; }
    .stat-label { font-size: 13px; color: #64748b; margin-top: 6px; font-weight: 600; }

    .dashboard-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    @media (max-width: 1024px) {
        .dashboard-row { grid-template-columns: 1fr; }
    }

    .card-panel {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
    }
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .panel-title {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }
    .btn-action {
        background: #10b981;
        color: #fff;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-action:hover { background: #059669; color: #fff; }

    table { width: 100%; border-collapse: collapse; }
    th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }
    td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    tr:hover td { background: #f8fafc; }

    .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
    }
    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-pending  { background: #fef3c7; color: #b45309; }
    .badge-sold     { background: #f1f5f9; color: #475569; }
    .badge-rejected { background: #fee2e2; color: #b91c1c; }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #f8fafc;
        border-radius: 12px;
        text-decoration: none;
        color: #0f172a;
        font-weight: 700;
        margin-bottom: 12px;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    .quick-link:hover {
        background: #ecfdf5;
        border-color: #34d399;
        color: #047857;
        transform: translateX(4px);
    }
</style>
@endsection

@section('content')

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($total_products) }}</div>
            <div class="stat-label">Active Pet Listings</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($pending_products) }}</div>
            <div class="stat-label">Pending Approval Queue</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($total_users) }}</div>
            <div class="stat-label">Registered Marketplace Users</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($verified_sellers) }}</div>
            <div class="stat-label">Verified Pet Breeders</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($pending_reports) }}</div>
            <div class="stat-label">Pending Listing Reports</div>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <!-- Recent Listings Panel -->
    <div class="card-panel">
        <div class="panel-header">
            <h3 class="panel-title">Recent Marketplace Activity</h3>
            <a href="/admin/moderation" class="btn-action">Go to Moderation Queue</a>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Listing Title</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_products as $i => $product)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 700;">{{ Str::limit($product->title, 32) }}</td>
                        <td>{{ $product->user->name ?? '—' }}</td>
                        <td><span style="background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 600;">{{ $product->category->name ?? '—' }}</span></td>
                        <td style="font-weight: 700; color: #059669;">
                            {{ $product->is_free ? 'Free Adoption' : ($product->price ? '৳'.number_format($product->price) : 'Negotiable') }}
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $product->status }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td>{{ $product->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="card-panel">
        <div class="panel-header">
            <h3 class="panel-title">Management Shortcuts</h3>
        </div>

        <a href="/admin/moderation" class="quick-link">
            <span style="background: #fef3c7; color: #b45309; padding: 8px; border-radius: 8px;">⏳</span>
            Review Pending Approval Queue ({{ $pending_products }})
        </a>

        <a href="/admin/categories" class="quick-link">
            <span style="background: #ecfdf5; color: #047857; padding: 8px; border-radius: 8px;">🏷️</span>
            Manage Pet Categories & Specs
        </a>

        <a href="/admin/users" class="quick-link">
            <span style="background: #eff6ff; color: #1d4ed8; padding: 8px; border-radius: 8px;">🛡️</span>
            Verify Breeders & Manage Users
        </a>

        <a href="/admin/reports" class="quick-link">
            <span style="background: #fee2e2; color: #b91c1c; padding: 8px; border-radius: 8px;">⚠️</span>
            Check User Reports & Abuse Queue ({{ $pending_reports }})
        </a>
    </div>
</div>

@endsection
