<?php

class upload
{
    //S=================图片上传参数===================
    private $files       = '';
    private $fileNum     = 0;       //上传文件数量
    private $filePath    = '';      //附件上传绝对路径
    private $publicPath  = '';      //返回给调用方的相对路径
    private $fileText    = '';      //附件说明
    private $attaMaxSize = 1024000; //附件大小限制
    private $nowDate     = '';

    //E=================图片上传参数===================
    //S=================水印/缩略图参数================

    public $isSmall      = 0;                   //是否生成缩略图;
    public $isMark       = 0;                   //是否生成水印图;
    public $waterText    = '';                  //水印文字
    public $textSize     = 18;                  //水印文字尺寸
    public $logoName     = '';                  //水印图片
    public $logoPos      = '';                  //水印定位
    public $logoAngle    = 0;                   //水印角度
    public $smallFolder  = 'small/';            //缩略图存放处
    public $markFolder   = 'mark/';             //水印图片存放处
    public $resizeFolder = 'resize/';           //裁切图片存放处
    public $fontType     = './inc/verdana.ttf'; //字体
    public $maxWidth     = 500;                 //水印图_图片最大宽度
    public $maxHeight    = 600;                 //水印图_图片最大高度
    public $smallWidth   = 400;                 //缩略图_图片最大宽度
    public $smallHeight  = 400;                 //缩略图_图片最大高度
    public $toFile       = true;
    //E=================水印/缩略图参数================
    //S=================剪切图片参数===================

    public $isResize  = 0; //是否剪切图片;
    public $newWidth  = 100;
    public $newHeight = 100;
    //E=================剪切图片参数===================
    //裁切图片: 将图片等比例裁切成设置的大小, 超出则裁切,不够则填补
    //缩略图: 按等比将图片缩小

    public function __construct($initArray)
    {
        if (! empty($initArray['files'])) {
            $this->files = $initArray['files'];
        }
        $relative = 'upload/' . date('Ym') . '/';
        if (! empty($initArray['filepath'])) {
            $candidate = str_replace('\\', '/', trim((string) $initArray['filepath']));
            if ($candidate !== '' && ! str_contains($candidate, '..') && ! str_starts_with($candidate, '/')) {
                $relative = rtrim($candidate, '/') . '/';
            }
        }
        $this->publicPath = $relative;
        $this->filePath   = LCY_ROOT . $relative;
        if (! empty($initArray['fileText'])) {
            $this->fileText = $initArray['fileText'];
        }
        if (! empty($initArray['attaMaxSize'])) {
            $this->attaMaxSize = $initArray['attaMaxSize'];
        }
        if (! empty($initArray['isSmall'])) {
            $this->isSmall = $initArray['isSmall'];
        }
        if (! empty($initArray['isMark'])) {
            $this->isMark = $initArray['isMark'];
        }
        if (! empty($initArray['waterText'])) {
            $this->waterText = $initArray['waterText'];
        }
        if (! empty($initArray['textSize'])) {
            $this->textSize = $initArray['textSize'];
        }
        if (! empty($initArray['logoName'])) {
            $this->logoName = $initArray['logoName'];
        }
        if (! empty($initArray['logoPos'])) {
            $this->logoPos = $initArray['logoPos'];
        }
        if (! empty($initArray['logoAngle'])) {
            $this->logoAngle = $initArray['logoAngle'];
        }
        if (! empty($initArray['smallFolder'])) {
            $this->smallFolder = $initArray['smallFolder'];
        }
        if (! empty($initArray['markFolder'])) {
            $this->markFolder = $initArray['markFolder'];
        }
        if (! empty($initArray['resizeFolder'])) {
            $this->resizeFolder = $initArray['resizeFolder'];
        }
        if (! empty($initArray['isResize'])) {
            $this->isResize = $initArray['isResize'];
        }
        if (! empty($initArray['newWidth'])) {
            $this->newWidth = $initArray['newWidth'];
        }
        if (! empty($initArray['newHeight'])) {
            $this->newHeight = $initArray['newHeight'];
        }
        $this->nowDate = date('YmdHis');
    }

