<template>
  <div id="svg-box" class="svg-box">
    <svg class="circular green-stroke">
      <circle class="path" cx="75" cy="75" r="50" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>
    </svg>
    <svg class="checkmark green-stroke">
      <g transform="matrix(0.79961,8.65821e-32,8.39584e-32,0.79961,-489.57,-205.679)">
        <path class="checkmark__check" fill="none" d="M616.306,283.025L634.087,300.805L673.361,261.53"></path>
      </g>
    </svg>
  </div>
</template>

<script>
export default {
  name: 'CheckmarkSvg'
}
</script>

<style lang="scss">

  @keyframes draw-check {
    0% {
      stroke-dasharray: 49, 80;
      stroke-dashoffset: 48;
      opacity: 0
    }
    50% {
      stroke-dasharray: 49, 80;
      stroke-dashoffset: 48;
      opacity: 1
    }
    100% {
      stroke-dasharray: 130, 80;
      stroke-dashoffset: 48
    }
  }

  @keyframes draw-circle {
    0% {
      stroke-dasharray: 0, 330;
      stroke-dashoffset: 0;
      opacity: 1
    }
    80% {
      stroke-dasharray: 330, 330;
      stroke-dashoffset: 0;
      opacity: 1
    }
    100% {
      opacity: .4
    }
  }

  #svg-box {
    display: block;
    position: relative;
    width: 150px;
    margin: 0 auto;

  svg {
    stroke: #7cb342;
  }

  circle {
    stroke-dasharray: 330;
    stroke-dashoffset: 0;
    stroke-linecap: round;
    opacity: .4;
    animation: .7s draw-circle ease-out;
  }

  .checkmark {
    stroke-width: 6.25;
    stroke-linecap: round;
    position: absolute;
    top: 56px;
    left: 49px;
    width: 52px;
    height: 40px;

  &__check {
     animation: 1s draw-check ease-out;
   }
  }
  }
</style>
