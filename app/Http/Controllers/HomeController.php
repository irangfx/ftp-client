<?php

namespace App\Http\Controllers;

use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private $downloadPath = 'tmp';
    private $basePath = '/imap/pz10448.parspack.net/public_html/premium/New/';

    public function index()
    {
        $list = $this->getFilesList();
        $this->downloadFiles($list);
        return $list;
    }

    private function getFilesList(): array
    {
        $files = Storage::disk('ftp')->allFiles($this->basePath);
        return $files;
    }

    private function downloadFiles(array $files)
    {
        foreach ($files as $file) {
            Storage::disk('local')->put('tmp/' . DIRECTORY_SEPARATOR . basename($file),
                Storage::disk('ftp')->get($file)
            );
        }
    }
}
