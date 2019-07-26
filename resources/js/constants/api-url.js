const apiUrlByEnvironment = {
    development: {
        ebanxpay: {
            url: 'https://sandbox.ebanxpay.com',
            integration_key: 'test_ik_DxxceuiTqYZAB4vB8UIhQQ'
        }
    },
    production: {
        ebanxpay: {
            url: 'https://api.ebanxpay.com',
            integration_key: 'test_ik_DxxceuiTqYZAB4vB8UIhQQ'
        }
    }
}

const apiUrlList = apiUrlByEnvironment[process.env.NODE_ENV || 'development']

export default apiUrlList
