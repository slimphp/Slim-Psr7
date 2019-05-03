<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Slim\Psr7\Environment;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

class UploadedFileTest extends TestCase
{
    private static $filename = './phpUxcOty';

    private static $tmpFiles = ['./phpUxcOty'];

    public static function setUpBeforeClass()
    {
        $fh = fopen(self::$filename, "w");
        fwrite($fh, "12345678");
        fclose($fh);
    }

    public static function tearDownAfterClass()
    {
        foreach (self::$tmpFiles as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    public function tearDown()
    {
        if (isset($GLOBALS['is_uploaded_file_return'])) {
            unset($GLOBALS['is_uploaded_file_return']);
        }
    }

    /**
     * @return UploadedFile
     */
    protected function generateNewTmpFile()
    {
        $filename = './php' . microtime();

        $fh = fopen($filename, "w");
        fwrite($fh, "12345678");
        fclose($fh);

        self::$tmpFiles[] = $filename;

        return new UploadedFile($filename);
    }

    /**
     * @param array $input    The input array to parse.
     * @param array $expected The expected normalized output.
     *
     * @dataProvider providerCreateFromGlobals
     */
    public function testCreateFromGlobalsFromFilesSuperglobal(array $input, array $expected)
    {
        $_FILES = $input;

        $uploadedFile = UploadedFile::createFromGlobals(Environment::mock());
        $this->assertEquals($expected, $uploadedFile);
    }

    /**
     * @param array $input The input array to parse.
     *
     * @dataProvider providerCreateFromGlobals
     */
    public function testCreateFromGlobalsFromUserData(array $input)
    {
        //If slim.files provided - it will return what was provided
        $userData['slim.files'] = $input;

        $uploadedFile = UploadedFile::createFromGlobals(Environment::mock($userData));
        $this->assertEquals($input, $uploadedFile);
    }

    public function testCreateFromGlobalsWithoutFile()
    {
        unset($_FILES);

        $uploadedFile = UploadedFile::createFromGlobals(Environment::mock());
        $this->assertEquals([], $uploadedFile);
    }

    /**
     * @return UploadedFile
     */
    public function testConstructor()
    {
        $attr = [
            'tmp_name' => self::$filename,
            'name' => 'my-avatar.txt',
            'size' => 8,
            'type' => 'text/plain',
            'error' => 0,
        ];

        $uploadedFile = new UploadedFile(
            $attr['tmp_name'],
            $attr['name'],
            $attr['type'],
            $attr['size'],
            $attr['error'],
            false
        );

        $this->assertEquals($attr['name'], $uploadedFile->getClientFilename());
        $this->assertEquals($attr['type'], $uploadedFile->getClientMediaType());
        $this->assertEquals($attr['size'], $uploadedFile->getSize());
        $this->assertEquals($attr['error'], $uploadedFile->getError());

        return $uploadedFile;
    }

    /**
     * @return UploadedFile
     */
    public function testConstructorSapi()
    {
        $attr = [
            'tmp_name' => self::$filename,
            'name' => 'my-avatar.txt',
            'size' => 8,
            'type' => 'text/plain',
            'error' => 0,
        ];

        $uploadedFile = new UploadedFile(
            $attr['tmp_name'],
            $attr['name'],
            $attr['type'],
            $attr['size'],
            $attr['error'],
            true
        );

        $this->assertEquals($attr['name'], $uploadedFile->getClientFilename());
        $this->assertEquals($attr['type'], $uploadedFile->getClientMediaType());
        $this->assertEquals($attr['size'], $uploadedFile->getSize());
        $this->assertEquals($attr['error'], $uploadedFile->getError());

        return $uploadedFile;
    }

    /**
     * @depends testConstructor
     *
     * @param UploadedFile $uploadedFile
     *
     * @return UploadedFile
     */
    public function testGetStream(UploadedFile $uploadedFile)
    {
        $stream = $uploadedFile->getStream();
        $this->assertEquals(true, $uploadedFile->getStream() instanceof Stream);
        $stream->close();

        return $uploadedFile;
    }

    /**
     * @depends testConstructor
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException InvalidArgumentException
     */
    public function testMoveToNotWritable(UploadedFile $uploadedFile)
    {
        $tempName = uniqid('file-');
        $path = 'some_random_dir' . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);
    }

    /**
     * @depends testConstructor
     *
     * @param UploadedFile $uploadedFile
     *
     * @return UploadedFile
     */
    public function testMoveTo(UploadedFile $uploadedFile)
    {
        $tempName = uniqid('file-');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);

        $this->assertFileExists($path);

        unlink($path);

        return $uploadedFile;
    }

