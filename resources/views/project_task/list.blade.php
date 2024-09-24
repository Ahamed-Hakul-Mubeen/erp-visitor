<div class="col-md-12">
    <div class="card">
        <div class="col-12">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Name') }}</th>
                                <th scope="col">{{ __('Stage') }}</th>
                                <th scope="col">{{ __('Priority') }}</th>
                                <th scope="col">{{ __('End Date') }}</th>
                                <th scope="col">{{ __('Assigned To') }}</th>
                                <th scope="col">{{ __('Completion') }}</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody class="list">


                            @if (count($tasks) > 0)
                                @foreach ($tasks as $task)
                                    <tr>
                                        @php
                                            $checkProject = \Auth::user()->checkProject($task->project_id);
                                        @endphp
                                        <td>
                                            <span class="mb-0 text-sm h6 font-weight-bold"><a
                                                    href="{{ route('projects.tasks.index', $task->project->id) }}">{{ $task->name }}</a></span>
                                            <span class="text-sm d-flex text-muted justify-content-between">
                                                <p class="m-0">{{ $task->project->project_name }}</p>
                                                <span
                                                    class="me-5 badge p-2 px-3 rounded bg-{{ $checkProject == 'Owner' ? 'success' : 'warning' }}">
                                                    {{ __($checkProject) }}</span>
                                            </span>
                                        </td>
                                        <td>{{ $task->stage->name }}</td>
                                        <td>
                                            <span
                                                class="status_badge badge p-2 px-3 rounded bg-{{ __(\App\Models\ProjectTask::$priority_color[$task->priority]) }}">{{ __(\App\Models\ProjectTask::$priority[$task->priority]) }}</span>
                                        </td>
                                        <td class="{{ strtotime($task->end_date) < time() ? 'text-danger' : '' }}">
                                            {{ Utility::getDateFormated($task->end_date) }}</td>
                                        <td>

                                            <div class="avatar-group">
                                                @php
                                                    $users = [];
                                                    $getUsers = App\Models\ProjectTask::getusers();
                                                    if (!empty($task->assign_to)) {
                                                        foreach (explode(',', $task->assign_to) as $key_user) {
                                                            if(isset([$key_user]['name']))
                                                            {
                                                                $user['name'] = $getUsers[$key_user]['name'];
                                                                $user['avatar'] = $getUsers[$key_user]['avatar'];
                                                                $users[] = $user;
                                                            }
                                                        }
                                                        $taskuser = $users;
                                                    } else {
                                                        $taskuser = [];
                                                    }
                                                @endphp

                                                @if (count($taskuser) > 0)
                                                    @foreach ($taskuser as $key => $user)
                                                        <a href="#" class="avatar rounded-circle avatar-sm">
                                                            <img data-original-title="{{ !empty($user) ? $user['name'] : '' }}"
                                                                @if ($user['avatar']) src="{{ asset('/storage/uploads/avatar/' . $user['avatar']) }}" @else src="{{ asset('/storage/uploads/avatar/avatar.png') }}" @endif
                                                                title="{{ $user['name'] }}" class="hweb">
                                                        </a>
                                                    @endforeach
                                                @else
                                                    {{ __('-') }}
                                                @endif
                                            </div>
                                            {{-- <div class="avatar-group">
                                                @php
                                                    $users = $task->users();
                                                @endphp
                                                @if ($users->count() > 0)
                                                    @if ($users)
                                                        @foreach ($users as $key => $user)
                                                            @if ($key < 3)
                                                                <a href="#"
                                                                    class="avatar rounded-circle avatar-sm">
                                                                    <img data-original-title="{{ !empty($user) ? $user->name : '' }}"
                                                                        @if ($user->avatar) src="{{ asset('/storage/uploads/avatar/' . $user->avatar) }}" @else src="{{ asset('/storage/uploads/avatar/avatar.png') }}" @endif
                                                                        title="{{ $user->name }}" class="hweb">
                                                                </a>
                                                            @else
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if (count($users) > 3)
                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                        <img data-original-title="{{ !empty($user) ? $user->name : '' }}"
                                                            @if ($user->avatar) src="{{ asset('/storage/uploads/avatar/' . $user->avatar) }}" @else src="{{ asset('/storage/uploads/avatar/avatar.png') }}" @endif
                                                            class="hweb">
                                                    </a>
                                                @endif
                                                @else
                                                    {{ __('-') }}
                                                @endif
                                            </div> --}}
                                        </td>
                                        <td>
                                            <div class="align-items-center">
                                                <span
                                                    class="completion">{{ $task->taskProgress($task)['percentage'] }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-{{ $task->taskProgress($task)['color'] }}"
                                                        role="progressbar"
                                                        style="width: {{ $task->taskProgress($task)['percentage'] }};">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end w-15">
                                            <div class="actions">
                                                <a class="px-1 action-item" data-bs-toggle="tooltip"
                                                    title="{{ __('Attachment') }}"
                                                    data-original-title="{{ __('Attachment') }}">
                                                    <i class="mr-2 ti ti-paperclip"></i>{{ count($task->taskFiles) }}
                                                </a>
                                                <a class="px-1 action-item" data-bs-toggle="tooltip"
                                                    title="{{ __('Comment') }}"
                                                    data-original-title="{{ __('Comment') }}">
                                                    <i
                                                        class="mr-2 ti ti-brand-hipchat"></i>{{ count($task->comments) }}
                                                </a>
                                                <a class="px-1 action-item" data-bs-toggle="tooltip"
                                                    title="{{ __('Checklist') }}"
                                                    data-original-title="{{ __('Checklist') }}">
                                                    <i
                                                        class="mr-2 ti ti-list-check"></i>{{ $task->countTaskChecklist() }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th scope="col" colspan="7">
                                        <h6 class="text-center">{{ __('No tasks found') }}</h6>
                                    </th>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
