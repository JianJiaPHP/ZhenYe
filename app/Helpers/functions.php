<?php

/**
 * 失败返回
 */
if (!function_exists('fail')) {
    function fail($message = '系统繁忙', $data = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => 500,
            'message' => $message,
            'data' => $data
        ]);
    }
}

/**
 * 成功返回
 */
if (!function_exists('success')) {
    function success($data = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $data
        ]);
    }
}
/**
 * 根据传入的值判断失败还是成功
 */
if (!function_exists('choose')) {
    function choose($data): \Illuminate\Http\JsonResponse
    {
        return $data ? success() : fail();
    }

}
/**
 * 其他状态返回
 */
if (!function_exists('otherReturn')) {
    function otherReturn($code, $message, $data = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
}
/**
 * @return int
 */
function getRand()
{
    $code = rand(100000,999999);
    if (strlen($code) !== 6) {
        getRand();
    }
    return $code;
}

/**
 * @param string $uid
 * @return string UUID
 */
function uuid($uid = '')
{
    $date = date('Ymd');
    $rand = rand(100000, 999999);
    $time = mb_substr(time(), 5, 5, 'utf-8');
    $serialNumber = $uid . $date . $time . $rand;
    // echo strlen($serialNumber).'<br />';
    return $serialNumber;
}

/**
 * @param $arr
 * @param $key
 * @return array
 * 二维数组分组
 */
function array_group_by($arr, $key)
{
    $grouped = [];
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    // Recursively build a nested grouping if more parameters are supplied
    // Each grouped array value is grouped according to the next sequential key
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}




