@php
    $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
    if (!empty(session()->get('lang'))) {
        $currantLang = session()->get('lang');
    } else {
        $currantLang = $company_settings['defult_language'];
    }
    $languages = languages();
    \App::setLocale($currantLang);
    $lang = $currantLang;

    $admin_settings = getAdminAllSetting();

    $favicon = isset($company_settings['favicon'])
        ? $company_settings['favicon']
        : (isset($admin_settings['favicon'])
            ? $admin_settings['favicon']
            : 'uploads/logo/favicon.png');
    $rtl = isset($company_settings['site_rtl'])
        ? $company_settings['site_rtl']
        : (isset($admin_settings['site_rtl'])
            ? $admin_settings['site_rtl']
            : 'off');
@endphp

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="quiz - management">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    <title>{{ __('Quiz Management') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="quiz - management">
    <meta name="keywords" content="quiz - management">


    <meta name="title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Modules Dash' }}">
    <meta name="keywords"
        content="{{ !empty($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'Modules Dash,SaaS solution,Multi-workspace' }}">
    <meta name="description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Modules.' }}">
    <!-- Open Graph / Facebook -->

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Modules Dash' }}">
    <meta property="og:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Modules.' }} ">
    <meta property="og:image"
        content="{{ get_file(!empty($admin_settings['meta_image']) ? (check_file($admin_settings['meta_image']) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png') : 'uploads/meta/meta_image.png') }}{{ '?' . time() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Modules Dash' }}">
    <meta property="twitter:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Modules.' }} ">
    <meta property="twitter:image"
        content="{{ get_file(!empty($admin_settings['meta_image']) ? (check_file($admin_settings['meta_image']) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png') : 'uploads/meta/meta_image.png') }}{{ '?' . time() }}">
    <link rel="icon"
        href="{{ check_file($favicon) ? get_file($favicon) : get_file('uploads/logo/favicon.png') }}{{ '?' . time() }}"
        type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @if ($currantLang == 'ar' || $rtl == 'on')
        <link rel="stylesheet"
            href="{{ asset('Modules/QuizManagement/Resources/assets/css/rtl-main-style.css') }}">
        <link rel="stylesheet"
            href="{{ asset('Modules/QuizManagement/Resources/assets/css/rtl-responsive.css') }}">
    @else
        <link rel="stylesheet"
            href="{{ asset('Modules/QuizManagement/Resources/assets/css/main-style.css') }}">
        <link rel="stylesheet"
            href="{{ asset('Modules/QuizManagement/Resources/assets/css/responsive.css') }}">
    @endif
    @stack('css')
</head>
