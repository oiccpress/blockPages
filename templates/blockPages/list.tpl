{if $item['data']['style'] == 'unordered'}
    {include "blocks:blockPages/ulsub.tpl" items=$item['data']['items']}
{else}
    {include "blocks:blockPages/olsub.tpl" items=$item['data']['items']}
{/if}

