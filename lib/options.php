<?

namespace Developx\Comments;

/**
 * Class Options
 */
class Options
{
    protected static $_instance;
    const MODULE_NAME = 'developx.comments';
    const LOG_PATH = '/bitrix/modules/developx.comments/log/captcha_fails_log.log';
    const CACHE_TIME = 36000000;

    /** @var array */
    public $arOptions = [
        'CAPTCHA_ACTIVE' => [
            'TYPE' => 'checkbox',
            'DEFAULT' => 'N'
        ],
        'CAPTCHA_KEY' => [
            'TYPE' => 'text',
            'SIZE' => 50
        ],
        'CAPTCHA_SECRET' => [
            'TYPE' => 'text',
            'SIZE' => 50
        ],
        'CAPTCHA_SENS' => [
            'TYPE' => 'text',
            'DEFAULT' => 0.5
        ],
        'CAPTCHA_FAILS_LOG' => [
            'TYPE' => 'checkbox',
            'DEFAULT' => 'Y'
        ]
    ];

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $obCache = new \CPHPCache();
        if ($obCache->InitCache(self::CACHE_TIME, self::MODULE_NAME . 'options', '/')) {
            $this->arOptions = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            foreach ($this->arOptions as $code => $prop) {
                $this->arOptions[$code]['VALUE'] = \COption::GetOptionString(self::MODULE_NAME, $code);
                if (empty($this->arOptions[$code]['VALUE']) && !empty($prop['DEFAULT'])) {
                    $this->arOptions[$code]['VALUE'] = $prop['DEFAULT'];
                }
            }
            $obCache->EndDataCache($this->arOptions);
        }
    }

    public function setOption($code, $value)
    {
        if ($this->arOptions[$code]['TYPE'] == 'checkbox' && empty($value)){
            $value = 'N';
        }
        \COption::SetOptionString(self::MODULE_NAME, $code, $value);
    }

    public function clearCache()
    {
        $cache = new \CPHPCache();
        $cache->Clean('cache' . self::MODULE_NAME, '/');
    }
    /**
     * @return array
     **/
    public function getOptions()
    {
        $arOptions = [];
        foreach ($this->arOptions as $code => $option){
            $arOptions[$code] = $option['VALUE'];
        }
        return $arOptions;
    }

    public function showHtmlOption($code, $title)
    {
        $params = $this->arOptions[$code];
        switch ($params['TYPE']) {
            case 'checkbox':
                echo '
                <tr>
                    <td width="50%">' . $title . '</td>
                    <td width="50%"><input type="checkbox" 
                    name="' . $code . '"
                    value="Y" ' . ($params['VALUE'] == "Y" ? "checked" : "") . '></td>
                </tr>';
                break;
            case 'text':
                echo '
                <tr>
                    <td width="50%">' . $title . '</td>
                    <td width="50%">
                    <input type="text" size="' . $params['SIZE'] . '" maxlength="255" value="' . $params['VALUE'] . '" name="' . $code . '">
                    </td>
                </tr>';
                break;
        }
    }

    public function getLogPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . self::LOG_PATH;
    }

    public function logCaptchaFail($data)
    {
        $logFile = $this->getLogPath();
        file_put_contents($logFile, date('d.m.Y h:i:s') . '----------------------'.PHP_EOL, FILE_APPEND);
        file_put_contents($logFile, print_r($_REQUEST, 1).PHP_EOL, FILE_APPEND);
        file_put_contents($logFile, print_r($data, 1).PHP_EOL, FILE_APPEND);
    }
}

?>