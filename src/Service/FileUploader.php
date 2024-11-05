<?php
// src/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string              $targetDirectoryImage,
        private string              $targetDirectory,
        private SluggerInterface    $slugger,
        private readonly Filesystem $filesystem,

    )
    {
    }

    public function uploadImage(?UploadedFile $file, ?string $currentFileName): string
    {
        if (!$file) {
            return $currentFileName ? : '';
        }

        if ($currentFileName) {
            $this->removeImage($currentFileName);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($this->targetDirectoryImage, $fileName);

        return $fileName;
    }

    public function uploadFile(string $filename, string $csvContent): string
    {
        if (!$filename) {
            return $filename ? : '';
        }

        $pathFile = $this->targetDirectory . '/' . $filename;

        if ($this->filesystem->exists($pathFile)) {
            $this->remove($filename);
        }

        $this->filesystem->touch($pathFile);

        $this->filesystem->appendToFile($pathFile, $csvContent);

        return $pathFile;
    }

    public function removeImage(?string $filename): void
    {
        if($filename && ($filename !== 'default.png')) $this->filesystem->remove($this->targetDirectoryImage . '/' . $filename);
    }

    public function remove(?string $filename): void
    {
        if($filename && ($filename !== 'default.png')) $this->filesystem->remove($this->targetDirectory . '/' . $filename);
    }
}