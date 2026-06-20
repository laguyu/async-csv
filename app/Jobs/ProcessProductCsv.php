<?php

namespace App\Jobs;

use App\Contracts\DataProcessorInterface;
use App\Models\CsvImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Throwable;

class ProcessProductCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Intenta ejecutar la tarea hasta 3 veces si hay fallos mecánicos o caídas temporales

    public function __construct(
        protected string $filePath,
        protected int $importId
    ) {}

    // Laravel resuelve automáticamente 'DataProcessorInterface' gracias al Service Provider
    public function handle(DataProcessorInterface $processor): void
    {
        $processor->process($this->filePath, $this->importId);
    }

   public function failed(Throwable $exception): void
{
    \App\Models\CsvImport::where('id', $this->importId)->update(['status' => 'failed']);
}

}
