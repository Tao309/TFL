<?php

namespace tfl\interfaces;

interface SessionBuilderInterface
{
    public function setRequestData();

    public function response();

    public function addErrorText(string $message);

    public function getErrorText();
}