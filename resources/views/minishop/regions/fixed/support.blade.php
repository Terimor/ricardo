@if (isset($freshchat_token))

  <div
    ref="support_toolbar"
    class="support-toolbar px-4 py-3 fixed-top d-flex align-items-center justify-content-center text-center text-white invisible">

    <div class="inside">
      {!! t('minishop.support_toolbar.content') !!}
    </div>

  </div>

@endif
