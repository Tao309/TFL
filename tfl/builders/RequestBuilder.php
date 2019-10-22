<?php

namespace tfl\builders;

use tfl\utils\tString;

class RequestBuilder
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    const METHOD_PUT = 'put';
    const METHOD_DELETE = 'delete';

    /**
     * @var $method string|null
     */
    private $method;

    public function __construct()
    {
        $this->setRequestMethod();
    }

    private function setRequestMethod(): void
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']) ?? self::METHOD_GET;
    }

    public function isPostRequest(): bool
    {
        return $this->method === 'post';
    }

    public function isAjaxRequest(): bool
    {
        //@todo исправить проверку
        $isAjax = false;
        return ($this->method === 'post' || $this->method === 'put') && $isAjax;
    }

    public function getRequestValue(string $method, string $nameValue)
    {
        $data = $this->getRequestData(mb_strtolower($method));

        if (isset($data[$nameValue])) {
            return tString::checkString($data[$nameValue]);
        }

        return null;
    }

    public function getRequestData(string $method): array
    {
        switch ($method) {
            case self::METHOD_POST:
                return $_POST ?? [];
                break;
            case self::METHOD_GET:
                return $_GET ?? [];
                break;
            case self::METHOD_PUT:
                return $_PUT ?? [];
                break;
            default:
                return [];
        }
    }
}