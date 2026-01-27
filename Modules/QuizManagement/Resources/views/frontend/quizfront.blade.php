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
    // dd(str_replace('_', '-', app()->getLocale('lang')));
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
@include('quiz-management::frontend.quizhead')

<body class="index">
    @include('quiz-management::frontend.quizheader')
    <main>
        @yield('content')
    </main>

    @include('quiz-management::frontend.quizfooter')
    @stack('script-page')
    @if ($message = Session::get('success'))
        <script>
            show_toastr('{{ __('Success') }}', '{{ __($message) }}', 'success');
        </script>
    @endif
    @if ($message = Session::get('error'))
        <script>
            show_toastr('{{ __('Error') }}', '{{ __($message) }}', 'error');
        </script>
    @endif
</body>

</html>
