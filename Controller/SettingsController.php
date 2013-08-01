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
use Symfony\Component\HttpKernel\Kernel;
use Wizad\SettingsBundle\DependencyInjection\ContainerInjectionManager;
use Wizad\SettingsBundle\Form\SettingsType;
use Wizad\SettingsBundle\Model\Settings;

class SettingsController extends Controller
{
    public function editAction()
    {
        /** @var Settings $settings */
        $settings = $this->get('wizad_settings.model.settings');
        $form = $this->createForm(new SettingsType(), $settings, array(
            'schema' => $settings->getSchema(),
            'action' => $this->generateUrl('wizad_settings_edit')
        ));

        $form->handleRequest($this->getRequest());

        if($form->isValid()) {
            // Save data in storage
            $settings->save();

            // Force container regeneration
            /** @var Kernel $kernel */
            $kernel = $this->get('kernel');
            /** @var ContainerInjectionManager $injectionManager */
            $injectionManager = $this->get('wizad_settings.dependency_injection.container_injection_manager');
            $injectionManager->rebuild($kernel);

            $this->get('session')->getFlashbag()->add('success', 'New settings were saved and applied.');
            return $this->redirect($this->getRequest()->getUri());
        }

        $template = $this->getRequest()->attributes->get('template', 'WizadSettingsBundle:Settings:edit.html.twig');
        return $this->render($template, array(
            'form' => $form->createView(),
            'settings' => $settings
        ));
    }

}