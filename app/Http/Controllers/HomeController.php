<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadFileFromFTPJob;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private $basePath = '/imap/pz10448.parspack.net/public_html/premium/New/';

    public function index()
    {
        $this->basePath = request()->get('path');
        if ($this->basePath === '') return '';

        $list = $this->getFilesList();
        $this->startProcess($list);

        return $list;
    }

    private function getFilesList(): array
    {
        $files = Storage::disk('ftp')->allFiles($this->basePath);
        return $files;
    }

    private function startProcess(array $files)
    {
        foreach ($files as $file) {
            $localPath = 'tmp/' . DIRECTORY_SEPARATOR . basename($file);

            if (!Storage::disk('local')->exists($localPath)) {
                \Log::info("Download => {$file}");
                dispatch(new DownloadFileFromFTPJob($file, $localPath));
            }
        }
    }
}
