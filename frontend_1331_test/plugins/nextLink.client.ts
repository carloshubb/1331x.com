export default defineNuxtPlugin(() => {

  const goNextLink = (torrent: any) => {
    const link = `/torrent/${torrent.id}/${torrent.slugged_title}`

    // Open the background page in a new tab
    // const popup = window.open(
    //   link,
    //   '_blank',     
    // )
  
    // Navigate the main page
    window.location.href = link
  }




  /**
   * ---------------------------
   * Polar Ads helper
   * ---------------------------
   */
  const triggerPolarAd = (code: string) => {
    if (typeof window !== 'undefined' && typeof (window as any).poClick === 'function') {
      ; (window as any).poClick(`code=${code}`)
    }
  }

  const downloadNextLink = (torrent_id: bigint, magnet_link: string) => {
    const key = 'download_shown_' + btoa(torrent_id.toString())

    if (!sessionStorage.getItem(key)) {
      triggerPolarAd('p51lxd')
      sessionStorage.setItem(key, '1')
      return
    }
    // Open magnet link
    window.open(
      magnet_link,
      '_blank',
      'noopener,noreferrer'
    )
  }

  return {
    provide: {
      goNextLink,
      downloadNextLink
    }
  }
})
