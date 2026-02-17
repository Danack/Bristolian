// Handle horizontal resizing for text_note_iframe_container
// The CSS resize property works, but flex layout can override the width
// This script ensures that when the container is resized, the flex-basis
// is updated to match, allowing horizontal resizing to work properly.

(function() {
    'use strict';
    
    function setupIframeResize() {
        const containers = document.querySelectorAll('.text_note_iframe_container');
        
        containers.forEach(function(container) {
            let lastWidth = container.offsetWidth;
            let updating = false;
            
            // Use ResizeObserver to detect when the element is resized
            let resizeObserver = new ResizeObserver(function(entries) {
                if (updating) {
                    return; // Prevent recursive updates
                }
                
                for (let entry of entries) {
                    const target = entry.target;
                    const currentWidth = entry.contentRect.width;
                    
                    // Only update if width actually changed
                    if (Math.abs(currentWidth - lastWidth) > 1) {
                        updating = true;
                        
                        // Update flex-basis to match the current width
                        // This ensures flex layout respects the resized width
                        target.style.flexBasis = currentWidth + 'px';
                        
                        lastWidth = currentWidth;
                        
                        // Allow updates again after a brief delay
                        requestAnimationFrame(function() {
                            updating = false;
                        });
                    }
                }
            });
            
            resizeObserver.observe(container);
        });
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupIframeResize);
    } else {
        setupIframeResize();
    }
})();

