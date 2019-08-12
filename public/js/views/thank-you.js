let socialType = 'facebook'

const sharingLinkList = {
    facebook: 'http://www.facebook.com/sharer/sharer.php?u=https://landingpage.thor-be.host/checkout&quote=',
    twitter: 'https://twitter.com/home?status=https://landingpage.thor-be.host/checkout%20'
}

function onClickSocialNetwork (type) {
    socialType = type
    document.querySelectorAll('#social-media-tabs li').forEach(item => item.classList.remove('active'))
    document.querySelector('#' + type).classList.add('active')
}

document.addEventListener('DOMContentLoaded', function (_) {
    document.querySelector('#share').addEventListener('click', function () {
        const quote = document.querySelector('#quote').value
        window.open(sharingLinkList[socialType] + quote, null, "width=450,height=450")
    })
})
