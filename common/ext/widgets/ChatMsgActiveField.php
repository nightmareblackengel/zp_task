<?php

namespace common\ext\widgets;

use common\ext\helpers\Html;
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
                    <span class="msgEmptyCmd">Выберите комманду</span>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/date</span></li>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/me {some string}</span></li>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/showmembers</span></li>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/kick {email}</span></li>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/clearhistory</span></li>
                    <li class="msgLinkCmd"><span class="nbeCmdCommandLine">/sendwithdelay {N} {message}</span></li>
                </ul>
            </div>'
            . Html::activeTextInput($this->model, $this->attribute, $options);

        return $this;
    }
}
