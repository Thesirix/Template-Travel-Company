<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accueil de l'agent : absences du jour, notes, tâches, calendrier et soldes de congés.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $agentId = Auth::guard('gent')->id();
        $today = today()->toDateString();

        return view('agent.home', [
            'todayevents' => LeaveRequest::where('gent_id', $agentId)->where('start_date', '<=', $today)->where('end_date', '>=', $today)->get(),
            'blacknotes' => Note::where('gent_id', $agentId)->get(),
            'todolists' => Task::where('gent_id', $agentId)->get(),
            'calendars' => LeaveRequest::where('gent_id', $agentId)->get(),
            'leaveBalances' => DB::table('leave_counters')->where('gent_id', $agentId)->pluck('balance', 'type'),
        ]);
    }
}
