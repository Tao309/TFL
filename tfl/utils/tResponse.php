<?php

namespace tfl\utils;

use tfl\builders\DbBuilder;
use tfl\units\Unit;

class tResponse
{
	const RESPONSE_OK = 'Ok';
	const RESPONSE_RESULT_SUCCESS = 1;
	const RESPONSE_RESULT_ERROR = 0;

	public static function resultSuccess(string $action, $echo = false, $json = true, Unit $model = null)
	{
		$data = [
			'result' => self::RESPONSE_RESULT_SUCCESS,
			'message' => self::RESPONSE_OK,
			'action' => $action,
		];

		if ($model && $addResponse = $model->getResponse($data['action'])) {
			$data['model'] = $addResponse;
		}

		if ($action == DbBuilder::TYPE_INSERT) {
			$data['event'] = 'clickButton';
		}

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

	public static function resultError(string $message, $echo = false, $json = true, Unit $model = null)
    {
        $data = [
	        'result' => tResponse::RESPONSE_RESULT_ERROR,
            'message' => $message,
            'action' => DbBuilder::TYPE_ERROR,
        ];

        if ($model) {
            if ($addResponse = $model->getResponse($data['action'])) {
                $data['model'] = $addResponse;
            }

            $data['requiredFields'] = $model->getSaveErrorsElements();
        }

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

    public static function modelNotFound($echo = false)
    {
        return self::resultError('Model not found!', $echo);
    }

	public static function modalWindow(string $title, string $content, array $classNames = [])
    {
        $data = [
            'headerTitle' => $title,
            'content' => $content,
            'className' => implode(' ', $classNames),

	        'result' => tResponse::RESPONSE_RESULT_SUCCESS,
            'action' => DbBuilder::TYPE_SHOW_MODAL_WINDOW,
        ];

        echo json_encode($data);
        exit;
    }
}