<table class="table table-striped my-3">
    <tbody>
        {foreach item=$row from=$item['data']['content']}
            <tr>
                {foreach item=$cell from=$row}
                    <td>{$cell}</td>
                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>