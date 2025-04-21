<?php
/**
 * @file HideJournalPlugin.php
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
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FormComponent;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class HideJournalPlugin extends GenericPlugin
{
    /** @copydoc GenericPlugin::register() */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
          
            Hook::add('Schema::get::context',[$this,'addCustomField']);
            Hook::add('Context::beforeSave',[$this,'saveCustomField']);
            // Use a hook to add a field to the masthead form context settings.

            Hook::add("TemplateManager::display",[$this,"filterJournalList"]);
            
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


    public function addCustomField($hookName, $args){
        $schema = $args[0];

        if(!property_exists($schema->properties, 'visibleInList')){
            $schema->properties->visibleInList = (object)[
                "type" => "boolean",
                "default" => true,
                "label" => "plugins.generic.hideJournal.label",
                "description" => "plugins.generic.hideJournal.description"
            ];
        }
       

        return false;
    }

    public function filterJournalList($hookName, $args){
    $templateMgr = $args[0];
    $template = $args[1];

    if (strpos($template, 'frontend/pages/indexSite.tpl') !== false) {
        $presses = $templateMgr->getTemplateVars('presses');
        $presses = array_filter($presses, function ($press) {
            return $press->getData('visibleInList') !== false;
        });
        $templateMgr->assign('presses', $presses);
    }

    return false;
    }

    public function saveCustomField($hookName,$args){
        $context = $args[0];
        $params = $args[1];
        
        
        if(isset($params["visibleInList"])){
            $context->setData('visibleInList',$params['visibleInList']);
        }

        return false;
    }
 
    /**
     * Extend the masthead form to add an input field
     * in the journal/press settings
     */
    public function addtoForm(string $hookName, FormComponent $form): bool
    {

        // Only modify the masthead form
        if (!defined('FORM_MASTHEAD') || $form->id !== FORM_MASTHEAD) {
            return true;
        }

        // Don't do anything at the site-wide level
        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return true;
        }
       
        // Add a field to the form
        $form->addField(new FieldOptions('visibleInList', [
        "options"=>[[
            'value' => $context->getData('visibleInList'),
            
            ]
        ],   'groupId' => 'publishing',
        'label' => __('plugins.generic.hideJournal.label'),
        ]));

        return false;
    }
}