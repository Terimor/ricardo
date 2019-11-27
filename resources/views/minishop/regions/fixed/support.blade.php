@if (isset($freshchat_token))

  <div
    ref="support_toolbar"
    class="support-toolbar fixed-top d-flex align-items-center justify-content-center text-center text-white invisible">

    <div class="inside px-4 py-3">
      {!! t('minishop.support_toolbar.content') !!}
    </div>

  </div>

@endif
