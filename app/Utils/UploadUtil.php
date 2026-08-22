<?php

namespace App\Utils;

use Illuminate\Support\Str;

class UploadUtil
{
    public function uploadImage($request, $dir, $attr = 'image', $file_name = '')
    {
        if (!is_dir(public_path('uploads') . $dir)) {
            mkdir(public_path('uploads') . $dir, 0777, true);
        }
        $file = $request->file($attr);
        $ext = $file->getClientOriginalExtension();

        if ($file_name == '') {
            $file_name = Str::random(25) . ".$ext";
        }
        $file->move(public_path('uploads') . $dir, $file_name);
        return $file_name;
    }

    public function unlinkImage($item, $dir, $attr = 'image')
    {
        if (isset($item->imagem)) {
            if (file_exists(public_path('uploads/') . $dir . "/$item->imagem") && $item->imagem != "") {
                unlink(public_path('uploads/') . $dir . "/$item->imagem");
            }
        } else {

            $fileName = $item[$attr];
           
            if (file_exists(public_path('uploads/') . $dir . "/$fileName") && $item[$attr] != "") {
                unlink(public_path('uploads/') . $dir . "/$fileName");
            }
        }
    }

    public function uploadImageExteds($request, $dir, $attr = 'image', $file_name = '')
    {
        
        $file = $request->file($attr);
        $ext = $file->getClientOriginalExtension();

        if ($file_name == '') {
            $file_name = Str::random(25) . ".$ext";
        }
        $file->move(public_path('/') . $dir, $file_name);
        return $file_name;
    }
    
}
