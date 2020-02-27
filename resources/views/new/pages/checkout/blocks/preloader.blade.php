<div
  v-if="preloader_visible"
  class="preloader">

  <div class="inside">

    @for ($i = 1; $i <= 3; $i++)
      <h3
        v-if="preloader_step === {{ $i }}"
        class="preloader-title">
        {{ t('checkout.preloader.step' . $i) }}
      </h3>
    @endfor

    <img
      v-if="preloader_step === 1"
      class="preloader-country-image lazy"
      data-src="{{ $cdn_url }}/assets/images/country-flags/{{ $countryCode }}.png">

    @for ($i = 2; $i <= 3; $i++)
      <div
        v-if="preloader_step === {{ $i }}"
        key="preloader-svg-box-{{ $i }}"
        class="preloader-svg-box">
        <svg>
          <circle cx="75" cy="75" r="50" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>
        </svg>
        <svg class="preloader-checkmark">
          <g transform="matrix(0.79961,8.65821e-32,8.39584e-32,0.79961,-489.57,-205.679)">
            <path class="preloader-checkmark-check" fill="none" d="M616.306,283.025L634.087,300.805L673.361,261.53"></path>
          </g>
        </svg>
      </div>
    @endfor

    <div class="preloader-progress">
      <div
        v-if="preloader_progress > 0"
        class="preloader-progress-value"
        :style="{ width: preloader_progress + '%' }">
        @{{ preloader_progress }}%
      </div>
    </div>

  </div>

</div>
