<div class="toc">
    <ul>
        {assign var=toclevel value=$toc[0][0]}
        {assign var=initiallevel value=$toc[0][0]}
        {foreach from=$toc item=item}
            {while $toclevel < $item[0]}
                <ul>
                {assign var=toclevel value=$toclevel+1}
            {/while}
            {while $toclevel > $item[0]}
                </ul>
                {assign var=toclevel value=$toclevel-1}
            {/while}
            <li>
                <a href="#{$item[1]}">
                    {$item[2]}
                </a>
                
            </li>
        {/foreach}
        {while $toclevel > $initiallevel}
            </ul>
            {assign var=toclevel value=$toclevel-1}
        {/while}
    </ul>
</div>