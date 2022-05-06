<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulse Advertising</title>
    <link rel="shortcut icon" href="{{reactAsset('favicon.ico')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @font-face {
            font-family: 'PoppinsNormal';
            src: url('{{reactAsset("fonts/poppins/Poppins-Regular.woff2")}}') format('woff2');
        }
        @font-face {
        font-family: 'PoppinsBold';
        src: url("{{reactAsset('fonts/poppins/Poppins-Bold.woff2')}}") format('woff2');
        }
    </style>
    <link rel="stylesheet" href="{{reactAsset('css/ReactApp.css?')}}">
    
</head>
<body class="bg-gray-100" baseUrl = {{url('/')}} assetUrl={{\getAssetUrl()}} csrf="{{csrf_token()}}"  host = "{{ getHostForNoti() }}">
    <div id="root"></div>
    <script src="{{reactAsset('js/ReactApp.js?'.time())}}"></script>
    <script src="{{reactAsset('js/prebid-ads.js')}}" type="text/javascript"></script>
    <script src="{{reactAsset('js/check.js?'.time())}}"></script>
</body>
</html>