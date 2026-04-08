<?php

namespace App\Logging;

use App\Models\SystemLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DatabaseHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $exception = $record->context['exception'] ?? null;

        SystemLog::create([
            'level'           => $record->level->getName(),
            'message'         => $record->message,
            'context'         => $record->context,
            'extra'           => $record->extra,
            'remote_addr'     => Request::ip(),
            'user_agent'      => Request::userAgent(),
            'user_id'         => Auth::id(),
            'url'             => Request::fullUrl(),
            'method'          => Request::method(),
            'exception_class' => $exception ? get_class($exception) : null,
            'file'            => $exception ? $exception->getFile() : null,
            'line'            => $exception ? $exception->getLine() : null,
            'stack_trace'     => $exception ? $exception->getTraceAsString() : null,
        ]);
    }
}
