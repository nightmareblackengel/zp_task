<?php
namespace frontend\widgets;

use yii\base\Widget;

class LeftNavBar extends Widget
{
    public function run()
    {
        return $this->render('@frontend/views/widgets/left-nav-bar', [
            'chatList' => $this->getChatData(),
        ]);
    }

    protected function getChatData(): array
    {
        // todo: test data;
        return [
            [
                'label' => 'What is Lorem Ipsum?',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
                'isChannel' => 1,
            ],
            [
                'label' => 'Why do we use it?',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
                'isActive' => true,
            ],
            [
                'label' => 'Where does it come from?',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
                'isChannel' => 1,
            ],
            [
                'label' => 'Where can I get some?',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
            ],
            [
                'label' => 'The standard Lorem Ipsum passage, used since the 1500s',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
            ],
            [
                'label' => '1914 translation by H. Rackham',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
            ],
            [
                'label' => 'Section 1.10.33 of "de Finibus Bonorum et Malorum", written by Cicero in 45 BC',
                'unreadCount' => rand(0, 100),
                'ajax-link' => '#',
                'isChannel' => 1,
            ],
        ];
    }
}
