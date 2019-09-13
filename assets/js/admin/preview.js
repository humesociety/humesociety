/*
Show live preview of HTML content (for pages and emails).
*/
const previews = Array.from(document.querySelectorAll('[data-preview]'))

previews.forEach((preview) => {
  const source = document.getElementById(preview.dataset.preview)
  preview.innerHTML = '<div style="max-width:600px;margin:0 auto;"><div><img src="/logo.png" style="height:auto;max-width:100%;"></div><div style="padding:1em;" data-content></div></div>'
  const content = preview.querySelector('[data-content]')
  content.innerHTML = source.value
  source.addEventListener('keyup', (e) => {
    content.innerHTML = source.value
  })
})
