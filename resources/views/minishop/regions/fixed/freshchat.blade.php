@if (!empty($freshchat_token) && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <img
    ref="freshchat_image"
    class="freshchat-image position-fixed d-none"
    src="{{ $cdn_url }}/assets/images/live_chat-full.png"
    @click="freshchat_click" />

@endif
