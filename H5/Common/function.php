<?php
function getData(){
    $url = "http://hq.sinajs.cn/etag.php?_=1505787622585&list=fx_susdjpy";
    $strGetCurl = curl_request($url);
    $arrGetData = explode(',', $strGetCurl);

    //获取时间
    $strYear = strstr(array_pop($arrGetData), '";', true);
    $strTime = str_replace('var hq_str_fx_susdjpy="',' ',$arrGetData[0]);
    $arrResutData['date'] =  strtotime($strYear . $strTime);

    //商品价格
    $arrResutData['buy'] =  '0'.strstr($arrGetData[1],'.');
    return $arrResutData;
}