    private function getInfo($photo)
    {
        $photo             = $this->filePath . $photo;
        $imageInfo         = getimagesize($photo);
        $imgInfo['width']  = $imageInfo[0];
        $imgInfo['height'] = $imageInfo[1];
        $imgInfo['type']   = $imageInfo[2];
        $imgInfo['name']   = basename($photo);
        return $imgInfo;
    }

    private function getType($img_name)
    {
        $name_array = explode('.', $img_name);
        if (preg_match('/\.(gif|jpg|jpeg|png)$/', $img_name, $matches)) {
            $type = strtolower($matches[1]);
        } else {
            $type = 'string';
        }
        return $type;
    }

    private function createImage($type, $img_name)
    {
        if (! $type) {
            $type = $this->getType($img_name);
        }
        switch ($type) {
            case 'gif':
                if (function_exists('imagecreatefromgif')) {
                    $tmp_img = @imagecreatefromgif($img_name);
                }
                break;
            case 'jpg':
                $tmp_img = imagecreatefromjpeg($img_name);
                break;
            case 'png':
                $tmp_img = imagecreatefrompng($img_name);
                break;
            default:
                $tmp_img = '';
                break;
        }
        return $tmp_img;
    }

    private function getPos($sourcefile_width, $sourcefile_height, $pos, $logo_image = '')
    {
        if ($logo_image) {
            $insertfile_width  = imagesx($logo_image);
            $insertfile_height = imagesy($logo_image);
        } else {
            $lineCount         = explode("\r\n", $this->waterText);
            $fontSize          = imagettfbbox($this->textSize, $this->logoAngle, $this->fontType, $this->waterText);
            $insertfile_width  = $fontSize[2] - $fontSize[0];
            $insertfile_height = count($lineCount) * ($fontSize[1] - $fontSize[7]);
            $insertfile_height = $insertfile_height == 0 ? $this->textSize * count($lineCount) * 1.5 : $insertfile_height;
        }
        switch ($pos) {
            case 1: //顶部居左
                $dest_x = 0;
                if ($this->waterText) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 2: //顶部居中
                $dest_x = ($sourcefile_width - $insertfile_width) / 2;
                if ($this->waterText) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 3: //顶部居右
                $dest_x = $sourcefile_width - $insertfile_width;
                if ($this->waterText) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 4: //中间居左
                $dest_x = 0;
                $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
                break;
            case 5: //居中
                $dest_x = $sourcefile_width / 2 - $insertfile_width / 2;
                $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
                break;
            case 6: //中间居右
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
                break;
            case 7: //底部居左
                $dest_x = 0;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 8: //底部居中
                $dest_x = ($sourcefile_width - $insertfile_width) / 2;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 9: //底部居右
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            default:
                if (is_array($pos)) {
                    if ($this->waterText) {
                        $dest_y = $pos[1] + $insertfile_height;
                    } else {
                        $dest_y = $pos[1];
                    }
                    $dest_x = $pos[0];
                } else {
                    $dest_x = $sourcefile_width - $insertfile_width;
                    $dest_y = $sourcefile_height - $insertfile_height;
                }
                break;
        }
        return ['dest_x' => $dest_x, 'dest_y' => $dest_y];
    }

