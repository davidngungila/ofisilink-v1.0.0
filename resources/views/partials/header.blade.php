<header class="ttr-header">
    <div class="ttr-header-wrapper">
        <div class="ttr-toggle-sidebar ttr-material-button">
            <i class="ti-close ttr-open-icon"></i>
            <i class="ti-menu ttr-close-icon"></i>
        </div>
		<div class="ttr-logo-box" style="display:flex;justify-content:center;align-items:center;width:100%;">
            <div>
				<a href="{{ url('/dashboard') }}" class="ttr-logo">
					<x-logo class="ttr-logo-mobile" width="72" alt="OfisiLink" />
					<x-logo class="ttr-logo-desktop" width="320" alt="OfisiLink" />
                </a>
            </div>
        </div>
        <div class="ttr-header-right ttr-with-seperator">
            <h2>Ofice Management System</h2>
            <ul class="ttr-header-navigation">
                <li>
                    <a href="#" class="ttr-material-button ttr-search-toggle"><i class="fa fa-search"></i></a>
                    
                </li>
                <li>
                    <a href="#" class="ttr-material-button ttr-submenu-toggle">
                        <span class="ttr-user-avatar">
                            @if(auth()->user()->photo)
                                @php
                                  $photoUrl = route('storage.photos', ['filename' => auth()->user()->photo]);
                                @endphp
                                <img alt="{{ auth()->user()->name }}" src="{{ $photoUrl }}?t={{ time() }}" width="32" height="32" class="rounded-circle user-profile-avatar" data-profile-image="true" style="object-fit: cover;">
                            @else
                                <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center user-profile-avatar" data-profile-image="true" style="width: 32px; height: 32px; font-size: 0.875rem;">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            @endif
                        </span>
                    </a>
                    <div class="ttr-header-submenu">
                        <ul>
                            <li><a href="{{ route('account.settings.index') }}">My profile</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" style="display:inline;" id="logout-form">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-decoration-none p-0" style="background:none;border:0;color:inherit;width:100%;text-align:left;">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
// Ensure logout form is properly submitted
document.addEventListener('DOMContentLoaded', function() {
    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    }
});
</script>

