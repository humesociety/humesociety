/*
Show live preview of HTML content (from form textarea).
*/
const previews = Array.from(document.querySelectorAll('[data-preview]'))

const prepare = (html, type) => {
  if (type === 'email') {
    html = html.replace(/{{ ?(firstname|lastname|email|username|title|abstract|ordinal|town|country|link) ?}}/g, '<span class="variable">{{ $1 }}</span>')
  }
  return html
}

previews.forEach((preview) => {
  const type = preview.dataset.preview
  const form = preview.dataset.form
  const source = document.querySelector(`form[name="${form}"] textarea`)
  preview.innerHTML = prepare(source.value, type)
  source.addEventListener('keyup', (e) => {
    preview.innerHTML = prepare(source.value, type)
  })
})
