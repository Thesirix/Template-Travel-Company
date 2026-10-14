<div style="font-family: Arial, Helvetica, sans-serif">
    <h2>Échec de la tâche « {{ $task }} »</h2>
    <p>Le {{ now()->format('d/m/Y à H:i') }}.</p>
    <pre style="white-space: pre-wrap">{{ $error }}</pre>
</div>
