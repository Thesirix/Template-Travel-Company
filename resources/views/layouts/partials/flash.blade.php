@php
    // Messages de session et erreurs de validation, affichés en toasts par public/js/tc/ui.js
    $tcFlash = [];
    $tcMap = [
        'success' => ['success', null],
        'good' => ['success', 'Opération enregistrée.'],
        'deletedsuccess' => ['success', 'Suppression effectuée.'],
        'status' => ['info', null],
        'message' => ['info', null],
        'info' => ['info', null],
        'warning' => ['warning', null],
        'error' => ['error', null],
    ];
    foreach ($tcMap as $key => [$type, $fallback]) {
        if (session()->has($key)) {
            $value = session($key);
            $text = is_string($value) && mb_strlen($value) > 6 ? $value : $fallback;
            if ($text) {
                $tcFlash[] = ['type' => $type, 'message' => $text];
            }
        }
    }
    if (isset($errors) && $errors->any()) {
        $tcFlash[] = ['type' => 'error', 'title' => request()->routeIs('login') ? 'Connexion impossible' : 'Formulaire incomplet', 'message' => $errors->all()];
    }
@endphp
<script>window.TC_FLASH = @json($tcFlash);</script>
