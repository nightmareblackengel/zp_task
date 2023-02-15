<?php

namespace common\ext\widgets;

use common\ext\helpers\Html;
use frontend\models\helpers\MessageCommandHelper;
use yii\widgets\ActiveField;

class ChatMsgActiveField extends ActiveField
{
    public $options = ['class' => 'form-group input-group'];

    public function sendMessageText($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        if ($this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_INPUT) {
            $this->addErrorClassIfNeeded($options);
        }

        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] =
            '<div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-plus"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <span class="msgEmptyCmd">Выберите комманду</span>'
                . $this->renderCommands() .
                '</ul>
            </div>'
            . Html::activeTextInput($this->model, $this->attribute, $options);

        return $this;
    }

    protected function renderCommands()
    {
        $list = [];
        // отобразим список системных комманд в форме отправки сообщения
        foreach (MessageCommandHelper::$chatDescriptions as $key => $descr) {
            $list[] = Html::tag(
                'li',
                Html::tag(
                    'span',
                    $descr, [
                        'class' => 'nbeCmdCommandLine',
                    ],
                ),
                [
                    'class' => 'msgLinkCmd',
                    'data' => [
                        'chat-type' => in_array($key, MessageCommandHelper::$channelList) ? '1' : '0',
                        'cmd' => $key,
                    ],
                ]
            );
        }

        return implode('',  $list);
    }
}
