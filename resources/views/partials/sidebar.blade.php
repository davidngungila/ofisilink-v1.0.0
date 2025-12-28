<ul class="menu-inner py-1">
  <!-- Dashboard -->
  <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-home-circle"></i>
      <div data-i18n="Dashboard" style="font-weight: bold;">Dashboard</div>
    </a>
  </li>

  @php
    $userRoles = auth()->user()->roles->pluck('name')->toArray();
    $isSystemAdmin = in_array('System Admin', $userRoles);
    $isCEO = in_array('CEO', $userRoles) || in_array('Director', $userRoles);
    $isHOD = in_array('HOD', $userRoles);
    $isAccountant = in_array('Accountant', $userRoles);
    $isHR = in_array('HR Officer', $userRoles);
    $isStaff = in_array('Staff', $userRoles);
  @endphp

  <!-- Petty Cash Management - Direct link to dashboard -->
  <li class="menu-item {{ request()->routeIs('petty-cash.*') ? 'active' : '' }}">
    <a href="{{ route('petty-cash.index') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-money"></i>
      <div data-i18n="Petty Cash Management" style="font-weight: bold;">Petty Cash Management</div>
    </a>
  </li>

  <!-- Imprest Management - Direct link to dashboard -->
  <li class="menu-item {{ request()->routeIs('imprest.*') ? 'active' : '' }}">
    <a href="{{ route('imprest.index') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-credit-card"></i>
      <div data-i18n="Imprest Management" style="font-weight: bold;">Imprest Management</div>
    </a>
  </li>

  <!-- Accounting Module - Accountant, Admin -->
  @if($isAccountant || $isSystemAdmin)
  <li class="menu-item {{ request()->routeIs('modules.accounting.*') ? 'active' : '' }}" data-menu-key="accounting-module">
    <a href="{{ route('modules.accounting.index') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
      <div data-i18n="Accounting Module" style="font-weight: bold;">Accounting Module</div>
    </a>
  </li>
  @endif

  <!-- File Management -->
  <li class="menu-item {{ request()->routeIs('modules.files.*') ? 'active open' : '' }}" data-menu-key="file-management">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon tf-icons bx bx-folder"></i>
      <div data-i18n="File Management" style="font-weight: bold;">File Management</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item {{ request()->routeIs('modules.files.digital') || request()->routeIs('modules.files.digital.*') ? 'active' : '' }}">
        <a href="{{ route('modules.files.digital') }}" class="menu-link">
          <div data-i18n="Digital Files" style="font-weight: bold;">Digital Files</div>
        </a>
      </li>
      <li class="menu-item {{ request()->routeIs('modules.files.physical') || request()->routeIs('modules.files.physical.*') ? 'active' : '' }}">
        <a href="{{ route('modules.files.physical') }}" class="menu-link">
          <div data-i18n="Physical Racks" style="font-weight: bold;">Physical Racks</div>
        </a>
      </li>
    </ul>
  </li>

