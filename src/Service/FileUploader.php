<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $bookImageDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        if(strlen($fileName) > 255) {
            $fileName = trim(substr($fileName, 0, 25), '-') 
            . '-' . uniqid() . '.' . $file->guessExtension();
        }

        try {
            $file->move($this->getBookImageDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException;
        }

        return $fileName;
    }

    public function getBookImageDirectory(): string
    {
        return $this->bookImageDirectory;
    }
}