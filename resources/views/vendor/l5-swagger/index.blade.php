<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body {
      margin:0;
      background: #fafafa;
    }
    </style>
    <!-- Add meta tags for JavaScript -->
    <meta name="urlToDocs" content="{{ url('/docs/api-docs.json') }}">
    <meta name="operationsSorter" content="{{ isset($operationsSorter) ? $operationsSorter : 'null' }}">
    <meta name="configUrl" content="{{ isset($configUrl) ? $configUrl : 'null' }}">
    <meta name="validatorUrl" content="{{ isset($validatorUrl) ? $validatorUrl : 'null' }}">
    <meta name="oauth2RedirectUrl" content="{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="docExpansion" content="{{ config('l5-swagger.defaults.ui.display.doc_expansion', 'none') }}">
    <meta name="filter" content="{{ config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' }}">
    <meta name="persistAuthorization" content="{{ config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' }}">
    <meta name="usePkceWithAuthorizationCodeGrant" content="{{ (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') ? 'true' : 'false' }}">
</head>

<body>
<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
<script src="{{ asset('js/swagger-config.js') }}"></script>
</body>
</html>