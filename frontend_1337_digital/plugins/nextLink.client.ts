export default defineNuxtPlugin(() => {

  const goNextLink = (torrent: any) => {
    const link = `/torrent/${torrent.id}/${torrent.slugged_title}`

    // remove ads → direct navigation
    window.location.href = link
    return;
    // Category 8 → popunder logic (OLD logic kept)
    if (torrent.category_id === 8) {
      const key = 'pop_shown_' + btoa(link)

      if (!sessionStorage.getItem(key)) {
        window.open(
          'https://adclickad.com/get/?spot_id=6105929&cat=25&subid=446018231',
          '_blank',
          'noopener,noreferrer'
        )

        sessionStorage.setItem(key, '1')
        return
      }
    }

    // Normal navigation
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
