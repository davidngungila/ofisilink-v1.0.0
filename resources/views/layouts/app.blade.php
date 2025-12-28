<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>@yield('title', 'OfisiLink - Office Management System')</title>

    <meta name="description" content="OfisiLink - Comprehensive Office Management System" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <!-- DataTables (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" />
    <!-- jQuery UI for sortable (CDN) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <!-- Page CSS -->
    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!-- Template customizer & Theme config files -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- Custom OfisiLink Styles -->
    <style>
      :root {
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-hover: #a80000 !important;
      }
      
      /* Primary Color Overrides */
      .btn-primary, .bg-primary { 
        background-color: #940000 !important; 
        border-color: #940000 !important; 
      }
      .btn-primary:hover, .bg-primary:hover { 
        background-color: #a80000 !important; 
        border-color: #a80000 !important; 
      }
      .text-primary { color: #940000 !important; }
      .badge-primary { background-color: #940000 !important; }
      .progress-bar { background-color: #940000 !important; }
      
      /* Menu and Header */
      .menu-vertical .menu-item.active > .menu-link {
        background-color: rgba(148, 0, 0, 0.1) !important;
        color: #940000 !important;
      }
      .menu-vertical .menu-link:hover {
        color: #940000 !important;
      }
      
      /* App Brand */
      .app-brand-logo svg use {
        fill: #940000 !important;
      }
      
      /* Navbar */
      .navbar-nav .nav-link.active {
        color: #940000 !important;
      }
      
      /* Cards */
      .card-header {
        border-bottom-color: rgba(148, 0, 0, 0.1) !important;
      }
      
      /* Tables */
      .table-primary {
        background-color: rgba(148, 0, 0, 0.1) !important;
      }
      
      /* Form Controls */
      .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25) !important;
      }
      
      /* Custom Components */
      .ofisi-primary {
        background-color: #940000 !important;
        color: white !important;
      }
      
      .ofisi-primary:hover {
        background-color: #a80000 !important;
        color: white !important;
      }
      
      /* Status Badges */
      .badge-pending { background-color: #ffc107 !important; }
      .badge-approved { background-color: #28a745 !important; }
      .badge-rejected { background-color: #dc3545 !important; }
      .badge-paid { background-color: #17a2b8 !important; }
      .badge-retired { background-color: #6c757d !important; }
      
      @media print {
        .btn-primary { 
          background-color: #940000 !important; 
          -webkit-print-color-adjust: exact; 
          print-color-adjust: exact; 
        }
      }
    </style>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('dashboard') }}" class="app-brand-link" style="display:flex;justify-content:center;align-items:center;width:100%;">
              <span class="app-brand-logo demo">
                <x-logo width="72" alt="OfisiLink" />
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          @include('partials.sidebar')
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown me-3" id="notifContainer">
                  <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notifBell">
                    <i class="bx bx-bell fs-4"></i>
                    <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display:none" id="notifCount">0</span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 280px;max-width: 320px;" id="notifMenu">
                    <li class="dropdown-header p-2" style="font-size:0.875rem;font-weight:600;">Notifications</li>
                    <li>
                      <div style="max-height: 280px; overflow:auto" id="notifList">
                        <div class="p-3 text-muted text-center" style="font-size:0.875rem;">Loading...</div>
                      </div>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li><a class="dropdown-item small py-1 px-2" href="#" onclick="loadNotifDropdown(true); return false;" style="font-size:0.8rem;">Refresh</a></li>
                  </ul>
                </li>



                <!-- Quick Settings -->
              {{--   <li class="nav-item me-3 d-none d-md-block">
                  <a class="nav-link" href="{{ route('account.settings.index') }}" title="Account Settings">
                    <i class="bx bx-cog fs-4"></i>
                  </a>
                </li> --}}



                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      @if(auth()->user()->photo)
                        @php
                          $photoUrl = route('storage.photos', ['filename' => auth()->user()->photo]);
                        @endphp
                        <img src="{{ $photoUrl }}?t={{ time() }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle user-profile-avatar" data-profile-image="true" style="object-fit: cover;" />
                      @else
                        <span class="avatar-initial rounded-circle bg-label-primary user-profile-avatar" data-profile-image="true">{{ substr(auth()->user()->name, 0, 1) }}</span>
                      @endif
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              @if(auth()->user()->photo)
                                @php
                                  $photoUrl = route('storage.photos', ['filename' => auth()->user()->photo]);
                                @endphp
                                <img src="{{ $photoUrl }}?t={{ time() }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle user-profile-avatar" data-profile-image="true" style="object-fit: cover;" />
                              @else
                                <span class="avatar-initial rounded-circle bg-label-primary user-profile-avatar" data-profile-image="true">{{ substr(auth()->user()->name, 0, 1) }}</span>
                              @endif
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->roles->first()->display_name ?? 'User' }}</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item">
                          <i class="bx bx-power-off me-2"></i>
                          <span class="align-middle">Log Out</span>
                        </button>
                      </form>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              @yield('breadcrumb')
              @yield('content')
            </div>
            <!-- / Content -->

          

 <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme" style="border-top: 2px solid #940000;">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-3 flex-md-row flex-column align-items-center">
                <div class="mb-2 mb-md-0">
                  <span class="text-muted">Version: 1.0.0</span>
                  <span class="text-muted mx-2">|</span>
                  <span class="text-muted">© 2025 OfisiLink</span>
                </div>
                <div class="text-md-end">
                  <span class="text-muted">All rights reserved.</span>
                  <span class="text-muted mx-2">|</span>
                  <span class="text-muted">Powered By EmCa Techonologies</span>
                </div>
              </div>
            </footer>
            <!-- / Footer -->








            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    @stack('modals')

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <!-- jQuery UI (for sortable) -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <!-- DataTables (CDN) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- Advanced Toast Notifications -->
    <script src="{{ asset('assets/js/advanced-toast.js') }}"></script>

    @stack('scripts')
    <script>
    (function(){
      const notifCount = document.getElementById('notifCount');
      const notifList = document.getElementById('notifList');
      async function loadNotifDropdown(force){
        try{
          const res = await fetch('{{ route('notifications.dropdown') }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          if(!res.ok) return;
          const data = await res.json();
          if(data.success){
            notifCount.style.display = data.count_unread>0 ? 'inline-block':'none';
            notifCount.textContent = data.count_unread;
            if(force || notifList.innerHTML.trim()==='' || notifList.innerText.includes('Loading')){
              notifList.innerHTML = (data.items||[]).map(function(n){
                const readClass = n.is_read ? '' : 'fw-bold';
                const href = n.link ? n.link : '#';
                const shortMsg = truncate(String(n.message||'').trim(), 45);
                // Add click handler to mark as read and handle navigation properly
                const onClick = href !== '#' ? `onclick="handleNotificationClick(event, ${n.id}, '${href}')"` : '';
                return `<a class="dropdown-item ${readClass} py-2 px-2" href="${href}" ${onClick} style="font-size:0.875rem;line-height:1.3;cursor:pointer;"><div>${escapeHtml(shortMsg)}</div></a>`;
              }).join('') || '<div class="p-3 text-muted text-center" style="font-size:0.875rem;">No notifications</div>';
            }
          }
        }catch(e){/* silent */}
      }
      function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

      // Advanced Toast for notifications with clickable action
      function showNotificationToast(notification){
        if(!notification || !notification.message) return;
        
        // Use AdvancedToast if available, otherwise fallback to simple alert
        if(typeof window.AdvancedToast !== 'undefined' && window.AdvancedToast){
          const actions = [];
          
          // Add "View" action button if link is available
          if(notification.link && notification.link !== '#'){
            actions.push({
              label: 'View',
              name: 'view',
              class: 'primary',
              callback: function(){
                // Mark as read if notification ID is available
                if(notification.id){
                  fetch(`{{ url('notifications') }}/${notification.id}/read`, {
                    method: 'POST',
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                      'X-Requested-With': 'XMLHttpRequest',
                      'Accept': 'application/json'
                    }
                  }).catch(() => {});
                }
                // Navigate to the link
                window.location.href = notification.link;
              },
              dismiss: true
            });
          }
          
          // Show notification with AdvancedToast
          window.AdvancedToast.info(
            'New Notification',
            notification.message,
            {
              duration: 8000,
              sound: true,
              actions: actions.length > 0 ? actions : undefined,
              onClose: function(){
                // Refresh notification dropdown after toast closes
                setTimeout(function(){ loadNotifDropdown(false); }, 500);
              }
            }
          );
        } else {
          // Fallback to simple alert if AdvancedToast is not available
          const text = truncate(String(notification.message||'').replace(/\s+/g,' ').trim(), 50);
          alert('Notification: ' + text);
        }
      }
      
      function truncate(s, n){ return (s && s.length>n) ? (s.slice(0, n-1)+'…') : s; }
      function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

      // Track shown notifications and timing
      const NOTIFICATION_STORAGE_KEY = 'ofisilink_notifications';
      const MIN_DELAY_BETWEEN_NOTIFICATIONS = 2 * 60 * 1000; // 2 minutes in milliseconds
      const MAX_DISPLAYS_PER_HOUR = 5;
      
      function getStoredNotificationData(){
        try {
          const stored = localStorage.getItem(NOTIFICATION_STORAGE_KEY);
          if(stored){
            const data = JSON.parse(stored);
            // Clean up old data (older than 1 hour)
            const oneHourAgo = Date.now() - (60 * 60 * 1000);
            if(data.lastHourReset && data.lastHourReset < oneHourAgo){
              // Reset hour counter
              data.displaysThisHour = 0;
              data.lastHourReset = Date.now();
            }
            return data;
          }
        } catch(e){}
        return {
          shownIds: [],
          lastDisplayTime: 0,
          displaysThisHour: 0,
          lastHourReset: Date.now(),
          pendingNotifications: []
        };
      }
      
      function saveStoredNotificationData(data){
        try {
          localStorage.setItem(NOTIFICATION_STORAGE_KEY, JSON.stringify(data));
        } catch(e){}
      }
      
      function canShowNotification(data){
        const now = Date.now();
        const oneHourAgo = now - (60 * 60 * 1000);
        
        // Reset hour counter if needed
        if(data.lastHourReset < oneHourAgo){
          data.displaysThisHour = 0;
          data.lastHourReset = now;
        }
        
        // Check if we've exceeded displays per hour
        if(data.displaysThisHour >= MAX_DISPLAYS_PER_HOUR){
          return false;
        }
        
        // Check if enough time has passed since last display
        const timeSinceLastDisplay = now - data.lastDisplayTime;
        if(timeSinceLastDisplay < MIN_DELAY_BETWEEN_NOTIFICATIONS){
          return false;
        }
        
        return true;
      }
      
      function processPendingNotifications(){
        const data = getStoredNotificationData();
        
        // Check if we can show a notification
        if(!canShowNotification(data)){
          return; // Wait for next check
        }
        
        // Get next pending notification
        if(data.pendingNotifications && data.pendingNotifications.length > 0){
          const notification = data.pendingNotifications.shift();
          
          // Show the notification
          showNotificationToast(notification);
          
          // Update tracking - mark as shown AFTER displaying
          data.lastDisplayTime = Date.now();
          data.displaysThisHour = (data.displaysThisHour || 0) + 1;
          // Mark this notification as shown so it won't be displayed again
          if(!data.shownIds.includes(notification.id)){
            data.shownIds.push(notification.id);
          }
          
          // Keep only last 100 shown IDs
          if(data.shownIds.length > 100){
            data.shownIds = data.shownIds.slice(-100);
          }
          
          saveStoredNotificationData(data);
          
          // Schedule next notification check after delay
          setTimeout(processPendingNotifications, MIN_DELAY_BETWEEN_NOTIFICATIONS);
        }
      }
      
      async function pollNotifications(){
        try {
          const res = await fetch('{{ route('notifications.unread') }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          if(!res.ok) return;
          const data = await res.json();
          if(data && data.success && Array.isArray(data.notifications)){
            const storedData = getStoredNotificationData();
            
            // Filter out already shown notifications and those already in pending queue
            const newNotifications = data.notifications.filter(function(n){
              // Skip if already shown
              if(storedData.shownIds.includes(n.id)){
                return false;
              }
              // Skip if already in pending queue
              const alreadyPending = storedData.pendingNotifications.some(function(p){
                return p.id === n.id;
              });
              return !alreadyPending;
            });
            
            // Add new notifications to pending queue (don't mark as shown yet)
            newNotifications.forEach(function(n){
              storedData.pendingNotifications.push(n);
            });
            
            // Keep only last 100 shown IDs
            if(storedData.shownIds.length > 100){
              storedData.shownIds = storedData.shownIds.slice(-100);
            }
            
            saveStoredNotificationData(storedData);
            
            // Try to process pending notifications
            processPendingNotifications();
            
            // Update bell count and refresh dropdown
            if(typeof data.count === 'number'){
              notifCount.style.display = data.count>0 ? 'inline-block':'none';
              notifCount.textContent = data.count;
              // Refresh dropdown to show new notifications
              loadNotifDropdown(false);
            }
          }
        } catch (e) {
          // silent
        }
      }

      // Handle notification click - mark as read and navigate
      window.handleNotificationClick = function(event, notifId, href) {
        event.preventDefault();
        
        // Mark notification as read via AJAX (if not already read)
        if (notifId) {
          fetch(`{{ url('notifications') }}/${notifId}/read`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          }).catch(() => {}); // Silent fail - don't block navigation
        }
        
        // Navigate to the link
        if (href && href !== '#') {
          window.location.href = href;
        }
      };
      
      // Initial and interval poll
      setTimeout(function(){ pollNotifications(); loadNotifDropdown(true); }, 1500);
      // Poll every 20 seconds to check for new notifications
      setInterval(pollNotifications, 20000);
      // Also check for pending notifications every 30 seconds
      setInterval(processPendingNotifications, 30000);
      document.getElementById('notifBell').addEventListener('click', function(){ loadNotifDropdown(true); });
    })();

    // Auto-logout on idle with 30-second warning
    (function() {
      @php
        $sessionTimeoutMinutes = \App\Models\SystemSetting::getValue('session_timeout_minutes', 120);
        // Ensure minimum of 2 minutes and maximum of 1440 minutes (24 hours) for timeout
        $sessionTimeoutMinutes = max(2, min(1440, (int) $sessionTimeoutMinutes));
      @endphp
      let idleTimer;
      let warningTimer;
      let countdownInterval;
      const IDLE_TIMEOUT = {{ $sessionTimeoutMinutes }} * 60 * 1000; // Session timeout in milliseconds from system settings
      const WARNING_TIME = 30 * 1000; // 30 seconds warning before logout
      const WARNING_TIMEOUT = Math.max(0, IDLE_TIMEOUT - WARNING_TIME); // Show warning 30 seconds before logout (ensure non-negative)
      
      // Debug: Log timeout values (remove in production)
      console.log('Session timeout configured:', {
        minutes: {{ $sessionTimeoutMinutes }},
        idleTimeout: IDLE_TIMEOUT,
        warningTimeout: WARNING_TIMEOUT
      });
      const logoutUrl = '{{ route("logout") }}';
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
      let warningModal = null;
      let countdownSeconds = 30;

      // Create warning modal HTML
      function createWarningModal() {
        if (document.getElementById('idleWarningModal')) {
          return; // Modal already exists
        }
        
        const modalHTML = `
          <div class="modal fade" id="idleWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 99999;">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark">
                  <h5 class="modal-title">
                    <i class="bx bx-time-five me-2"></i>Session Timeout Warning
                  </h5>
                </div>
                <div class="modal-body text-center py-4">
                  <div class="mb-3">
                    <i class="bx bx-time fs-1 text-warning"></i>
                  </div>
                  <h6 class="mb-3">System Idle Detected</h6>
                  <p class="text-muted mb-3">
                    Your session has been idle for a while. The system will automatically log you out due to inactivity.
                  </p>
                  <div class="alert alert-warning mb-3">
                    <strong>Auto-logout in: <span id="countdownTimer" class="text-danger fs-4">30</span> seconds</strong>
                  </div>
                  <p class="text-muted small">
                    Click "Continue" below if you want to stay logged in.
                  </p>
                </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-primary btn-lg" id="continueSessionBtn">
                    <i class="bx bx-check-circle me-2"></i>Continue Session
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modalElement = document.getElementById('idleWarningModal');
        warningModal = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
        
        // Set up continue button handler
        const continueBtn = document.getElementById('continueSessionBtn');
        if (continueBtn) {
          continueBtn.addEventListener('click', function() {
            resetIdleTimer();
          });
        }
      }

      // Show warning modal
      function showWarningModal() {
        createWarningModal();
        countdownSeconds = 30;
        document.getElementById('countdownTimer').textContent = countdownSeconds;
        
        if (warningModal) {
          warningModal.show();
        }
        
        // Start countdown
        countdownInterval = setInterval(function() {
          countdownSeconds--;
          const countdownEl = document.getElementById('countdownTimer');
          if (countdownEl) {
            countdownEl.textContent = countdownSeconds;
          }
          
          if (countdownSeconds <= 0) {
            clearInterval(countdownInterval);
            // Auto-logout
            performLogout();
          }
        }, 1000);
      }

      // Hide warning modal
      function hideWarningModal() {
        if (countdownInterval) {
          clearInterval(countdownInterval);
          countdownInterval = null;
        }
        if (warningModal) {
          warningModal.hide();
        }
        countdownSeconds = 30;
      }

      // Perform logout
      function performLogout() {
        hideWarningModal();
        
        // Use relative URL to avoid port issues
        const form = document.createElement('form');
        form.method = 'POST';
        // Extract path from logoutUrl to avoid absolute URL issues
        const url = new URL(logoutUrl, window.location.origin);
        form.action = url.pathname;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
      }

      function resetIdleTimer() {
        // Clear existing timers
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        hideWarningModal();
        
      // Set warning timer (30 seconds before logout) - only if there's enough time
      if (WARNING_TIMEOUT > 0 && IDLE_TIMEOUT > WARNING_TIME) {
        warningTimer = setTimeout(function() {
          showWarningModal();
        }, WARNING_TIMEOUT);
      }
      
      // Set logout timer
      idleTimer = setTimeout(function() {
        performLogout();
      }, IDLE_TIMEOUT);
      }


      // Events that indicate user activity
      const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
      
      activityEvents.forEach(function(eventName) {
        document.addEventListener(eventName, resetIdleTimer, true);
      });

      // Initialize timer on page load
      resetIdleTimer();
    })();

    // Global AJAX error handler for session expiration
    (function() {
      const loginUrl = '{{ route("login") }}';
      
      // Handle jQuery AJAX errors
      if (typeof jQuery !== 'undefined') {
        $(document).ajaxError(function(event, xhr, settings) {
          // Check for session expiration errors
          if (xhr.status === 401 || xhr.status === 419 || xhr.status === 403) {
            // Don't redirect if already on login page
            if (window.location.pathname.includes('/login')) {
              return;
            }
            
            // Clear any existing session data
            sessionStorage.clear();
            localStorage.removeItem('session_data');
            
            // Redirect to login page
            window.location.href = loginUrl;
          }
        });
      }
      
      // Handle Fetch API errors globally
      const originalFetch = window.fetch;
      window.fetch = function(...args) {
        return originalFetch.apply(this, args)
          .then(response => {
            // Check for session expiration errors
            if (response.status === 401 || response.status === 419 || response.status === 403) {
              // Don't redirect if already on login page
              if (window.location.pathname.includes('/login')) {
                return response;
              }
              
              // Clear any existing session data
              sessionStorage.clear();
              localStorage.removeItem('session_data');
              
              // Redirect to login page
              window.location.href = loginUrl;
              return response;
            }
            return response;
          })
          .catch(error => {
            // Network errors or other fetch errors
            throw error;
          });
      };
      
      // Handle XMLHttpRequest errors
      const originalOpen = XMLHttpRequest.prototype.open;
      const originalSend = XMLHttpRequest.prototype.send;
      
      XMLHttpRequest.prototype.open = function(method, url, ...rest) {
        this._url = url;
        return originalOpen.apply(this, [method, url, ...rest]);
      };
      
      XMLHttpRequest.prototype.send = function(...args) {
        this.addEventListener('loadend', function() {
          if (this.status === 401 || this.status === 419 || this.status === 403) {
            // Don't redirect if already on login page
            if (window.location.pathname.includes('/login')) {
              return;
            }
            
            // Clear any existing session data
            sessionStorage.clear();
            localStorage.removeItem('session_data');
            
            // Redirect to login page
            window.location.href = loginUrl;
          }
        });
        
        return originalSend.apply(this, args);
      };
    })();
    
    // Auto-show Laravel flash messages with Advanced Toast
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            window.AdvancedToast.success('Success', '{{ session('success') }}', { duration: 5000, sound: true });
        @endif
        
        @if(session('error'))
            window.AdvancedToast.error('Error', '{{ session('error') }}', { duration: 7000, sound: true });
        @endif
        
        @if(session('warning'))
            window.AdvancedToast.warning('Warning', '{{ session('warning') }}', { duration: 6000, sound: true });
        @endif
        
        @if(session('info'))
            window.AdvancedToast.info('Information', '{{ session('info') }}', { duration: 5000, sound: true });
        @endif
        
        @if(session('message') && !session('success') && !session('error') && !session('warning') && !session('info'))
            window.AdvancedToast.info('Notification', '{{ session('message') }}', { duration: 5000 });
        @endif
    });
    </script>
  </body>
</html>