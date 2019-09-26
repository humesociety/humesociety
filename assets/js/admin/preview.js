/*
Show live preview of HTML content (for pages and emails).
*/
const previews = Array.from(document.querySelectorAll('[data-preview]'))

const prepare = html =>
  html.replace(/{{ ?(firstname|lastname|email|username|title|abstract|ordinal|town|country) ?}}/g, '<span class="variable">{{ $1 }}</span>')

previews.forEach((preview) => {
  const source = document.getElementById('email_content') || document.getElementById('email_template_content')
  preview.innerHTML = prepare(source.value)
  source.addEventListener('keyup', (e) => {
    preview.innerHTML = prepare(source.value)
  })
})
