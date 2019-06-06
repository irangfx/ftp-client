<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MakeBackupFileToFTPJob
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
     * @param string $localPath
     * @param string $ftpPath
     */
    public function __construct(string $localPath, string $ftpPath)
    {
        $this->ftpPath = $ftpPath;
        $this->localPath = $localPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Start Make Backup From Original file => ' . basename($this->ftpPath));
        if (!Storage::disk('ftp')->exists("{$this->ftpPath}.back")) {
            Storage::disk('ftp')->move($this->ftpPath, "{$this->ftpPath}.back");
            Log::info('Finish Make Backup From Original file => ' . basename($this->ftpPath));
            dispatch(new UploadFileToFTPJob($this->localPath, $this->ftpPath));
        } else {
            Log::info('Ignore Make Backup From Original file => ' . basename($this->ftpPath));
        }
    }
}
