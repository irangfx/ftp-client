<?php

namespace App\Http\Controllers;

use File;
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
//        $this->downloadFiles($list);
//        $this->prepareFiles($list);
//        $this->uploadFiles($list);

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
            if (!Storage::disk('local')->exists('tmp/' . DIRECTORY_SEPARATOR . basename($file)))
                $this->ftpToLocal->copy(
                    'ftp://' . $file,
                    'local://tmp/' . DIRECTORY_SEPARATOR . basename($file)
                );
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
            $newName = str_replace('tarhan.ir', 'irangfx.com', $file);
            if (Storage::disk('local')->exists('tmp/' . DIRECTORY_SEPARATOR . $newName))
                $this->localToFtp->copy(
                    'local://tmp/' . DIRECTORY_SEPARATOR . basename($newName),
                    'ftp://' . $newName
                );
        }
    }
}
