<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Wizad\SettingsBundle\Model\SettingElementInterface;
use Wizad\SettingsBundle\Model\SettingsInterface;

class DynamicSettingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData'
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var SettingsInterface|SettingElementInterface[] $settings */
        $settings = $event->getData();

        if (!$settings instanceof SettingsInterface) {
            return;
        }

        foreach ($settings as $element) {
            $formOptions = array_merge([
                'label' => $element->getId(),
                'required' => false,
                'attr' => ['placeholder' => $element->getDefaultValue()]
            ], $element->getFormOptions());

            $form->add($element->getFormName(), $element->getFormType(), $formOptions);
        }
    }
}