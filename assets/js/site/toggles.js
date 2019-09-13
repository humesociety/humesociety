/*
Set up elements to toggle the 'is-active' class on click (for the site menu on small screens).
*/
const toggles = Array.from(document.querySelectorAll('[data-toggle]'))
const tabs = Array.from(document.querySelectorAll('[data-tab]'))

const toggle = (e) => {
  e.currentTarget.classList.toggle('is-active')
  document.getElementById(e.currentTarget.dataset.toggle).classList.toggle('is-active')
}

const tab = (e) => {
  activate(e.currentTarget)
  activate(document.getElementById(e.currentTarget.dataset.tab))
}

const activate = (element) => {
  Array.from(element.parentElement.children).forEach((x) => { x.classList.remove('is-active') })
  element.classList.add('is-active')
}

toggles.forEach((x) => { x.addEventListener('click', toggle) })
tabs.forEach((x) => { x.addEventListener('click', tab) })
