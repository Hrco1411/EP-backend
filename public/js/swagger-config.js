window.onload = function() {
    // Build a system
    const ui = SwaggerUIBundle({
        dom_id: '#swagger-ui',
        url: document.querySelector('meta[name="urlToDocs"]').getAttribute('content'),
        operationsSorter: document.querySelector('meta[name="operationsSorter"]').getAttribute('content'),
        configUrl: document.querySelector('meta[name="configUrl"]').getAttribute('content'),
        validatorUrl: document.querySelector('meta[name="validatorUrl"]').getAttribute('content'),
        oauth2RedirectUrl: document.querySelector('meta[name="oauth2RedirectUrl"]').getAttribute('content'),

        requestInterceptor: function(request) {
            request.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            return request;
        },

        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],

        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],

        layout: "StandaloneLayout",
        docExpansion : document.querySelector('meta[name="docExpansion"]').getAttribute('content'),
        deepLinking: true,
        filter: document.querySelector('meta[name="filter"]').getAttribute('content') === 'true',
        persistAuthorization: document.querySelector('meta[name="persistAuthorization"]').getAttribute('content') === 'true',
    });

    window.ui = ui;

    if (document.querySelector('meta[name="usePkceWithAuthorizationCodeGrant"]').getAttribute('content') === 'true') {
        ui.initOAuth({
            usePkceWithAuthorizationCodeGrant: true
        });
    }
}
