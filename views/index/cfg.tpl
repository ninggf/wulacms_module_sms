<div class="container-fluid wulaui m-t-sm">
    <form id="edit-form" name="EditForm" {if $rules}data-validate="{$rules|escape}"{/if} action="{'sms/save'|app}"
          data-ajax method="post" data-loading>
        <input type="hidden" name="id" value="{$id}"/>
        {$form|render}
    </form>
</div>