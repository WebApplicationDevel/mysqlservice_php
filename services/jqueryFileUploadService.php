<?php
/*****************************************
 * php文件:jqueryFileUploadService.php
 * 说   明:jquery-file-upload 文件上传服务
 * 作   者:LYP
 * 时   间:2017.1.18.
 *****************************************/

//error_reporting(E_ALL | E_STRICT);
include_once (dirname(__FILE__) . '/../config/config.php');
require(dirname(__FILE__) .'/../lib/jquery-file-upload-server/UploadHandler.php');

$ymd = date("Ym");
$options = array(
            //'script_url' => "http://localhost/test",
            'upload_dir' => UPLOAD_PATH,
            ////(strtoupper(substr(PHP_OS,0,3))==='WIN'
                            //    ?'D:/xampp/htdocs/test/upload/'
                            //    :'/Library/WebServer/Documents/test/upload/').$ymd."/",
            'upload_url' => UPLOAD_URL.$ymd."/",//$this->get_full_url().'/files/',            
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(doc|docx|ppt|pptx|xls|xlsx|gif|jpe?g|png|wmv|mpe?g|avi|mp3|pdf|mp4|rm|rmvb)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => 50*1024*1024,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Use exif_imagetype on all files to correct file extensions:
            'correct_image_extensions' => false,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1
            
        );

$upload_handler = new UploadHandler($options);