{{--   <!-- Asset Management - HR or Accountant -->
  @if($isHR || $isAccountant || $isSystemAdmin)
  <li class="menu-item {{ request()->routeIs('modules.assets') ? 'active' : '' }}">
    <a href="{{ route('modules.assets') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-package"></i>
      <div data-i18n="Asset Management">Asset Management</div>
    </a>
  </li>
  @endif --}}


  

  <!-- HR Tools - Available to ALL USERS (Staff) -->
  <li class="menu-item {{ request()->routeIs('modules.hr.*') || request()->routeIs('sick-sheets.*') || request()->routeIs('leave.hr.*') || request()->routeIs('departments.*') || request()->routeIs('positions.*') || request()->routeIs('attendance.*') ? 'active open' : '' }}" data-menu-key="hr-management">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon tf-icons bx bx-briefcase"></i>
      <div data-i18n="HR Tools" style="font-weight: bold;">HR Management</div>
    </a>
    <ul class="menu-sub">
      <!-- Leave - Available to ALL -->
      <li class="menu-item {{ request()->routeIs('modules.hr.leave') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.leave') }}" class="menu-link">
          <div data-i18n="Leave" style="font-weight: bold;">Leave</div>
        </a>
      </li>
      
      <!-- Employee - Available to ALL (access controlled in controller) -->
      <li class="menu-item {{ request()->routeIs('modules.hr.employees*') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.employees') }}" class="menu-link">
          <div data-i18n="Employee" style="font-weight: bold;">Employee</div>
        </a>
      </li>
      
      <!-- Attendance - Available to ALL (access controlled in controller) -->
      <li class="menu-item {{ request()->routeIs('modules.hr.attendance') || request()->routeIs('attendance.*') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.attendance') }}" class="menu-link">
          <div data-i18n="Attendance" style="font-weight: bold;">Attendance</div>
        </a>
      </li>
      
      <!-- Assessments (All Users) -->
      <li class="menu-item {{ request()->routeIs('modules.assessments') || request()->routeIs('modules.assessments.*') ? 'active' : '' }}">
        <a href="{{ route('modules.assessments') }}" class="menu-link">
          <div data-i18n="Assessments" style="font-weight: bold;">Assessments</div>
        </a>
      </li>
      
      <!-- Sick Sheet - Available to ALL -->
      <li class="menu-item {{ request()->routeIs('modules.hr.sick-sheets') || request()->routeIs('sick-sheets.*') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.sick-sheets') }}" class="menu-link">
          <div data-i18n="Sick Sheet" style="font-weight: bold;">Sick Sheet</div>
        </a>
      </li>
      
      <!-- Permissions - Available to ALL -->
      <li class="menu-item {{ request()->routeIs('modules.hr.permissions') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.permissions') }}" class="menu-link">
          <div data-i18n="Permissions" style="font-weight: bold;">Permissions</div>
        </a>
      </li>
      
      <!-- Payroll - Available to ALL (access controlled in controller) -->
      <li class="menu-item {{ request()->routeIs('modules.hr.payroll*') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.payroll') }}" class="menu-link">
          <div data-i18n="Payroll" style="font-weight: bold;">Payroll</div>
        </a>
      </li>
      
      <!-- Departments Management - HR and Admin only -->
      @if($isHR || $isSystemAdmin)
      <li class="menu-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
        <a href="{{ route('departments.index') }}" class="menu-link">
          <div data-i18n="Departments" style="font-weight: bold;">Departments</div>
        </a>
      </li>
      @endif
      
      <!-- Positions Management - HR and Admin only -->
      @if($isHR || $isSystemAdmin)
      <li class="menu-item {{ request()->routeIs('positions.*') || request()->routeIs('modules.hr.positions') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.positions') }}" class="menu-link">
          <div data-i18n="Positions" style="font-weight: bold;">Positions</div>
        </a>
      </li>
      @endif
      
      <!-- Recruitment - Available to HR, HOD, CEO, System Admin -->
      @if($isHR || $isHOD || $isCEO || $isSystemAdmin)
      <li class="menu-item {{ request()->routeIs('modules.hr.recruitment') ? 'active' : '' }}">
        <a href="{{ route('modules.hr.recruitment') }}" class="menu-link">
          <div data-i18n="Recruitment" style="font-weight: bold;">Recruitment</div>
        </a>
      </li>
      @endif
      
    </ul>
  </li>


  <!-- Task Management -->
  <li class="menu-item {{ request()->routeIs('modules.tasks') ? 'active' : '' }}">
    <a href="{{ route('modules.tasks') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-clipboard"></i>
      <div data-i18n="Task Management" style="font-weight: bold;">Task Management</div>
    </a>
  </li>

  <!-- Meeting Management -->
  <li class="menu-item {{ request()->routeIs('modules.meetings.*') ? 'active open' : '' }}">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon tf-icons bx bx-group"></i>
      <div data-i18n="Meetings & Minutes" style="font-weight: bold;">Meetings & Minutes</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item {{ request()->routeIs('modules.meetings.index') ? 'active' : '' }}">
        <a href="{{ route('modules.meetings.index') }}" class="menu-link">
          <div data-i18n="Meetings">Meetings</div>
        </a>
      </li>
      <li class="menu-item {{ request()->routeIs('modules.meetings.minutes.*') ? 'active' : '' }}">
        <a href="{{ route('modules.meetings.minutes.index') }}" class="menu-link">
          <div data-i18n="Minutes">Minutes</div>
        </a>
      </li>
    </ul>
  </li>



  <!-- Incident Management -->
  <li class="menu-item {{ request()->routeIs('modules.incidents.*') ? 'active' : '' }}">
    <a href="{{ route('modules.incidents.dashboard') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-error-circle"></i>
      <div data-i18n="Incident Management" style="font-weight: bold;">Incident Management</div>
    </a>
  </li>

  <!-- Reporting -->
{{--   <li class="menu-item {{ request()->routeIs('modules.reports') ? 'active' : '' }}">
    <a href="{{ route('modules.reports') }}" class="menu-link">
      <i class="menu-icon tf-icons bx bx-bar-chart"></i>
      <div data-i18n="Reporting">Reporting</div>
    </a>
  </li> --}}

  <!-- System Administration -->
  @if($isSystemAdmin)
 
  <li class="menu-item {{ request()->routeIs('admin.*') || request()->routeIs('settings.*') ? 'active open' : '' }}" data-menu-key="system-settings">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon tf-icons bx bx-cog"></i>
      <div data-i18n="System Settings" style="font-weight: bold;">System Settings</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <a href="{{ route('admin.users.index') }}" class="menu-link">
          <div data-i18n="Manage Users" style="font-weight: bold;">Manage Users</div>
        </a>
      </li>


      <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <a href="{{ route('admin.permissions.index') }}" class="menu-link">
          <div data-i18n="System Permissions" style="font-weight: bold;">System Permissions</div>
        </a>
      </li>


      <li class="menu-item {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
        <a href="{{ route('admin.roles') }}" class="menu-link">
          <div data-i18n="Manage Roles" style="font-weight: bold;">Manage Roles</div>
        </a>
      </li>
     
      <li class="menu-item {{ request()->routeIs('admin.settings*') || request()->routeIs('settings.*') ? 'active' : '' }}">
        <a href="{{ route('admin.settings.organization') }}" class="menu-link">
         
          <div data-i18n="Organization Settings" style="font-weight: bold;">Organization Settings</div>
        </a>
      </li>
      <li class="menu-item {{ request()->routeIs('admin.system*') && !request()->routeIs('admin.system.errors*') ? 'active' : '' }}">
        <a href="{{ route('admin.system') }}" class="menu-link">
        
          <div data-i18n="System Status & Health" style="font-weight: bold;">System Health</div>
        </a>
      </li>
      <li class="menu-item {{ request()->routeIs('admin.system.errors*') ? 'active' : '' }}">
        <a href="{{ route('admin.system.errors') }}" class="menu-link">
          
          <div data-i18n="Recent System Errors" style="font-weight: bold;">System Errors</div>
        </a>
      </li>
      <li class="menu-item {{ request()->routeIs('admin.activity-log*') ? 'active' : '' }}">
        <a href="{{ route('admin.activity-log') }}" class="menu-link">
       
          <div data-i18n="Activity Log" style="font-weight: bold;">Activity Log</div>
        </a>
      </li>
    </ul>
  </li>
  @endif
