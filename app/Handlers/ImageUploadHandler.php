<?php

namespace App\Handlers;

use Image;

class ImageUploadHandler
{
    // 只允许以下后缀的图片上传
    protected $allowed_ext = ['png','jpg','gif','jpeg'];

    public function save($file,$folder,$file_prefix,$max_width = false)
    {
        // 构建存储的文件夹规则， 值如：uploads/images/avatars/201709/21/
        // 文件夹切割能让查找效率更好
        $folder_name = "uploads/images/$folder/" . date("Y/m/d",time());

        // 文件具体存储的物理路径，'public_path()' 获取的是 'public' 文件夹的物理路径
        // 值如：/home/vagrant/Code/larabbs/public/uploads/images/avatars/201709/21/
        $upload_path = public_path(). '/' . $folder_name;

        // 获取文件的后缀名，因图片从剪切板里面粘贴是后缀为空，所以此处确保后缀一直有
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 拼接文件名， 加前缀是为了增加辨析度，前缀可以是相关数据模型的 ID
        // 值如：1_1493521050_7BVc9v9ujP.png
        $filename = $file_prefix . '_'.time().'_'.str_random(10).'.'.$extension;

        // 如果上传的不是图片将终止操作
        if ( ! in_array($extension,$this->allowed_ext)) {
            return false;
        }

        // 将图片移动到我们的目标路径中
        $file->move($upload_path,$filename);

        // 如果限制了图片宽度，就进行剪裁
        if ($max_width && $extension != 'gif') {
            // 此类中封装的函数，用于剪裁图片
            $this->reduceSize($upload_path . '/' . $filename, $max_width);
        }

        return [
            'path'  => config('app.url') . "/$folder_name/$filename"
        ];
    }

    public function reduceSize($file_path,$max_width)
    {
        // 先实例化，传参是文件的物理磁盘路径
        $image->resize($max_width,null , function ($constrains) {

            // 设定宽度是 $max_width ，高度等比例缩放
            $constrains->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constrains->upsize();
        });

        $image->save();
    }

}