@php
    /** @var \App\Features\Shared\Configuration\BitPayConfiguration $configuration **/
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $configuration->getDesign()->getHero()->getTitle() }}</title>
    <link rel="stylesheet" href="/css/styles.css" type="text/css" media="all">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @yield('head_scripts')
</head>

