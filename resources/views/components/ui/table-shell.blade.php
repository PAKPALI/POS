<div {{ $attributes->class('saas-ui-datatable saas-table-shell') }}>
    @isset($toolbar)<div class="saas-ui-datatable-toolbar">{{ $toolbar }}</div>@endisset
    <div class="saas-ui-datatable-scroll"><table {{ $table->attributes->class('saas-data-table') }}>{{ $table }}</table></div>
    @isset($footer)<div class="saas-ui-datatable-footer">{{ $footer }}</div>@endisset
</div>
