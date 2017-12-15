<?php
function edit_img($info){
	foreach($info as $key => $value){

		if(is_array($value)){
			foreach($value as $k => $v){
				if(is_array($v)){
					foreach($v as $kk => $kv){
						$info[$key][$k][$kk] = ((strpos($kv,'.png') !== false) || 
						(strpos($kv,'.jpg') !== false) || 
						(strpos($kv,'.jpeg') !== false)) 
						? 'http://'.$_SERVER['HTTP_HOST'].$kv : $kv;
					}

				}else{

					$info[$key][$k] = ((strpos($v,'.png') != false) || 
					(strpos($v,'.jpg') != false) || 
					(strpos($v,'.jpeg') != false)) 
					? 'http://'.$_SERVER['HTTP_HOST'].$v : $v;
				
		
				}

			}
		}else{
			$info[$key] = ((strpos($value,'.png') !== false) || 
				(strpos($value,'.jpg') !== false) || 
				(strpos($value,'.jpeg') !== false)) 
				? 'http://'.$_SERVER['HTTP_HOST'].$value : $value;
		}
	}

		return $info;
}