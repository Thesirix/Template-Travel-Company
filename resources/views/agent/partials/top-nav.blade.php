@include('layouts.partials.topbar', [
    'space' => 'Espace agent',
    'user' => \Illuminate\Support\Facades\Auth::guard('gent')->user(),
    'roleLabel' => \Illuminate\Support\Facades\Auth::guard('gent')->user()?->is_super_agent ? 'Super agent' : 'Agent',
    'profileUrl' => route('agent.profil'),
    'logoutUrl' => route('agent.logout'),
    'farewell' => true,
])
