<?php
    $pic_con = file_get_contents("php://input");
    $type = get_imgtype($pic_con);
    $savePath = 'avatar/6130';
    if(!file_exists($savePath)) {
        mkdir($savePath);
    }
    if ($type=='jpeg'){
    	$type = 'jpg';
    }
    $pic_id = time().'_'.rand();
    if ($_REQUEST['type']=='source'){
    	//上传原图
    	$src = $savePath . "/".$pic_id.'.'.$type;
    	$fh = fopen($src, 'w');
    	$iswrite = fwrite($fh, $pic_con);
    	fclose($fh);
        if ($iswrite) {
            $fbPic = '';
        }
    }else{
    	//生成缩略图
    	$big = $savePath.'/'.$pic_id."_big.".$type;
    	if (file_exists($big)){
    		$big = $savePath.'/'.$pic_id."_big.".$type;
    	}
    	$fh = fopen($big, 'w');
    	$iswrite = fwrite($fh, $pic_con);
    	fclose($fh);
        if ($iswrite){
            $middle = $savePath.'/'.$pic_id."_middle.".$type;
            $fbPic = $middle;
            $small = $savePath.'/'.$pic_id."_small.".$type;
            thumb($big, $middle, '', 100, 100,false);
            thumb($big, $small, '', 50, 50,false);
        }
    }

    if ($iswrite){
    		echo '{"code":200, "msg":"上传成功","pic":"'.$fbPic.'"}';
    }else{
    	echo '{"code":404, "msg":"上传失败","pic":""}';
    }

    function get_imgtype ( $file_con ){
        $header = substr($file_con, 0,5);
        //echo bin2hex($header);
        if ( $header { 0 }. $header { 1 }== "\x89\x50" ){
            return 'png' ;
        }else if( $header { 0 }. $header { 1 } == "\xff\xd8" ){
            return 'jpg' ;
        }else if( $header { 0 }. $header { 1 }. $header { 2 } == "\x47\x49\x46" ){
            return 'gif';
        }else{
            return 'jpg';
        }
    }

    function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    function thumb($image, $thumbname, $type='', $maxWidth=200, $maxHeight=50, $interlace=true, $quality = 100) {
        // 获取原图信息
        $info = getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int) ($srcWidth * $scale);
                $height = (int) ($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);
            
            //修复Png透明(By Soul)
            if ('gif' == $type || 'png' == $type) {
                //echo $type;exit;
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                //$background_color = imagecolorallocate($thumbImg, 0, 255, 0);  //  指派一个绿色
                //imagecolortransparent($thumbImg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            // 复制图片
            if (function_exists("ImageCopyResampled")){
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            }else{
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);
            
            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            
            //修复png生成失败问题(By Soul)
            if ('gif' == $type || 'png' == $type) {
                $imageFun($thumbImg, $thumbname);
            }else{
                $imageFun($thumbImg, $thumbname, $quality);
            }
            
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }
?>