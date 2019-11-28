<div
  ref="home_welcome"
  class="home-welcome row">

  <div class="content col-12 col-lg-6">
    {!! t('minishop.home.welcome.content', ['websitename' => $website_name]) !!}
  </div>

  <div class="image col-12 col-lg-6">
    <img
      src="{{ $cdn_url }}/assets/images/minishop/CS_1.jpg"
      class="d-block mw-100"
      alt="" />
  </div>

</div>
