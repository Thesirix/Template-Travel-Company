<script>
    // Toasts avec son (ajout de client, validation, erreur), affichés par le système de toasts du thème (public/js/tc/ui.js)
    const tcSounds = {
        success: new Audio("/sys/piece.mp3"),
        valide: new Audio("/sys/valide.mp3"),
        error: new Audio("/sys/error-8.mp3"),
    };
    Object.values(tcSounds).forEach((a) => { a.preload = 'auto'; });

    function tcPlay(sound) {
        const audio = tcSounds[sound];
        if (!audio) return;
        audio.currentTime = 0;
        audio.play().catch(() => { /* lecture bloquée tant que l'utilisateur n'a pas interagi */ });
    }

    function showValideToast(message) {
        TC.toast.success(message, 'Validé');
        tcPlay('valide');
    }

    function showSuccessToast(message) {
        TC.toast.success(message, 'Ajouté');
        tcPlay('success');
    }

    function showErrorToast(message) {
        TC.toast.error(message);
        tcPlay('error');
    }
</script>
