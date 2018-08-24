<?php

namespace down;

/**
 * Csv导入导出
 * Class Csv
 * @package service
 * @date 2017/03/22 15:32
 */
class Csv
{
    /**
     * CSV导入数据
     * @param string $file_name 导入文件名
     * @param array $filedList 数据字段
     * @param int $size 切割数
     * @param bool $is_cutting 是否切割
     * @return array|string $array
     */
    public static function uploadCsv($file_name = '', $filedList = [], $size = 3000, $is_cutting = false)
    {
        $handle = fopen($file_name, 'r');
        $arr = [];
        $i = 0;
        while ($data = fgetcsv($handle)) {
            if ($i++ > 0) {
                $arr[] = $data;
            }
        }
        $array = [];
        foreach ($arr as $k => $v) {
            foreach ($filedList as $key => $val) {
                $array[$k][$val] = iconv('GB2312//IGNORE', 'UTF-8', trim($v[$key]));
            }
        }
        if ($is_cutting) return array_chunk($array, $size);
        return $array;
    }

    /**
     * CSV导出数据
     * @param string $file_name 导出文件名
     * @param array $titleList 文件表头
     * @param array $data 导出的数据
     */
    public static function exportCsv($file_name = '导出数据', $titleList = [], $data = [])
    {
        if (count($data) > 50000) exit;
        header('Content-type:text/html; charset=utf-8');
        header('Content-Type:application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename=' . $file_name . '.csv');
        header('Cache-Control:max-age=0');
        $file = fopen('php://output', 'a');
        $limit = 1000;
        $calc = 0;
        $title = [];
        $tarr = [];
        foreach ($titleList as $v) {
            $title[] = iconv('UTF-8', 'GB2312//IGNORE', $v);
        }
        fputcsv($file, $title);
        foreach ($data as $v) {
            $calc++;
            if ($limit == $calc) {
                ob_flush();
                flush();
                $calc = 0;
            }
            foreach ($v as $t) {
                $tarr[] = iconv('UTF-8', 'GB2312//IGNORE', $t);
            }
            fputcsv($file, $tarr);
            unset($tarr);
        }
        fclose($file);
        exit();
    }
}
