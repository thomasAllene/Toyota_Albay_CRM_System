<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
            <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="bx bx-sm"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                            <span class="align-middle"><i class="bx bx-sun me-2"></i>Light</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                            <span class="align-middle"><i class="bx bx-moon me-2"></i>Dark</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                            <span class="align-middle"><i class="bx bx-desktop me-2"></i>System</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Notification -->
            {{-- <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <span class="position-relative">
                    <i class="icon-base bx bx-bell icon-md"></i>
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                    <h6 class="mb-0 me-auto">Notification</h6>
                    <div class="d-flex align-items-center h6 mb-0">
                        <span class="badge bg-label-primary me-2">8 New</span>
                        <a href="javascript:void(0)" class="dropdown-notifications-all p-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i class="icon-base bx bx-envelope-open text-heading"></i></a>
                    </div>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <img src="../../assets/img/avatars/1.png" alt class="rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Congratulation Lettie 🎉</h6>
                            <small class="mb-1 d-block text-body">Won the monthly best seller gold badge</small>
                            <small class="text-body-secondary">1h ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-danger">CF</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Charles Franklin</h6>
                            <small class="mb-1 d-block text-body">Accepted your connection</small>
                            <small class="text-body-secondary">12hr ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <img src="../../assets/img/avatars/2.png" alt class="rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">New Message ✉️</h6>
                            <small class="mb-1 d-block text-body">You have new message from Natalie</small>
                            <small class="text-body-secondary">1h ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-success"><i class="icon-base bx bx-cart"></i></span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Whoo! You have new order 🛒</h6>
                            <small class="mb-1 d-block text-body">ACME Inc. made new order $1,154</small>
                            <small class="text-body-secondary">1 day ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <img src="../../assets/img/avatars/9.png" alt class="rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Application has been approved 🚀</h6>
                            <small class="mb-1 d-block text-body">Your ABC project application has been approved.</small>
                            <small class="text-body-secondary">2 days ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-success"><i class="icon-base bx bx-pie-chart-alt"></i></span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Monthly report is generated</h6>
                            <small class="mb-1 d-block text-body">July monthly financial report is generated </small>
                            <small class="text-body-secondary">3 days ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <img src="../../assets/img/avatars/5.png" alt class="rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">Send connection request</h6>
                            <small class="mb-1 d-block text-body">Peter sent you connection request</small>
                            <small class="text-body-secondary">4 days ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <img src="../../assets/img/avatars/6.png" alt class="rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">New message from Jane</h6>
                            <small class="mb-1 d-block text-body">Your have new message from Jane</small>
                            <small class="text-body-secondary">5 days ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-warning"><i class="icon-base bx bx-error"></i></span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="small mb-0">CPU is running high</h6>
                            <small class="mb-1 d-block text-body">CPU Utilization Percent is currently at 88.63%,</small>
                            <small class="text-body-secondary">5 days ago</small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                        </div>
                    </li>
                    </ul>
                </li>
                <li class="border-top">
                    <div class="d-grid p-4">
                    <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                        <small class="align-middle">View all notifications</small>
                    </a>
                    </div>
                </li>
                </ul>
            </li> --}}
            <!--/ Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="/profile" data-bs-toggle="dropdown">
                    <div class="avatar">
                        {{-- <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" /> --}}
                        @php
                            $user = Auth::user();
                            $firstInitial = strtoupper(substr($user->first_name, 0, 1));
                            $lastInitial = strtoupper(substr($user->last_name, 0, 1));
                            // $position = $user->usertype->name;
                        @endphp
                        <h6 class="w-px-40 rounded-circle text-primary" style="font-size: 4vh;"><b>{{ $firstInitial }}{{ $lastInitial }}</b></h6>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="/profile">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        {{-- <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" /> --}}
                                        <h6 class="w-px-40 rounded-circle text-primary" style="font-size: 4vh;"><b>{{ $firstInitial }}{{ $lastInitial }}</b></h6>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}</span>
                                    <small class="text-muted">{{ $user->usertype->name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    {{-- <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                <span class="flex-grow-1 align-middle ms-1">Billing</span>
                                <span
                                    class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                            </span>
                        </a>
                    </li> --}}
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>

                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
