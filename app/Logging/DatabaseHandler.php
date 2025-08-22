<?php 

namespace App\Logging;

use App\Models\LogEntry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        LogEntry::create([
            'level'   => $record->level->getName(),   
            'message' => $record->message,
            'context' => $record->context,
        ]);
    }
}
