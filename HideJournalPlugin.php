<?php
/**
 * @file HideJuornalPlugin.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HideJournalPlugin
 * @brief Plugin class for the HideJournal plugin.
 */

namespace APP\plugins\generic\hideJournal;

use APP\core\Application;
use PKP\components\forms\FieldText;
use PKP\components\forms\FormComponent;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use stdClass;

class HideJournalPlugin extends GenericPlugin
{
    /** @copydoc GenericPlugin::register() */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
          
            Hook::add('Schema::get::journal',[$this,'changeEnabled']);

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
        return __('plugins.generic.hideJournal.displayName');
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDescription(): string
    {
        return __('plugins.generic.hideJournal.description');
    }


    public function changue($hookName, $args){
        $schema = $args[0];
        if(issset($schema->properties->enabled)){
            $schema->properties->enabled = false;
        }


        return true;
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
        $form->addField(new FieldText('hideJournal', [
            'label' => 'Hide Journal',
            'groupId' => 'journals',
            'value' => $context->getData('hideJournal'),
        ]));

        return false;
    }

    public function isSitePlugin(){
        return true;
    }
}