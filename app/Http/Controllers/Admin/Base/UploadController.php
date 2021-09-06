<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\Files;
use App\Utils\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{

    /**
     * 上传文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * author II
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $path = Upload::upload($file, 1, auth('admin')->id()?:1);

        return success([
            'path' => $path,
        ]);

    }
}