    /**
     * @depends testConstructorSapi
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException RuntimeException
     */
    public function testMoveToSapiNonUploadedFile(UploadedFile $uploadedFile)
    {
        $tempName = uniqid('file-');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);
    }

    /**
     * @depends testConstructorSapi
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp ~Error moving uploaded file.*~
     */
    public function testMoveToSapiMoveUploadedFileFails(UploadedFile $uploadedFile)
    {
        $GLOBALS['is_uploaded_file_return'] = true;

        $tempName = uniqid('file-');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);
    }

    /**
     * @depends testMoveTo
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException RuntimeException
     */
    public function testMoveToCannotBeDoneTwice(UploadedFile $uploadedFile)
    {
        $tempName = uniqid('file-');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);
        $this->assertFileExists($path);
        unlink($path);

        $uploadedFile->moveTo($path);
    }

    /**
     * This test must run after testMoveTo
     *
     * @depends testConstructor
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException RuntimeException
     */
    public function testMoveToAgain(UploadedFile $uploadedFile)
    {
        $tempName = uniqid('file-');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempName;
        $uploadedFile->moveTo($path);
    }

    /**
     * This test must run after testMoveTo
     *
     * @depends testConstructor
     *
     * @param UploadedFile $uploadedFile
     *
     * @expectedException RuntimeException
     */
    public function testMovedStream(UploadedFile $uploadedFile)
    {
        $uploadedFile->getStream();
    }

    public function testMoveToStream()
    {
        $uploadedFile = $this->generateNewTmpFile();

        $fileProperty = new ReflectionProperty($uploadedFile, 'file');
        $fileProperty->setAccessible(true);
        $fileName = $fileProperty->getValue($uploadedFile);

        $contents = file_get_contents($fileName);

        ob_start();
        $uploadedFile->moveTo('php://output');
        $movedFileContents = ob_get_clean();

        $this->assertEquals($contents, $movedFileContents);
        $this->assertFileNotExists($fileName);
    }

    public function providerCreateFromGlobals()
    {
        return [
            // no nest: <input name="avatar" type="file">
            [
                // $_FILES array
                [
                    'avatar' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
                // expected format of array
                [
                    'avatar' => new UploadedFile('phpUxcOty', 'my-avatar.png', 'image/png', 90996, UPLOAD_ERR_OK, true)
                ]
            ],
            // no nest, with error: <input name="avatar" type="file">
            [
                // $_FILES array
                [
                    'avatar' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 7,
                    ],
                ],
                // expected format of array
                [
                    'avatar' => new UploadedFile(
                        'phpUxcOty',
                        'my-avatar.png',
                        'image/png',
                        90996,
                        UPLOAD_ERR_CANT_WRITE,
                        true
                    )
                ]
            ],

            // array of files: <input name="avatars[]" type="file">
            [
                // $_FILES array
                [
                    'avatars' => [
                        'tmp_name' => [
                            0 => __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                            1 => __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                        ],
                        'name' => [
                            0 => 'file0.txt',
                            1 => 'file1.html',
                        ],
                        'type' => [
                            0 => 'text/plain',
                            1 => 'text/html',
                        ],
                        'error' => [
                            0 => 0,
                            1 => 0
                        ],
                        'size' => [
                            0 => 0,
                            1 => 0
                        ]
                    ],
                ],
                // expected format of array
                [
                    'avatars' => [
                        0 => new UploadedFile(
                            __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                            'file0.txt',
                            'text/plain',
                            null,
                            UPLOAD_ERR_OK,
                            true
                        ),
                        1 => new UploadedFile(
                            __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                            'file1.html',
                            'text/html',
                            null,
                            UPLOAD_ERR_OK,
                            true
                        ),
                    ],
                ]
            ],
            // array of files as multidimensional array: <input name="avatars[]" type="file">
            [
                // $_FILES array
                [
                    [
                        'avatars' => [
                            'tmp_name' => [
                                0 => __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                                1 => __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                            ],
                            'name' => [
                                0 => 'file0.txt',
                                1 => 'file1.html',
                            ],
                            'type' => [
                                0 => 'text/plain',
                                1 => 'text/html',
                            ],
                            'size' => [
                                0 => 0,
                                1 => 0,
                            ],
                        ],
                    ],
                ],
                // expected format of array
                [
                    0 =>
                        [
                            'avatars' =>
                                [
                                    'tmp_name' => [],
                                    'name' => [],
                                    'type' => [],
                                    'size' => [],
                                ],
                        ],
                ],
            ],
            // single nested file: <input name="details[avatar]" type="file">
            [
                // $_FILES array
                [
                    'details' => [
                        'tmp_name' => [
                            'avatar' => __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                        ],
                        'name' => [
                            'avatar' => 'file0.txt',
                        ],
                        'type' => [
                            'avatar' => 'text/plain',
                        ],
                        'error' => [
                            'avatar' => 0,
                        ],
                        'size' => [
                            'avatar' => 0,
                        ],
                    ],
                ],
                // expected format of array
                [
                    'details' => [
                        'avatar' => new UploadedFile(
                            __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                            'file0.txt',
                            'text/plain',
                            null,
                            UPLOAD_ERR_OK,
                            true
                        ),
                    ],
                ]
            ],
            // nested array of files: <input name="files[details][avatar][]" type="file">
            [
                [
                    'files' => [
                        'tmp_name' => [
                            'details' => [
                                'avatar' => [
                                    0 => __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                                    1 => __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                                ],
                            ],
                        ],
                        'name' => [
                            'details' => [
                                'avatar' => [
                                    0 => 'file0.txt',
                                    1 => 'file1.html',
                                ],
                            ],
                        ],
                        'type' => [
                            'details' => [
                                'avatar' => [
                                    0 => 'text/plain',
                                    1 => 'text/html',
                                ],
                            ],
                        ],
                        'error' => [
                            'details' => [
                                'avatar' => [
                                    0 => 0,
                                    1 => 0
                                ],
                            ],
                        ],
                        'size' => [
                            'details' => [
                                'avatar' => [
                                    0 => 0,
                                    1 => 0
                                ],
                            ],
                        ],
                    ],
                ],
                // expected format of array
                [
                    'files' => [
                        'details' => [
                            'avatar' => [
                                0 => new UploadedFile(
                                    __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                                    'file0.txt',
                                    'text/plain',
                                    null,
                                    UPLOAD_ERR_OK,
                                    true
                                ),
                                1 => new UploadedFile(
                                    __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                                    'file1.html',
                                    'text/html',
                                    null,
                                    UPLOAD_ERR_OK,
                                    true
                                ),
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }
}
