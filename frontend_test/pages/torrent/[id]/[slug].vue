<template>
  <AppLayout>
    <div v-if="isUploading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <!-- Spinner -->
      <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-if="torrent" class="mt-2">
      <!-- Main Content -->
      <!-- Title -->
      <div class="bg-gray-700 px-4 py-2 gap-2 rounded-t flex justify-between">
        <input v-if="isLoggedIn" input type="text" placeholder="Add a title"
          class="w-full px-2 py-1 border border-sky-500 bg-gray-700" v-model="torrent_name" />
        <h1 v-else class="text-lg font-semibold min-w-0 flex-1 break-words">{{ torrent.name }}</h1>
        <div v-if="isLoggedIn" class="flex gap-4">
          <button @click="updateTorrent()"
            class="bg-cyan-700 hover:bg-cyan-500 text-white px-4 py-1 rounded flex items-center justify-center font-semibold">
            Update
          </button>
          <button @click="deleteTorrent(torrent.id)"
            class="bg-orange-600 hover:bg-orange-300 text-white px-4 py-1 rounded flex items-center justify-center font-semibold">
            Delete
          </button>
        </div>
      </div>

      <!-- Torrent Info -->
      <div class="bg-gray-800 rounded-b mb-4">
        <div class="grid lg:grid-cols-3 gap-2 p-4 text-sm">
          <div class="space-y-2">
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Category</span>
              <span>: {{ torrent.category_name }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Type</span>
              <span>: {{ torrent.subcategory_name }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Language</span>
              <span>: {{ torrent.language }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Total Size</span>
              <span>: {{ torrent.size }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Uploaded By</span>
              <a :href="`/user/${torrent.uploaded_by}/1`"><span class="text-green-400">: {{ torrent.uploaded_by
              }}</span></a>
            </div>
          </div>
          <div class="space-y-2">
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Downloads</span>
              <span v-if="isLoggedIn">: <input type="text" v-model="torrent_download_count"
                  class="w-32 px-2 text-gray-700 border-gray-300 rounded-md" /></span>
              <span v-else class="text-green-400">: {{ torrent.downloads }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Last checked</span>
              <span>: {{ torrent.uploaded_ago }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Date uploaded</span>
              <span>: {{ torrent.uploaded_ago }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Seeders</span>
              <span v-if="isLoggedIn">: <input type="text" v-model="torrent_seeders"
                  class="w-32 px-2 text-gray-700 border-gray-300 rounded-md" /></span>
              <span v-else class="text-green-400">: {{ torrent.seeders }}</span>
            </div>
            <div class="grid grid-cols-2">
              <span class="text-gray-400">Leechers</span>
              <span v-if="isLoggedIn">: <input type="text" v-model="torrent_leechers"
                  class="w-32 px-2 text-gray-700 border-gray-300 rounded-md" /></span>
              <span v-else class="text-red-400">: {{ torrent.leechers }}</span>
            </div>
          </div>
          <!-- Download Buttons -->
          <div class="space-y-2">
            <button @click="downloadNextLink(torrent.id, torrent.magnet_link)" rel="noreferrer"
              class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded flex items-center justify-center space-x-2 font-semibold">
              <i class="flaticon-torrent-download"></i>
              <span>MAGNET DOWNLOAD</span>
            </button>
            <button @click="downloadTorrent"
              class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center space-x-2 font-semibold">
              <i class="flaticon-two-down-arrows"></i>
              <span>TORRENT DOWNLOAD</span>
            </button>
            <transition name="slide">
              <div v-if="showDropdown" class="dropdown">
                <a :href="`http://itorrents.org/torrent/${torrent.infohash}.torrent`" target="_blank">ITORRENTS
                  MIRROR</a>
                <a :href="`http://torrage.info/torrent.php?h=${torrent.infohash}`" target="_blank">TRRAGE
                  MIRROR</a>
                <a :href="`http://btcache.me/torrent/${torrent.infohash}`" target="_blank">BTCACHE MIRROR</a>
                <a :href="`${torrent.magnet_link}`">NONE WORKING? USERMAGNET</a>
              </div>

            </transition>
            <a v-if="torrent.media_info" :href="torrent.media_info" target="_blank"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded flex items-center justify-center space-x-2 font-semibold">
              <i class="flaticon-arw-down"></i>
              <span>PLAY NOW (STREAM)</span>
            </a>

            <a href="https://venisonglum.com/kan7dg6hp?key=d6c6d8377e0c946826c106f0554fd294" target="_blank"
              rel="noreferrer"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded flex items-center justify-center space-x-2 font-semibold">
              <i class="flaticon-download"></i>
              <span>DIRECT DOWNLOAD</span>
            </a>
          </div>
        </div>
        <!-- poster box & detail -->
        <div v-if="isLoggedIn">
          <div class="grid grid-cols-3 lg:grid-cols-5 md:grid-cols-4 p-2 gap-2">
            <div class="col-1">
              <div v-if="torrent.cover_image && coverImage" class="relative">
                <button @click="removeImage"
                  class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-2 hover:bg-red-700">
                  ✕
                </button>
                <img :src="torrent.cover_image" :alt="torrent.title" class="h-56 w-auto mx-auto mb-2 rounded">
              </div>
              <div v-else class="bg-gray-700 w-full h-56 content-center text-center">
                <!-- Hidden file input -->
                <input v-if="!previewUrl" ref="fileInput" type="file" class="hidden" @change="handleFileChange" />
                <!-- Image preview -->
                <span v-if="!previewUrl" @click="openFilePicker"
                  class="text-gray-400 select-none  cursor-pointer py-12">Add a poster image</span>
                <div v-if="previewUrl" class="relative inline-block">
                  <button @click="removePreviewImage"
                    class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-2 hover:bg-red-700">
                    ✕
                  </button>
                  <img :src="previewUrl" alt="Preview" class="h-56 w-auto mx-auto mb-2 rounded" />
                </div>
              </div>
            </div>

            <div class="col-span-3 lg:col-span-4 md:grid-cols-3 space-y-2">
              <div>
                <span class="text-gray-400">* the title for SEO ( please write within 65 characters. now {{ titleLength
                }})</span>
                <input @input="onTitleInput" type="text" placeholder="Add a title" class="w-full px-2 py-1 bg-gray-700"
                  v-model="torrent_title" />
              </div>
              <div>
                <span class="text-gray-400">* the poster name for SEO</span>
                <input type="text" placeholder="Add a poster description" class="w-full px-2 bg-gray-700"
                  v-model="torrent_poster_alt" />
              </div>
              <div>
                <span class="text-gray-400">* the genre for SEO (keep `span` tag)</span>
                <input type="text" placeholder="Add a genre" class="w-full px-2 bg-gray-700" v-model="torrent_genre" />
              </div>
              <div>
                <span class="text-gray-400">* the description for SEO ( please write within 150 characters. now {{
                  descriptionLength }} )</span>
                <textarea @input="onDescriptionInput" class="w-full px-3 py-2 bg-gray-700" rows="5"
                  v-model="torrent_description">
                    </textarea>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="torrent.cover_image" class="grid lg:grid-cols-5 md:grid-cols-4 pt-2 border-t border-gray-700">
          <div class="col-1" hidden>
            <img v-if="torrent.cover_image != null" :src="torrent.cover_image"
              :alt="torrent.poster_alt ? torrent.poster_alt : torrent.title ? torrent.title : torrent.name"
              class="h-56 w-auto mx-auto mb-2 rounded">
          </div>
          <div class="lg:col-span-4 md:grid-cols-3 px-2">
            <h3 class="text-2xl text-red-400 break-all md:break-normal">{{ torrent.title ? torrent.title : torrent.name
            }}</h3>
            <div class="torrent-slogan text-orange-700">
              <p v-if="torrent.slogan" v-html="torrent.slogan"></p>
            </div>
            <p class="text-gray-400 break-all md:break-normal" v-if="torrent.description">{{ torrent.description }}</p>
          </div>
        </div>

      </div>

      <!-- Hash -->
      <div class="text-center text-xs text-black mb-4">
        <span class="font-semibold">INFO HASH:</span> {{ torrent.infohash }}
      </div>

      <!-- Tabs -->
      <div class="bg-gray-800 rounded">
        <div class="flex border-b border-gray-700">
          <button v-for="tab in tabs" :key="tab" @click="activeTab = tab" :class="[
            'px-4 py-2 text-sm font-medium',
            activeTab === tab
              ? 'bg-gray-700 text-white'
              : 'text-gray-400 hover:text-white'
          ]">
            {{ tab }}
          </button>
        </div>
        <div class="p-2">
          <div v-if="activeTab === 'DESCRIPTION'">
            <tiptap-editor v-if="isLoggedIn" v-model="torrent_description_html" />
            <div v-if="!isLoggedIn && torrent?.content" class="relative">
              <div v-html="contentWithAd" ref="contentContainer"
                class="text-gray-300 prose break-words custom-html image-loading-container"></div>
            </div>
          </div>
          <div v-else-if="activeTab === 'FILES'">
            <div v-if="torrent?.files" v-html="torrent.files" class="text-gray-300 prose break-words custom-html"></div>
          </div>

          <div v-else-if="activeTab === 'COMMENTS'">
            <div v-if="torrent?.comments" v-html="torrent.comments" class="text-gray-300 prose break-words custom-html">
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>

import AppLayout from '~/layouts/AppLayout.vue';
import { useRoute } from 'vue-router'
import TiptapEditor from '~/components/TiptapEditor.vue'

const { $torrentApi, $AD_NATIVE_CONTAINER_HTML } = useNuxtApp()
const { $downloadNextLink } = useNuxtApp()
const isLoggedIn = ref(false)
// get slug from URL
const route = useRoute()
const config = useRuntimeConfig()
// fetch blog data from Laravel API (SSR)
const { data: torrent, error } = await useFetch(`${config.public.apiBase}/torrent/get/${route.params.id}/${route.params.slug}`)
if (error.value?.statusCode === 404) {
  // handle 404 
  navigateTo('/')
}
if (route.params?.slug !== torrent.value?.slugged_title) {
  // 301 redirect to canonical slug
  throw navigateTo(`/torrent/${route.params.id}/${torrent.value.slugged_title}`, { redirectCode: 301 })
}
const description = ref('')
const torrent_name = ref('')
const torrent_title = ref('')
const torrent_poster_alt = ref('')
const torrent_genre = ref('')
const torrent_description = ref('')
const torrent_cover_image = ref('')
const torrent_download_count = ref(0)
const torrent_seeders = ref(0)
const torrent_leechers = ref(0)
const torrent_description_html = ref('')

const titleLength = ref(0)
const descriptionLength = ref(0)


// Inject SEO tags dynamically
if (torrent.value) {

  // for edit variable
  torrent_name.value = torrent.value.name
  var title = torrent.value.title ? torrent.value.title : torrent.value.name
  const MAX = 56;
  const SUFFIX = ' '; // or '...' or '>'
  if (title.length > MAX) {
    title = title.substring(0, MAX - SUFFIX.length).trim() + SUFFIX;
  } else {
    title = torrent.value.name.substring(0, MAX - SUFFIX.length).trim() + SUFFIX;
  }


  torrent_title.value = title
  torrent_genre.value = torrent.value.slogan

  torrent_description.value = torrent.value.description
  torrent_download_count.value = torrent.value.downloads
  torrent_seeders.value = torrent.value.seeders
  torrent_leechers.value = torrent.value.leechers
  torrent_cover_image.value = torrent.value.cover_image
  torrent_poster_alt.value = torrent.value.poster_alt
  // Note: Image tags are already fixed in the backend (getDetailData)
  torrent_description_html.value = torrent.value.content
  // //
  titleLength.value = torrent_title.value.length
  descriptionLength.value = torrent.value.description ? torrent.value.description.length : 0

  // // // for user view 
  let desc = torrent.value.content.replace(/<img[^>]*>/gi, '').replace(/<[^>]*>/g, '')
  if (desc.length > 150) {
    desc = desc.substring(0, 145).trim() + '…'; // add ellipsis
  }
  description.value = torrent.value.description ? torrent.value.description : desc

  let meta_desc = description.value;
  if (meta_desc.length > 150) {
    meta_desc = meta_desc.substring(0, 145).trim() + '…'; // add ellipsis
  }

  useHead({
    title: torrent_title,
    meta: [
      { name: 'description', content: meta_desc },
      // 
      { property: 'og:title', content: torrent_title },
      { property: 'og:description', content: meta_desc },
      { property: 'og:image', content: torrent_cover_image },
      { property: 'og:type', content: 'article' }
    ]
  })
}

const isUploading = ref(false)
const fileInput = ref(null)
const previewUrl = ref(null)
const selectedFile = ref(null)
const coverImage = ref(true)

const openFilePicker = () => {
  fileInput.value.click()
}

const removeImage = () => {
  coverImage.value = false
  torrent_cover_image.value = null
}

const removePreviewImage = () => {
  selectedFile.value = null
  previewUrl.value = null
  fileInput.value.value = null // reset input so same file can be reselected
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const contentWithAd = computed(() => {
  const content = torrent.value?.content
  if (!content) return ''
  
  // Only add ad container if primary script is available
  const adContainer = $AD_NATIVE_CONTAINER_HTML?.value || ''
  if (!adContainer) return content
  
  const imgRegex = /<img[^>]*>/gi
  let match
  let count = 0
  let secondImgEnd = -1
  while ((match = imgRegex.exec(content)) !== null) {
    count++
    if (count === 2) {
      secondImgEnd = match.index + match[0].length
      break
    }
  }
  if (secondImgEnd !== -1) {
    return content.slice(0, secondImgEnd) + adContainer + content.slice(secondImgEnd)
  }
  return content + adContainer
})

const activeTab = ref('DESCRIPTION')
const showDropdown = ref(false)
const tabs = ref(['DESCRIPTION', 'FILES', 'COMMENTS']);
const isDesktop = ref(false);
const contentContainer = ref(null)

const downloadTorrent = () => {
  showDropdown.value = !showDropdown.value;
}

const router = useRouter()
const deleteTorrent = async (id) => {
  if (window.confirm('Are you sure you want to delete this torrent?')) {

    await $torrentApi.deleteTorrent(id);
    //
    goBackAndReload()
  }
}
const handleClick = (torrent) => {
  if (torrent.category_id === 5) {
    window.open(
      "https://yhbcii.com/af?o=f90d17d60f27916963522bebb816c085:6542732bc483ac02090ffed9b08e6ba8",
      "_blank"
    )
  }
  // else if (typeof window !== 'undefined' && typeof window.poClick === 'function') {
  //   window.poClick('code=xu0ap9')
  // }
}

////// update ///////


const updateTorrent = async () => {
  if (window.confirm('Are you sure you want to update this torrent?')) {


    try {
      isUploading.value = true
      // upload image
      if (selectedFile.value) {
        const formData = new FormData()
        formData.append('image', selectedFile.value)
        const apiUrl = config.public.apiImageUrl
        const apiKey = config.public.apiImageKey
        const res = await $fetch(apiUrl, {
          method: "POST",
          query: {
            key: apiKey
          },
          body: formData
        })
        torrent_cover_image.value = res.data.url
      }

      let data = {
        id: torrent.value.id,
        cover_image: torrent_cover_image.value,
        name: torrent_name.value,
        title: torrent_title.value,
        genre: torrent_genre.value,
        poster_alt: torrent_poster_alt.value,
        description: torrent_description.value,
        seeders: torrent_seeders.value,
        download_count: torrent_download_count.value,
        leechers: torrent_leechers.value,
        description_html: torrent_description_html.value,
        // 
      }

      await $torrentApi.saveTorrent(data);

      goBackAndReload()

    } catch (error) {
      console.error('Image upload failed', error)
    } finally {
      isUploading.value = false
    }

  }
}

const goBackAndReload = () => {
  if (document.referrer) {
    // Go back to the previous page as a full server request
    window.location.href = document.referrer
  } else {
    // fallback if no referrer
    window.location.href = '/'
  }
}

function onTitleInput(event) {
  titleLength.value = event.target.value.length
}

function onDescriptionInput(event) {
  descriptionLength.value = event.target.value.length
}
const downloadNextLink = (torrent_id, magnet_link) => {
  $downloadNextLink(torrent_id, magnet_link)
}
/////////////////////

// Track image loading in content with individual progress bars
// Note: Backend already wraps images in progress bar containers
const trackImageLoading = () => {
  if (!contentContainer.value || isLoggedIn.value) return
  
  nextTick(() => {
    const container = contentContainer.value
    if (!container) return
    
    // Find all images that are already wrapped by backend (image-progress-wrapper)
    const wrappers = container.querySelectorAll('.image-progress-wrapper')
    if (wrappers.length === 0) return
    
    wrappers.forEach((wrapper) => {
      const img = wrapper.querySelector('img')
      const progressBar = wrapper.querySelector('.image-progress-bar')
      const progressFill = wrapper.querySelector('.image-progress-fill')
      
      if (!img || !progressBar || !progressFill) return
      
      // Skip if already processed
      if (img.classList.contains('progress-tracked')) return
      img.classList.add('progress-tracked')
      
      // Track loading progress
      const updateProgress = (progress) => {
        progressFill.style.width = `${progress}%`
      }
      
      const handleLoad = () => {
        updateProgress(100)
        // Hide progress bar after a short delay
        setTimeout(() => {
          progressBar.style.opacity = '0'
          setTimeout(() => {
            if (progressBar.parentNode) {
              progressBar.remove()
            }
          }, 300)
        }, 200)
      }
      
      const handleError = () => {
        updateProgress(100)
        // Hide progress bar on error
        setTimeout(() => {
          progressBar.style.opacity = '0'
          setTimeout(() => {
            if (progressBar.parentNode) {
              progressBar.remove()
            }
          }, 300)
        }, 200)
      }
      
      // If image is already loaded
      if (img.complete && img.naturalHeight !== 0) {
        handleLoad()
      } else {
        // Simulate progress for better UX (since we can't track actual download progress)
        let simulatedProgress = 0
        const progressInterval = setInterval(() => {
          if (simulatedProgress < 90) {
            simulatedProgress += Math.random() * 10 + 5
            updateProgress(Math.min(simulatedProgress, 90))
          }
        }, 150)
        
        // Track when image loads
        const loadHandler = () => {
          clearInterval(progressInterval)
          handleLoad()
          img.removeEventListener('load', loadHandler)
          img.removeEventListener('error', errorHandler)
        }
        
        // Track if image fails to load
        const errorHandler = () => {
          clearInterval(progressInterval)
          handleError()
          img.removeEventListener('load', loadHandler)
          img.removeEventListener('error', errorHandler)
        }
        
        img.addEventListener('load', loadHandler)
        img.addEventListener('error', errorHandler)
      }
    })
  })
}

// Watch for tab changes to track images when DESCRIPTION tab is active
watch(activeTab, (newTab) => {
  if (newTab === 'DESCRIPTION' && !isLoggedIn.value) {
    trackImageLoading()
  }
})

// Watch for content changes to re-track images
watch(contentWithAd, () => {
  if (activeTab.value === 'DESCRIPTION' && !isLoggedIn.value) {
    trackImageLoading()
  }
})

onMounted(() => {
  isDesktop.value = window.innerWidth >= 1024 && !('ontouchstart' in window)
  
  // Track images after content is rendered
  if (activeTab.value === 'DESCRIPTION' && !isLoggedIn.value) {
    trackImageLoading()
  }
});

</script>
<style>
.torrent-slogan p {
  margin-top: 3px;
}

.torrent-slogan p span {
  margin-right: 12px;
}

.btn {
  display: block;
  width: 250px;
  padding: 10px;
  color: white;
  font-weight: bold;
  text-align: left;
  border: none;
  cursor: pointer;
  margin-bottom: 5px;
}

.btn .icon {
  margin-right: 8px;
}

.torrent {
  background-color: #a63b20;
}

/* Dropdown styles */
.dropdown {
  background: #8b1818;
  display: flex;
  flex-direction: column;
  padding: 5px;
  margin-bottom: 5px;
}

.dropdown a {
  color: white;
  text-decoration: none;
  padding: 5px;
  font-weight: bold;
  padding-top: 6px;

}

.dropdown a:hover {
  background: #313030;
}

/* Slide transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-height: 200px;
  /* adjust for more links */
  opacity: 1;
}

/* Image progress bar styles */
.image-progress-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
  max-width: 100%;
}

.image-progress-wrapper img {
  display: block;
  width: 100%;
  height: auto;
}

.image-progress-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background-color: rgba(0, 0, 0, 0.3);
  overflow: hidden;
  transition: opacity 0.3s ease;
  z-index: 10;
}

.image-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #f97316, #ef4444, #dc2626);
  width: 0%;
  transition: width 0.2s ease;
  box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
}

/* Ensure images in content are properly styled */
.image-loading-container img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 1rem 0;
}
</style>