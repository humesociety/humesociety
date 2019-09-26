/*
Show live preview of HTML content (from form textarea).
*/
const previews = Array.from(document.querySelectorAll('[data-preview]'))

const prepare = (html, type) => {
  if (type === 'email') {
    html = html.replace(/{{ ?(firstname|lastname|email|username|title|abstract|ordinal|town|country) ?}}/g, '<span class="variable">{{ $1 }}</span>')
  }
  return html
}

previews.forEach((preview) => {
  const source = document.querySelector('form textarea')
  const type = source.dataset.preview
  preview.innerHTML = prepare(source.value, type)
  source.addEventListener('keyup', (e) => {
    preview.innerHTML = prepare(source.value)
  })
})
