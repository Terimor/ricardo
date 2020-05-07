<?php
namespace App\Services;


use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class S3Service
{

    /**
     * Load configs from database
     * @return void
     */
    public static function loadConfig(): void
    {
        $settings = Setting::getValue(['aws_access_key', 'aws_access_secret', 'aws_default_bucket', 'aws_default_region']);
        config(
            ['filesystems.disks.s3' => [
            'driver' => 's3',
                'key' => $settings['aws_access_key'],
                'secret' => $settings['aws_access_secret'],
                'region' => $settings['aws_default_region'],
                'bucket' => $settings['aws_default_bucket'],
            ]]);
    }

    /**
     * Return file response
     * @param string $s3_url
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function returnFileResponse(string $s3_url, string $filename): array
    {
        // set configs from database
        static::loadConfig();
        $s3_file_path = urldecode(parse_url($s3_url, PHP_URL_PATH));
        $exists = Storage::disk('s3')->exists($s3_file_path);

        if ($exists) {
            $tempFilepath = tempnam(sys_get_temp_dir(), '');
            $file_data = Storage::disk('s3')->get($s3_file_path);
            file_put_contents($tempFilepath, $file_data);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            // Prepare the headers
            $type = finfo_file($finfo, $tempFilepath) ?? 'application/octet-stream';
            $headers = [
                'Content-Description' => 'File Transfer',
                'Content-Type' => $type,
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => 0,
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'public',
                'Content-Length' => filesize($tempFilepath),
                'Content-Disposition' => 'attachment; filename=' . $filename,
                'file-name' => $filename
            ];
            finfo_close($finfo);
            return [
                'tempFilepath' => $tempFilepath,
                'headers' => $headers,
                'filename' => $filename
            ];
        }
        abort(404, 'File not found');
    }
}
