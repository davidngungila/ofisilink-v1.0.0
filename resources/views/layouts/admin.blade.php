<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>Ofisi System</title>
	<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/assets.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/calendar/fullcalendar.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/typography.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/shortcodes/shortcodes.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/dashboard.css') }}">
	<link class="skin" rel="stylesheet" type="text/css" href="{{ asset('assets/css/color/color-1.css') }}">
	@stack('styles')
</head>
<body class="ttr-opened-sidebar ttr-pinned-sidebar">
	@include('partials.header')
	@include('partials.sidebar')
	<main class="ttr-wrapper">
		<div class="container-fluid">
			@yield('content')
		</div>
	</main>
	<div class="ttr-overlay"></div>

	<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/bootstrap/js/popper.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/bootstrap-select/bootstrap-select.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/bootstrap-touchspin/jquery.bootstrap-touchspin.js') }}"></script>
	<script src="{{ asset('assets/vendors/magnific-popup/magnific-popup.js') }}"></script>
	<script src="{{ asset('assets/vendors/counter/waypoints-min.js') }}"></script>
	<script src="{{ asset('assets/vendors/counter/counterup.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/imagesloaded/imagesloaded.js') }}"></script>
	<script src="{{ asset('assets/vendors/masonry/masonry.js') }}"></script>
	<script src="{{ asset('assets/vendors/masonry/filter.js') }}"></script>
	<script src="{{ asset('assets/vendors/owl-carousel/owl.carousel.js') }}"></script>
	<script src="{{ asset('assets/vendors/scroll/scrollbar.min.js') }}"></script>
	<script src="{{ asset('assets/js/functions.js') }}"></script>
	<script src="{{ asset('assets/vendors/chart/chart.min.js') }}"></script>
	<script src="{{ asset('assets/js/admin.js') }}"></script>
	<script src="{{ asset('assets/vendors/calendar/moment.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/calendar/fullcalendar.js') }}"></script>
	<script src="{{ asset('assets/vendors/switcher/switcher.js') }}"></script>
	@stack('scripts')
	
	<!-- Auto-logout on idle with 30-second warning -->
	<script>
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
									<i class="fa fa-clock-o me-2"></i>Session Timeout Warning
								</h5>
							</div>
							<div class="modal-body text-center py-4">
								<div class="mb-3">
									<i class="fa fa-clock-o fa-3x text-warning"></i>
								</div>
								<h6 class="mb-3">System Idle Detected</h6>
								<p class="text-muted mb-3">
									Your session has been idle for a while. The system will automatically log you out due to inactivity.
								</p>
								<div class="alert alert-warning mb-3">
									<strong>Auto-logout in: <span id="countdownTimer" class="text-danger" style="font-size: 1.5rem;">30</span> seconds</strong>
								</div>
								<p class="text-muted small">
									Click "Continue" below if you want to stay logged in.
								</p>
							</div>
							<div class="modal-footer justify-content-center">
								<button type="button" class="btn btn-primary btn-lg" id="continueSessionBtn">
									<i class="fa fa-check-circle me-2"></i>Continue Session
								</button>
							</div>
						</div>
					</div>
				</div>
			`;
			
			document.body.insertAdjacentHTML('beforeend', modalHTML);
			const modalElement = document.getElementById('idleWarningModal');
			if (modalElement && typeof bootstrap !== 'undefined') {
				warningModal = new bootstrap.Modal(modalElement, {
					backdrop: 'static',
					keyboard: false
				});
			} else if (modalElement && typeof jQuery !== 'undefined') {
				// Fallback for jQuery/bootstrap 4
				warningModal = jQuery(modalElement);
			}
			
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
			const countdownEl = document.getElementById('countdownTimer');
			if (countdownEl) {
				countdownEl.textContent = countdownSeconds;
			}
			
			if (warningModal) {
				if (typeof bootstrap !== 'undefined') {
					warningModal.show();
				} else if (typeof jQuery !== 'undefined') {
					jQuery(warningModal).modal('show');
				}
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
				if (typeof bootstrap !== 'undefined') {
					warningModal.hide();
				} else if (typeof jQuery !== 'undefined') {
					jQuery(warningModal).modal('hide');
				}
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
	</script>
</body>
</html>


