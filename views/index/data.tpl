<tbody data-total="0">
{foreach $rows as $row}
    <tr>
        <td><input type="checkbox" value="{$row.id}" class="grp"/></td>
        <td>{$row.id}</td>
        <td>
            <a href="{'sms/log'|app}?type={$row.id}" title="发送日志[{$row.id}]" data-tab="&#xe64a;">{$row.name}</a>
        </td>
        <td class="{if $row.status}active{/if}">
            <i class="fa fa-check text-success text-active"></i>
            <i class="fa fa-times text-danger text"></i>
        </td>
        <td>{$row.desc}</td>
        <td class="text-right">
            <div class="btn-group">
                {if $row.hasForm}
                    <a href="{'sms/cfg'|app}/{$row.id}" class="cfg-app btn btn-xs btn-default" data-ajax="dialog"
                       data-area="600px,300px" title="配置通道[{$row.name}]">
                        <i class="fa fa-gear"></i>
                    </a>
                {/if}
                <a href="{'sms/tpl'|app}/{$row.id}" data-tab="&#xe63c;" class="btn btn-xs btn-primary" title="短信模板:{$row.name}">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="6">无数据</td>
    </tr>
{/foreach}
</tbody>