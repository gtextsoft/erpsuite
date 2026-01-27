@extends('layouts.main')
@section('breadcrumb')
@endsection
@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('Quizzes') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('packages/Modules/QuizManagement/src/Resources/assets/css/custom.css') }}">
@endpush

@section('content')
    @permission('quiz dashboard manage')
        @if (\Auth::user()->type == 'company')
            <div class="row row-gap mb-4 ">
                <div class="col-xl-6 col-12">
                    <div class="dashboard-card">
                        <img src="{{ asset('assets/images/layer.png') }}" class="dashboard-card-layer" alt="layer">
                        <div class="card-inner">
                            <div class="card-content">
                                <h2>{{ !empty($workspace) ? $workspace->name : 'Modules' }}</h2>
                                <p>{!! 'The Quiz Management add-on for Modules Dash simplifies quiz creation, tacking, and performance analysis' !!}
                                </p>
                                <div class="btn-wrp d-flex gap-3">
                                    <a href="#" class="btn btn-primary d-flex align-items-center gap-1 cp_link"
                                        data-link="{{ route('quizzes.participate.show', $workspace->slug) }}"
                                        data-bs-whatever="{{ __('Quiz Participate Link') }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Quiz Participate') }}"
                                        title="{{ __('Click to copy quiz participate link') }}">
                                        <i class="ti ti-link text-white"></i>
                                        <span>{{ __('Quiz Participate Link') }}</span>
                                    </a>
                                </div>
                            </div>

                            <div class="card-icon d-flex align-items-center justify-content-center">
                                <svg width="66" height="66" viewBox="0 0 66 66" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M55.6875 4.125H40.2188C38.5781 4.1263 37.0051 4.77861 35.845 5.93871C34.6849 7.09881 34.0325 8.67187 34.0312 10.3125V25.7812C34.0325 27.4219 34.6849 28.9949 35.845 30.155C37.0051 31.3151 38.5781 31.9675 40.2188 31.9688H55.6875C57.3281 31.9675 58.9012 31.3151 60.0613 30.155C61.2214 28.9949 61.8737 27.4219 61.875 25.7812V10.3125C61.8737 8.67187 61.2214 7.09881 60.0613 5.93871C58.9012 4.77861 57.3281 4.1263 55.6875 4.125ZM53.4187 22.4812C53.4167 23.3412 53.0735 24.1652 52.4644 24.7723C51.8554 25.3795 51.0303 25.7201 50.1703 25.7194H45.7359C44.876 25.7201 44.0509 25.3795 43.4418 24.7723C42.8328 24.1652 42.4895 23.3412 42.4875 22.4812V11.4056C42.4923 11.1353 42.6031 10.8777 42.796 10.6883C42.9889 10.4988 43.2484 10.3927 43.5188 10.3927C43.7891 10.3927 44.0487 10.4989 44.2415 10.6883C44.4344 10.8778 44.5452 11.1354 44.55 11.4057V22.4812C44.5522 22.7942 44.6782 23.0935 44.9004 23.3138C45.1226 23.5341 45.423 23.6574 45.7359 23.6569H50.1703C50.4832 23.6574 50.7836 23.5341 51.0059 23.3138C51.2281 23.0935 51.3541 22.7942 51.3562 22.4812V11.4056C51.3562 11.1321 51.4649 10.8698 51.6583 10.6764C51.8517 10.483 52.114 10.3744 52.3875 10.3744C52.661 10.3744 52.9233 10.483 53.1167 10.6764C53.3101 10.8698 53.4187 11.1321 53.4187 11.4056V22.4812Z"
                                        fill="#18BF6B" />
                                    <path opacity="0.6"
                                        d="M55.6875 34.0312H40.2188C38.5781 34.0325 37.0051 34.6849 35.845 35.845C34.6849 37.0051 34.0325 38.5781 34.0312 40.2188V55.6875C34.0325 57.3281 34.6849 58.9012 35.845 60.0613C37.0051 61.2214 38.5781 61.8737 40.2188 61.875H55.6875C57.3281 61.8737 58.9012 61.2214 60.0613 60.0613C61.2214 58.9012 61.8737 57.3281 61.875 55.6875V40.2188C61.8737 38.5781 61.2214 37.0051 60.0613 35.845C58.9012 34.6849 57.3281 34.0325 55.6875 34.0312ZM52.3875 55.6256H43.5188C43.3334 55.6255 43.1514 55.5757 42.9918 55.4814C42.8322 55.3871 42.7008 55.2517 42.6112 55.0894C42.5253 54.9235 42.4848 54.7379 42.4939 54.5513C42.5029 54.3648 42.5612 54.1839 42.6628 54.0272L50.4591 42.3431H43.5188C43.2479 42.3391 42.9895 42.2287 42.7994 42.0357C42.6093 41.8427 42.5027 41.5827 42.5027 41.3118C42.5027 41.041 42.6093 40.781 42.7994 40.588C42.9895 40.395 43.2479 40.2846 43.5188 40.2806H52.3875C52.5729 40.2807 52.7549 40.3305 52.9145 40.4249C53.0741 40.5192 53.2055 40.6545 53.295 40.8169C53.3809 40.9827 53.4214 41.1684 53.4123 41.3549C53.4033 41.5415 53.345 41.7223 53.2434 41.8791L45.4472 53.5631H52.3875C52.6584 53.5671 52.9168 53.6776 53.1069 53.8705C53.297 54.0635 53.4036 54.3235 53.4036 54.5944C53.4036 54.8653 53.297 55.1253 53.1069 55.3182C52.9168 55.5112 52.6584 55.6216 52.3875 55.6256Z"
                                        fill="#18BF6B" />
                                    <path
                                        d="M25.7812 34.0312H10.3125C8.67187 34.0325 7.09881 34.6849 5.93871 35.845C4.77861 37.0051 4.1263 38.5781 4.125 40.2188V55.6875C4.1263 57.3281 4.77861 58.9012 5.93871 60.0613C7.09881 61.2214 8.67187 61.8737 10.3125 61.875H25.7812C27.4219 61.8737 28.9949 61.2214 30.155 60.0613C31.3151 58.9012 31.9675 57.3281 31.9688 55.6875V40.2188C31.9675 38.5781 31.3151 37.0051 30.155 35.845C28.9949 34.6849 27.4219 34.0325 25.7812 34.0312ZM19.0781 52.9341C19.0731 53.2043 18.9623 53.4617 18.7694 53.6511C18.5766 53.8404 18.3171 53.9464 18.0468 53.9464C17.7766 53.9464 17.5171 53.8403 17.3243 53.651C17.1314 53.4617 17.0206 53.2042 17.0156 52.934V39.6412C17.0198 39.3705 17.1303 39.1123 17.3233 38.9223C17.5162 38.7323 17.7761 38.6259 18.0469 38.6259C18.3177 38.6259 18.5776 38.7324 18.7705 38.9224C18.9634 39.1123 19.0739 39.3706 19.0781 39.6413V52.9341Z"
                                        fill="#18BF6B" />
                                    <path opacity="0.6"
                                        d="M25.245 4.125H10.8488C9.06559 4.12531 7.35556 4.8338 6.09468 6.09468C4.8338 7.35556 4.12531 9.06559 4.125 10.8488V25.245C4.12532 27.0282 4.83382 28.7382 6.09469 29.9991C7.35557 31.2599 9.0656 31.9684 10.8488 31.9688H25.245C27.0282 31.9684 28.7382 31.2599 29.9991 29.9991C31.2599 28.7382 31.9684 27.0282 31.9688 25.245V10.8488C31.9684 9.06559 31.26 7.35556 29.9991 6.09468C28.7382 4.8338 27.0282 4.12531 25.245 4.125ZM25.8019 25.7194H14.9841C14.347 25.7187 13.7362 25.4654 13.2857 25.0149C12.8353 24.5644 12.5819 23.9536 12.5812 23.3166V12.7772C12.5819 12.1401 12.8352 11.5293 13.2857 11.0788C13.7362 10.6284 14.347 10.375 14.9841 10.3744H21.1097C21.7468 10.375 22.3576 10.6284 22.808 11.0788C23.2585 11.5293 23.5119 12.1401 23.5125 12.7772V23.3166C23.5102 23.4311 23.4964 23.5451 23.4713 23.6569H25.8019C26.0727 23.6609 26.3311 23.7713 26.5213 23.9643C26.7114 24.1572 26.8179 24.4172 26.8179 24.6881C26.8179 24.959 26.7114 25.219 26.5213 25.412C26.3311 25.6049 26.0727 25.7154 25.8019 25.7194Z"
                                        fill="#18BF6B" />
                                    <path opacity="0.6"
                                        d="M21.1066 12.4365H14.9809C14.793 12.4365 14.6406 12.5889 14.6406 12.7768V23.3162C14.6406 23.5042 14.793 23.6565 14.9809 23.6565H21.1066C21.2945 23.6565 21.4469 23.5042 21.4469 23.3162V12.7768C21.4469 12.5889 21.2945 12.4365 21.1066 12.4365Z"
                                        fill="#18BF6B" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-12">
                    <div class="row dashboard-wrp">
                        <div class="col-sm-6 col-12">
                            <div class="dashboard-project-card">
                                <div class="card-inner  d-flex justify-content-between">
                                    <div class="card-content">
                                        <div class="theme-avtar bg-white">
                                            <i class="ti ti-puzzle text-danger"></i>
                                        </div>
                                        <a href="{{ route('quizzes.index') }}">
                                            <h3 class="mt-3 mb-0 text-danger">{{ __('Total Quizzes') }}</h3>
                                        </a>
                                    </div>
                                    <h3 class="mb-0">{{ $totalQuizzes }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="dashboard-project-card">
                                <div class="card-inner  d-flex justify-content-between">
                                    <div class="card-content">
                                        <div class="theme-avtar bg-white">
                                            <i class="ti ti-notebook"></i>
                                        </div>
                                        <a href="{{ route('quiz.questions.index') }}">
                                            <h3 class="mt-3 mb-0">{{ __('Total Questions') }}</h3>
                                        </a>
                                    </div>
                                    <h3 class="mb-0">{{ $quizQuestions }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="dashboard-project-card">
                                <div class="card-inner  d-flex justify-content-between">
                                    <div class="card-content">
                                        <div class="theme-avtar bg-white">
                                            <i class="ti ti-users"></i>
                                        </div>
                                        <a href="{{ route('quiz.participate.index') }}">
                                            <h3 class="mt-3 mb-0">{{ __('Total Participants') }}</h3>
                                        </a>
                                    </div>
                                    <h3 class="mb-0">{{ $quizParticipants }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="dashboard-project-card">
                                <div class="card-inner d-flex justify-content-between">
                                    <div class="card-content">
                                        <div class="theme-avtar bg-white">
                                            <i class="ti ti-presentation-analytics"></i>
                                        </div>
                                        <a href="{{ route('quiz.results.index') }}">
                                            <h3 class="mt-3 mb-0">{{ __('Total Results') }}</h3>
                                        </a>
                                    </div>
                                    <h3 class="mb-0">{{ $quizResults }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-xxl-7 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        {{ __('Results') }}
                                    </h5>
                                </div>
                                <div class="float-end">
                                    <small>
                                        <b>
                                            @if (count($resultsLast_5) >= 5)
                                                {{ __('Last 5 Quiz Results') }}
                                            @else
                                                {{ __('Last') }} {{ count($resultsLast_5) }} {{ __('Quiz Results') }}
                                            @endif
                                        </b>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body ">
                        <div class="table-responsive custom-scrollbar" style="max-height: 515px; overflow-y:auto;">
                            <table class="table table-centered table-hover mb-0 animated">
                                <tbody>
                                    @forelse ($resultsLast_5 as $quizResult)
                                        <tr>
                                            <td>
                                                <div class="font-14 my-1">
                                                    <a href="{{ route('quiz.results.index') }}" class="text-body">
                                                        {{ optional($quizResult->participants)->name ?? '-' }}
                                                    </a>
                                                </div>
                                                <span class="text-muted font-13">{{ __('Attempt Date : ') }}
                                                    <span
                                                        class="text-success">{{ date('Y-m-d', strtotime($quizResult->attempt_date)) }}</span>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted font-13">{{ __('Status') }}</span> <br>
                                                @if ($quizResult->status == 'Passed')
                                                    <span class="badge bg-primary p-2 px-3">{{ __('Passed') }}</span>
                                                @else
                                                    <span class="badge bg-danger p-2 px-3">{{ __('Failed') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted font-13">{{ __('Quiz') }}</span>
                                                <div class="font-14 mt-1 font-weight-normal">
                                                    {{ optional($quizResult->quizzes)->name ?? '-' }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('layouts.nodatafound')
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Quiz Analytics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="quiz-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    @endpermission
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });

        (function() {
            var options = {
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                        name: 'Quizzes',
                        data: {!! json_encode($datachartArr['quizzes']) !!},
                    },
                    {
                        name: 'Participants',
                        data: {!! json_encode($datachartArr['participants']) !!},
                    },
                    {
                        name: 'Passed',
                        data: {!! json_encode($datachartArr['passed']) !!},
                    },
                    {
                        name: 'Failed',
                        data: {!! json_encode($datachartArr['failed']) !!},
                    }
                ],
                xaxis: {
                    categories: {!! json_encode($datachartArr['day']) !!},
                    title: {
                        text: '{{ __('Date') }}'
                    }
                },
                colors: ['#ffa21d', '#3ec9d6', '#0CAF60', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                markers: {
                    size: 4,
                    colors: ['#ffa21d', '#3ec9d6', '#0CAF60', '#FF3A6E'],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                },
            };
            var chart = new ApexCharts(document.querySelector("#quiz-chart"), options);
            chart.render();
        })();
    </script>
@endpush
