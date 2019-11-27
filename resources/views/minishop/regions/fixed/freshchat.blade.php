@if (isset($freshchat_token))

  <img
    ref="freshchat_image"
    class="freshchat-image position-fixed d-none"
    src="{{ $cdn_url }}/assets/images/live_chat-full.png"
    @click.stop="freshchat_click" />

@endif
