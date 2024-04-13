{**
 * templates/editblockPageForm.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Form for editing a static page
 *}
<script src="{$pluginJavaScriptURL}/main.js"></script>
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#blockPageForm').pkpHandler(
			'$.pkp.controllers.form.blockPages.BlockPageFormHandler',
			{ldelim}
				previewUrl: {url|json_encode router=\PKP\core\PKPApplication::ROUTE_PAGE page="pages" op="preview"},
				uploadUrl: {$uploadUrl|json_encode},
				blockConfigs: {$blockConfigs|json_encode}
			{rdelim}
		);
	{rdelim});
</script>

{capture assign=actionUrl}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.blockPages.controllers.grid.BlockPageGridHandler" op="updateBlockPage" existingPageName=$blockName escape=false}{/capture}
<form class="pkp_form" id="blockPageForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $blockPageId}
		<input type="hidden" name="blockPageId" value="{$blockPageId|escape}" />
	{/if}
	{fbvFormArea id="blockPagesFormArea" class="border"}
		{fbvFormSection}
			{fbvElement type="text" label="plugins.generic.blockPages.path" id="path" value=$path maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" label="plugins.generic.blockPages.pageTitle" id="title" value=$title maxlength="255" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			{capture assign="exampleUrl"}{url|replace:"REPLACEME":"%PATH%" router=\PKP\core\PKPApplication::ROUTE_PAGE context=$currentContext->getPath() page="REPLACEME"}{/capture}
			{translate key="plugins.generic.blockPages.viewInstructions" pagesPath=$exampleUrl}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.blockPages.content" for="content"}
			<div id="editorjs"></div>
			<input type="hidden" name="content" id="content" value="{$content|escape}" />
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormSection class="formButtons"}
		{assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
		{fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
	{/fbvFormSection}
</form>
