/*
Toggle any checkboxes inside an element when that element is clicked.
*/
const links = document.querySelectorAll('[data-action="toggle-review-comments"]')

links.forEach((link) => {
  link.addEventListener('click', (e) => {
    const comments = link.parentElement.parentElement.parentElement.querySelector('.review-comments')
    if (link.innerHTML === 'Show Comments') {
      link.innerHTML = 'Hide Comments'
    } else {
      link.innerHTML = 'Show Comments'
    }
    comments.classList.toggle('is-active')
  })
})
