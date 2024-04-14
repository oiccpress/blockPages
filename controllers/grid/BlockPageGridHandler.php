<?php

/**
 * @file controllers/grid/StaticPageGridHandler.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StaticPageGridHandler
 *
 * @ingroup controllers_grid_staticPages
 *
 * @brief Handle static pages grid requests.
 */

namespace APP\plugins\generic\blockPages\controllers\grid;

use APP\core\Application;
use APP\plugins\generic\blockPages\classes\BlockPagesDAO;
use APP\plugins\generic\blockPages\controllers\grid\form\BlockPageForm;
use APP\plugins\generic\blockPages\BlockPagesPlugin;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\core\JSONMessage;
use PKP\core\PKPRequest;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\form\Form;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\Role;

class BlockPageGridHandler extends GridHandler
{
    /** @var BlockPageGridHandler The static pages plugin */
    public $plugin;

    /**
     * Constructor
     */
    public function __construct(BlockPagesPlugin $plugin)
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['index', 'fetchGrid', 'fetchRow', 'addBlockPage', 'editBlockPage', 'updateBlockPage', 'delete']
        );
        $this->plugin = $plugin;
    }


    //
    // Overridden template methods
    //
    /**
     * @copydoc PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        // $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);
        $context = $request->getContext();

        // Set the grid details.
        $this->setTitle('plugins.generic.blockPages.blockPages');
        $this->setEmptyRowText('plugins.generic.blockPages.noneCreated');

        // Get the pages and add the data to the grid
        /** @var BlockPagesDAO */
        $staticPagesDao = DAORegistry::getDAO('BlockPagesDAO');
        $this->setGridDataElements($staticPagesDao->getByContextId(
            $context?->getId() ?? null
        ));

        // Add grid-level actions
        $router = $request->getRouter();
        $this->addAction(
            new LinkAction(
                'addBlockPage',
                new AjaxModal(
                    $router->url($request, null, null, 'addBlockPage'),
                    __('plugins.generic.blockPages.addBlockPage'),
                    'modal_add_item'
                ),
                __('plugins.generic.blockPages.addBlockPage'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new BlockPageGridCellProvider();
        $this->addColumn(new GridColumn(
            'title',
            'plugins.generic.blockPages.pageTitle',
            null,
            'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
            $cellProvider
        ));
        $this->addColumn(new GridColumn(
            'path',
            'plugins.generic.blockPages.path',
            null,
            'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
            $cellProvider
        ));
    }

    //
    // Overridden methods from GridHandler
    //
    /**
     * @copydoc GridHandler::getRowInstance()
     */
    public function getRowInstance()
    {
        return new BlockPageGridRow();
    }

    //
    // Public Grid Actions
    //
    /**
     * Display the grid's containing page.
     *
     * @param array $args
     * @param PKPRequest $request
     * @return JSONMessage
     */
    public function index($args, $request)
    {
        $form = new Form($this->plugin->getTemplateResource('blockPages.tpl'));
        return new JSONMessage(true, $form->fetch($request));
    }

    /**
     * An action to add a new custom static page
     *
     * @param array $args Arguments to the request
     * @param PKPRequest $request Request object
     */
    public function addBlockPage($args, $request)
    {
        // Calling editStaticPage with an empty ID will add
        // a new static page.
        return $this->editBlockPage($args, $request);
    }

    /**
     * An action to edit a static page
     *
     * @param array $args Arguments to the request
     * @param PKPRequest $request Request object
     *
     * @return JSONMessage Serialized JSON object
     */
    public function editBlockPage($args, $request)
    {
        $staticPageId = $request->getUserVar('blockPageId');
        $context = $request->getContext();
        $this->setupTemplate($request);

        // Create and present the edit form
        $staticPageForm = new BlockPageForm($this->plugin, $context?->getId() ?? null, $staticPageId);
        $staticPageForm->initData();
        return new JSONMessage(true, $staticPageForm->fetch($request));
    }

    /**
     * Update a custom block
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage Serialized JSON object
     */
    public function updateBlockPage($args, $request)
    {
        $staticPageId = $request->getUserVar('blockPageId');
        $context = $request->getContext();
        $this->setupTemplate($request);

        // Create and populate the form
        $staticPageForm = new BlockPageForm($this->plugin, $context?->getId() ?? null, $staticPageId);
        $staticPageForm->readInputData();

        // Check the results
        if ($staticPageForm->validate()) {
            // Save the results
            $staticPageForm->execute();
            return DAO::getDataChangedEvent();
        }
        // Present any errors
        return new JSONMessage(true, $staticPageForm->fetch($request));
    }

    /**
     * Delete a static page
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage Serialized JSON object
     */
    public function delete($args, $request)
    {
        if (!$request->checkCSRF()) return new JSONMessage(false);

        $staticPageId = $request->getUserVar('blockPageId');
        $context = $request->getContext();

        // Delete the static page
        /** @var StaticPagesDAO */
        $staticPagesDao = DAORegistry::getDAO('BlockPagesDAO');
        $staticPage = $staticPagesDao->getById($staticPageId, $context?->getId() ?? null);
        $staticPagesDao->deleteObject($staticPage);

        return DAO::getDataChangedEvent();
    }
}
