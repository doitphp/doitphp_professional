<?php
/**
 * CSV操作类
 *
 * CSV文件的读取及生成
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Csv.php 2.0 2012-12-23 15:56:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Csv {

    /**
     * 将CSV文件转化为数组
     *
     * @access public
     *
     * @param string $fileName csv文件名(路径)
     * @param string $delimiter 单元分割符(逗号或制表符)
     *
     * @return array
     */
    public static function readCsv($fileName, $delimiter = ",") {

        //参数分析
        if (!$fileName) {
            return false;
        }

        setlocale(LC_ALL, 'en_US.UTF-8');

        //读取csv文件内容
        $handle       = fopen($fileName, 'r');

        $outputArray  = array();
        $row          = 0;
        while ($data = fgetcsv($handle, 1000, $delimiter)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i ++) {
                $outputArray[$row][$i] = $data[$i];
            }
            $row++;
        }
        fclose($handle);

        return $outputArray;
    }

    /**
     * 生成csv文件
     *
     * @access public
     *
     * @param string $fileName 所要生成的文件名
     * @param array $data csv数据内容, 注:本参数为二维数组
     * @param boolean $isDownLoad 生成Csv文件的方式是否为浏览器下载（true:是/false:不是）
     *
     * @return mixed
     */
    public static function createCsv($fileName, $data, $isDownLoad = true) {

        //参数分析
        if (!$fileName || !$data || !is_array($data)) {
            return false;
        }
        if (stripos($fileName, '.csv') === false) {
            $fileName .= '.csv';
        }

        //分析$data内容
        $content = '';
        foreach ($data as $lines) {
            if ($lines && is_array($lines)) {
                foreach ($lines as $key=>$value) {
                    if (is_string($value)) {
                        $lines[$key] = '"' . $value . '"';
                    }
                }
                $content .= implode(",", $lines) . "\n";
            }
        }

        //当文件生成方式不为浏览器下载时
        if ($isDownLoad === false) {
            //分析文件所在的目录
            $dirPath = dirname($fileName);
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            return file_put_contents($fileName, $content, LOCK_EX);
        }

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Expires:0");
        header("Pragma:public");
        header("Cache-Control: public");
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $fileName);

        echo $content;
    }
}
