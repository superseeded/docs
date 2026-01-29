/**
 * SuperSeeded Upload Widget Initializer
 * Initializes all SuperSeeded upload containers on the page
 */
(function() {
    'use strict';

    // Wait for DOM and SuperSeed SDK to be ready
    function init() {
        if (typeof SuperSeed === 'undefined') {
            console.error('SuperSeeded: Embed script not loaded');
            return;
        }

        if (typeof superseededConfig === 'undefined') {
            console.error('SuperSeeded: Configuration not found');
            return;
        }

        // Find all upload containers
        var containers = document.querySelectorAll('.superseeded-upload-container');

        containers.forEach(function(container) {
            initializeWidget(container);
        });
    }

    function initializeWidget(container) {
        // Get instance-specific overrides from data attributes
        var merchantId = container.dataset.merchantId || superseededConfig.merchantId;
        var theme = container.dataset.theme || superseededConfig.theme;

        // Build configuration
        var config = {
            container: '#' + container.id,
            tokenEndpoint: superseededConfig.tokenEndpoint,
            theme: theme,
            allowedFileTypes: superseededConfig.allowedFileTypes,
            maxFileSize: superseededConfig.maxFileSize,

            // Custom token fetch to include nonce and merchant_id
            beforeTokenFetch: function(fetchOptions) {
                fetchOptions.headers = fetchOptions.headers || {};
                fetchOptions.headers['X-WP-Nonce'] = superseededConfig.nonce;
                fetchOptions.method = 'POST';
                fetchOptions.body = JSON.stringify({ merchant_id: merchantId });
                fetchOptions.headers['Content-Type'] = 'application/json';
                return fetchOptions;
            },

            onFileAdded: function(file) {
                // Dispatch custom event for WordPress integration
                container.dispatchEvent(new CustomEvent('superseeded:file-added', {
                    detail: file,
                    bubbles: true
                }));
            },

            onProgress: function(progress) {
                container.dispatchEvent(new CustomEvent('superseeded:progress', {
                    detail: progress,
                    bubbles: true
                }));
            },

            onComplete: function(result) {
                container.dispatchEvent(new CustomEvent('superseeded:complete', {
                    detail: result,
                    bubbles: true
                }));
            },

            onError: function(error) {
                container.dispatchEvent(new CustomEvent('superseeded:error', {
                    detail: error,
                    bubbles: true
                }));
            }
        };

        // Initialize the widget
        try {
            var instance = SuperSeed.init(config);
            container._superseededInstance = instance;
        } catch (error) {
            console.error('SuperSeeded: Failed to initialize widget', error);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose initialization function for dynamic content
    window.SuperSeededWordPress = {
        init: init,
        initializeWidget: initializeWidget
    };
})();
