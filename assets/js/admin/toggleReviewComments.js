/*
Toggle any checkboxes inside an element when that element is clicked.
*/
const links = document.querySelectorAll('[data-action="toggle-review-comments"]')

links.forEach((link) => {
  link.addEventListener('click', (e) => {
    const review = link.dataset.review
    const comments = document.querySelector(`.review-comments[data-review="${review}"]`)
    if (link.innerHTML === 'Show Comments') {
      link.innerHTML = 'Hide Comments'
    } else {
      link.innerHTML = 'Show Comments'
    }
    comments.classList.toggle('is-active')
  })
})
