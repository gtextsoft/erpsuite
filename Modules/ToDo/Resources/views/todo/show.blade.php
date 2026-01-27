<div class="modal-body">
    
    <style>
    .desc-text.checked {
        text-decoration: line-through;
        color: green;
    }
</style>

    <div class="table-responsive">
        <table class="table table-bordered ">
            
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($task->title) ? $task->title : '' }}</td>
            </tr>
         <tr>
    <th>{{ __('Description') }}</th>
    <td class="text-wrap text-break">
        @php
            $descriptions = json_decode($task->description, true);
        @endphp
        @if(!empty($descriptions))
            <ol class="ps-3 text-wrap text-break list-unstyled">
                @foreach($descriptions as $item)
                    <li>
                        <span class="desc-text {{ isset($item['completed']) && $item['completed'] ? 'checked' : '' }}">
                            {{ $item['text'] }}
                        </span>
                    </li>
                @endforeach
            </ol>
        @else
            <p>{{ __('No descriptions available.') }}</p>
        @endif
    </td>
</tr>


  <tr>
                <th>{{ __('Comments') }}</th>
                <td>{{ $task->comments }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ company_date_formate($task->start_date) }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ company_date_formate($task->due_date) }}</td>
            </tr>
            <tr>
                <th>{{ __('Assigned To') }}</th>
                <td>
                    {{Modules\ToDo\Entities\ToDo::getTeams($task->assigned_to)}}
                </td>
            </tr>
            <tr>
                <th>{{ __('Assigned By') }}</th>
                <td>{{ $task->assignedByUser->name }}</td>
            </tr>
           
        </table>
    </div>
</div>
