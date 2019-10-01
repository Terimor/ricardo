<template></template>

<script>
    import wait from '../../utils/wait';

    export default {
        mounted() {
            window.closeLeaveModal = () => document.querySelector('#bio_ep_close').dispatchEvent(new CustomEvent('click'))
            window.agreeLeaveModal = () => {
                window.closeLeaveModal()
                document.querySelector('.main__deal').scrollIntoView()
            }
            wait(
                () => !!window.bioEp,
                () => bioEp.init({
                    width: 450,
                    html: `
                        <div class="leave-modal">
                            <img src="https://static-backend.saratrkr.com/image_assets/EchoBeat-logo.00" alt="" />
                            <p>Wait!! You Have Been Selected For a Special Promotional Offer</p>
                            <p class="green">Buy 2 Get 1 FREE! Just $92.99</p>
                            <button class="offer-btn" onclick="agreeLeaveModal()">Claim Your Special Offer Today</button>
                            <button class="close-btn" onclick="closeLeaveModal()">No thanks, I don't want to take this one time special offer</button>
                        </div>
                        `,
                    cookieExp: 0,
                    delay: 15
                }),
            );
        }
    }
</script>

<style lang="scss">
#bio_ep {
    padding: 3% 4% 5%;
    height: auto;

    & > div {
        display: flex;
        flex-direction: column;

        & > img {
            width: 80%;
            height: auto;
            margin: 0 auto 20px;
        }

        p {
            font-weight: 700;
            font-size: 1.1rem;
        }
    }
    #bio_ep_close {
        display: none;
    }
}

.leave-modal {
    p {
        line-height: 1.7rem;
        text-align: center;
    }

    .green {
        margin: 0.2rem 0;
        font-weight: 400;
        color: #0f9b0f;
    }

    .offer-btn {
        font-size: 1.2rem;
        background: linear-gradient(180deg, #9fdf23, #6ac800);
        border: 1px solid #a4a4a4;
        color: #fff;
        border-radius: 3px;
        padding: 7px 5px;
        font-weight: 700;
        margin: 15px 0 22px;
    }

    .close-btn {
        color: #a4a4a4;
        background: transparent;
        border: 0;
        text-decoration: underline;
        transform: none !important;
    }

    button {
        outline: none;
        cursor: pointer;
    }

    button:active {
        transform: translateY(1px)
    }
}
</style>
