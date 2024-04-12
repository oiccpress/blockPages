{**
 * templates/staticPages.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Static pages plugin -- displays the StaticPagesGrid.
 *}
<tab id="blockPages" label="{translate key="plugins.generic.blockPages.blockPages"}">
	{capture assign=staticPageGridUrl}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.blockPages.controllers.grid.BlockPageGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="blockPageGridContainer" url=$staticPageGridUrl}
</tab>
