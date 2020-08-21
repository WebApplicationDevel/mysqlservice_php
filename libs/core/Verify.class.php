<?php
include_once(dirname(dirname(__FILE__)).'/model/Session.model.php');

class verify {

    public $codeSet;
    public $fontSize = 16;
    public $useCurve = true;
    public $useNoise = true;
    public $imageH;
    public $imageL;
    public $length;
    public $code_filetype;
    public $bg = array(243, 251, 254);
    protected $_image = null;
    protected $_color = null;

    function __construct($width = '110', $height = '40'
            , $strlength = '4'
            , $filetype = 'gif'
            , $code_type = '3') {

        $this->imageL = $width;
        $this->imageH = $height;
        $this->length = $strlength;
        $this->code_filetype = $filetype;
        if ($code_type == '1') {
            $this->codeSet = '1234567890';
        } elseif ($code_type == '2') {
            $this->codeSet = 'ABCDEFGHJKLMNPQRTUVWXY';
        } else {
            $this->codeSet = '3456789ABCDEFGHJKLMNPQRTUVWXY';
        }
    }

    /**
     * 生成验证码图片
     */
    public function entry() {
        $requestData = ($_SERVER ["REQUEST_METHOD"] === "GET" ? $_GET : $_POST);
        global $config;
        if ($config["cross_domain"] == 1) {
            if (!isset($requestData["UID"])) {
                echo json_encode(array("msg" => "用户ID错误！", code => 1000));
                return false;
            }
        }
        
        $this->imageL || $this->imageL = $this->length * $this->fontSize * 1.5 + $this->fontSize * 1.5;
        $this->imageH || $this->imageH = $this->fontSize * 2;
        $this->_image = imagecreate($this->imageL, $this->imageH);
        imagecolorallocate($this->_image, $this->bg[0], $this->bg[1], $this->bg[2]);
        $this->_color = imagecolorallocate($this->_image, mt_rand(1, 120), mt_rand(1, 120), mt_rand(1, 120));

        $ttf = dirname(__FILE__) . '/ttfs/t' . 2 . '.ttf';

        if ($this->useNoise) {
            $this->_writeNoise();
        }
        if ($this->useCurve) {
            $this->_writeCurve();
        }

        $code = array();
        $codeNX = 0;

        for ($i = 0; $i < $this->length; $i++) {

            $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
            $codeNX += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
            imagettftext($this->_image, $this->fontSize, mt_rand(-40, 70), $codeNX, $this->fontSize * 1.5, $this->_color, $ttf, $code[$i]);
        }
        
        global $config;
        if ($config["cross_domain"]==1) {
            //向数据库中保存用户验证码
            $session = new Session($requestData["UID"], "_AUTH_CODE_", implode("", $code));
            //$session->cleanup();  //先清除同样的session
            $session->save();
        } else { //如果没跨域，则直接使用会话存储验证码
            session_start();
            $_SESSION["_AUTH_CODE_"] = implode("", $code);
        }

        header('Pragma: no-cache');
        if ($this->code_filetype == "png") {
            @header("Content-Type:image/png");
        } elseif ($this->code_filetype == "jpg") {
            @header("Content-Type:image/jpeg");
        } else {
            @header("Content-Type:image/gif");
        }

        imageJPEG($this->_image);
        imagedestroy($this->_image);
    }
    
    /**
     * 检查验证码
     * @param type $code 验证码
     */
    public function checkCode($code) {
        global $config;
        if ($config["cross_domain"]==1) {
            $requestData = ($_SERVER ["REQUEST_METHOD"] === "GET" ? $_GET : $_POST);
             if (!isset($requestData["UID"])) {
                echo json_encode(array("msg" => "用户ID错误！", code => 1000));
                return false;
            }
            $session = new Session($requestData["UID"], "_AUTH_CODE_");
            $data = $session->get();
            if (empty($data))
                return false;
            return $data == $code;
        }
        else {
            session_start();
            if (isset($_SESSION["_AUTH_CODE_"]))
                return $_SESSION["_AUTH_CODE_"] == $code;
            else
                return false;
        }
    }

    protected function _writeCurve() {
        $A = mt_rand(1, $this->imageH / 2);
        $b = mt_rand(-$this->imageH / 4, $this->imageH / 4);
        $f = mt_rand(-$this->imageH / 4, $this->imageH / 4);
        $T = mt_rand($this->imageH * 1.5, $this->imageL * 2);
        $w = (2 * M_PI) / $T;

        $px1 = 0;
        $px2 = mt_rand($this->imageL / 2, $this->imageL * 0.667);
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2;
                $i = (int) (($this->fontSize - 6) / 4);
                while ($i > 0) {
                    imagesetpixel($this->_image, $px + $i, $py + $i, $this->_color);
                    $i--;
                }
            }
        }

        $A = mt_rand(1, $this->imageH / 2);
        $f = mt_rand(-$this->imageH / 4, $this->imageH / 4);
        $T = mt_rand($this->imageH * 1.5, $this->imageL * 2);
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
        $px1 = $px2;
        $px2 = $this->imageL;
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2;
                $i = (int) (($this->fontSize - 8) / 4);
                while ($i > 0) {
                    imagesetpixel($this->_image, $px + $i, $py + $i, $this->_color);
                    $i--;
                }
            }
        }
    }

    protected function _writeNoise() {
        for ($i = 0; $i < 10; $i++) {
            $noiseColor = imagecolorallocate(
                    $this->_image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225)
            );
            for ($j = 0; $j < 5; $j++) {
                imagestring(
                        $this->_image, 5, mt_rand(-10, $this->imageL), mt_rand(-10, $this->imageH), $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)], $noiseColor
                );
            }
        }
    }

}

?>