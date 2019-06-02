<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadFileFromFTPJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private $basePath = '';

    public function index()
    {
        $this->basePath = request()->get('path');
        if ($this->basePath === '') return '';

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
            return preg_match('/\.(zip|rar)$/', $file);
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
