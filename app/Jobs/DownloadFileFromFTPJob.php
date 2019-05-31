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
     * @var MountManager
     */
    private $mountManager;

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

        $this->mountManager = new MountManager([
            'ftp' => Storage::disk('ftp')->getDriver(),
            'local' => Storage::disk('local')->getDriver()
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \League\Flysystem\FileExistsException
     */
    public function handle()
    {
        $this->mountManager->copy("ftp://{$this->ftpPath}", "local://{$this->localPath}");
    }
}
