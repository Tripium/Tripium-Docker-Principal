<?php

namespace WilokeRepository\Helpers;

/**
 * Class Repository
 * @package HSBlogCore\Helpers
 */
class WilokeRepository
{
    /*
     * All of configurations of items
     *
     * @var array
     */
    protected $aItems = [];
    protected $fileName;
    protected $fileNameIncExt;
    protected $dir;
    protected $arrKey;
    protected $value = null;

    public static function init($dir)
    {
        $oInit = new self();
        $oInit->setConfigDir($dir);

        return $oInit;
    }

    /**
     * @param string $dir
     *
     * @return $this
     */
    private function setConfigDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return mixed
     */
    private function fileGetConfigurations()
    {
        if (isset($this->aItems[$this->fileName])) {
            return $this->aItems[$this->fileName];
        }

        if (!file_exists($this->dir . $this->fileNameIncExt)) {
            $this->aItems[$this->fileName] = [];

            return $this->aItems[$this->fileName];
        }
        $this->aItems[$this->fileName] = include $this->dir . $this->fileNameIncExt;

        return $this->aItems[$this->fileName];
    }

    /**
     * Parse key to get the file name and the array key
     *
     * @param $key
     * @return $this
     */
    private function parseKey($key)
    {
        $aParseKey = explode(':', $key);
        $this->fileName = $aParseKey[0];
        $this->fileNameIncExt = $this->fileName . '.php';
        $this->arrKey = isset($aParseKey[1]) ? $aParseKey[1] : '';

        return $this;
    }

    public function reset()
    {
        $this->value = null;
        return $this;
    }

    /**
     * Get the specified configuration value
     *
     * @param string $key
     * @param bool $isChainingAble
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $isChainingAble = false, $default = '')
    {
        $aValue = $this->value === null ? $this->getAll() : $this->value;
        $value = isset($aValue[$key]) ? $aValue[$key] : $default;

        if ($isChainingAble) {
            $this->value = $value;
        } else {
            $this->reset();
        }

        return $isChainingAble ? $this : $value;
    }

    public function setFile($key)
    {
        $this->reset()->parseKey($key)->fileGetConfigurations();
        return $this;
    }

    /**
     * Get all configs of the specified file
     *
     * @param array $default
     *
     * @return array
     */
    public function getAll($default = [])
    {
        if (isset($this->aItems[$this->fileName])) {
            return $this->aItems[$this->fileName];
        }

        return $default;
    }
}
