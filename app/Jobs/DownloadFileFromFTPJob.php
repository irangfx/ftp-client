<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class DownloadFileFromFTPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
     * @param string $ftpPath
     * @param string $localPath
     */
    public function __construct(string $ftpPath, string $localPath)
    {
        $this->ftpPath = $ftpPath;
        $this->localPath = $localPath;
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
        Log::info("Downloading => " . basename($this->ftpPath));

        if (!Storage::disk('local')->exists($this->localPath)) {
            Storage::disk('local')->writeStream($this->localPath,
                Storage::disk('ftp')->readStream($this->ftpPath)
            );
            Log::info("Download Finish => " . basename($this->ftpPath));
        } else {
            Log::info("Download Ignored => " . basename($this->ftpPath));
        }

        dispatch(new PrepareArchiveJob($this->localPath, $this->ftpPath));
    }
}
