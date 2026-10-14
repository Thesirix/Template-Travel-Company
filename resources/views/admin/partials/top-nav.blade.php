@include('layouts.partials.topbar', [
    'space' => 'Administration',
    'user' => \Illuminate\Support\Facades\Auth::guard('gestion')->user(),
    'roleLabel' => 'Administrateur',
    'profileUrl' => route('admin.profil'),
    'settingsUrl' => route('admin.parametres'),
    'logoutUrl' => route('admin.logout'),
])