    public function smallImg($photo)
    {
        $imgInfo = $this->getInfo($photo);
        $photo   = $this->filePath . $photo; //获得图片源
        $type    = $this->getType($photo);
        $newName = substr($imgInfo['name'], 0, strrpos($imgInfo['name'], '.')) . '.jpg'; //缩略图片名称
        $img     = $this->createImage($type, $photo);
        if (empty($img)) {
            return false;
        }
        $width  = $this->smallWidth > $imgInfo['width'] ? $imgInfo['width'] : $this->smallWidth;
        $height = $this->smallHeight > $imgInfo['height'] ? $imgInfo['height'] : $this->smallHeight;
        $srcW   = $imgInfo['width'];
        $srcH   = $imgInfo['height'];
        if ($srcW * $width > $srcH * $height) {
            $height = round(($srcH * $width) / $srcW);
        } else {
            $width = round(($srcW * $height) / $srcH);
        }

        if (function_exists('imagecreatetruecolor')) {
            $newImg = imagecreatetruecolor($width, $height);
            ImageCopyResampled($newImg, $img, 0, 0, 0, 0, $width, $height, $imgInfo['width'], $imgInfo['height']);
        } else {
            $newImg = imagecreate($width, $height);
            ImageCopyResized($newImg, $img, 0, 0, 0, 0, $width, $height, $imgInfo['width'], $imgInfo['height']);
        }
        if ($this->toFile) {
            if (file_exists($this->filePath . $this->smallFolder . $newName)) {
                @unlink($this->filePath . $this->smallFolder . $newName);
            }
            ImageJPEG($newImg, $this->filePath . $this->smallFolder . $newName);
            return $this->filePath . $this->smallFolder . $newName;
        } else {
            ImageJPEG($newImg);
        }
        ImageDestroy($newImg);
        ImageDestroy($img);
    }

    public function waterMark($photo)
    {
        $imgInfo = $this->getInfo($photo);
        $photo   = $this->filePath . $photo;
        $type    = $this->getType($photo);
        $newName = substr($imgInfo['name'], 0, strrpos($imgInfo['name'], '.')) . '_mark.jpg'; //加水印的图片名
        $img     = $this->createImage($type, $photo);
        if (empty($img)) {
            return false;
        }
        $width  = $this->maxWidth > $imgInfo['width'] ? $imgInfo['width'] : $this->maxWidth;
        $height = $this->maxHeight > $imgInfo['height'] ? $imgInfo['height'] : $this->maxHeight;
        $srcW   = $imgInfo['width'];
        $srcH   = $imgInfo['height'];
        if ($srcW * $width > $srcH * $height) {
            $height = round(($srcH * $width) / $srcW);
        } else {
            if (function_exists('imagecreatetruecolor')) {
                $newImg = imagecreatetruecolor($width, $height);
            } else {
                $newImg = imagecreate($width, $height);
            }
            @ImageCopyResized($newImg, $img, 0, 0, 0, 0, $width, $height, $imgInfo['width'], $imgInfo['height']);
        }

        if (! empty($this->logoName)) {
            if (! file_exists($this->logoName)) {
                return false;
            }
            $this->logoName  = strtolower(trim($this->logoName));
            $logo_image_type = $this->getType($this->logoName);
            $logo_image      = $this->createImage($logo_image_type, $this->logoName);
            $logo_image_w    = imagesx($logo_image);
            $logo_image_h    = imagesy($logo_image);
            $temp_logo_image = $this->getPos($width, $height, $this->logoPos, $logo_image);
            $logo_image_x    = (int) ceil($temp_logo_image['dest_x']);
            $logo_image_y    = (int) ceil($temp_logo_image['dest_y']);
            @imagecopymerge($newImg, $logo_image, $logo_image_x, $logo_image_y, 0, 0, $logo_image_w, $logo_image_h, 75);
        } else {
            $white          = imageColorAllocate($newImg, 255, 255, 255);
            $black          = imageColorAllocate($newImg, 0, 0, 0);
            $alpha          = imageColorAllocateAlpha($newImg, 230, 230, 230, 40);
            $temp_logo_text = $this->getPos($width, $height, $this->logoPos);
            $logo_text_x    = (int) ceil($temp_logo_text['dest_x']);
            $logo_text_y    = (int) ceil($temp_logo_text['dest_y']);
            @ImageTTFText($newImg, $this->textSize, $this->logoAngle, $logo_text_x, $logo_text_y, $black, $this->fontType, $this->waterText);
        }

        if ($this->toFile) {
            if (file_exists($this->filePath . $this->markFolder . '/' . $newName)) {
                @unlink($this->filePath . $this->markFolder . '/' . $newName);
            }
            ImageJPEG($newImg, $this->filePath . $this->markFolder . '/' . $newName);
            return $this->filePath . $this->markFolder . '/' . $newName;
        } else {
            ImageJPEG($newImg);
        }
        ImageDestroy($newImg);
        ImageDestroy($img);
    }

