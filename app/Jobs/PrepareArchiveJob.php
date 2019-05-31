<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PrepareArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $oldName;
    /**
     * @var string
     */
    private $newName;
    /**
     * @var string
     */
    private $fileName;

    /**
     * Create a new job instance.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;

        $this->oldName = basename($fileName);
        $this->newName = str_replace('tarhan.ir', 'irangfx.com', $this->oldName);;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $storagePath = storage_path('app/tmp');
        $command = "cd {$storagePath}; ./rar-extractor.sh '{$this->oldName}' '{$this->newName}'";
        $process = new Process([$command]);
        $process->run();
        if (!$process->isSuccessful())
            throw new ProcessFailedException($process);
    }
}
