export default defineNuxtPlugin(() => {

    if (process.server) return

    // Prevent duplicate load
    if (document.getElementById('adsterra-popunder-script')) return

    // Ad container HTML constant for adsterra native ads
    const AD_NATIVE_CONTAINER_HTML = '<div id="container-d8798c431a8567a69212bdf164a8ddac"></div>'

    // Reactive ref to track if primary script is available
    const isPrimaryAvailable = ref<string | null>(null)

    const primaryScriptUrl = 'https://venisonglum.com/0d/a8/4e/0da84ef399a1f7c2f0bb6239bc4becb2.js'

    // Check if primary script is available before loading
    checkScriptAvailability(primaryScriptUrl)
        .then((isAvailable) => {
            if (isAvailable) {
                // Script is available, set the container HTML and load it
                isPrimaryAvailable.value = AD_NATIVE_CONTAINER_HTML
                loadPrimaryScript()
            } else {
                // Script not available, load fallback
                console.log('Primary script not available, loading fallback')
                loadFallbackScript()
            }
        })
        .catch(() => {
            // On error, try loading primary anyway (fallback will handle if it fails)
            isPrimaryAvailable.value = AD_NATIVE_CONTAINER_HTML
            loadPrimaryScript()
        })

    function checkScriptAvailability(url: string): Promise<boolean> {
        return new Promise((resolve) => {
            const timeout = setTimeout(() => {
                resolve(false)
            }, 3000) // 3 second timeout

            fetch(url, { method: 'HEAD', cache: 'no-cache' })
                .then((response) => {
                    clearTimeout(timeout)
                    // Consider 200-299 and redirects as available
                    resolve(response.ok || response.status === 0)
                })
                .catch(() => {
                    clearTimeout(timeout)
                    resolve(false)
                })
        })
    }

    function isMobileDevice(): boolean {
        // Check if device is mobile based on width and touch capability
        const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0
        const isSmallScreen = window.innerWidth < 1024
        return hasTouch && isSmallScreen
    }

    // Get banner configuration based on device type
    function getBannerConfig() {
        const isMobile = isMobileDevice()

        if (isMobile) {
            // Mobile banner configuration
            return {
                key: 'ea2d198d29f73625ce04b640ff53feea',
                format: 'iframe',
                height: 50,
                width: 320,
                params: {},
                scriptUrl: 'https://venisonglum.com/ea2d198d29f73625ce04b640ff53feea/invoke.js',
                scriptId: 'adsterra-mobile-banner-script'
            }
        } else {
            // PC banner configuration
            return {
                key: '894a46663353d63dfb207df72ea5037e',
                format: 'iframe',
                height: 600,
                width: 160,
                params: {},
                scriptUrl: 'https://venisonglum.com/894a46663353d63dfb207df72ea5037e/invoke.js',
                scriptId: 'adsterra-pc-banner-script'
            }
        }
    }

    function loadBannerScript() {
        const bannerConfig = getBannerConfig()

        // Create and append banner configuration script
        const bannerConfigScript = document.createElement('script')
        bannerConfigScript.textContent = `
          atOptions = {
            'key' : '${bannerConfig.key}',
            'format' : '${bannerConfig.format}',
            'height' : ${bannerConfig.height},
            'width' : ${bannerConfig.width},
            'params' : {}
          };
        `
        document.body.appendChild(bannerConfigScript)

        // Create and append banner script
        const bannerScript = document.createElement('script')
        bannerScript.id = bannerConfig.scriptId
        bannerScript.src = bannerConfig.scriptUrl
        bannerScript.async = true
        document.body.appendChild(bannerScript)
    }

    // Ad of adsterra
    function loadPrimaryScript() {

        // Adsterra popunder ad script
        const popunderScript = document.createElement('script')
        popunderScript.id = 'adsterra-popunder-script'
        popunderScript.src = primaryScriptUrl
        popunderScript.async = true

        document.body.appendChild(popunderScript)

        // Load banner script based on device type
        // loadBannerScript()

        // Adsterra native ad script
        const nativeScript = document.createElement('script')
        nativeScript.id = 'adsterra-native-script'
        nativeScript.src = 'https://venisonglum.com/d8798c431a8567a69212bdf164a8ddac/invoke.js'
        nativeScript.async = true
        document.body.appendChild(nativeScript)

        // Adsterra social bar ad script
        const socialBarScript = document.createElement('script')
        socialBarScript.id = 'adsterra-socialbar-script'
        socialBarScript.src = 'https://venisonglum.com/3d/2b/c4/3d2bc4281dfc26aae54aafe3273ccadd.js'
        socialBarScript.async = true
        document.body.appendChild(socialBarScript);
    }

    // Ad of ad-maven
    function loadFallbackScript() {

        return

        if (document.getElementById('fallback-ad-script')) return

        // Ad-maven popunder ad script
        const fallbackScript = document.createElement('script')
        fallbackScript.id = 'fallback-ad-script'
        fallbackScript.src = '//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1239564'
        fallbackScript.async = true
        fallbackScript.setAttribute('data-cfasync', 'false')

        document.body.appendChild(fallbackScript)

        // Ad-maven in-page-push ad script 
        // like as social bar
        const inPagePushScript = document.createElement('script')
        inPagePushScript.id = 'inpagepush-ad-script'
        inPagePushScript.src = '//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1239690'
        inPagePushScript.async = true
        inPagePushScript.setAttribute('data-cfasync', 'false')
        document.body.appendChild(inPagePushScript)

        // Ad-maven native ad script
        const nativeAdScript = document.createElement('script')
        nativeAdScript.id = 'native-ad-script'
        nativeAdScript.src = '//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1240207'
        nativeAdScript.async = true
        nativeAdScript.setAttribute('data-cfasync', 'false')
        document.body.appendChild(nativeAdScript)

        // Ad-maven Lightbox banner ad script
        const lightboxAdScript = document.createElement('script')
        lightboxAdScript.id = 'lightbox-ad-script'
        lightboxAdScript.src = '//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1240208'
        lightboxAdScript.async = true
        lightboxAdScript.setAttribute('data-cfasync', 'false')
        document.body.appendChild(lightboxAdScript)


    }

    // Provide AD_NATIVE_CONTAINER_HTML and banner config for use in components
    const isMobile = ref(isMobileDevice())

    return {
        provide: {
            AD_NATIVE_CONTAINER_HTML: isPrimaryAvailable,
            isMobileDevice: isMobile,
            getBannerConfig: () => getBannerConfig()
        }
    }
})
