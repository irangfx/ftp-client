<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadFileFromFTPJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private $basePath = null;

    public function index()
    {
        $this->basePath = request()->get('path');
        if ($this->basePath === null) return '';

        $list = $this->getFilesList();
        $this->startProcess($list);

        return $list;
    }

    /**
     * @return array
     */
    private function getFilesList(): array
    {
        $files = Storage::disk('ftp')->allFiles($this->basePath);
        return array_filter($files, function ($file) {
            return preg_match('/\.(zip|rar)$/', $file) && !preg_match('/\irangfx\.com/', $file);
        });
    }

    private function startProcess(array $files)
    {
        foreach ($files as $file) {
            $localPath = 'tmp/' . DIRECTORY_SEPARATOR . basename($file);

            if (!Storage::disk('local')->exists($localPath)) {
                \Log::info("Start Download => " . basename($file));
                dispatch(new DownloadFileFromFTPJob($file, $localPath));
            }
        }
    }
}
