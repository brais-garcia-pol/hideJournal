<?php
/**
 * @file HideIssuePlugin.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HideIssuePlugin
 * @brief Plugin class for the HideIssue plugin.
 */

namespace APP\plugins\generic\hideIssue;

use APP\core\Application;
use PKP\components\forms\FieldText;
use PKP\components\forms\FormComponent;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use stdClass;

class HideIssuePlugin extends GenericPlugin
{
    /** @copydoc GenericPlugin::register() */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
          
            Hook::add('Schema::get::issues',[$this,'addHideIssueField']);

            // Use a hook to add a field to the masthead form context settings.
            Hook::add('Form::config::before', [$this, 'addToForm']);
        }

        return $success;
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.hideIssue.displayName');
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDescription(): string
    {
        return __('plugins.generic.HideIssue.description');
    }


    public function addHideIssueField($hookName, $args){
        $schema = $args[0];
        $schema->properties->hideIssue = (object) [
            "type" => "bool",
            "default" => true,
        ];

        return false;
    }



    public function addToForm($hookName, $form){
        
        // Only modify the masthead form
        if (!defined('FORM_MASTHEAD') || $form->id !== FORM_MASTHEAD) {
            return;
        }

          // Don't do anything at the site-wide level
        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return;
        }

        
        // Add a field to the form
        $form->addField(new FieldText('hideIssue', [
            'label' => 'Hide Issue',
            'groupId' => 'issues',
            'value' => $context->getData('hideIssue'),
        ]));

        return false;
    }

    }

    /**
     * Add a settings action to the plugin's entry in the plugins list.
     *
     * @param Request $request
     * @param array $actionArgs
     */
    public function getActions($request, $actionArgs): array
    {
        $actions = new Actions($this);
        return $actions->execute($request, $actionArgs, parent::getActions($request, $actionArgs));
    }

    /**
     * Load a form when the `settings` button is clicked and
     * save the form when the user saves it.
     *
     * @param array $args
     * @param Request $request
     */
    public function manage($args, $request): JSONMessage
    {
        $manage = new Manage($this);
        return $manage->execute($args, $request);
    }
}