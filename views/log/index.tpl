<section class="hbox stretch wulaui" id="oauth-page">
    <section>
        <div class="vbox">
            <header class="bg-light header b-b clearfix">
                <div class="row m-t-sm">
                    <div class="col-xs-12 m-b-xs text-right">
                        <form id="search-form" class="form-inline" data-table-form="#table">
                            <input type="hidden" name="type" value="{$id}" id="type"/>
                            <div data-datepicker class="input-group date" data-end="#time1">
                                <input id="time" type="text" style="width: 100px;" class="input-sm form-control"
                                       name="time" placeholder="开始时间"/>
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <div data-datepicker class="input-group date" data-start="#time">
                                <input id="time1" type="text" style="width: 100px;" class="input-sm form-control"
                                       name="time1" placeholder="结束时间"/>
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="input-sm form-control" placeholder="{'Search'|t}"
                                       id="sqkey"/>
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <section class="w-f">
                <div class="table-responsive">
                    <table id="table" data-auto data-table="{'sms/log/data'|app}" data-sort="create_time,d"
                           style="min-width: 800px">
                        <thead>
                        <tr>
                            <th width="150" data-sort="create_time,d">发送时间</th>
                            <th width="120" data-sort="tid,a">模板</th>
                            <th width="120">手机号码</th>
                            <th width="120">通道</th>
                            <th>内容</th>
                            <th width="100" data-sort="status,d">状态</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <footer class="footer b-t">
                <div data-table-pager="#table" data-limit="50"></div>
            </footer>
        </div>
    </section>
    <aside class="aside aside-sm b-l hidden-xs">
        <div class="vbox">
            <header class="bg-light dk header b-b">
                <p>短信通道</p>
            </header>
            <section class="hidden-xs scrollable m-t-xs">
                <ul class="nav nav-pills nav-stacked no-radius" id="app-list">
                    <li {if !$id}class="active"{/if}>
                        <a href="javascript:;"> 全部 </a>
                    </li>
                    {foreach $groups as $gp=>$name}
                        <li {if $id==$gp}class="active"{/if}>
                            <a href="javascript:;" rel="{$gp}" title="{$name}"> {$name}</a>
                        </li>
                    {/foreach}
                </ul>
            </section>
        </div>
    </aside>
</section>
<script>
	layui.use(['jquery', 'bootstrap', 'wulaui'], function ($, b, wui) {
		var group = $('#app-list');
		group.find('a').click(function () {
			var me = $(this), mp = me.closest('li');
			if (mp.hasClass('active')) {
				return;
			}
			group.find('li').not(mp).removeClass('active');
			mp.addClass('active');
			$('#type').val(me.attr('rel'));
			$('#search-form').submit();
			return false;
		});
	});
</script>