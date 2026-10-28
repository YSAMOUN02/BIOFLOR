<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\LaunchChecklist;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(LaunchChecklist $checklist)
    {
        $tasks = $checklist->tasks();

        return view('admin.checklist', [
            'tasks' => $tasks,
            'done'  => $tasks->where('done', true)->count(),
        ]);
    }

    /** Tick or untick a step that cannot check itself. */
    public function toggle(Request $request, LaunchChecklist $checklist, string $step)
    {
        abort_unless(in_array($step, $checklist->manualStepIds(), true), 404);

        $setting = Setting::firstOrNew(['key' => 'checklist:'.$step]);
        $setting->fill([
            'group' => 'checklist',
            'label' => 'Checklist: '.$step,
            'type'  => 'boolean',
            'value' => $setting->value === '1' ? '0' : '1',
        ])->save();

        return redirect()->to(route('admin.checklist').'#task-'.$request->input('task'));
    }
}
