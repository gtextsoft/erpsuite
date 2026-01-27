    <!-- header start here -->

    <header class="site-header">
        <div class="container">
            <div class="main-navigationbar d-flex align-items-center justify-content-between">
                <div class="logo-col">
                    <h1 class="mb-0">
                        <a href="{{ route('quizzes.participate.show', [$workspace->slug, $quiz->id ?? 1]) }}">
                            @if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on')
                                <img src="{{ check_file(company_setting('logo_light', $workspace->created_by, $workspace->id)) ? get_file(company_setting('logo_light', $workspace->created_by, $workspace->id)) : get_file(!empty(admin_setting('logo_light')) ? admin_setting('logo_light') : 'uploads/logo/logo_light.png') }}{{ '?' . time() }}"
                                    class="navbar-brand-img auth-navbar-brand">
                            @else
                                <img src="{{ check_file(company_setting('logo_dark', $workspace->created_by, $workspace->id)) ? get_file(company_setting('logo_dark', $workspace->created_by, $workspace->id)) : get_file(!empty(admin_setting('logo_dark')) ? admin_setting('logo_dark') : 'uploads/logo/logo_dark.png') }}{{ '?' . time() }}"
                                    class="navbar-brand-img auth-navbar-brand">
                            @endif
                        </a>
                    </h1>
                </div>
                <div class="menu-item-right d-flex align-items-center justify-content-end">
                    <div class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="true">
                            <span class="drp-text hide-mob">{{ Str::upper($currantLang) }}</span>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" data-popper-placement="bottom-end"
                            style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 58px);">
                            @foreach ($languages as $key => $language)
                                <a href="{{ route('quiz.change.languagestore',[$slug,$key]) }}" class="dropdown-item @if($key == $currantLang) text-primary active @endif">
                                    <span>{{$language}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header end here -->
