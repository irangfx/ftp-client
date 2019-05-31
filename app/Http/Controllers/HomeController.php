<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Flysystem\MountManager;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HomeController extends Controller
{
    private $downloadPath = 'tmp';
    private $ftpToLocal = null;
    private $localToFtp = null;
    private $basePath = '/imap/pz10448.parspack.net/public_html/premium/New/';

    public function index()
    {
        $this->basePath = request()->get('path');
        if ($this->basePath === '') return '';

        $this->init();

        $list = $this->getFilesList();
        $this->downloadFiles($list);
        $this->prepareFiles($list);
        $this->uploadFiles($list);

        return $list;
    }

    private function init()
    {
        $this->ftpToLocal = new MountManager([
            'ftp' => Storage::disk('ftp')->getDriver(),
            'local' => Storage::disk('local')->getDriver(),
        ]);

        $this->localToFtp = new MountManager([
            'local' => Storage::disk('local')->getDriver(),
            'ftp' => Storage::disk('ftp')->getDriver()
        ]);
    }

    private function getFilesList(): array
    {
        $files = Storage::disk('ftp')->allFiles($this->basePath);
        return $files;
    }

    private function downloadFiles(array $files)
    {
        foreach ($files as $file) {
            $localPath = 'tmp/' . DIRECTORY_SEPARATOR . basename($file);

            if (!Storage::disk('local')->exists($localPath)) {
                \Log::info($localPath);
                $this->ftpToLocal->copy("ftp://{$file}", "local://{$localPath}");
            }
        }
    }

    private function prepareFiles(array $files)
    {
        foreach ($files as $file) {
            $newFileName = str_replace('tarhan.ir', 'irangfx.com', basename($file));
            $command = 'cd ' . storage_path('app/tmp') . '; ./rar-extractor.sh "' . basename($file) . '" "' . $newFileName . '"';
            $process = new Process($command);
            $process->run();
            if (!$process->isSuccessful())
                throw new ProcessFailedException($process);
        }
    }

    private function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $ftpPath = str_replace('tarhan.ir', 'irangfx.com', $file);
            $localPath = 'tmp/' . DIRECTORY_SEPARATOR . basename($ftpPath);

            if (Storage::disk('local')->exists($localPath) && !Storage::disk('ftp')->exists($ftpPath))
                $this->localToFtp->copy("local://{$localPath}", "ftp://{$ftpPath}");
        }
    }
}
