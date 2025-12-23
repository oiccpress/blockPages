<?php

/**
 * @file StaticPagesHandler.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 *
 * @class StaticPagesHandler
 *
 * @brief Find static page content and display it when requested.
 */

namespace APP\plugins\generic\blockPages;

use APP\core\Application;
use APP\plugins\generic\blockPages\classes\BlockPage;
use APP\template\TemplateManager;
use Illuminate\Support\Str;
use PKP\core\PKPRequest;
use PKP\security\Role;
use PKP\template\PKPTemplateResource;
use Smarty;

class BlockPagesHandler extends \APP\handler\Handler
{
    /** @var BlockPagesPlugin The static pages plugin */
    protected $plugin;

    /** @var BlockPage The static page to view */
    protected $staticPage;

    public function __construct(BlockPagesPlugin $plugin, BlockPage $staticPage)
    {
        $this->plugin = $plugin;
        $this->staticPage = $staticPage;
    }

    /**
     * Handle index request (redirect to "view")
     *
     * @param array $args Arguments array.
     * @param PKPRequest $request Request object.
     */
    public function index($args, $request)
    {
        $request->redirect(null, null, 'view', $request->getRequestedOp());
    }

    /**
     * Handle view page request (redirect to "view")
     *
     * @param array $args Arguments array.
     * @param PKPRequest $request Request object.
     */
    public function view($args, $request)
    {
        $path = array_shift($args);
        $context = $request->getContext();

        // Ensure that if we're previewing, the current user is a manager or admin.
        $roles = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_ROLES);
        if (!$this->staticPage->getId() && count(array_intersect([Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN], $roles)) == 0) {
            fatalError('The current user is not permitted to preview.');
        }

        // Assign the template vars needed and display
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->registerPlugin( Smarty::PLUGIN_MODIFIER, 'slugify', [ Str::class, 'slug' ] );

        $content = json_decode($this->staticPage->getLocalizedContent(), true);
        $toc = [];
        foreach($content['blocks'] as $item) {
            if($item['type'] == 'header') {
                $toc[] = [
                    $item['data']['level'],
                    Str::slug( $item['data']['text'] ),
                    $item['data']['text']
                ];
            }
        }
        $templateMgr->assign('toc', $toc);

        $this->setupTemplate($request);
        $templateMgr->assign('title', $this->staticPage->getLocalizedTitle());

        $templateMgr->assign('content', $content);

        $templateMgr->registerResource('blocks', new PKPTemplateResource([
            $this->plugin->getPluginPath() . '/templates/', 'templates', 'lib/pkp/templates']));

        $templateMgr->display($this->plugin->getTemplateResource('content.tpl'));
    }
}
