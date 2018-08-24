<?php

namespace down;

/**
 * Excel导入导出
 * Class Excel
 * @package service
 * @date 2017/03/22 15:32
 */
class Excel
{
    /**
     * Excel导入数据
     * @param string $file_name 文件名
     * @param array $filedList 需要导入保存的字段列表
     * @param array $cellList 列名列表 array
     * @return array|string $data
     */
    public static function uploadExcel($file_name, $filedList = [], $cellList = [])
    {
        vendor("PHPExcel.PHPExcel");
        $info = pathinfo($file_name);
        if ($info['extension'] == "xlsx") {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');//创建读取实例
        } else {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//创建读取实例
        }
        $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');//加载文件
        $sheet = $objPHPExcel->getSheet(0);//取得sheet(0)表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn();
        if ($cellList[count($filedList) - 1] != $highestColumn) {
            return '';
        }
        $data = array();
        for ($i = 0; $i < $highestRow - 1; $i++) {
            for ($j = 0; $j < count($cellList); $j++) {
                $data[$i][$filedList[$j]] = $objPHPExcel->getActiveSheet()->getCell($cellList[$j] . ($i + 2))
                    ->getFormattedValue();
                if (is_object($data[$i][$filedList[$j]])) $data[$i][$filedList[$j]] = $data[$i][$filedList[$j]]->__toString();
                $data[$i][$filedList[$j]] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $data[$i][$filedList[$j]]);
            }
        }
        return $data;
    }

    /**
     * Excel导出数据
     * @param string $filename 导出文件名
     * @param array $titleList 文件表头
     * @param array $data 导出数据
     */
    public static function exportExcel($filename = '导出数据', $titleList = [], $data = [])
    {
        $xlsTitle = iconv('utf-8', 'gb2312', $filename);//文件名称 将字符串从utf-8编码转为gb2312编码
        $fileName = $filename;//设置文件名称
        $cellNum = count($titleList);//获取文件的列数
        $dataNum = count($data);//获取数据的条数
        vendor("PHPExcel.PHPExcel");//导入PHPExcal类库
        $objPHPExcel = new \PHPExcel();//生成PHPExcel类实例
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP');
        $objPHPExcel->getProperties()->setCreator("Sam.c")//设置文档属性作者
        ->setLastModifiedBy("Sam.c Test")->setTitle("Microsoft Office Excel Document")->setSubject("Test0")->setDescription("Test1")
            ->setKeywords("Test2")->setCategory("Test result file");
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setBold(true)->setSize(13);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($cellName[$i] . '1', $titleList[$i][1]);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(12);
        }
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($cellName[$j] . ($i + 2), $data[$i][$titleList[$j][0]], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->getStyle($cellName[$j] . ($i + 2), $data[$i][$titleList[$j][0]])->getFont()->setSize(12);
            }
        }
        ob_end_clean();//清除缓冲区,避免乱码
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }


}