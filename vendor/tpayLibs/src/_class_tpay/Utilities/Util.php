<?php

/*
 * Created by tpay.com
 */

namespace tpayLibs\src\_class_tpay\Utilities;

/**
 * Class Util
 *
 * Utility class which helps with:
 *  - parsing template files
 *  - log library operations
 *  - handle POST array
 *
 * @package tpay
 */
class Util
{
    const REMOTE_ADDR = 'REMOTE_ADDRESS';

    static $lang = 'en';

    static $path = null;

    /**
     * Parse template file
     * @param string $templateFileName filename
     * @param array $data
     * @return string
     */
    public static function parseTemplate($templateFileName, $data = array())
    {
        $data['static_files_url'] = is_null(static::$path) ? $_SERVER['REQUEST_URI'] . '/../../src/' : static::$path;

        $templateDirectory = dirname(__FILE__) . '/../../View/Templates/';
        $buffer = false;

        if (ob_get_length() > 0) {
            $buffer = ob_get_contents();
            ob_clean();
        }
        ob_start();

        if (!file_exists($templateDirectory . $templateFileName . '.phtml')) {
            return '';
        }
        $lang = new Lang();
        $lang->setLang(static::$lang);
        include_once $templateDirectory . $templateFileName . '.phtml';
        $parsedHTML = ob_get_contents();
        ob_clean();

        if ($buffer !== false) {
            ob_start();
            echo $buffer;
        }

        return $parsedHTML;
    }

    /**
     * Save text to log file with details
     *
     * @param string $title action name
     * @param string $text text to save
     */
    public static function log($title, $text)
    {
        $text = (string)$text;
        $logFilePath = dirname(__FILE__) . '/../../log';

        $ip = (isset($_SERVER[static::REMOTE_ADDR])) ? $_SERVER[static::REMOTE_ADDR] : '';

        $logText = "\n===========================";
        $logText .= "\n" . $title;
        $logText .= "\n===========================";
        $logText .= "\n" . date('Y-m-d H:i:s');
        $logText .= "\nip: " . $ip;
        $logText .= "\n";
        $logText .= $text;
        $logText .= "\n\n";

        if (file_exists($logFilePath) && is_writable($logFilePath)) {
            file_put_contents($logFilePath, $logText, FILE_APPEND);
        }
    }

    /**
     * Save one line to log file
     *
     * @param string $text text to save
     */
    public static function logLine($text)
    {
        $text = (string)$text;
        $logFilePath = dirname(__FILE__) . '/../../log';
        if (file_exists($logFilePath) && is_writable($logFilePath)) {
            file_put_contents($logFilePath, "\n" . $text, FILE_APPEND);
        }
    }

    /**
     * Get value from $_POST array.
     * If not exists return false
     *
     * @param string $name
     * @param string $type variable type
     *
     * @return mixed
     * @throws TException
     */
    public static function post($name, $type)
    {
        if (!isset($_POST[$name])) {
            return false;
        }
        $val = $_POST[$name];
        if ($type === 'int') {
            $val = (int)$val;
        } elseif ($type === 'float') {
            $val = (float)$val;
        } elseif ($type === 'string') {
            $val = (string)$val;
        } elseif ($type === 'array') {
            $val = (array)$val;
        } else {
            throw new TException('Undefined $_POST variable type');
        }

        return $val;
    }

    public function setLanguage($lang)
    {
        static::$lang = $lang;
        return $this;
    }

    /**
     * Set custom library path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        static::$path = $path;
        return $this;
    }
}
