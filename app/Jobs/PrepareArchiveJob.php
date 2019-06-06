<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PrepareArchiveJob
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
    private $localPath;
    /**
     * @var string
     */
    private $ftpPath;

    /**
     * Create a new job instance.
     *
     * @param string $localPath
     * @param string $ftpPath
     */
    public function __construct(string $localPath, string $ftpPath)
    {
        $this->ftpPath = $ftpPath;
        $this->localPath = $localPath;

        $this->oldName = basename($localPath);
        $this->newName = str_replace('tarhan.ir', 'irangfx.com', $this->oldName);
        $this->newName = str_replace('.zip', '.rar', $this->newName);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $storagePath = storage_path('app/tmp/');

        Log::info('Start Convert file from `' . $this->oldName .'` to => ' . $this->newName);

        if (Storage::disk('local')->exists($storagePath . $this->newName))
            Storage::disk('local')->delete($storagePath . $this->newName);

        $command = "cd {$storagePath}; ./rar-extractor.sh '{$this->oldName}' '{$this->newName}'";
        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful())
            throw new ProcessFailedException($process);

        Log::info('Finished Convert archive file => ' . $this->newName);

        if (preg_match('/tarhan\.ir/', $this->ftpPath))
            dispatch(new UploadFileToFTPJob($this->localPath, $this->ftpPath));
        else
            dispatch(new MakeBackupFileToFTPJob($this->localPath, $this->ftpPath));
    }
}
