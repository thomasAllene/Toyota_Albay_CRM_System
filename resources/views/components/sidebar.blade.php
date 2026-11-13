<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="#" class="app-brand-link">
        <img src="{{asset('assets/myimg/logo.png')}}" class="app-brand-logo w-px-30 h-auto me-2 " alt="logo" />
            <span class="app-brand-text menu-text fw-bold">TOYOTA
              <br />
              <span class="fs-tiny fw-medium">VSMS System</span>
            </span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="bx bx-chevron-left bx-sm align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">


      @can('view_dashboard')
        <li class="menu-item {{ request()->is('dashboard') || request()->is('sales-funnel-management-dashboard') || request()->is('vehicle-to-sales-dashboard') || request()->is('ranking-dashboard') ? 'active' : '' }}">
            <a href="/dashboard" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-dashboard'></i>
              <div class="text-truncate" data-i18n="Page 2">Dashboard</div>
            </a>
        </li>
      @endcan

      @if(auth()->user()->can('view_leads') ||
        auth()->user()->can('view_application') ||
        auth()->user()->can('view_vehicle_reservation') ||
        auth()->user()->can('view_vehicle_releases'))
        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Transactions</div>
        </li>
      @endif

      @can('view_leads')
        <!-- Your menu item -->
        <li class="menu-item {{ request()->is('leads') ? 'active' : '' }}">
            <a href="/leads" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-layer-plus'></i>
                <div class="text-truncate" data-i18n="Page 2">Leads</div>
                {{-- <span id="sideNavLeadsBadge" class="badge badge-dot bg-danger ms-2" style="display:;">1</span> --}}
                <span id="sideNavLeadsBadge" class="position-absolute top-0 start-100 badge badge-dot badge-notifications border border-2 p-1 bg-danger" style="display: none;"></span>
            </a>
        </li>
      @endcan
      @can('view_application')
        <li class="menu-item {{ request()->is('application') ? 'active' : '' }}">
            <a href="/application" class="menu-link">
                <i class='menu-icon tf-icons bx bx-list-plus'></i>
              <div class="text-truncate" data-i18n="Page 2">Application</div>
              {{-- <span id="sideNavApplicationBadge" class="badge bg-danger ms-2" style="display:;">1</span> --}}
              <span id="sideNavApplicationBadge" class="position-absolute top-0 start-100 badge badge-dot badge-notifications border border-2 p-1 bg-danger" style="display: none;"></span>
            </a>
        </li>
      @endcan
      @can('view_vehicle_reservation')
        <li class="menu-item {{ request()->is('vehicle-reservation') ? 'active' : '' }}">
            <a href="/vehicle-reservation" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-car'></i>
                <div class="text-truncate" data-i18n="Page 2">Vehicle Reservation</div>
                {{-- <span id="sideNavReservationBadge" class="badge bg-danger ms-2" style="display:;">1</span> --}}
                <span id="sideNavReservationBadge" class="position-absolute top-0 start-100 badge badge-dot badge-notifications border border-2 p-1 bg-danger" style="display: none;"></span>
            </a>
        </li>
      @endcan
      @can('view_vehicle_releases')
        <li class="menu-item {{ request()->is('vehicle-releases') ? 'active' : '' }}">
            <a href="/vehicle-releases" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-right-top-arrow-circle'></i>
              <div class="text-truncate" data-i18n="Page 2">Vehicle Releases</div>
              {{-- <span id="sideNavReleasesBadge" class="badge bg-danger ms-2" style="display:;">1</span> --}}
              <span id="sideNavReleasesBadge" class="position-absolute top-0 start-100 badge badge-dot badge-notifications border border-2 p-1 bg-danger" style="display: none;"></span>
            </a>
        </li>
      @endcan

      @can('view_disputes')
        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Dispute Transactions</div>
        </li>

        <li class="menu-item {{ request()->is('dispute') ? 'active' : '' }}">
            <a href="/dispute" class="menu-link" onclick="updateDisputeStatus()">
                <i class='menu-icon tf-icons bx bxs-x-square'></i>
              <div class="text-truncate" data-i18n="Page 2">Disputes</div>
              {{-- <span id="sideNavDisputeTabBadge" class="badge bg-danger ms-2" style="display:;">1</span> --}}
              <span id="sideNavDisputeTabBadge" class="position-absolute top-0 start-100 badge badge-dot badge-notifications border border-2 p-1 bg-danger" style="display: none;"></span>

            </a>
        </li>
      @endcan

      @can('view_vehicle_inventory')
        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Inventory</div>
        </li>

        <li class="menu-item {{ request()->is('vehicle-inventory') ? 'active' : '' }}">
            <a href="/vehicle-inventory" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-car-garage'></i>
              <div class="text-truncate" data-i18n="Page 2">Vehicle Inventory</div>
            </a>
          </li>
      @endcan
      @can('view_banks')
        <li class="menu-item">
          <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Bank</div>
        </li>
        <li class="menu-item {{ request()->is('banks') ? 'active' : '' }}">
            <a href="/banks" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-bank'></i>
              <div class="text-truncate" data-i18n="Page 2">Banks</div>
            </a>
        </li>
        @endcan

      {{-- @can('view_sales')
        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Statistics</div>
        </li>
        <li class="menu-item {{ request()->is('') ? 'active' : '' }}">
            <a href="" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-pie-chart-alt-2'></i>
              <div class="text-truncate" data-i18n="Page 2">Sales</div>
            </a>
        </li>
      @endcan --}}

        @can('view_users')
        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">User Management</div>
        </li>
        <li class="menu-item {{ request()->is('team') ? 'active' : '' }}">
            <a href="/team" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-group'></i>
              <div class="text-truncate" data-i18n="Page 2">Group</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('user-management') ? 'active' : '' }}">
            <a href="/user-management" class="menu-link">
                <i class='menu-icon tf-icons bx bx-male-female'></i>
              <div class="text-truncate" data-i18n="Page 2">Users</div>
            </a>
        </li>
        @endcan

        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Upload Backlogs</div>
        </li>
        <li class="menu-item {{ request()->is('inventory_backlogs') ? 'active' : '' }}">
            <a href="/inventory_backlogs" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-group'></i>
              <div class="text-truncate" data-i18n="Page 2">Inventory Backlogs</div>
            </a>
        </li>
        <li class="menu-item d-none {{ request()->is('releases_backlogs') ? 'active' : '' }}">
            <a href="/releases_backlogs" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-group'></i>
              <div class="text-truncate" data-i18n="Page 2">Releases Backlogs</div>
            </a>
        </li>

      </ul>
  </aside>
