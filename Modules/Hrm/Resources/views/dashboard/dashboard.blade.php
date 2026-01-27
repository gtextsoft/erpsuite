@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Hrm') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Hrm/Resources/assets/css/main.css') }}">
    <style>
        .present-btn { background-color: #28a745; color: white; padding: 2px 6px; border-radius: 4px; }
        .absent-btn { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; }
    </style>
@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        @if (!in_array(Auth::user()->type, Auth::user()->not_emp_type))
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xxl-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __("Holiday's ") }}</h5>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="card" style="height: 232px;">
                            <div class="card-header">
                                <h5>{{ __('Mark Attendance ') }}<span>{{ company_date_formate(date('Y-m-d')) }}</span></h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted pb-0-5">
                                    {{ __('My Office Time: ' . $officeTime['startTime'] . ' to ' . $officeTime['endTime']) }}
                                </p>
                                <div class="row">
                                    <div class="col-md-6 float-right border-right">
                                        {{ Form::open(['url' => route('attendance.clockin'), 'method' => 'post', 'id' => 'clockInForm']) }}
                                            @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary">{{ __('CLOCK IN') }}</button>
                                            @else
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn disabled" disabled>{{ __('CLOCK IN') }}</button>
                                            @endif
                                        {{ Form::close() }}
                                    </div>
                                    <div class="col-md-6 float-left">
                                        @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                            {{ Form::model($employeeAttendance, ['route' => ['attendance.update', $employeeAttendance->id], 'method' => 'PUT', 'id' => 'clockOutForm']) }}
                                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                                            {{ Form::close() }}
                                        @else
                                            <button type="submit" value="1" name="out" id="clock_out" class="btn btn disabled" disabled>{{ __('CLOCK OUT') }}</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body" style="height: 270px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ company_date_formate($announcement->start_date) }}</td>
                                                    <td>{{ company_date_formate($announcement->end_date) }}</td>
                                                    <td>{{ $announcement->description }}</td>
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
                </div>
            </div>
        @else
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xl-5">
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __("Today's Not Clocked In") }}</h5>
                            </div>
                            <div class="card-body" style="height: 290px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($notClockedIn as $employee)
                                                <tr>
                                                    <td>{{ $employee['name'] }}</td>
                                                    <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center">{{ __('All employees have clocked in today.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body" style="height: 270px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ company_date_formate($announcement->start_date) }}</td>
                                                    <td>{{ company_date_formate($announcement->end_date) }}</td>
                                                    <td>{{ $announcement->description }}</td>
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
                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __("Holiday's & Event's") }}</h5>
                            </div>
                            <div class="card-body card-635">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- New Clock-In Status Table for Admins -->
                <div class="row mt-4">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Clock-In Status for ') . company_date_formate($currentDate) }}</h5>
                            </div>
                            <div class="card-body" style="height: 300px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Clock-In Time') }}</th>
                                                <th>{{ __('Clock-Out Time') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Clocked-In Employees -->
                                            @forelse ($clockedIn as $employee)
                                                <tr>
                                                    <td>{{ $employee['name'] }}</td>
                                                    <td><span class="present-btn">{{ __('Clocked In') }}</span></td>
                                                    <td>{{ $employee['clock_in'] }}</td>
                                                    <td>{{ $employee['clock_out'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">{{ __('No employees have clocked in today.') }}</td>
                                                </tr>
                                            @endforelse
                                            <!-- Not Clocked-In Employees -->
                                            @forelse ($notClockedIn as $employee)
                                                <tr>
                                                    <td>{{ $employee['name'] }}</td>
                                                    <td><span class="absent-btn">{{ __('Not Clocked In') }}</span></td>
                                                    <td>{{ 'N/A' }}</td>
                                                    <td>{{ 'N/A' }}</td>
                                                </tr>
                                            @empty
                                                @if ($clockedIn)
                                                    <tr>
                                                        <td colspan="4" class="text-center">{{ __('All employees have clocked in today.') }}</td>
                                                    </tr>
                                                @endif
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('Modules/Hrm/Resources/assets/js/main.min.js') }}"></script>
    <script type="text/javascript">
        (function() {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    timeGridDay: "{{ __('Day') }}",
                    timeGridWeek: "{{ __('Week') }}",
                    dayGridMonth: "{{ __('Month') }}"
                },
                themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: {!! json_encode($events) !!},
            });
            calendar.render();
        })();
    </script>
@endpush