<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class CustomizeFormatter
{
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter("[%datetime% %extra.url% %extra.requestId%] %channel%.%level_name% %extra%: %message% %context%\n", null, true, true)
            );
        }

        // Monolog 3 : les entrées de log sont immuables, on ajoute le contexte via with()
        $logger->pushProcessor(function (LogRecord $record) {
            $request = request();

            return $record->with(extra: [
                'url' => $request->url(),
                'requestId' => $request->attributes->get('request_id'),
                'agent_id' => auth()->guard('gent')->id(),
                'admin_id' => auth()->guard('gestion')->id(),
                'groupId' => is_object($group = $request->route('group')) ? $group->getKey() : $group,
                'requested_by' => $request->ip(),
            ]);
        });
    }
}
