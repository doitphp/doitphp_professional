<?php
/**
 * ZIP压缩类
 *
 * 用于文件及目录的打包、压缩
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Zip.php 2.0 2012-12-23 12:36:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Zip {

    /**
     * array to store compressed data
     *
     * @var array
     */
    protected $_datasec = array();

    /**
     * array of directories that have been created already
     *
     * @var array
     */
    protected $_dirs = array();

    /**
     * central directory
     *
     * @var array
     */
    protected $_ctrlDir = array();

    /**
     * end of Central directory record
     *
     * @var string
     */
    protected $_eofCtrlDir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    /**
     * $oldOffset
     *
     * @var boolean
     */
    protected $_oldOffset = 0;

    /**
     * 向zip包内添加名字为$name的文件夹
     *
     * @access public
     *
     * @param string $name 目录名
     *
     * @return void
     */
    public function addDir($name) {

        //参数分析
        if (!$name) {
            return false;
        }

        $name = str_replace("\\", "/", $name);
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x0a\x00";    // version needed to extract
        $fr .= "\x00\x00";    // general purpose bit flag
        $fr .= "\x00\x00";    // compression method
        $fr .= "\x00\x00\x00\x00"; // last mod time and date
        $fr .= pack("V",0); // crc32
        $fr .= pack("V",0); //compressed filesize
        $fr .= pack("V",0); //uncompressed filesize
        $fr .= pack("v",strlen($name)); //length of pathname
        $fr .= pack("v", 0); //extra field length
        $fr .= $name;
        // end of "local file header" segment

        // no "file data" segment for path

        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr .= pack("V",0); //crc32
        $fr .= pack("V",0); //compressed filesize
        $fr .= pack("V",0); //uncompressed filesize

        // add this entry to array
        $this->_datasec[] = $fr;

        $newOffset = strlen(implode("", $this->_datasec));

        // ext. file attributes mirrors MS-DOS directory attr byte, detailed
        // at http://support.microsoft.com/support/kb/articles/Q125/0/19.asp

        // now add to central record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .="\x00\x00";    // version made by
        $cdrec .="\x0a\x00";    // version needed to extract
        $cdrec .="\x00\x00";    // general purpose bit flag
        $cdrec .="\x00\x00";    // compression method
        $cdrec .="\x00\x00\x00\x00"; // last mod time and date
        $cdrec .= pack("V",0); // crc32
        $cdrec .= pack("V",0); //compressed filesize
        $cdrec .= pack("V",0); //uncompressed filesize
        $cdrec .= pack("v", strlen($name) ); //length of filename
        $cdrec .= pack("v", 0 ); //extra field length
        $cdrec .= pack("v", 0 ); //file comment length
        $cdrec .= pack("v", 0 ); //disk number start
        $cdrec .= pack("v", 0 ); //internal file attributes
        $cdrec .= pack("V", 16 ); //external file attributes  - 'directory' bit set

        $cdrec .= pack("V", $this->_oldOffset); //relative offset of local header
        $this->_oldOffset = $newOffset;

        $cdrec .= $name;
        // optional extra field, file comment goes here
        // save to array
        $this->_ctrlDir[] = $cdrec;
        $this->_dirs[] = $name;
    }

    /**
     * 向zip包内添加名字$name,内容为$data的文件
     *
     * @access public
     *
     * @param string $data 文件内容
     * @param string $name 文件名
     *
     * @return void
     */
    public function addFile($data, $name) {

        //参数分析
        if (!$name || !$data) {
            return false;
        }

        $name = str_replace("\\", "/", $name);

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";    // version needed to extract
        $fr .= "\x00\x00";    // general purpose bit flag
        $fr .= "\x08\x00";    // compression method
        $fr .= "\x00\x00\x00\x00"; // last mod time and date

        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr($zdata, 2, -4); // fix crc bug
        $c_len = strlen($zdata);
        $fr .= pack("V",$crc); // crc32
        $fr .= pack("V",$c_len); //compressed filesize
        $fr .= pack("V",$unc_len); //uncompressed filesize
        $fr .= pack("v", strlen($name) ); //length of filename
        $fr .= pack("v", 0 ); //extra field length
        $fr .= $name;
        // end of "local file header" segment

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr .= pack("V",$crc); // crc32
        $fr .= pack("V",$c_len); // compressed filesize
        $fr .= pack("V",$unc_len); // uncompressed filesize

        // add this entry to array
        $this->_datasec[] = $fr;

        $newOffset = strlen(implode("", $this->_datasec));

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .="\x00\x00";    // version made by
        $cdrec .="\x14\x00";    // version needed to extract
        $cdrec .="\x00\x00";    // general purpose bit flag
        $cdrec .="\x08\x00";    // compression method
        $cdrec .="\x00\x00\x00\x00"; // last mod time & date
        $cdrec .= pack("V",$crc); // crc32
        $cdrec .= pack("V",$c_len); //compressed filesize
        $cdrec .= pack("V",$unc_len); //uncompressed filesize
        $cdrec .= pack("v", strlen($name) ); //length of filename
        $cdrec .= pack("v", 0 ); //extra field length
        $cdrec .= pack("v", 0 ); //file comment length
        $cdrec .= pack("v", 0 ); //disk number start
        $cdrec .= pack("v", 0 ); //internal file attributes
        $cdrec .= pack("V", 32 ); //external file attributes - 'archive' bit set

        $cdrec .= pack("V", $this->_oldOffset); //relative offset of local header
        $this->_oldOffset = $newOffset;

        $cdrec .= $name;
        // optional extra field, file comment goes here
        // save to central directory
        $this->_ctrlDir[] = $cdrec;
    }

    /**
     * 读取名字为$name的zip包的内容
     *
     * @access public
     *
     * @param string $name 所读取的zip文件的路径
     *
     * @return array
     */
    public function readZip($name) {

        //参数分析
        if (!$name) {
            return false;
        }
        if (!is_file($name)) {
            Controller::halt("The File: {$name} is not found!");
        }

        // File information
        $size        = filesize($name);

        // Read file
        $fh          = fopen($name, "rb");
        $filedata    = fread($fh, $size);
        fclose($fh);

        // Break into sections
        $filesecta   = explode("\x50\x4b\x05\x06", $filedata);

        // ZIP Comment
        $unpackeda   = unpack('x16/v1length', $filesecta[1]);

        $comment = substr($filesecta[1], 18, $unpackeda['length']);
        $comment = str_replace(array("\r\n", "\r"), "\n", $comment); // CR + LF and CR -> LF

        // Cut entries from the central directory
        $filesecta = explode("\x50\x4b\x01\x02", $filedata);
        $filesecta = explode("\x50\x4b\x03\x04", $filesecta[0]);
        array_shift($filesecta); // Removes empty entry/signature

        $files = array();
        foreach($filesecta as $filedata) {
            // CRC:crc, FD:file date, FT: file time, CM: compression method, GPF: general purpose flag, VN: version needed, CS: compressed size, UCS: uncompressed size, FNL: filename length
            $entrya = array();

            $unpackeda = unpack("v1version/v1general_purpose/v1compress_method/v1file_time/v1file_date/V1crc/V1size_compressed/V1size_uncompressed/v1filename_length", $filedata);

            // Check for value block after compressed data
            if($unpackeda['general_purpose'] & 0x0008)
            {
                $unpackeda2 = unpack("V1crc/V1size_compressed/V1size_uncompressed", substr($filedata, -12));

                $unpackeda['crc'] = $unpackeda2['crc'];
                $unpackeda['size_compressed'] = $unpackeda2['size_uncompressed'];
                $unpackeda['size_uncompressed'] = $unpackeda2['size_uncompressed'];

                unset($unpackeda2);
            }

            $entrya['name'] = substr($filedata, 26, $unpackeda['filename_length']);

            if(substr($entrya['name'], -1) == "/") // skip directories
            {
                continue;
            }

            $entrya['dir']  = dirname($entrya['name']);
            $entrya['dir']  = ($entrya['dir'] == "." ? "" : $entrya['dir']);
            $entrya['name'] = basename($entrya['name']);

            $files[] = $entrya;
        }

        return $files;
    }

    /**
     * 整理所要生成的zip包的内容
     *
     * @access public
     * @return string
     */
    public function zippedFile()
    {
        $data = implode("", $this->_datasec);
        $ctrldir = implode("", $this->_ctrlDir);

        return $data.
                $ctrldir.
                $this->_eofCtrlDir.
                pack("v", sizeof($this->_ctrlDir)). // total number of entries "on this disk"
                pack("v", sizeof($this->_ctrlDir)). // total number of entries overall
                pack("V", strlen($ctrldir)). // size of central dir
                pack("V", strlen($data)). // offset to start of central dir
                "\x00\x00"; // .zip file comment length
    }

   /**
    * Zip文件下载
    *
    * @access public
    *
    * @param string $downloadName 所要下载的Zip文件名
    *
    * @return void
    */
    public function download($downloadName){

        if(!$downloadName){
            return false;
        }
        header('Pragma: no-cache');
        header("Content-Type: application/zip; name=\"{$downloadName}.zip\"");
        header("Content-disposition: attachment; filename={$downloadName}.zip");
        echo $this->zippedFile();
    }
}