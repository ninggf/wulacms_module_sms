<section class="vbox wulaui layui-hide" id="tpl-list">
    <section>
        <div class="table-responsive">
            <table data-table>
                <thead>
                <tr>
                    <th width="120">模板编号</th>
                    <th width="120">模板名称</th>
                    <th>默认内容</th>
                    {if $hasVendorTpl}
                        <th>第三方模板</th>
                    {else}
                        <th>自定义内容</th>
                    {/if}
                    <th width="100">发送间隔</th>
                    <th width="60"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $tpls as $tpl}
                    <tr>
                        <td>{$tpl.tpl}</td>
                        <td>{$tpl.name}</td>
                        <td>{$tpl.template}</td>
                        <td>
                            <input style="width: 100%" class="form-control" type="text" name="content"
                                   value="{$tpl.content|escape}">
                        </td>
                        <td>
                            <input style="width: 100%" class="form-control" type="text" name="interval"
                                   value="{$tpl.interval|escape}">
                        </td>
                        <td>
                            <a href="#" class="btn btn-primary save-tpl" rel="{$tpl.tpl}">
                                <i class="fa fa-save"></i>
                            </a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </section>
</section>
<script>
	layui.use(['jquery', 'wulaui'], ($, $$) => {
		var tplId = '{$id}';
		$('#tpl-list').removeClass('layui-hide').on('click', '.save-tpl', function () {

			var tpl      = $(this).attr('rel'),
				p        = $(this).parent().parent(),
				content  = p.find('input[name=content]').val(),
				interval = p.find('input[name=interval]').val();

			if (!/^[1-9]\d*$/.test(interval)) {
				$$.toast.error('发送间隔只能是数字');
			} else {
				$.post($$.app('sms/tpl/save-tpl'), {
					id      : tplId,
					tpl     : tpl,
					content : content,
					interval: interval
				}, function (data) {
					if (data.message) {
						$$.toast.success(data.message);
					}
				}, 'json');
			}
			return false;
		});
	});
</script>