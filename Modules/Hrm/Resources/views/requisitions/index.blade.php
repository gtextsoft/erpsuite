@extends('layouts.main')

@section('page-title')
    {{ __('Manage Requisitions') }}
@endsection

@section('page-breadcrumb')
    {{ __('Requisition') }}
@endsection

@section('page-action')
    <div>
        <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Requisition') }}"
            data-url="{{ route('requisitions.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="requisitions">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <!--<th>{{ __('Amount')}}</th>-->
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Submitted On') }}</th>
                                    <th>{{ __('First Approver') }}</th>
                                    <th>{{ __('First Approver Status') }}</th>
                                    <th>{{ __('First Approver Remark') }}</th>
                                    <th>{{ __('GCOO Status') }}</th>
                                    <th>{{ __('GCOO Remark') }}</th>
                                    <th>{{ __('Accountant Status') }}</th>
                                    <th>{{ __('Accountant Remark') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requisitions as $requisition)
                                    <tr>
                                        <td>{{ !empty($requisition->employee) ? $requisition->employee->name : '--' }}</td>
                                        <td>{{ $requisition->title }}</td>
                                        <!--<td>{{ $requisition->amount }}</td>-->
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $requisition->description }}
                                            </p>
                                        </td>
                                        <td>{{ company_date_formate($requisition->created_at) }}</td>
                                        <td>{{ !empty($requisition->firstApprover) ? $requisition->firstApprover->name : '--' }}</td>
                                        <td>
                                            <div class="badge bg-{{ $requisition->first_approver_status == 'approved' ? 'success' : ($requisition->first_approver_status == 'rejected' ? 'danger' : 'warning') }} p-2 px-3 rounded">
                                                {{ ucfirst($requisition->first_approver_status) }}
                                            </div>
                                        </td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $requisition->first_approver_remark ?? '--' }}
                                            </p>
                                        </td>
                                        <td>
                                            <div class="badge bg-{{ $requisition->gcoo_status == 'approved' ? 'success' : ($requisition->gcoo_status == 'rejected' ? 'danger' : ($requisition->first_approver_status == 'approved' ? 'warning' : 'secondary')) }} p-2 px-3 rounded">
                                                {{ $requisition->first_approver_status == 'approved' ? ucfirst($requisition->gcoo_status) : 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $requisition->gcoo_remark ?? '--' }}
                                            </p>
                                        </td>
                                        <td>
                                            <div class="badge bg-{{ $requisition->accountant_status == 'approved' ? 'success' : ($requisition->accountant_status == 'rejected' ? 'danger' : ($requisition->gcoo_status == 'approved' ? 'warning' : 'secondary')) }} p-2 px-3 rounded">
                                                {{ $requisition->gcoo_status == 'approved' ? ucfirst($requisition->accountant_status) : 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $requisition->accountant_remark ?? '--' }}
                                            </p>
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <!-- First Approver Actions -->
                                                @if ($requisition->first_approver_status == 'pending' && $requisition->first_approver_id == Auth::user()->id)
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('requisitions.approve.form', $requisition->id) }}"
                                                           data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                           title="{{ __('Approve/Reject') }}" data-title="{{ __('Requisition Action') }}">
                                                            <i class="ti ti-check text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                <!-- GCOO Actions -->
                                                @if ($requisition->gcoo_status == 'pending' && $requisition->gcoo_id == Auth::user()->id && $requisition->first_approver_status == 'approved')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('requisitions.approve.form', $requisition->id) }}"
                                                           data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                           title="{{ __('Approve/Reject') }}" data-title="{{ __('Requisition Action') }}">
                                                            <i class="ti ti-check text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                <!-- Accountant Actions -->
                                                @if ($requisition->accountant_status == 'pending' && $requisition->accountant_id == Auth::user()->id && $requisition->gcoo_status == 'approved')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('requisitions.approve.form', $requisition->id) }}"
                                                           data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                           title="{{ __('Approve/Reject') }}" data-title="{{ __('Requisition Action') }}">
                                                            <i class="ti ti-check text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                <!-- Edit and Delete Actions -->
                                                @if ($requisition->overall_status == 'pending' && $requisition->employee_id == Auth::user()->id)
                                                    @permission('requisition edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a class="mx-3 btn btn-sm align-items-center"
                                                               data-url="{{ route('requisitions.edit', $requisition->id) }}"
                                                               data-ajax-popup="true" data-size="md"
                                                               data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                               data-title="{{ __('Edit Requisition') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('requisition delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {{ Form::open(['route' => ['requisitions.destroy', $requisition->id], 'class' => 'm-0', 'id' => 'delete-form-' . $requisition->id]) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm align-items-center bs-pass-para show_confirm"
                                                               data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                               data-confirm="{{ __('Are You Sure?') }}"
                                                               data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="delete-form-{{ $requisition->id }}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
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
        $(document).ready(function () {
            // Initialize DataTable
            $('#requisitions').DataTable();
        });
    </script>
@endpush