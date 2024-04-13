<section class="blockPage-image {if $item['data']['withBorder']}with-border{/if}
    {if $item['data']['withBackground']}bg-light{/if} 
    {if $item['data']['stretched']}stretched{/if} ">
    <figure>
        <img class="img-fluid" src="{$item['data']['file']['url']}" />
        <figcaption>
            {$item['data']['caption']}
        </figcaption>
    </figure>
</section>