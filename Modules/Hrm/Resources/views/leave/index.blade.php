@extends('layouts.main')
@section('page-title')
    {{ __('Manage Leave') }}
@endsection
@section('page-breadcrumb')
    {{ __('Leave') }}
@endsection
@section('page-action')
    <div>
        @stack('addButtonHook')
        @permission('leave create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Leave') }}"
                data-url="{{ route('leave.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                        <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Reason') }}</th>
                                    <th>{{ __('Line Manager') }}</th>
                                    <th>{{ __('Approver Status') }}</th>
                                    <th>{{ __('Line Manager Remark') }}</th>
                                    <th>{{ __('Final Status') }}</th>
                                    <th>{{ __('HR Remark') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                            <td>{{ !empty($leave->user) ? $leave->user->name : '--' }}</td>
                                        <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}</td>
                                        <td>{{ company_date_formate($leave->applied_on) }}</td>
                                        <td>{{ company_date_formate($leave->start_date) }}</td>
                                        <td>{{ company_date_formate($leave->end_date) }}</td>
                                        <td>{{ $leave->total_leave_days }}</td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}
                                            </p>
                                        </td>
                                        <td>{{ !empty($leave->approved_by) ? $leave->approver->name : '--' }}</td>
                                        <td>
                                            @if ($leave->approver_status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">{{ $leave->approver_status }}</div>
                                            @elseif($leave->approver_status == 'Recommended')
                                                <div class="badge bg-info p-2 px-3 rounded status-badge5">{{ $leave->approver_status }}</div>
                                            @else
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">{{ $leave->approver_status }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ !empty($leave->recommender_remark) ? $leave->recommender_remark : '--' }}
                                            </p>
                                        </td>
                                        <td>
                                            @if ($leave->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @elseif($leave->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @else
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ !empty($leave->final_remark) ? $leave->final_remark : '--' }}
                                            </p>
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <!-- Initial Approver Actions (Normal Users) -->
                                                @if ($leave->approved_by == Auth::user()->id && $leave->approver_status == 'Pending' && !Auth::user()->isAbleTo('leave approver manage'))
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('leave.recommend', $leave->id) }}"
                                                           data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                           title="{{ __('Recommend') }}" data-title="{{ __('Add Recommender Remark') }}">
                                                            <i class="ti ti-check text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('leave.reject', $leave->id) }}"
                                                           data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                           title="{{ __('Reject') }}" data-title="{{ __('Add Recommender Remark') }}">
                                                            <i class="ti ti-x text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                             <!-- Final Approval Actions (Admin/HR with leave approver manage) -->
@permission('leave approver manage')
    @if ($leave->approver_status != 'Pending' && $leave->status == 'Pending')
        <div class="action-btn bg-success ms-2">
            <a href="#" class="mx-3 btn btn-sm align-items-center"
               data-url="{{ route('leave.approve', $leave->id) }}"
               data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
               title="{{ __('Approve') }}" data-title="{{ __('Add Final Remark') }}">
                <i class="ti ti-check text-white"></i>
            </a>
        </div>
        <div class="action-btn bg-danger ms-2">
            <a href="#" class="mx-3 btn btn-sm align-items-center"
               data-url="{{ route('leave.decline', $leave->id) }}"
               data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
               title="{{ __('Decline') }}" data-title="{{ __('Add Final Remark') }}">
                <i class="ti ti-x text-white"></i>
            </a>
        </div>
    @endif
@endpermission

                                                <!-- Edit and Delete Actions -->
                                                @if ($leave->status == 'Pending' && $leave->approver_status == 'Pending')
                                                    @permission('leave edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a class="mx-3 btn btn-sm align-items-center"
                                                               data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                               data-ajax-popup="true" data-size="md"
                                                               data-bs-toggle="tooltip" title=""
                                                               data-title="{{ __('Edit Leave') }}"
                                                               data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('leave delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {{ Form::open(['route' => ['leave.destroy', $leave->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm align-items-center bs-pass-para show_confirm"
                                                               data-bs-toggle="tooltip" title=""
                                                               data-bs-original-title="Delete" aria-label="Delete"
                                                               data-confirm="{{ __('Are You Sure?') }}"
                                                               data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="delete-form-{{ $leave->id }}"><i
                                                                    class="ti ti-trash text-white"></i></a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    @endpermission
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();
            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append('<option value="">{{ __('Select Leave Type') }}</option>');
                    $.each(data, function(key, value) {
                        if (value.total_leave >= value.days) {
                            $('#leave_type_id').append('<option value="' + value.id + '" disabled>' + value.title + ' (' + value.total_leave + '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' + value.title + ' (' + value.total_leave + '/' + value.days + ')</option>');
                            if (oldval && oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr("selected", "selected");
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush