<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadFileToFTPJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $ftpPath;
    /**
     * @var string
     */
    private $localPath;

    /**
     * Create a new job instance.
     *
     * @param string $localPath
     * @param string $ftpPath
     */
    public function __construct(string $localPath, string $ftpPath)
    {
        $this->ftpPath = str_replace('tarhan.ir', 'irangfx.com', $ftpPath);
        $this->ftpPath = str_replace('.zip', '.rar', $this->ftpPath);
        $this->localPath = str_replace('tarhan.ir', 'irangfx.com', $localPath);
        $this->localPath = str_replace('.zip', '.rar', $this->localPath);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function handle()
    {
        Log::info('Start Upload archive file => ' . basename($this->localPath));
        if (Storage::disk('local')->exists($this->localPath) && !Storage::disk('ftp')->exists($this->ftpPath)) {
            Storage::disk('ftp')->writeStream($this->ftpPath,
                Storage::disk('local')->readStream($this->localPath)
            );
            Log::info('Finish Upload archive file => ' . basename($this->localPath));
            Storage::disk('local')->delete($this->localPath);
            Log::info('Delete Upload archive file => ' . basename($this->localPath));
        }
    }
}
