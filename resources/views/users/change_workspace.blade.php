@extends('layouts.main')

@section('page-title')
    {{ __('Change Workspace') }}
@endsection

@section('page-breadcrumb')
    {{ __('Change Workspace') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Change Workspace for') }} {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update.workspace', $user->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="workspace_id">{{ __('Select Workspace') }}</label>
                            <select name="workspace_id" id="workspace_id" class="form-control">
                                @foreach ($workspaces as $workspace)
                                    <option value="{{ $workspace->id }}" {{ $workspace->id == $user->workspace_id ? 'selected' : '' }}>
                                        {{ $workspace->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">{{ __('Update Workspace') }}</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection