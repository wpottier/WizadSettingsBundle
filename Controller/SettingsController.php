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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Wizad\SettingsBundle\DependencyInjection\ContainerInjectionManager;
use Wizad\SettingsBundle\Form\ImportType;
use Wizad\SettingsBundle\Form\SettingsType;
use Wizad\SettingsBundle\Model\Import;
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

    public function exportAction()
    {
        /** @var Settings $settings */
        $settings = $this->get('wizad_settings.model.settings');

        $filename = $this->getRequest()->attributes->get('filename', 'settings_');
        $response = new Response(Yaml::dump($settings->getDataAsArray(), 2, 4, true), 200, array(
            'Content-type' => 'text/yaml',
            'Content-Disposition' => sprintf('attachment; filename="%s%s.yml"', $filename, date('YmdHis'))
        ));
        return $response;
    }

    public function importAction()
    {
        $import = new Import();
        $form = $this->createForm(new ImportType(), $import, array(
            'action' => $this->generateUrl('wizad_settings_import')
        ));

        $form->handleRequest($this->getRequest());

        if($form->isValid()) {

            $data = Yaml::parse($import->getFileContent());

            if($data) {
                /** @var Settings $settings */
                $settings = $this->get('wizad_settings.model.settings');

                foreach($data as $key => $value) {
                    if($settings->keyExistInSchema($key)) {
                        $settings->{'setting_'.$settings->formName($key)} = $value;
                    }
                }

                $settings->save();

                // Force container regeneration
                /** @var Kernel $kernel */
                $kernel = $this->get('kernel');
                /** @var ContainerInjectionManager $injectionManager */
                $injectionManager = $this->get('wizad_settings.dependency_injection.container_injection_manager');
                $injectionManager->rebuild($kernel);

                $this->get('session')->getFlashbag()->add('success', 'Settings was successfully loaded from file !');

                return $this->redirect($this->generateUrl('wizad_settings_edit'));
            }
            else {
                $this->get('session')->getFlashbag()->add('error', 'Unable to understand imported file');
            }
        }

        $template = $this->getRequest()->attributes->get('template', 'WizadSettingsBundle:Settings:import.html.twig');
        return $this->render($template, array(
            'form' => $form->createView(),
            'import' => $import
        ));
    }

}