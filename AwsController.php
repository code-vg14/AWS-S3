<?php
declare(strict_types=1);

namespace App\Controller;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class AwsController extends AppController
{
    /**
     * Instantiates and returns a configured S3 client.
     *
     * @throws \RuntimeException If S3 credentials are missing or invalid.
     * @return \Aws\S3\S3Client
     */
    private function getS3Client(): S3Client
    {
        $credentials = Configure::read('S3_CREDENTIALS');

        if (empty($credentials['KEY']) || empty($credentials['SECRET'])) {
            throw new \RuntimeException('S3 credentials are not configured.');
        }

        return new S3Client([
            'credentials' => [
                'key'    => $credentials['KEY'],
                'secret' => $credentials['SECRET'],
            ],
            'region'  => $credentials['REGION'],
            'version' => $credentials['VERSION'] ?? 'latest',
        ]);
    }

    /**
     * Downloads an S3 object to a local temp file and returns the temp file path.
     *
     * @param string $path The S3 object key.
     * @throws \Aws\S3\Exception\S3Exception On S3 errors.
     * @throws \RuntimeException If the temp file cannot be created.
     * @return string Absolute path to the downloaded temp file.
     */
    public function getS3Content(string $path): string
    {
        $this->autoRender = false;

        $credentials = Configure::read('S3_CREDENTIALS');
        $tempFile = tempnam(sys_get_temp_dir(), 's3_');

        if ($tempFile === false) {
            throw new \RuntimeException('Unable to create a temporary file.');
        }

        try {
            $s3 = $this->getS3Client();

            $s3->getObject([
                'Bucket' => $credentials['BUCKET'],
                'Key'    => $path,
                'SaveAs' => $tempFile,
            ]);

            return $tempFile;

        } catch (S3Exception $e) {
            // Clean up the empty temp file if the download failed.
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }

    /**
     * Generates a short-lived pre-signed URL for an S3 object.
     *
     * @param string $path    The S3 object key.
     * @param string $expires A strtotime-compatible expiry string (default: '+15 minutes').
     * @throws \Aws\S3\Exception\S3Exception On S3 errors.
     * @return string The pre-signed URL.
     */
    public function getS3PresignedUrl(string $path, string $expires = '+15 minutes'): string
    {
        $this->autoRender = false;

        $credentials = Configure::read('S3_CREDENTIALS');

        $s3  = $this->getS3Client();
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $credentials['BUCKET'],
            'Key'    => $path,
        ]);

        $request = $s3->createPresignedRequest($cmd, $expires);

        return (string) $request->getUri();
    }
}