    public function myImageResize($src_file, $dst_file = '')
    {
        if ($dst_file == '') {
            $src_file_ = explode('.', $src_file);
            $dst_file  = $src_file_[0] . '_s.' . $src_file_[1];
        }
        $src_file = $this->filePath . $src_file;
        $dst_file = $this->filePath . $this->resizeFolder . $dst_file;
        if ($this->newWidth < 1 || $this->newHeight < 1) {
            exit();
        }
        if (! file_exists($src_file)) {
            exit();
        }
        // 图像类型
        $type = $this->getType($src_file);

        $src_img = $this->createImage($type, $src_file);

        $w       = imagesx($src_img);
        $h       = imagesy($src_img);
        $ratio_w = (1.0 * $this->newWidth) / $w;
        $ratio_h = (1.0 * $this->newHeight) / $h;
        $ratio   = 1.0;
        // 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
        if (($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
            if ($ratio_w < $ratio_h) {
                $ratio = $ratio_h; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
            } else {
                $ratio = $ratio_w;
            }
            // 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
            $inter_w   = (int) ($this->newWidth / $ratio);
            $inter_h   = (int) ($this->newHeight / $ratio);
            $inter_img = imagecreatetruecolor($inter_w, $inter_h);
            imagecopy($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h);
            // 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
            // 定义一个新的图像
            $new_img = imagecreatetruecolor($this->newWidth, $this->newHeight);
            imagecopyresampled($new_img, $inter_img, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $inter_w, $inter_h);
            switch ($type) {
                case 'jpg':
                    imagejpeg($new_img, $dst_file, 100); // 存储图像
                    break;
                case 'png':
                    imagepng($new_img, $dst_file, 100);
                    break;
                case 'gif':
                    imagegif($new_img, $dst_file, 100);
                    break;
                default:
                    break;
            }
        }
        // end if 1
        // 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
        // =if( ($ratio_w < 1 && $ratio_h > 1) || ($ratio_w >1 && $ratio_h <1) )
        else {
            $ratio = $ratio_h > $ratio_w ? $ratio_h : $ratio_w; //取比例大的那个值
                                                                // 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
            $inter_w   = (int) ($w * $ratio);
            $inter_h   = (int) ($h * $ratio);
            $inter_img = imagecreatetruecolor($inter_w, $inter_h);
            //将原图缩放比例后裁剪
            imagecopyresampled($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h, $w, $h);
            // 定义一个新的图像
            $new_img = imagecreatetruecolor($this->newWidth, $this->newHeight);
            imagecopy($new_img, $inter_img, 0, 0, 0, 0, $this->newWidth, $this->newHeight);
            switch ($type) {
                case 'jpg':
                    imagejpeg($new_img, $dst_file, 100); // 存储图像
                    break;
                case 'png':
                    imagepng($new_img, $dst_file, 100);
                    break;
                case 'gif':
                    imagegif($new_img, $dst_file, 100);
                    break;
                default:
                    reak;
            }
        } // if3
        return $dst_file;
    } // end function

    private function creatName($thisFileName)
    {
        $fileExt = image_extension((string) $thisFileName);
        if ($fileExt === '') {
            return [
                'type' => '',
                'file' => '',
            ];
        }
        $return = [
            'type' => '',
            'file' => '',
        ];
        for ($i = 0; $i < 5; $i++) {
            $fileNewName = $this->nowDate . '_' . get_random_str(8, 'Aa') . '.' . $fileExt;
            if (! file_exists($this->filePath . $fileNewName)) {
                return [
                    'type' => $fileExt,
                    'file' => $fileNewName,
                ];
            }
        }
        return $return;
    }

    private function createDir($dir)
    {
        if ($dir === '' || is_dir($dir)) {
            return;
        }
        mkdir($dir, 0755, true);
    }

    private function emptyUpload(string $message, string $oldName = ''): array
    {
        return [
            'err_msg'   => $message,
            'imgurl'    => '',
            'smallurl'  => '',
            'markurl'   => '',
            'resizeimg' => '',
            'fileText'  => '',
            'filepath'  => '',
            'oldurl'    => $oldName,
        ];
    }

    private function storeOne(string $tmp, string $name, int $size, $fileText): array
    {
        if ($size > $this->attaMaxSize) {
            return $this->emptyUpload('文件大小超出限制(' . $this->attaMaxSize . '字节)', $name);
        }
        if (! is_uploaded_image($tmp, $name)) {
            return $this->emptyUpload('文件类型不允许', $name);
        }
        $newFileName = $this->creatName($name);
        if ($newFileName['file'] === '') {
            return $this->emptyUpload('文件类型不允许', $name);
        }
        $fileNewName = $newFileName['file'];
        $this->createDir($this->filePath);
        if (! move_uploaded_file($tmp, $this->filePath . $fileNewName)) {
            return $this->emptyUpload('文件上传失败', $name);
        }

        $newSmallImg = '';
        $newMark     = '';
        $newResize   = '';
        if ($this->isSmall == 1) {
            $this->createDir($this->filePath . $this->smallFolder);
            $newSmallImg = $this->smallImg($fileNewName) ?: '';
        }
        if ($this->isMark == 1) {
            $this->createDir($this->filePath . $this->markFolder);
            $newMark = $this->waterMark($fileNewName) ?: '';
        }
        if ($this->isResize == 1) {
            $this->createDir($this->filePath . $this->resizeFolder);
            $newResize = $this->myImageResize($fileNewName) ?: '';
        }

        $text = is_string($fileText) ? trim($fileText) : '';
        if ($text === '') {
            $text = $name;
        }
        return [
            'err_msg'   => '',
            'imgurl'    => $fileNewName,
            'smallurl'  => $newSmallImg,
            'markurl'   => $newMark,
            'resizeimg' => $newResize,
            'fileText'  => $text,
            'filepath'  => $this->publicPath . $fileNewName,
            'oldurl'    => $name,
        ];
    }

    public function upMore()
    {
        $thisFiles = $this->files;
        if (! is_array($thisFiles) || ! isset($thisFiles['tmp_name'])) {
            return $this->emptyUpload('文件不能为空');
        }
        if (! is_array($thisFiles['tmp_name'])) {
            $text = is_array($this->fileText) ? (string) ($this->fileText[0] ?? '') : $this->fileText;
            return $this->storeOne(
                (string) $thisFiles['tmp_name'],
                (string) ($thisFiles['name'] ?? ''),
                (int) ($thisFiles['size'] ?? 0),
                $text
            );
        }

        $count  = count($thisFiles['tmp_name']);
        $maxNum = $this->fileNum > 0 ? min((int) $this->fileNum, $count) : $count;
        $result = [];
        for ($i = 0; $i < $maxNum; $i++) {
            $text = '';
            if (is_array($this->fileText)) {
                $text = (string) ($this->fileText[$i] ?? '');
            }
            $result[] = $this->storeOne(
                (string) ($thisFiles['tmp_name'][$i] ?? ''),
                (string) ($thisFiles['name'][$i] ?? ''),
                (int) ($thisFiles['size'][$i] ?? 0),
                $text
            );
        }
        return $result;
    }

    public function upOne()
    {
        if (empty($this->files['name']) || empty($this->files['tmp_name'])) {
            return ['err_msg' => '文件不能为空'];
        }
        return $this->storeOne(
            (string) $this->files['tmp_name'],
            (string) $this->files['name'],
            (int) ($this->files['size'] ?? 0),
            $this->fileText
        );
    }
}
