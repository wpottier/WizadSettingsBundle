<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wizad\SettingsBundle\Form\SettingsType;
use Wizad\SettingsBundle\Model\Settings;

class SettingsController extends Controller
{
    public function editAction()
    {
        /** @var Settings $object */
        $object = $this->get('wizad_settings.model.settings');
        $form = $this->createForm(new SettingsType(), $object, array('schema' => $object->getSchema()));

        $template = $this->getRequest()->attributes->get('template', 'WizadSettingsBundle:Settings:edit.html.twig');

        return $this->render($template, array(
            'form' => $form->createView()
        ));
    }

}