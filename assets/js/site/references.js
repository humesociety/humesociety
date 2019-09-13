/*
Parallel reference lookup tool.
*/
import treatise from './references/treatise.js'
import enquiries from './references/enquiries.js'

// grab elements
const treatiseId = document.getElementById('treatise-id')
const treatisePage = document.getElementById('treatise-page')
const enquiriesId = document.getElementById('enquiries-id')
const enquiriesPage = document.getElementById('enquiries-page')

// define inverse lookup function
const pageToId = (lookup, page) => {
  const hits = []
  Object.keys(lookup).forEach((key) => {
    const pages = lookup[key].replace(', ', '-').split('-')
    if (pages.includes(page)) hits.push(key)
  })
  return hits.join('; ') || 'bad page'
}

// bind lookups to inputs
if (treatiseId && treatisePage && enquiriesId && enquiriesPage) {
  treatiseId.addEventListener('keypress', (event) => {
    if (event.keyCode === 13) {
      treatisePage.value = treatiseId.value
        ? (treatise[treatiseId.value] || 'bad reference') : ''
    }
  })
  treatisePage.addEventListener('keypress', (event) => {
    if (event.keyCode === 13) {
      treatiseId.value = treatisePage.value
        ? pageToId(treatise, treatisePage.value) : ''
    }
  })
  enquiriesId.addEventListener('keypress', (event) => {
    if (event.keyCode === 13) {
      enquiriesPage.value = enquiriesId.value
        ? (enquiries[enquiriesId.value] || 'bad reference') : ''
    }
  })
  enquiriesPage.addEventListener('keypress', (event) => {
    if (event.keyCode === 13) {
      enquiriesId.value = enquiriesPage.value
        ? pageToId(enquiries, enquiriesPage.value) : ''
    }
  })
}
