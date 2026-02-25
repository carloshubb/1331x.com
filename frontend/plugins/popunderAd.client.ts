export default defineNuxtPlugin(() => {

    // stop it temporarily on server side rendering
    return;

    if (process.server) return

    // Prevent duplicate load
    if (document.getElementById('primary-ad-script')) return
    //Adsterra popunder ad script
    const primaryScript = document.createElement('script')
    primaryScript.id = 'primary-ad-script'
    primaryScript.src =
        'https://venisonglum.com/0d/a8/4e/0da84ef399a1f7c2f0bb6239bc4becb2.js'
    primaryScript.async = true

    // If primary fails → load fallback
    primaryScript.onerror = () => {
        if (document.getElementById('fallback-ad-script')) return
        // Ad-maven popunder ad script
        const fallbackScript = document.createElement('script')
        fallbackScript.id = 'fallback-ad-script'
        fallbackScript.src =
            '//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1239564'
        fallbackScript.async = true
        fallbackScript.setAttribute('data-cfasync', 'false')

        document.body.appendChild(fallbackScript)
    }

    document.body.appendChild(primaryScript)
})
