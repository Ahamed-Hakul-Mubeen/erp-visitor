<div class="modal-body">
    <form action="{{ route('asset_management.unassignAsset', $asset->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="employee_id">{{ __('Select Employee') }}</label>
            <select name="employee_id" class="form-control" required>
                <option value="" disabled selected>{{ __('Select Employee') }}</option>
                @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" 
                    @if (isset($latestHistory))
                        @if ($latestHistory->action === 'assigned' && $latestHistory->employee_id == $employee->id)
                            selected
                        @elseif ($latestHistory->action === 'transferred' && $latestHistory->to_employee_id == $employee->id)
                            selected disabled
                        @endif
                    @endif>
                    {{ $employee->name }}
                </option>
            @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="unassign_description">{{ __('Unassign Description') }}</label>
            <textarea name="unassign_description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="assigned_date">{{ __('UnAssigned Date') }}</label>
            <input type="date" name="assigned_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('UnAssign') }}</button>
    </form>
</div>