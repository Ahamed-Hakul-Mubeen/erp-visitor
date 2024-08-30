<div class="modal-body">
    <table class="table table-striped table-responsive">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('By') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historyRecords as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record->action_date)->format('d M Y') }}</td>
                    <td>
                        @if($record->action == 'assigned')
                            {{ __('Assigned') }}
                        @elseif($record->action == 'unassigned')
                            {{ __('Unassigned') }}
                        @elseif($record->action == 'transferred')
                            {{ __('Transferred') }}
                        @endif
                    </td>
                    <td>
                        @if($record->action == 'assigned' || $record->action == 'unassigned')
                            {{ optional($record->employee)->name }}
                        @elseif($record->action== 'transferred')
                            {{ optional($record->fromEmployee)->name }} to {{ optional($record->toEmployee)->name }}
                        @endif
                    </td>
                    <td>{{ optional($record->createdBy)->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>
<style>
    .modal-body {
    overflow-x: auto;
}

.table th, .table td {
    white-space: nowrap;
}
</style>