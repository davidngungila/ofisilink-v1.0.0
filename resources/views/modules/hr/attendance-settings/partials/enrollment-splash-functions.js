// Registration Splash Screen Functions
function showRegistrationSplash(employeeName, enrollId) {
    const splash = document.getElementById('registrationSplash');
    const title = document.getElementById('splashTitle');
    const message = document.getElementById('splashMessage');
    
    if (!splash) return;
    
    title.textContent = 'Registering User to Device';
    message.textContent = 'Registering "' + employeeName + '" (Enroll ID: ' + enrollId + ') to device...';
    
    // Reset steps
    for (let i = 1; i <= 5; i++) {
        const step = document.getElementById('step' + i);
        if (step) {
            step.className = 'splash-step';
            step.textContent = step.textContent.replace(/^✓ /, '');
        }
    }
    
    // Reset icon
    const splashIcon = splash.querySelector('.splash-icon');
    if (splashIcon) {
        splashIcon.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
        splashIcon.className = 'splash-icon text-primary';
    }
    
    splash.classList.add('show');
    updateSplashStep(1, 'Connecting to device...');
}

function updateSplashStep(stepNum, message) {
    // Mark previous steps as completed
    for (let i = 1; i < stepNum; i++) {
        const step = document.getElementById('step' + i);
        if (step && !step.classList.contains('completed')) {
            step.classList.add('completed');
            step.classList.remove('active');
        }
    }
    
    // Update current step
    const currentStep = document.getElementById('step' + stepNum);
    if (currentStep) {
        currentStep.classList.add('active');
        currentStep.classList.remove('completed');
        if (message) {
            currentStep.textContent = message;
        }
    }
    
    // Update progress bar
    const progressBar = document.getElementById('splashProgressBar');
    if (progressBar) {
        const progress = (stepNum / 5) * 100;
        progressBar.style.width = progress + '%';
    }
}

function hideRegistrationSplash() {
    const splash = document.getElementById('registrationSplash');
    if (splash) {
        splash.classList.remove('show');
    }
}









