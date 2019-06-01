<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;

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
     * @var MountManager
     */
    private $mountManager;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        \Log::info("Downloading => " . basename($this->ftpPath));
        Storage::disk('local')->writeStream($this->localPath,
            Storage::disk('ftp')->readStream($this->ftpPath)
        );
        \Log::info("Download Finish => " . basename($this->ftpPath));
        dispatch(new PrepareArchiveJob($this->localPath, $this->ftpPath));
    }
}
