<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-shop"></i> EZKIOS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard - SEMUA USER BISA LIHAT -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- MENU DENGAN PERMISSION - PAKAI MANUAL -->
                @php
                    $user = Auth::user();
                @endphp

                @if($user && $user->hasPermission('view_products'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') || request()->routeIs('stock-adjustments.*') ? 'active' : '' }}" 
                       href="#" id="navbarProduk" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-box"></i> Produk
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('products.index') }}">Daftar Produk</a></li>
                        <li><a class="dropdown-item" href="{{ route('stock-adjustments.index') }}">Koreksi Stok (Opname)</a></li>
                    </ul>
                </li>
                @endif

                @if($user && $user->hasPermission('view_suppliers'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                       href="{{ route('suppliers.index') }}">
                        <i class="bi bi-people"></i> Supplier
                    </a>
                </li>
                @endif

                @if($user && $user->hasPermission('view_purchases'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
                       href="{{ route('purchases.index') }}">
                        <i class="bi bi-truck"></i> Pembelian
                    </a>
                </li>
                @endif

                @if($user && $user->hasPermission('view_sales'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}"
                       href="{{ route('sales.index') }}">
                        <i class="bi bi-cart"></i> Penjualan
                    </a>
                </li>
                @endif

                @if($user && $user->hasPermission('view_promotions'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}"
                       href="{{ route('promotions.index') }}">
                        <i class="bi bi-tags"></i> Promosi
                    </a>
                </li>
                @endif

                @if($user && $user->hasPermission('view_cash_flow'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cash-flow.*') ? 'active' : '' }}"
                       href="{{ route('cash-flow.index') }}">
                        <i class="bi bi-wallet"></i> Arus Kas
                    </a>
                </li>
                @endif

                <!-- DROPDOWN MANAGEMENT -->
                @if($user && ($user->hasPermission('manage_users') || $user->hasPermission('manage_roles')))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Management
                    </a>
                    <ul class="dropdown-menu">
                        @if($user->hasPermission('manage_users'))
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">
                                <i class="bi bi-people-fill"></i> User Management
                            </a>
                        </li>
                        @endif

                        @if($user->hasPermission('manage_roles'))
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                               href="{{ route('roles.index') }}">
                                <i class="bi bi-shield-lock"></i> Role & Permission
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        {{ Auth::user()->name }}
                        <span class="badge bg-light text-dark ms-1">
                            {{ Auth::user()->role->display_name ?? 'No Role' }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text">
                                <small class="text-muted">
                                    <i class="bi bi-envelope"></i> {{ Auth::user()->email }}
                                </small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
