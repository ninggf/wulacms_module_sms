<tbody data-total="{$total}">
{foreach $rows as $row}
    <tr {if $row.note}rel="{$row.id}"{/if} class="{if $row.status}success{else}danger{/if}">
        <td>{date('Y-m-d H:i:s',$row.create_time)}</td>
        <td>{$tpls[$row.tid]}</td>
        <td>{$row.phone}</td>
        <td>{$vendors[$row.vendor]}</td>
        <td>{$row.content}</td>
        <td>
            {if $row.status==1}
                成功
            {else}
                失败
            {/if}
        </td>
    </tr>
    {if $row.note}
        <tr class="hidden danger">
            <td colspan="5">
                <strong>错误:</strong>{$row.note|escape}
            </td>
        </tr>
    {/if}
    {foreachelse}
    <tr>
        <td colspan="5" class="text-center">无数据</td>
    </tr>
{/foreach}
</tbody>