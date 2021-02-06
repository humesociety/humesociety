/*
Tab functionality.
*/
const tabs = Array.from(document.querySelectorAll('[data-tab]'))
const panes = Array.from(document.querySelectorAll('.tab-pane'))

tabs.forEach((tab) => {
  tab.addEventListener('click', (e) => {
    tabs.forEach((x) => { x.classList.remove('is-active') })
    panes.forEach((x) => { x.classList.remove('is-active') })
    tab.classList.add('is-active')
    document.getElementById(tab.dataset.tab).classList.add('is-active')
  })
})
