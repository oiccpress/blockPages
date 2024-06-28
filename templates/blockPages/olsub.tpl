{if !empty($items)}
    <ol>
        {foreach item=$item from=$items}
            <li>{$item['content']}{include "blocks:blockPages/olsub.tpl" items=$item['items']}</li>
        {/foreach}
    </ol>
{/if}