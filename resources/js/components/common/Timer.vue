<template>
  <div
    class="timer-component"
    id="timer-component"
    :class="{ 'is-mobile': isMobile || displayGreenTimer }"
  >
    <div v-if="isMobile || displayGreenTimer" class="mobile">
      <template v-if="time === '00:00'">
        <span v-html="textTimeOver"></span>
      </template>
      <template v-else>
        <span v-html="textValidFor"></span> <span>{{time}}</span>
      </template>
    </div>
    <div v-else class="desktop">
      <span v-html="textValidFor"></span>
      <div class="d-flex" dir="ltr">
        <div class="timer-component__minutes">
          <div><div class="line"></div>{{time[0]}}</div>
          <div><div class="line"></div>{{time[1]}}</div>
        </div>
        <div class="timer-component__dots">
          <span></span>
          <span></span>
        </div>
        <div class="timer-component__seconds">
          <div><div class="line"></div>{{time[3]}}</div>
          <div><div class="line"></div>{{time[4]}}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { t } from '../../utils/i18n';
import isMobile from '../../mixins/isMobile'
import queryToComponent from '../../mixins/queryToComponent';

let interval = null;
let startMinutes = 13;
let startSeconds = Math.floor(Math.random() * 59) + 1;


export default {
  name: 'Timer',
  mixins: [
    isMobile,
    queryToComponent
  ],

  finishTime: new Date(new Date().getTime() + startMinutes * 60 * 1000 + startSeconds * 1000),

  data () {
    return {
      time: ('0' + startMinutes).slice(-2) + ':' + ('0' + startSeconds).slice(-2),
      displayGreenTimer: false,
    }
  },

  computed: {

    textValidFor: () => t('checkout.timer.valid_for'),
    textTimeOver: () => t('checkout.timer.time_over'),

  },

  methods: {
    changeTimeByDifference () {
      let diff = this.$options.finishTime.getTime() - new Date().getTime();
      diff = Math.floor(diff / 1000);

      const minutes = Math.floor(diff / 60) % 60;
      const seconds = diff % 60;

      this.time = [minutes, seconds]
        .map(numbers => ('0' + numbers).slice(-2))
        .join(':');

      if (this.time === '00:00') {
        clearInterval(interval)
      }
    },

    setHeader() {
      if (this.displayGreenTimer || this.isMobile) {
        document.querySelector('#header').style.marginTop = '51px';
      } else {
        document.querySelector('#header').style.marginTop = '';
      }
    },

    isVmpPage() {
      if (this.queryParams.tpl && this.queryParams.tpl.indexOf('vmp') >= 0) {
        this.displayGreenTimer = true
      }
    },
  },

  mounted () {
    interval = setInterval(this.changeTimeByDifference, 1000)

    this.isVmpPage();
    this.setHeader();
  },

  watch: {

    isMobile() {
      this.setHeader();
    },

  },
}
</script>

<style lang="scss">
@import "../../../sass/variables";

.timer-component {
  flex-grow: 1;
  position: absolute;
  width: 100%;
  top: 0;
  right: 0;
  left: 0;

  &.is-mobile {
    position: fixed;
    z-index: 1;
  }

  .mobile {
    padding: 15px 0;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    text-align: center;
    background-color: #22ab9f;

    & > span {
      margin-left: 5px;
    }
  }

  @media screen and ($s-up) {
    position: relative;
    order: 1;

    & > .desktop {
      display: flex;
      justify-content: flex-end;
      height: 100%;
      align-items: center;
      font-family: "Open Sans", sans-serif;

      & > * {
        padding: 7px 0;
      }

      & > span {
        font-weight: 700;
        font-size: 20px;
        text-align: center;
        width: 50%;
      }

      .timer-component__minutes, .timer-component__seconds {
        display: flex;

        & > div {
          padding: 0px 2px;
          font-weight: 700;
          color: #ccc;
          text-shadow: 0 1px 2px #000;
          text-align: center;
          background-color: #333;
          border-radius: 6px;
          font-size: 54px;
          margin-right: 5px;
          position: relative;

          .line {
            position: absolute;
            width: 100%;
            height: 1px;
            background-color: #000;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
          }
        }
      }

      .timer-component__dots {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin-right: 5px;
        padding: 18px 0;
        height: auto;

        span {
          display: block;
          background: #323434;
          width: 7px;
          height: 7px;
          border-radius: 50%;
          box-shadow: 0 0 5px rgba(0,0,0,0.5);
        }
      }
    }
  }
}
</style>
