<?php
namespace Done\Extend;

class Upload{

	protected $upload_dir;
	protected $access_dir;

	function __construct($upload_dir=""){
		if($upload_dir==""){
			$upload_dir = APP_PUBLIC_PATH."uploads";
			$access_dir = HOST.dirname(ACCESS_APP_PATH)."/Public/uploads";
		}
		$this->upload_dir = $upload_dir;
		$this->access_dir = $access_dir;
	}

	//上传base64编码的图像
	function base64_upload($base64_image,$bmiddle=true,$thumbnail=true,$watermark=false){

		//返回结果
		$res = array();
		//post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
		$base64_image = str_replace(' ','+', $base64_image);
		//格式匹配
		if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image,$matches)){
			$type = $matches[2];
			if($type='jpeg'){
				$file_name = uniqid().'.jpg';
			}else{
				$file_name = uniqid().".".$type;
			}
			$original_path = $this->upload_dir."/".$file_name; //原始图保存路径
			$content = base64_decode(str_replace($matches[1],'',$base64_image));
			if(file_put_contents($original_path,$content)){
				$res['original_pic'] = $this->access_dir."/".$file_name;

				$image = new \Classes\Image();
				$imageInfo = $image->getInfo($original_path);
				$percent = 0.5;
				if($bmiddle){
					$width = $imageInfo['width'] * $percent;
					$height = $imageInfo['height'] * $percent;
					if($bmiddle_name = $image->thumb($file_name,$width,$height,"half_")){
						$res['bmiddle_pic'] = $this->access_dir."/".$bmiddle_name;
					}
				}
				if($thumbnail){
					$width = "100px";
					$height = "100px";
					if($thumbnail_name = $image->thumb($file_name,$width,$height)){
						$res['thumbnail_pic'] = $this->upload_dir."/".$thumbnail_name;
					}
					
				}
				if($watermark){
					$water_name = "20160508215402_926.png";
					$copy_name = "wa_".$file_name;
					if($image->copyImage($file_name,$copy_name)){
						if($watermark_name = $image->waterMark($copy_name,$water_name,5)){
							$res['watermark_pic'] = $this->upload_dir."/".$watermark_name;
						}						
					}
				}
			}
		}
		$res['created_at'] = date('Y/m/d h:i:sa');
		return $res;
		
	}

	//普通上传
	// function normal_upload(){
	// 	if ((($_FILES["file"]["type"] == "image/gif")|| ($_FILES["file"]["type"] == "image/jpeg")|| ($_FILES["file"]["type"] == "image/pjpeg")) && ($_FILES["file"]["size"] < 20000)){
	// 		if ($_FILES["file"]["error"]> 0){
	// 		    echo "Return Code: ".$_FILES["file"]["error"]."<br />";
	// 		}else{
	// 		    if (file_exists($this->upload_dir."/".$_FILES["file"]["name"])){
	// 		        echo $_FILES["file"]["name"]." already exists. ";
	// 		    }else{
	// 			    move_uploaded_file($_FILES["file"]["tmp_name"],$this->upload_dir."/". $_FILES["file"]["name"]);
	// 			    echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
	// 			}
	//         }
	//     }else{
	// 	  echo "Invalid file";
	// 	}
	// }
}

?>