@if (isset($freshchat_token))

  <img
    ref="freshchat_image"
    :class="freshchat_class"
    class="freshchat-image position-fixed"
    src="{{ $cdn_url }}/assets/images/live_chat-full.png"
    @click.stop="freshchat_click" />

@endif
