<?php

namespace App\Logging;

use JsonException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class LogtailHandler extends AbstractProcessingHandler
{
    private string $endpoint;
    private string $token;

    public function __construct(
        string $endpoint,
        string $token,
        int|string|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->endpoint = $endpoint;
        $this->token = $token;
        parent::__construct($level, $bubble);
    }

    /**
     * @throws JsonException
     */
    protected function write(LogRecord $record): void
    {
        $data = [
            'message' => $record->message,
            'level' => $record->level->getName(),
            'timestamp' => $record->datetime->format('c'),
            'context' => $record->context,
            'extra' => $record->extra,
        ];

        $this->sendToLogtail($data);
    }

    /**
     * @throws JsonException
     */
    private function sendToLogtail(array $data): void
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_FAILONERROR => false, // Don't throw errors on HTTP errors
        ]);

        $result = curl_exec($ch);

        // Optional: Log curl errors (but don't create infinite loops)
        if (curl_error($ch) && !str_contains(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'] ?? '', 'LogtailHandler')) {
            error_log('Logtail logging failed: ' . curl_error($ch));
        }

        curl_close($ch);
    }
}
