<?php

namespace tfl\builders;

use tfl\utils\tHtmlForm;
use tfl\utils\tString;

class RequestBuilder
{
    const METHOD_POST = 'post';
    const METHOD_FILES = 'files';
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
        return $this->method === self::METHOD_POST;
    }

    public function isGetRequest(): bool
    {
        return $this->method === self::METHOD_GET;
    }

    /**
     * @param string|null $forceMethod
     * @return bool
     */
    public function isAjaxRequest($forceMethod = null): bool
    {
        $isAjax = $this->hasTflNmHeader();

        $methodAccept = true;

        if ($forceMethod) {
            $methodAccept = $this->checkForceMethod($forceMethod);
        }

        return $isAjax && $methodAccept;
    }

    public function checkForceMethod($forceMethod)
    {
        $requestMethod = self::METHOD_GET;

        switch ($forceMethod) {
            case self::METHOD_DELETE:
            case self::METHOD_POST:
            case self::METHOD_PUT:
                $requestMethod = self::METHOD_POST;
                break;
        }

        $value = \TFL::source()->request->getRequestValue($requestMethod, tHtmlForm::NAME_METHOD);
        return ($this->method == $requestMethod) && ($value == $forceMethod);
    }

    private function hasTflNmHeader()
    {
        $attr = \TFL::source()->config('web')['HTTP_REQUEST'] ?? null;
        $value = \TFL::source()->config('web')[$attr] ?? null;

        return isset($_SERVER[$attr]) && $_SERVER[$attr] == $value;
    }

    public function getRequestValue(string $method, string $nameValue)
    {
        $data = $this->getRequestData(mb_strtolower($method));

        if (isset($data[$nameValue])) {
            return tString::encodeString($data[$nameValue]);
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
            case self::METHOD_FILES:
                return $_FILES ?? [];
                break;
            default:
                return [];
        }
    }
}