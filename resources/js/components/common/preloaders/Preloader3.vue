<template>
  <transition name="el-fade-in-linear" duration="1000">
    <div v-if="showPreloader" class="full-background-white">
      <div class="full-background-white__container">
        <h3>{{steps[currentStep].title}}</h3>
        <component v-if="isShowed" :is="steps[currentStep].children" :data="steps[currentStep].data"></component>
        <el-progress :text-inside="true" :stroke-width="26" :percentage="Math.round(progress)"></el-progress>
      </div>
    </div>
  </transition>
</template>

<script>
import CheckmarkSvg from '../CheckmarkSvg'
import CountryImage from '../CountryImage'

export default {
  name: 'Preloader3',
  props: {
    countryCode: String,
    steps: {
      type: Array,
      default () {
        return [
          {
            title: 'Checking 3 warehouses for available stock...',
            children: CountryImage,
            data: {
              percent: 33,
              countryCode: this.countryCode
            }
          }, {
            title: 'Limited stock available in Warehouse 2. Reserving your units...',
            children: CheckmarkSvg,
            data: {
              percent: 67
            }
          }, {
            title: 'Special 50% Discount still available - Applying 50% Discount!',
            children: CheckmarkSvg,
            data: {
              percent: 100
            }
          }
        ]
      }
    },
    showPreloader: Boolean,
  },
  data () {
    return {
      currentStep: 0,
      progress: 0,
      isShowed: true
    }
  },
  methods: {
    forceRerender () {
      this.isShowed = false
      setTimeout(() => { this.isShowed = true })
    },
    goProgress () {
      const stepsQuantity = this.steps.length

      let interval

      const doProgress = () => {
        interval = setInterval(() => {
          this.progress += 1

          if (this.progress === this.steps[this.currentStep].data.percent) {
            clearInterval(interval)

            if (this.currentStep < stepsQuantity - 1) {
              this.currentStep++
              setTimeout(doProgress, 1500)
              this.forceRerender()
            } else {
              setTimeout(() => this.$emit('finish-preload'), 1300)
            }
          }
        }, 44)
      }
      doProgress()
    }
  },
  mounted () {
    this.goProgress()
  }
}
</script>

<style lang="scss">
  .full-background-white {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-grow: 1;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #f3f3f3;

    &__container {
      width: 680px;
      background: #fff;
      border-radius: 3px;
      border: 1px solid gray;
      padding: 40px;
      max-width: 100%;
      height: 460px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      margin: auto 15px;

      #svg-box {
        svg {
          max-width: 100%;
        }
      }

      h3 {
        text-align: center;
        font-size: 24px;
        color: #333;
      }

      img {
        margin: auto;
      }
    }

    .el-progress-bar {
      &__inner {
        display: flex;
        background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
        background-size: 40px 40px;
        background-color: #337ab7;
        transition: width .08s ease;
        animation: progress-bar-stripes 2s linear infinite;

        &Text {
          margin: auto;
          font-weight: bold;
          font-size: 16px;
        }
      }
    }
  }
</style>
