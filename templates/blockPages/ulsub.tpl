{if !empty($items)}
    <ul>
        {foreach item=$item from=$items}
            <li>{$item['content']}{include "blocks:blockPages/ulsub.tpl" items=$item['items']}</li>
        {/foreach}
    </ul>
{/if}