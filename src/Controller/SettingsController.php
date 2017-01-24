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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Wizad\SettingsBundle\Form\ImportType;
use Wizad\SettingsBundle\Form\SettingsType;
use Wizad\SettingsBundle\Model\Import;
use Wizad\SettingsBundle\Model\Settings;

class SettingsController extends Controller
{
    public function editAction(Request $request)
    {
        /** @var Settings $settings */
        $settings = $this->get('wizad_settings.model.settings');
        $form = $this->createForm('\Wizad\SettingsBundle\Form\SettingsType', $settings, [
            'action' => $this->generateUrl('wizad_settings_edit')
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            // Save data in storage
            $settings->save();

            $this->addFlash('success', 'New settings were saved and applied.');
            return $this->redirect($request->getUri());
        }

        return $this->render($this->guessTemplate($request, 'WizadSettingsBundle:Settings:edit.html.twig'), [
            'form' => $form->createView(),
            'settings' => $settings
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function exportAction(Request $request)
    {
        /** @var Settings $settings */
        $settings = $this->get('wizad_settings.model.settings');
        $filename = $request->attributes->get('filename', 'settings_');

        return new Response(Yaml::dump($settings->toArray(), 2, 4, true), 200, [
            'Content-type' => 'text/yaml',
            'Content-Disposition' => sprintf('attachment; filename="%s%s.yml"', $filename, date('YmdHis'))
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function importAction(Request $request)
    {
        $import = new Import();
        $form = $this->createForm(new ImportType(), $import, [
            'action' => $this->generateUrl('wizad_settings_import')
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = Yaml::parse($import->getFileContent());

            if ($data) {
                /** @var Settings $settings */
                $settings = $this->get('wizad_settings.model.settings');
                $settings->updateFromArray($data)->save();

                $this->addFlash('success', 'Settings was successfully loaded from file !');

                return $this->redirect($this->generateUrl('wizad_settings_edit'));
            }


            $this->addFlash('error', 'Unable to understand imported file');
        }

        return $this->render($this->guessTemplate($request, 'WizadSettingsBundle:Settings:import.html.twig'), [
            'form' => $form->createView(),
            'import' => $import
        ]);
    }

    /**
     * @param Request $request
     * @param $default
     * @return mixed
     */
    protected function guessTemplate(Request $request, $default)
    {
        return $request->attributes->get('_template', $default);
    }
}