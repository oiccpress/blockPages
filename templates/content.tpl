{**
 * templates/content.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Display Static Page content
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$title}

<div class="blockPage-container">
<div class="page container py-5 py-lg-6">
	<h2>{$title|escape}</h2>
	<div class="blockPage-content">
		{foreach from=$content['blocks'] item=item}
			{capture assign=file}blocks:blockPages/{$item['type']}.tpl{/capture}
			{include file=$file}
		{/foreach}
	</div>
</div>
</div>

{include file="frontend/components/footer.tpl"}
