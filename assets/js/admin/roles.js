/*
Update user roles based on checkbox selection.
*/
const roles = Array.from(document.querySelectorAll('[data-role]'))

roles.forEach((input) => {
  input.addEventListener('change', (e) => {
    if (input.checked) {
      window.fetch(`/admin/user/account/role/${input.dataset.user}/add/${input.dataset.role}`)
    } else {
      window.fetch(`/admin/user/account/role/${input.dataset.user}/remove/${input.dataset.role}`)
      console.log(e.checked)
    }
  })
})
