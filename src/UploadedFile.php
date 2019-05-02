<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;

class UploadedFile implements UploadedFileInterface
{
    /**
     * The client-provided full path to the file
     *
     * @var string
     */
    protected $file;

    /**
     * The client-provided file name.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The client-provided media type of the file.
     *
     * @var string|null
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * A valid PHP UPLOAD_ERR_xxx code for the file upload.
     *
     * @var int
     */
    protected $error = UPLOAD_ERR_OK;

    /**
     * Indicates if the upload is from a SAPI environment.
     *
     * @var bool
     */
    protected $sapi = false;

    /**
     * @var StreamInterface|null
     */
    protected $stream;

    /**
     * Indicates if the uploaded file has already been moved.
     *
     * @var bool
     */
    protected $moved = false;

    /**
     * @param string      $file  The full path to the uploaded file provided by the client.
     * @param string|null $name  The file name.
     * @param string|null $type  The file media type.
     * @param int|null    $size  The file size in bytes.
     * @param int         $error The UPLOAD_ERR_XXX code representing the status of the upload.
     * @param bool        $sapi  Indicates if the upload is in a SAPI environment.
     */
    public function __construct(
        string $file,
        ?string $name = null,
        ?string $type = null,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        bool $sapi = false
    ) {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new RuntimeException(sprintf('Uploaded file %s has already been moved', $this->name));
        }

        if (!$this->stream) {
            $this->stream = (new StreamFactory())->createStreamFromFile($this->file);
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file already moved');
        }

        $targetIsStream = strpos($targetPath, '://') > 0;
        if (!$targetIsStream && !is_writable(dirname($targetPath))) {
            throw new InvalidArgumentException('Upload target path is not writable');
        }

        if ($targetIsStream) {
            if (!copy($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath));
            }

            if (!unlink($this->file)) {
                throw new RuntimeException(sprintf('Error removing uploaded file %s', $this->name));
            }
        } elseif ($this->sapi) {
            if (!is_uploaded_file($this->file)) {
                throw new RuntimeException(sprintf('%s is not a valid uploaded file', $this->file));
            }

            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath));
            }
        } else {
            if (!rename($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath));
            }
        }

        $this->moved = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Create a normalized tree of UploadedFile instances from the Environment.
     *
     * @internal This method is not part of the PSR-7 standard.
     *
     * @param array $globals The global server variables.
     *
     * @return array A normalized tree of UploadedFile instances or null if none are provided.
     */
    public static function createFromGlobals(array $globals): array
    {
        if (isset($globals['slim.files']) && is_array($globals['slim.files'])) {
            return $globals['slim.files'];
        }

        if (!empty($_FILES)) {
            return static::parseUploadedFiles($_FILES);
        }

        return [];
    }

    /**
     * Parse a non-normalized, i.e. $_FILES superglobal, tree of uploaded file data.
     *
     * @internal This method is not part of the PSR-7 standard.
     *
     * @param array $uploadedFiles The non-normalized tree of uploaded file data.
     *
     * @return array A normalized tree of UploadedFile instances.
     */
    private static function parseUploadedFiles(array $uploadedFiles): array
    {
        $parsed = [];
        foreach ($uploadedFiles as $field => $uploadedFile) {
            if (!isset($uploadedFile['error'])) {
                if (is_array($uploadedFile)) {
                    $parsed[$field] = static::parseUploadedFiles($uploadedFile);
                }
                continue;
            }

            $parsed[$field] = [];
            if (!is_array($uploadedFile['error'])) {
                $parsed[$field] = new static(
                    $uploadedFile['tmp_name'],
                    isset($uploadedFile['name']) ? $uploadedFile['name'] : null,
                    isset($uploadedFile['type']) ? $uploadedFile['type'] : null,
                    isset($uploadedFile['size']) ? $uploadedFile['size'] : null,
                    $uploadedFile['error'],
                    true
                );
            } else {
                $subArray = [];
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    // Normalize sub array and re-parse to move the input's key name up a level
                    $subArray[$fileIdx]['name'] = $uploadedFile['name'][$fileIdx];
                    $subArray[$fileIdx]['type'] = $uploadedFile['type'][$fileIdx];
                    $subArray[$fileIdx]['tmp_name'] = $uploadedFile['tmp_name'][$fileIdx];
                    $subArray[$fileIdx]['error'] = $uploadedFile['error'][$fileIdx];
                    $subArray[$fileIdx]['size'] = $uploadedFile['size'][$fileIdx];

                    $parsed[$field] = static::parseUploadedFiles($subArray);
                }
            }
        }

        return $parsed;
    }
}
