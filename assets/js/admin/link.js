/*
Update links based on dropdown menu selection.
*/
const links = Array.from(document.querySelectorAll('[data-menu]'))

links.forEach((link) => {
  const menu = document.getElementById(link.dataset.menu)
  const href = link.href
  link.href = `${href}/${menu.value}`
  menu.addEventListener('change', (e) => {
    link.href = `${href}/${menu.value}`
  })
})
