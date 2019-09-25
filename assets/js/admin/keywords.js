/*
Update submission keywords via AJAX.
*/
const keywordSelect = document.getElementById('show-keyword')

const keywords = Array.from(document.querySelectorAll('[data-keywords]'))

const createOption = (value) => {
  const option = document.createElement('option')
  option.value = value
  option.innerHTML = value
  return option
}

keywords.forEach((keyword) => {
  keyword.addEventListener('change', (e) => {
    const submissionId = keyword.dataset.keywords
    const keywords = keyword.value
    const url = `/admin/conference/submission/keywords/${submissionId}/${keywords}`
    window.fetch(url).then((response) => {
      response.json().then((data) => {
        if (data.ok) {
          keyword.parentElement.parentElement.dataset.keyword = keyword.value
        } else {
          window.alert('Failed to update keywords data.')
        }
      })
      window.fetch('/data/conference/keywords').then((response) => {
        response.json().then((keywords) => {
          const frag = document.createDocumentFragment()
          frag.appendChild(createOption('all'))
          keywords.forEach((keyword) => {
            frag.appendChild(createOption(keyword))
          })
          keywordSelect.innerHTML = ''
          keywordSelect.appendChild(frag)
        })
      })
    })
  })
})
