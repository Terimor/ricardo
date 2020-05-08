<?php
namespace App\Services;


use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class MediaService
{

    /**
     * @var array Vimeo video quality priority
     */
    public $vimeoQualityPrioritet = ['1080p', '720p', '540p', '360p'];

    /**
     * Load configs from database
     * @return void
     */
    public function loadS3Config(): void
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
    public function getS3FileContent(string $s3Url, string $filename): array
    {
        // set configs from database
        $this->loadS3Config();
        $s3FilePath = urldecode(parse_url($s3Url, PHP_URL_PATH));
        $exists = Storage::disk('s3')->exists($s3FilePath);

        if ($exists) {
            $tempFilepath = tempnam(sys_get_temp_dir(), '');
            $fileData = Storage::disk('s3')->get($s3FilePath);
            file_put_contents($tempFilepath, $fileData);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            // Prepare the headers
            $type = finfo_file($finfo, $tempFilepath) ?? 'application/octet-stream';
            $headers = $this->prepareHeaders($tempFilepath, $type, $filename);
            finfo_close($finfo);
            return [
                'tempFilepath' => $tempFilepath,
                'headers' => $headers,
                'filename' => $filename
            ];
        }
        abort(404, 'File not found');
    }

    /**
     * Get remote file content
     * @param string $url
     * @param string $filename
     * @return array
     */
    public function getFileContent(string $url, string $filename): array
    {
        $tempFilepath = tempnam(sys_get_temp_dir(), '');
        $fileData = file_get_contents($url);
        file_put_contents($tempFilepath, $fileData);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tempFilepath) ?? 'application/octet-stream';
        $headers = $this->prepareHeaders($tempFilepath, $type, $filename);
        return [
            'tempFilepath' => $tempFilepath,
            'headers' => $headers,
            'filename' => $filename
        ];
    }

    /**
     * Prepare headers for returned file content
     * @param string $tempFilepath
     * @param string $type
     * @param string $filename
     * @return array
     */
    public function prepareHeaders(string $tempFilepath, string $type, string $filename): array
    {
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
        return $headers;
    }

    /**
     * Get direct URL to Vimeo video file
     * @param string $url to video on Vimeo
     * @return string
     */
    public function getVimeoDirectUrl($url): string
    {
        $result = '';
        $videoInfo = $this->getVimeoVideoInfo($url);
        if ($videoInfo && $videoObject = $this->getVimeoQualityVideo($videoInfo->request->files)) {
            $result = $videoObject->url;
        }
        return $result;
    }

    /**
     * Get Vimeo Player, Video config object
     */
    public function getConfigObjectFromHtml($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Get Vimeo video info
     * @param string $url to video on Vimeo
     * @return \stdClass|null
     */
    public function getVimeoVideoInfo($url)
    {
        $videoInfo = null;
        $page = $this->getRemoteContent($url);
        $html = $this->getConfigObjectFromHtml($page, 'clip_page_config = ', 'window.can_preload');
        $json = substr($html, 0, strpos($html, '}};') + 2);
        $videoConfig = json_decode($json);

        if (isset($videoConfig->player->config_url)) {
            $videoObj = json_decode($this->getRemoteContent($videoConfig->player->config_url));
            if (!property_exists($videoObj, 'message')) {
                $videoInfo = $videoObj;
            }
        }

        return $videoInfo;
    }

    /**
     * Get vimeo video object
     * @param stdClass $files object of Vimeo files
     * @return stdClass|null
     */
    public function getVimeoQualityVideo($files): ?\stdClass
    {
        $video = null;

        if (count($files->progressive)) {
            $this->vimeoVideoQuality = $files->progressive;
        }

        foreach ($this->vimeoQualityPrioritet as $k => $quality) {
            if ($this->vimeoVideoQuality[$k]->quality == $quality) {
                $video = $this->vimeoVideoQuality[$k];
                break;
            }
        }

        if (!$video) {
            foreach (get_object_vars($this->vimeoVideoQuality) as $file) {
                $video = $file;
                break;
            }
        }

        return $video;
    }

    /**
     * Get remote content by URL
     * @param string $url remote page URL
     * @return string
     */
    public function getRemoteContent($url): string
    {
        $data = file_get_contents($url);
        return $data;
    }
}