</ul>

@push('scripts')
<script>
(function() {
    'use strict';
    
    /**
     * Check if a menu item has any active children
     */
    function hasActiveChild(menuItem) {
        const menuSub = menuItem.querySelector('.menu-sub');
        if (!menuSub) return false;
        
        return menuSub.querySelector('.menu-item.active') !== null;
    }
    
    /**
     * Expand parent menu ONLY if it has active children
     */
    function expandActiveParents() {
        // First, collapse all menus that don't have active children
        document.querySelectorAll('.menu-item[data-menu-key]').forEach(function(menuItem) {
            const hasActive = hasActiveChild(menuItem);
            const menuSub = menuItem.querySelector('.menu-sub');
            
            if (hasActive) {
                // Expand if it has active children
                menuItem.classList.add('open', 'active');
                if (menuSub) {
                    menuSub.style.display = 'block';
                }
            } else {
                // Collapse if it doesn't have active children (unless manually opened)
                // Only remove 'open' if it was opened due to active state, not manual toggle
                // We'll keep manual toggles working, but on page load, only expand active ones
                if (!menuItem.classList.contains('manually-opened')) {
                    menuItem.classList.remove('open');
                    if (menuSub) {
                        menuSub.style.display = 'none';
                    }
                }
            }
        });
        
        // Also ensure all parent menus of active items are expanded
        document.querySelectorAll('.menu-item.active').forEach(function(activeItem) {
            // Check if it's a child item (inside menu-sub)
            const menuSub = activeItem.closest('.menu-sub');
            if (menuSub) {
                // Find parent menu item
                const parentMenuItem = menuSub.closest('.menu-item');
                if (parentMenuItem) {
                    // Expand parent
                    parentMenuItem.classList.add('open', 'active');
                    const parentMenuSub = parentMenuItem.querySelector('.menu-sub');
                    if (parentMenuSub) {
                        parentMenuSub.style.display = 'block';
                    }
                }
            }
        });
    }
    
    /**
     * Initialize menu toggle handlers
     */
    function initMenuToggles() {
        document.querySelectorAll('.menu-link.menu-toggle').forEach(function(toggle) {
            // Remove any existing listeners by cloning
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const menuItem = this.closest('.menu-item');
                if (!menuItem) return;
                
                const menuSub = menuItem.querySelector('.menu-sub');
                if (!menuSub) return;
                
                const isOpen = menuItem.classList.contains('open');
                
                if (isOpen) {
                    menuItem.classList.remove('open');
                    menuSub.style.display = 'none';
                    menuItem.classList.remove('manually-opened');
                } else {
                    menuItem.classList.add('open', 'manually-opened');
                    menuSub.style.display = 'block';
                }
            });
        });
    }
    
    /**
     * Ensure active menu items are visible and parent menus are expanded
     */
    function ensureActiveVisible() {
        document.querySelectorAll('.menu-item.active').forEach(function(activeItem) {
            // Expand all parent menus
            let parent = activeItem.parentElement;
            while (parent && parent !== document.body) {
                if (parent.classList.contains('menu-sub')) {
                    const parentMenuItem = parent.closest('.menu-item');
                    if (parentMenuItem) {
                        parentMenuItem.classList.add('open', 'active');
                        parent.style.display = 'block';
                    }
                }
                parent = parent.parentElement;
            }
        });
    }
    
    /**
     * Initialize sidebar menu functionality
     */
    function initSidebarMenu() {
        // Wait for main menu to initialize first
        setTimeout(function() {
            // First, collapse all menus
            document.querySelectorAll('.menu-item[data-menu-key]').forEach(function(menuItem) {
                const menuSub = menuItem.querySelector('.menu-sub');
                if (menuSub) {
                    menuItem.classList.remove('open');
                    menuSub.style.display = 'none';
                }
            });
            
            // Then expand only those with active children
            expandActiveParents();
            
            // Ensure active items are visible
            ensureActiveVisible();
            
            // Initialize toggle handlers
            initMenuToggles();
            
            // Re-check after a short delay to ensure everything is set
            setTimeout(function() {
                expandActiveParents();
                ensureActiveVisible();
            }, 100);
        }, 100);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarMenu);
    } else {
        initSidebarMenu();
    }
    
    // Also initialize after window load (in case menu system loads later)
    window.addEventListener('load', function() {
        setTimeout(initSidebarMenu, 200);
    });
    
    // Re-check on navigation (for AJAX navigation)
    let lastPath = window.location.pathname;
    setInterval(function() {
        const currentPath = window.location.pathname;
        if (currentPath !== lastPath) {
            lastPath = currentPath;
            setTimeout(function() {
                // Remove manually-opened flag on route change
                document.querySelectorAll('.menu-item.manually-opened').forEach(function(item) {
                    item.classList.remove('manually-opened');
                });
                expandActiveParents();
                ensureActiveVisible();
            }, 100);
        }
    }, 500);
})();
</script>
@endpush
