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

        $this->mountManager = new MountManager([
            'ftp' => Storage::disk('ftp')->getDriver(),
            'local' => Storage::disk('local')->getDriver()
        ]);
    }

    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \League\Flysystem\FileExistsException
     */
    public function handle()
    {
        \Log::info("Downloading => " . basename($this->ftpPath));
        $this->mountManager->copy("ftp://{$this->ftpPath}", "local://{$this->localPath}");
        \Log::info("Download Finish => " . basename($this->ftpPath));
        dispatch(new PrepareArchiveJob($this->localPath, $this->ftpPath));
    }
}
