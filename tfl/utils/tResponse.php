<?php

namespace tfl\utils;

use tfl\builders\DbBuilder;

class tResponse
{
    public static function resultSuccess($input = [], $echo = false, $json = true)
    {
        list($message, $action) = $input;

        $data = [
            'result' => tString::RESPONSE_RESULT_SUCCESS,
            'message' => $message ?? tString::RESPONSE_RESULT_SUCCESS,
            'action' => $action ?? DbBuilder::TYPE_ERROR,
        ];

        if ($json) {
            $data = json_encode($data);
        }

        if ($echo) {
            echo $data;
            exit;
        } else {
            return $data;
        }
    }

    public static function resultError($message, $echo = false, $json = true)
    {
        $data = [
            'result' => tString::RESPONSE_RESULT_ERROR,
            'message' => $message,
            'action' => DbBuilder::TYPE_ERROR,
        ];

        if ($json) {
            $data = json_encode($data);
        }

        if ($echo) {
            echo $data;
            exit;
        } else {
            return $data;
        }
    }

    public static function modelNotFound()
    {
        return self::resultError('Model not found!');
    }

    public static function modalWindow($title, $content, $classNames = [])
    {
        $data = [
            'headerTitle' => $title,
            'content' => $content,
            'className' => implode(' ', $classNames),

            'result' => tString::RESPONSE_RESULT_SUCCESS,
            'action' => DbBuilder::TYPE_SHOW_MODAL_WINDOW,
        ];

        echo json_encode($data);
        exit;
    }

//    public static function delete(UnitActive $model)
//    {
//        $data = [
//            'result' => tString::RESPONSE_RESULT_ERROR,
//            'message' => 'Error delete!',
//        ];
//
//        if(!$model->delete())
//        {
//            $data['message'] = $model->getDeleteErrors();
//        }
//        else
//        {
//            $data['result'] = \tString::RESPONSE_RESULT_SUCCESS;
//            $data['message'] = \tString::RESPONSE_OK;
//            $data['modelType'] = mb_strtolower($model->getModelClassName());
//            $data['modelId'] = $model->id;
//            $data['action'] = \DB::TYPE_DELETE;
//        }
//
//        return json_encode($data);
//    }
}