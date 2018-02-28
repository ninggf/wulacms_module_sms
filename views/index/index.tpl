<div class="vbox wulaui layui-hide" id="app-list">
    <header class="bg-light header b-b clearfix">
        <div class="row m-t-sm">
            <div class="col-sm-12 m-b-xs">
                <div class="btn-group">
                    <a href="{'sms/set-status/0'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                       data-confirm="你真的要禁用这些通道吗？" data-warn="请选择要禁用的通道" class="btn btn-sm btn-warning"><i
                                class="fa fa-square-o"></i>
                        禁用</a>
                    <a href="{'sms/set-status/1'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                       data-confirm="你真的要激活这些通道吗？" data-warn="请选择要激活的通道" class="btn btn-sm btn-primary"><i
                                class="fa fa-check-square-o"></i>
                        激活</a>
                </div>
            </div>
        </div>
    </header>
    <section>
        <table id="table" data-auto data-table="{'sms/data'|app}" data-sort="status,d">
            <thead>
            <tr>
                <th width="20"><input type="checkbox" class="grp"/></th>
                <th width="100">ID</th>
                <th width="150" data-sort="name,a">名称</th>
                <th width="100" data-sort="status,d">状态</th>
                <th>说明</th>
                <th width="80"></th>
            </tr>
            </thead>
        </table>
    </section>
</div>
<script>
	layui.use(['jquery', 'layer', 'wulaui'], ($, layer, $$) => {
		$('#app-list').on('before.dialog', '.cfg-app', function (e) {
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#edit-form').submit();
				return false;
			};
		}).removeClass('layui-hide');
		$('body').on('ajax.success', '#edit-form', function () {
			layer.closeAll();
		});
	});
</script>