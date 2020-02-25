<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use function Safe\file_get_contents;
use SplFileInfo;
use Webimpress\SafeWriter\FileWriter;
use Webmozart\Assert\Assert;

/**
 * Represents a response you get when you wait for kraken to process your image
 */
final class WaitResponse extends Response
{
    /** @var string */
    private $dir;

    /** @var SplFileInfo */
    private $file;

    /** @var string */
    private $fileName;

    /** @var int */
    private $originalSize;

    /** @var int */
    private $krakedSize;

    /** @var int */
    private $savedBytes;

    /** @var string */
    private $krakedUrl;

    public function __construct(array $data, string $dir = null)
    {
        parent::__construct($data);

        if (null === $dir) {
            $dir = sys_get_temp_dir();
        }

        $this->dir = $dir;

        Assert::keyExists($data, 'file_name');
        Assert::keyExists($data, 'original_size');
        Assert::keyExists($data, 'kraked_size');
        Assert::keyExists($data, 'saved_bytes');
        Assert::keyExists($data, 'kraked_url');

        $this->fileName = $data['file_name'];
        $this->originalSize = $data['original_size'];
        $this->krakedSize = $data['kraked_size'];
        $this->savedBytes = $data['saved_bytes'];
        $this->krakedUrl = $data['kraked_url'];
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getOriginalSize(): int
    {
        return $this->originalSize;
    }

    public function getKrakedSize(): int
    {
        return $this->krakedSize;
    }

    public function getSavedBytes(): int
    {
        return $this->savedBytes;
    }

    public function getKrakedUrl(): string
    {
        return $this->krakedUrl;
    }

    public function getFile(): SplFileInfo
    {
        if (null === $this->file) {
            do {
                $filename = $this->dir . '/' . uniqid('optimized-image-file-', true);
            } while (file_exists($filename));

            FileWriter::writeFile($filename, file_get_contents($this->krakedUrl));

            $this->file = new SplFileInfo($filename);
        }

        return $this->file;
    }
}
