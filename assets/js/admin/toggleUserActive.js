/*
Toggle any checkboxes inside an element when that element is clicked.
*/
const inputs = document.querySelectorAll('[data-action="toggle-user-active"]')

inputs.forEach((input) => {
  input.addEventListener('change', (e) => {
    const user = input.dataset.user
    window.fetch(`/conference-manager/user/toggle/${user}`).then((response) => {
      response.json().then((data) => {
        if (data.success) input.parentElement.parentElement.classList.toggle('is-active')
      })
    })
  })
})
