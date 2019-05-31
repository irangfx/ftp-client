<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;

class UploadFileToFTPJob implements ShouldQueue
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
        $this->ftpPath = str_replace('tarhan.ir', 'irangfx.com', $ftpPath);
        $this->localPath = str_replace('tarhan.ir', 'irangfx.com', $localPath);

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
        if (Storage::disk('local')->exists($this->localPath) && !Storage::disk('ftp')->exists($this->ftpPath))
            $this->mountManager->copy("local://{$this->localPath}", "ftp://{$this->ftpPath}");
    }
}
