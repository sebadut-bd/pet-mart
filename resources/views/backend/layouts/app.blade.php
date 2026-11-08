<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | PetMart Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ asset('backend_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/custom.min.css') }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; color: #111827; display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: 240px;
            background: #1e2139;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-logo .icon {
            width: 36px; height: 36px;
            background: #1a56db;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .sidebar-logo .icon svg { width: 20px; height: 20px; fill: #fff; }
        .sidebar-logo span { font-size: 18px; font-weight: 700; color: #fff; }

        .nav-section {
            padding: 16px 12px 0;
            flex: 1;
            overflow-y: auto;
        }
        .nav-label {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 10px;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background .15s, color .15s;
            margin-bottom: 2px;
        }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: #fff; }
        .nav-item.active { background: #1a56db; color: #fff; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            margin-bottom: 8px;
        }
        .user-avatar {
            width: 34px; height: 34px;
            background: #1a56db;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: #f9fafb; }
        .user-role { font-size: 11px; color: #6b7280; }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 9px 12px;
            background: none;
            border: none;
            border-radius: 8px;
            color: #ef4444;
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background .15s;
        }
        .logout-btn:hover { background: rgba(239,68,68,0.1); }
        .logout-btn svg { width: 16px; height: 16px; }

        /* ── Main ── */
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }

        .topbar {
            background: #fff;
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            position: sticky; top: 0; z-index: 10;
        }
        .topbar h2 { font-size: 17px; font-weight: 700; color: #111827; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-date { font-size: 12.5px; color: #6b7280; }

        .content { padding: 28px; flex: 1; }

        @yield('extra-styles')
    </style>
</head>
<body>

<div id="app" style="width: 100%; display: flex;">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="icon">
                <svg viewBox="0 0 64 64"><ellipse cx="16" cy="14" rx="6" ry="8"/><ellipse cx="30" cy="10" rx="5" ry="7"/><ellipse cx="44" cy="14" rx="6" ry="8"/><ellipse cx="52" cy="28" rx="5" ry="7"/><path d="M32 26c-9 0-18 7-15 18 1.5 5.5 6 9 10 11 2 1 5 1.5 5 1.5s3-.5 5-1.5c4-2 8.5-5.5 10-11 3-11-6-18-15-18z"/></svg>
            </div>
            <span>PetMart</span>
        </div>

        <nav class="nav-section">
            <div class="nav-label">Main</div>
            <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>

            <div class="nav-label">Marketplace</div>
            <a href="/admin/marketplace/moderation" class="nav-item {{ request()->is('admin/marketplace/moderation') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                Product Approval
            </a>
            <a href="/admin/marketplace/categories" class="nav-item {{ request()->is('admin/marketplace/categories') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                Categories & Specs
            </a>

            <div class="nav-label">Users & Moderation</div>
            <a href="/admin/users" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                User & Breeder Management
            </a>
            <a href="/admin/marketplace/reports" class="nav-item {{ request()->is('admin/marketplace/reports') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Abuse Reports
            </a>

            <div class="nav-label">Content</div>
            <a href="/admin/marketplace/banners" class="nav-item {{ request()->is('admin/marketplace/banners') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Homepage Banners
            </a>

            <div class="nav-label">RBAC & Accesses</div>
            <a href="/admin/module" class="nav-item {{ request()->is('admin/module') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Modules / Menus CRUD
            </a>
            <a href="/admin/role" class="nav-item {{ request()->is('admin/role') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Roles Management
            </a>
            <a href="/admin/role_permissions" class="nav-item {{ request()->is('admin/role_permissions') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Role Permissions Matrix
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main">
        <header class="topbar">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <div class="topbar-right">
                <span class="topbar-date">{{ now()->format('d M Y') }}</span>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('backend_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    window.baseUrl = "{{ url('/') }}";
    window.publicPath = "{{ asset('') }}";
    window.UploadPath = "{{ asset('uploads') }}";
    var baseUrl = window.baseUrl;
    var publicPath = window.publicPath;
    var UploadPath = window.UploadPath;
</script>
<script src="{{ mix('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>
