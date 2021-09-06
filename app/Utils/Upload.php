<?php


namespace App\Utils;


use App\Models\Files;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use OSS\Core\OssException;
use OSS\OssClient;
use ZipArchive;

class Upload
{

    private static $bucket;

    /**
     * 创建阿里云oss
     * @return OssClient|null
     * author hy
     */
    private static function createClient()
    {
        $ossConfig = config('aliyun.oss');
        $accessKeyId = $ossConfig['accessKeyId'];
        $accessKeySecret = $ossConfig['accessKeySecret'];
        $endpoint = $ossConfig['endpoint'];
        self::$bucket = $ossConfig['bucket'];
        try {
            return new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            \Log::info("阿里云oss异常" . $e->getMessage());
        }
        return null;
    }


    /**
     * 创建zip
     * @param $files
     * @param $zipName
     * author hy
     */
    public static function zip($files, $zipName)
    {
        // 初始化zip
        $zip = new ZipArchive();
        // zip名称
        $fileName = $zipName . '.zip';
        // 打开zip
        $zip->open($fileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        // 写入文件
        foreach ($files as $v) {
            $ossClient = self::createClient();
            $content = $ossClient->getObject(self::$bucket, $v);
            $zip->addFromString($v, $content);
        }
        // 关闭zip
        $zip->close();
        // 向浏览器输出zip
        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($fileName));
        header("Content-Disposition: attachment; filename=\"a_zip_file.zip\"");
        readfile($fileName);
        // 删除zip
        unlink($fileName);
    }


    /**
     * 文件上传
     * @param UploadedFile $file
     * @param $type //上传者类型(0=用户 1=管理员)
     * @param $id // 上传者ID
     * @return mixed|string
     * author hy
     */
    public static function upload(UploadedFile $file, $type, $id)
    {
        // 文件原始名称
        $originalName = $file->getClientOriginalName();

        // 查看文件是否已经上传
        $has = Files::query()->where('original_name', $originalName)->first();
        if ($has) {
            return ['id'=>$has['id'],'url'=>$has['path']];
        }
        // 临时路径
        $realPath = $file->getRealPath();
        // 文件大小（字节数)
        $size = $file->getSize();
        // 后缀
        $extension = $file->getClientOriginalExtension();
        // 存入路径
        $object = date('Y-m-d/') . str_replace('-', "", \Str::uuid()) . '.' . $extension;
        try {
            $ossClient = self::createClient();
            // 阿里云上传
            $result = $ossClient->uploadFile(self::$bucket, $object, $realPath);
            if (!$result) {
                return '';
            }
            $red_id = Files::query()->create([
                'file_size' => $size,
                'original_name' => $originalName,
                'path' => $result['oss-request-url'],
                'object' => $object,
                'uploader_id' => $id,
                'uploader_type' => $type,
                'upload_at' => Carbon::now()->toDateTimeString(),
            ]);
            return ['id'=>$red_id,'url'=>$result['oss-request-url']];

        } catch (OssException $e) {
            \Log::info("上传文件出错" . $e->getMessage());
            return '';
        }
    }


    /**
     * 删除文件
     * @param string|array $object
     * @return null
     * author hy
     */
    public static function deleteFile($object)
    {
        $ossClient = self::createClient();
        if (is_array($object)) {
            return $ossClient->deleteObjects(self::$bucket, $object);
        }
        return $ossClient->deleteObject(self::$bucket, $object);
    }
}
