<script>
// Global modal fix for all accounting pages
$(document).ready(function() {
    // Clean up modals on page load
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    // Fix modal backdrop stacking
    $(document).on('show.bs.modal', '.modal', function() {
        // Remove any existing backdrops
        $('.modal-backdrop').not(':last').remove();
        
        // Set proper z-index
        $(this).css('z-index', 1050);
    });
    
    $(document).on('shown.bs.modal', '.modal', function() {
        // Ensure proper z-index after modal is shown
        $(this).css('z-index', 1050);
        $('.modal-backdrop').last().css('z-index', 1040);
        
        // Enable pointer events
        $(this).find('.modal-content').css('pointer-events', 'auto');
        $(this).find('.modal-body').css('pointer-events', 'auto');
        $(this).find('.modal-footer').css('pointer-events', 'auto');
        $(this).find('.modal-header').css('pointer-events', 'auto');
    });
    
    $(document).on('hidden.bs.modal', '.modal', function() {
        // Clean up backdrops when modal is hidden
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    
    // Prevent body scroll when modal is open
    $(document).on('show.bs.modal', '.modal', function() {
        $('body').addClass('modal-open');
    });
    
    $(document).on('hidden.bs.modal', '.modal', function() {
        $('body').removeClass('modal-open');
    });
});

// Function to safely show modals
function showModal(modalId, options = {}) {
    // Close any existing modals
    $('.modal').modal('hide');
    
    // Wait for cleanup
    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        // Show new modal
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: options.backdrop || 'static',
                keyboard: options.keyboard !== undefined ? options.keyboard : false
            });
            
            modal.show();
            
            // Ensure responsiveness
            modalElement.addEventListener('shown.bs.modal', function() {
                $(this).css('z-index', 1050);
                $('.modal-backdrop').css('z-index', 1040);
                $(this).find('.modal-content').css('pointer-events', 'auto');
            }, { once: true });
        }
    }, options.delay || 100);
}
</script>


